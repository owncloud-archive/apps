<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * User authentication against an IMAP mail server
 *
 * @category Apps
 * @package  UserExternal
 * @author   Robin Appelman <icewind@owncloud.com>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
class OC_User_IMAP extends \OCA\user_external\Base {
	private $mailbox;

	/**
	 * Create new IMAP authentication provider
	 *
	 * @param string $mailbox PHP imap_open mailbox definition, e.g.
	 *                        {127.0.0.1:143/imap/readonly}
	 */
	public function __construct($mailbox) {
		parent::__construct($mailbox);
		$this->mailbox=$mailbox;
	}

	/**
	 * Check if the password is correct without logging in the user
	 *
	 * @param string $uid      The username
	 * @param string $password The password
	 *
	 * @return true/false
	 */
	public function checkPassword($uid, $password) {
		if (!function_exists('imap_open')) {
			OCP\Util::writeLog('user_external', 'ERROR: PHP imap extension is not installed', OCP\Util::ERROR);
			return false;
		}

		$filename = dirname(__FILE__) . '/../imap_users.csv';
		$user_allowed = false;
		if (file_exists($filename)) {
			if (($handle = fopen($filename, "r"))  !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE && $user_allowed !== TRUE) {
					if (in_array($uid, $data)) {
						$user_allowed = true;
						$displayName = $data[1];
						$group = $data[3];
						if (filter_var($data[0], FILTER_VALIDATE_EMAIL) && empty($data[2])) {
							$email = $data[0];
						}else{
							$email = $data[2];
						}
					}
				}
				fclose($handle);
				if ($user_allowed !== TRUE) {
					return false;
				}
			}
		}

		if  (substr($this->mailbox, 1, 1) === '.') {
			$this->mailbox = ltrim($this->mailbox, '{');
			$this->mailbox = '{' . $uid . $this->mailbox;
		}

		$mbox = @imap_open($this->mailbox, $uid, $password, OP_HALFOPEN);
		imap_errors();
		imap_alerts();
		if($mbox !== FALSE) {
			imap_close($mbox);
            		if ($user_allowed) {
            			$this->storeUser($uid, $displayName, $email, $group);
            		}else{
            			$this->storeUser($uid);
            		}
			return $uid;
		}else{
			return false;
		}
	}
}
