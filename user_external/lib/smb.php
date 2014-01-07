<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class OC_User_SMB extends OC_User_Backend{
	private $host;

	const smbclient='smbclient';
	const loginError='NT_STATUS_LOGON_FAILURE';

	public function __construct($host) {
		parent::__construct($host);
		$this->host=$host;
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
		$uidEscaped=escapeshellarg($uid);
		$password=escapeshellarg($password);
		$result=array();
		$command=self::smbclient.' //'.$this->host.'/dummy -U'.$uidEscaped.'%'.$password;
		$lastline = exec($command, $output, $retval);
		if ($retval === 127) {
			OCP\Util::writeLog('user_external', 'ERROR: smbclient executable missing', OCP\Util::ERROR);
			return false;
		} else if (strpos($lastline, self::loginError) !== false) {
			//normal login error
			return false;
		} else if ($retval != 0) {
			//some other error
			OCP\Util::writeLog('user_external', 'ERROR: smbclient error: ' . trim($lastline), OCP\Util::ERROR);
			return false;
		} else {
			$this->storeUser($uid);
			return $uid;
		}
	}
}