<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class OC_User_IMAP extends OC_User_Backend{
	private $mailbox;

	public function __construct($mailbox) {
		$this->mailbox=$mailbox;
	}

	/**
	 * @brief Check if the password is correct
	 * @param $uid The username
	 * @param $password The password
	 * @returns true/false
	 *
	 * Check if the password is correct without logging in the user
	 */
	public function checkPassword($uid, $password) {
		if (!function_exists('imap_open')) {
			OCP\Util::writeLog('user_external', 'ERROR: PHP imap extension is not installed', OCP\Util::ERROR);
			return false;
		}
		$mbox = @imap_open($this->mailbox, $uid, $password);
		imap_errors();
		imap_alerts();
		if($mbox) {
			imap_close($mbox);
			return $uid;
		}else{
			return false;
		}
	}

	public function userExists($uid) {
		return true;
	}
}
