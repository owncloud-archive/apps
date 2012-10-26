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

namespace OCA\Mail;

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
			try {
				$response[] = $account->getListArray();
			} catch (\Horde_Imap_Client_Exception $e) {
				$response[] = array('id' => $account->getId(), 'name' => $account->getName(), 'error' => $e->getMessage());
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
			//@TODO: i18n
			return array('error' => 'unknown account');
		}

		try {
			$mailbox = $account->getMailbox($folder_id);
			$messages = $mailbox->getMessages($from, $count);

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
			//@TODO: i18n
			return array('error' => 'unknown account');
		}

		try {
			$mailbox = $account->getMailbox($folder_id);
			$m = $mailbox->getMessage($message_id);
			$message = $m->as_array();

			return array('error' => $conn->error, 'message' => $message);
		} catch (\Horde_Imap_Client_Exception $e) {
			return array('error' => $e->getMessage());
		}
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

			$accounts[$id] = new Account(array(
				'id'       => $id,
				'name'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[name]'),
				'host'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[host]'),
				'port'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[port]'),
				'user'     => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[user]'),
				'password' => base64_decode(\OCP\Config::getUserValue($user_id, 'mail', $account_string . '[password]')),
				'ssl_mode' => \OCP\Config::getUserValue($user_id, 'mail', $account_string . '[ssl_mode]')
			));
		}

		return $accounts;
	}

	private static function getAccount($user_id, $account_id) {
		$accounts = App::getAccounts($user_id);

		if (isset($accounts[$account_id])) {
			return $accounts[$account_id];
		}

		return false;
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
		if ($account_ids) {
			$account_ids = explode(',', $account_ids);
		} else {
			$account_ids = array();
		}
		$account_ids[] = $id;
		$account_ids = implode(",", $account_ids);

		\OCP\Config::setUserValue($user_id, 'mail', 'accounts', $account_ids);

		return $id;
	}

	public static function autoDetectAccount($user_id, $email, $password) {
		list($user, $host) = explode("@", $email);

		$new_account = self::testAccount($user_id, $email, $host, $user, $password);

		// try full email address as user name now (e.g. gmail does so)
		if ($new_account == null) {
			$new_account = self::testAccount($user_id, $email, $host, $email, $password);
		}

		return $new_account;
	}

	private static function testAccount($user_id, $email, $host, $user, $password) {
		/*
	    IMAP - port 143
	    Secure IMAP (IMAP4-SSL) - port 585
	    IMAP4 over SSL (IMAPS) - port 993
		 */
		$account = array(
			'name'     => $email,
			'host'     => $host,
			'user'     => $user,
			'password' => $password,
		);

		$ports = array(143, 585, 993);
		$sec_modes = array('ssl', 'tls', null);
		$host_prefixes = array('', 'imap.');
		foreach ($host_prefixes as $host_prefix) {
			$h = $host_prefix . $host;
			$account['host'] = $h;
			foreach ($ports as $port) {
				$account['port'] = $port;
				foreach ($sec_modes as $sec_mode) {
					$account['ssl_mode'] = $sec_mode;
					try {
						$test_account = new Account($account);
						$client = $test_account->getImapConnection();
						return App::addAccount($user_id, $h, $port, $user, $password, $sec_mode);
					} catch (\Horde_Imap_Client_Exception $e) {
						// nothing to do
						error_log("Failed: $user_id, $h, $port, $user, $password, $sec_mode");
					}
				}
			}
		}

		return null;
	}
}
