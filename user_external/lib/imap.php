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
		$mbox = @imap_open($this->mailbox, $uid, $password, OP_HALFOPEN, 1);
		imap_errors();
		imap_alerts();
		if($mbox !== FALSE) {
			imap_close($mbox);
			$uid = mb_strtolower($uid);
			$this->storeUser($uid);
			return $uid;
		}else{
			return false;
		}
	}
}
