<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * User authentication via samba (smbclient)
 *
 * @category Apps
 * @package  UserExternal
 * @author   Robin Appelman <icewind@owncloud.com>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
class OC_User_SMB extends \OCA\user_external\Base{
	private $host;

	const SMBCLIENT = 'smbclient';
	const LOGINERROR = 'NT_STATUS_LOGON_FAILURE';

	/**
	 * Create new samba authentication provider
	 *
	 * @param string $host Hostname or IP of windows machine
	 */
	public function __construct($host) {
		parent::__construct($host);
		$this->host=$host;
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
		$uidEscaped=escapeshellarg($uid);
		$password=escapeshellarg($password);
		$result=array();
		$command=self::SMBCLIENT.' //'.$this->host.'/dummy -U'.$uidEscaped.'%'.$password;
		$lastline = exec($command, $output, $retval);
		if ($retval === 127) {
			OCP\Util::writeLog(
				'user_external', 'ERROR: smbclient executable missing',
				OCP\Util::ERROR
			);
			return false;
		} else if (strpos($lastline, self::LOGINERROR) !== false) {
			//normal login error
			return false;
		} else if ($retval != 0) {
			//some other error
			OCP\Util::writeLog(
				'user_external', 'ERROR: smbclient error: ' . trim($lastline),
				OCP\Util::ERROR
			);
			return false;
		} else {
			$this->storeUser($uid);
			return $uid;
		}
	}
}