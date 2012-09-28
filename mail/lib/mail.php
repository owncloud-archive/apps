<?php
/**
 * ownCloud - Mail app
 *
 * @author Thomas Müller
 * @copyright 2012 Thomas Müller thomas.mueller@tmit.eu
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA_Mail;

require_once 'Horde/Translation/Handler.php';
require_once 'Horde/Translation/Handler/Gettext.php';
require_once 'Horde/Translation.php';

require_once 'Horde/Array.php';
require_once 'Horde/Exception.php';
require_once 'Horde/Exception/Wrapped.php';

require_once 'Horde/Imap/Client/Auth/DigestMD5.php';
require_once 'Horde/Imap/Client/Base.php';
require_once 'Horde/Imap/Client/Cache.php';
require_once 'Horde/Imap/Client/Data/AclCommon.php';
require_once 'Horde/Imap/Client/Data/Acl.php';
require_once 'Horde/Imap/Client/Data/AclNegative.php';
require_once 'Horde/Imap/Client/Data/AclRights.php';
require_once 'Horde/Imap/Client/Data/Envelope.php';
require_once 'Horde/Imap/Client/Data/Fetch.php';
require_once 'Horde/Imap/Client/Data/Thread.php';
require_once 'Horde/Imap/Client/DateTime.php';
require_once 'Horde/Imap/Client/Exception.php';
require_once 'Horde/Imap/Client/Exception/NoSupportExtension.php';
require_once 'Horde/Imap/Client/Fetch/Query.php';
require_once 'Horde/Imap/Client/Ids.php';
require_once 'Horde/Imap/Client/Mailbox.php';
require_once 'Horde/Imap/Client/Search/Query.php';
require_once 'Horde/Imap/Client/Socket.php';
require_once 'Horde/Imap/Client/Sort.php';
require_once 'Horde/Imap/Client/Translation.php';
require_once 'Horde/Imap/Client/Utf7imap.php';
require_once 'Horde/Imap/Client/Utils.php';
require_once 'Horde/Imap/Client.php';

require_once 'Horde/Util.php';
require_once 'Horde/String.php';
require_once 'Horde/Mime.php';
require_once 'Horde/Mime/Headers.php';
require_once 'Horde/Mail/Rfc822.php';


class App
{
	/**
	 * Loads all user's accounts, connects to each server and queries all folders
	 *
	 * @static
	 * @param $user_id
	 * @return array
	 */
	public static function getFolders($user_id) {
		$response = array();

		// get all account configured by the user
		$accounts = App::getAccounts($user_id);

		// iterate ...
		foreach ($accounts as $account) {
			$folders_out = array();

			try {
				// open the imap connection
				$conn = App::getImapConnection($account);

				// if successful -> get all folders of that account
				$mboxes = $conn->listMailboxes('*');

				foreach ($mboxes as $folder) {

					$status = $conn->status($folder['mailbox']);

					$folders_out[] = array('id'     => $folder['mailbox'], 'name' => $folder['mailbox'],
					                       'unseen' => $status['unseen'], 'total' => $status['messages']);
				}

				usort($folders_out, function ($a, $b) {
					return strcmp($a['id'], $b['id']);
				});

				$response[] = array('id' => $account['id'], 'name' => $account['name'], 'folders' => $folders_out);

				// close the connection
				$conn->close();
			} catch (\Horde_Imap_Client_Exception $e) {
				$response[] = array('id' => $account['id'], 'name' => $account['name'], 'error' => $e->getMessage());
			}
		}

		return $response;
	}

	/**
	 * @static
	 * @param $user_id
	 * @param $account_id
	 * @param $folder_id
	 * @param int $from
	 * @param int $count
	 * @return array
	 */
	public static function getMessages($user_id, $account_id, $folder_id, $from = 0, $count = 20) {
		// get the account
		$account = App::getAccount($user_id, $account_id);
		if (!$account) {
			#TODO: i18n
			return array('error' => 'unknown account');
		}

		try {
			// connect to the imap server
			$conn = App::getImapConnection($account);

			$messages = array();

//			$mb = new \Horde_Imap_Client_Mailbox($folder_id);
			$status = $conn->status($folder_id, \Horde_Imap_Client::STATUS_MESSAGES);
			$total = $status['messages'];

			if (($from + $count) > $total) {
				$count = $total - $from;
			}

			$headers = array();

			$fetch_query = new \Horde_Imap_Client_Fetch_Query();
			$fetch_query->envelope();
			$fetch_query->flags();
			$fetch_query->seq();
			$fetch_query->size();
			$fetch_query->uid();
			$fetch_query->imapDate();

			$headers = array_merge($headers, array(
				'importance',
				'list-post',
				'x-priority'
			));
			$headers[] = 'content-type';

			$fetch_query->headers('imp', $headers, array(
				'cache' => true,
				'peek'  => true
			));

			$opt = array('ids' => ($from + 1) . ':' . ($from + 1 + $count));
			// $list is an array of Horde_Imap_Client_Data_Fetch objects.
			$headers = $conn->fetch($folder_id, $fetch_query);

			foreach ($headers as $header) {
				$flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);
//					\Horde_Imap_Client_Data_Fetch::HEADER_PARSE

				$f = $header->getFlags();
				$date = $header->getImapDate()->format('U');
				$id = $header->getUid();
				$e = $header->getEnvelope();
				$flags = array();
				$to = $e->to_decoded[0];
				$to = $to['personal']; //."<".$to['mailbox']."@".$to['host'].">";
				$from = $e->from_decoded[0];
				$from = $from['personal']; //."<".$from['mailbox']."@".$from['host'].">";
				$messages[] = array('id'   => $id, 'from' => $from, 'to' => $to, 'subject' => $e->subject_decoded,
				                    'date' => $date, 'size' => $header->getSize(), 'flags' => $flags);
			}

			return array('account_id' => $account_id, 'folder_id' => $folder_id, 'messages' => $messages);
		} catch (\Horde_Imap_Client_Exception $e) {
			return array('error' => $e->getMessage());
		}
	}

	/**
	 * @static
	 * @param $user_id
	 * @param $account_id
	 * @param $folder_id
	 * @param $message_id
	 * @return array
	 */
	public static function getMessage($user_id, $account_id, $folder_id, $message_id) {
		// get the account
		$account = App::getAccount($user_id, $account_id);
		if (!$account) {
			#TODO: i18n
			return array('error' => 'unknown account');
		}

		try {

			// connect to the imap server
			$conn = App::getImapConnection($account);

			$message = array();
			$m = new Message($conn, $folder_id, $message_id);
			$message = $m->as_array();

			return array('error' => $conn->error, 'message' => $message);
		} catch (\Horde_Imap_Client_Exception $e) {
			return array('error' => $e->getMessage());
		}
	}

	private static function getImapConnection($account) {
		//
		// TODO: cash connections for / within accounts???
		//
		$host = $account['host'];
		$user = $account['user'];
		$password = $account['password'];
		$port = $account['port'];
		$ssl_mode = $account['ssl_mode'];

		$client = new \Horde_Imap_Client_Socket(array(
			'username' => $user, 'password' => $password, 'hostspec' => $host, 'port' => $port, 'secure' => $ssl_mode));
		$client->login();
		return $client;
	}

	private static function getAccounts($user_id) {
		$account_ids = \OCP\Config::getUserValue($user_id, 'mail', 'accounts', '');
		if ($account_ids == "") {
			return array();
		}

		$account_ids = explode(',', $account_ids);

		$accounts = array();
		foreach ($account_ids as $id) {
			$account_string = 'account[' . $id . ']';

			$accounts[$id] = array(
				'id'       => $id,
				'name'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[name]'),
				'host'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[host]'),
				'port'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[port]'),
				'user'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[user]'),
				'password' => base64_decode(\OCP\Config::getUserValue($user_id, 'mail', $account_string . '[password]')),
				'ssl_mode' => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[ssl_mode]'));
		}

		return $accounts;
	}

	public static function addAccount($user_id, $host, $port, $user, $password, $ssl_mode) {
		$id = time();
		$account_string = 'account[' . $id . ']';
		\OCP\Config::setUserValue($user_id, 'mail', $account_string . '[name]', $user);
		\OCP\Config::setUserValue($user_id, 'mail', $account_string . '[host]', $host);
		\OCP\Config::setUserValue($user_id, 'mail', $account_string . '[port]', $port);
		\OCP\Config::setUserValue($user_id, 'mail', $account_string . '[user]', $user);
		\OCP\Config::setUserValue($user_id, 'mail', $account_string . '[password]', base64_encode($password));
		\OCP\Config::setUserValue($user_id, 'mail', $account_string . '[ssl_mode]', $ssl_mode);

		$account_ids = \OCP\Config::getUserValue($user_id, 'mail', 'accounts', '');
		$account_ids = explode(',', $account_ids);
		$account_ids[] = $id;
		$account_ids = implode(",", $account_ids);

		\OCP\Config::setUserValue($user_id, 'mail', 'accounts', $account_ids);

		return $id;
	}


	private static function getAccount($user_id, $account_id) {
		$accounts = App::getAccounts($user_id);

		if (isset($accounts[$account_id])) {
			return $accounts[$account_id];
		}

		return false;
	}
}
