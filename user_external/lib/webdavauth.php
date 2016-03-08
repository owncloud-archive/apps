<?php
/**
 * Copyright (c) 2015 Thomas Müller <thomas.mueller@tmit.eu>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\user_external;

class WebDavAuth extends Base {

	private $webDavAuthUrl;

	public function __construct($webDavAuthUrl) {
		parent::__construct($webDavAuthUrl);
		$this->webDavAuthUrl =$webDavAuthUrl;
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
		$arr = explode('://', $this->webDavAuthUrl, 2);
		if( ! isset($arr) OR count($arr) !== 2) {
			\OCP\Util::writeLog('OC_USER_WEBDAVAUTH', 'Invalid Url: "'.$this->webDavAuthUrl.'" ', 3);
			return false;
		}
		list($protocol, $path) = $arr;
		$url= $protocol.'://'.urlencode($uid).':'.urlencode($password).'@'.$path;
		$headers = get_headers($url);
		if($headers==false) {
			\OCP\Util::writeLog('OC_USER_WEBDAVAUTH', 'Not possible to connect to WebDAV Url: "'.$protocol.'://'.$path.'" ', 3);
			return false;

		}
		$returnCode= substr($headers[0], 9, 3);

		if(substr($returnCode, 0, 1) === '2') {
			$this->storeUser($uid);
			return $uid;
		} else {
			return false;
		}
	}
}
