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

//require_once '3rdparty/rcube_imap.php';
require_once '3rdparty/rcube_imap_generic.php';

class App
{
	/**
	 * Loads all user's accounts, connects to each server and queries all folders
	 *
	 * @static
	 * @return array
	 */
	public static function getFolders($user_id)
	{
		$response = array();

		// get all account configured by the user
		$accounts = App::getAccounts($user_id);

		// iterate ...
		foreach ($accounts as $account) {
			$folders_out = array();

			// open the imap connection
			$conn = App::getImapConnection($account);

			// if successfull -> get all folders of that account
			if ($conn->errornum == \rcube_imap_generic::ERROR_OK) {
				$mboxes = $conn->listMailboxes('', '*');

				foreach ($mboxes as $folder) {
					$status = $conn->status($folder);
					$folders_out[] = array('id' => $folder, 'name' => end(explode('.', $folder)), 'unseen' => $status['UNSEEN'], 'total' => $status['MESSAGES']);
				}


				usort($folders_out, function ($a, $b)
				{
					return strcmp($a['id'], $b['id']);
				});
			}

			$response[] = array('id' => $account['id'], 'name' => $account['name'], 'folders' => $folders_out, 'error' => $conn->error);

			// close the connection
			$conn->closeConnection();
		}

		return $response;
	}

	/**
	 * @static
	 * @param $account_id
	 * @param $folder_id
	 * @param int $from
	 * @param int $count
	 * @return array
	 */
	public static function getMessages($user_id, $account_id, $folder_id, $from = 0, $count = 20)
	{
		// get the account
		$account = App::getAccount($user_id, $account_id);
		if (!$account) {
			#TODO: i18n
			return array('error' => 'unknown account');
		}

		// connect to the imal server
		$conn = App::getImapConnection($account);

		$messages = array();
		if ($conn->errornum == \rcube_imap_generic::ERROR_OK) {

			$total = $conn->countMessages($folder_id);

			if (($from + $count) > $total) {
				$count = $total - $from;
			}

			$headers = $conn->fetchHeaders($folder_id, ($from + 1) . ':' . ($from + $count));
			foreach ($headers as $header) {
				//				$flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);

				$flags = array();
				$messages[] = array('id' => $header->id, 'from' => $header->from, 'to' => $header->to, 'subject' => $header->subject, 'date' => $header->timestamp, 'size' => $header->size, 'flags' => $flags);
			}
		}

		return array('account_id' => $account_id, 'folder_id' => $folder_id, 'messages' => $messages, 'error' => $conn->error);
	}

	/**
	 * @static
	 * @param $account_id
	 * @param $folder_id
	 * @param $message_id
	 * @return array
	 */
	public static function getMessage($user_id, $account_id, $folder_id, $message_id)
	{
		// get the account
		$account = App::getAccount($user_id, $account_id);
		if (!$account) {
			#TODO: i18n
			return array('error' => 'unknown account');
		}

		// connect to the imap server
		$conn = App::getImapConnection($account);

		$message = array();
		if ($conn->errornum == \rcube_imap_generic::ERROR_OK) {
			$m= new Message($conn, $folder_id, $message_id);
			$message= $m->as_array();
		}

		return array('error' => $conn->error, 'message' => $message);
	}

	private static function getImapConnection($account)
	{
		//
		// TODO: cash connections for / within accounts???
		//
		$host = $account['host'];
		$user = $account['user'];
		$password = $account['password'];
		$port = $account['port'];
		$ssl_mode = $account['ssl_mode'];

		// connect to
		$conn = new \rcube_imap_generic();
		$conn->connect($host, $user, $password, array('port' => $port, 'ssl_mode' => $ssl_mode, 'timeout' => 30));

		return $conn;
	}

	private static function getAccounts($user_id)
	{
		$account_ids = \OCP\Config::getUserValue( $user_id, 'mail', 'accounts', '' );
		$account_ids = explode(',', $account_ids );

		$accounts = array();
		foreach( $account_ids as $id ){
			$account_string = 'account['.$id.']';
			
			$accounts[$id] = array(
				'id' => $id,
				'name' => \OCP\Config::getUserValue( $user_id, 'mail', $account_string.'[name]' ),
				'host' => \OCP\Config::getUserValue( $user_id, 'mail', $account_string.'[host]' ),
				'port' => \OCP\Config::getUserValue( $user_id, 'mail', $account_string.'[port]' ),
				'user' => \OCP\Config::getUserValue( $user_id, 'mail', $account_string.'[user]' ),
				'password' => base64_decode( \OCP\Config::getUserValue( $user_id, 'mail', $account_string.'[password]' )),
				'ssl_mode' => \OCP\Config::getUserValue( $user_id, 'mail', $account_string.'[ssl_mode]' ));
		}
		
		return $accounts;
	}

	private static function getAccount($user_id, $account_id)
	{
		$accounts = App::getAccounts($user_id);
		
		if( isset( $accounts[$account_id] )){
			return $accounts[$account_id];
		}

		return false;
	}
}
