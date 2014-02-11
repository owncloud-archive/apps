<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class OC_User_FTP extends OC_User_Backend{
	private $host;
	private $secure;
	private $protocol;

	public function __construct($host,$secure=false) {
		$this->host=$host;
		$this->secure=$secure;
		$this->protocol='ftp';
		if($this->secure) {
			$this->protocol.='s';
		}
		$this->protocol.='://';
	}

	/**
	 * @brief Check if the password is correct
	 * @param string $uid The username
	 * @param string $password The password
	 * @return bool
	 *
	 * Check if the password is correct without logging in the user
	 */
	public function checkPassword($uid, $password) {
		// opendir handles the as %-encoded string, but this is not true for usernames and passwords, encode them before passing them
		$url = sprintf('%s://%s:%s@%s/', $this->protocol, urlencode($uid), urlencode($password), $this->host);
		$result=@opendir($url);
		if(is_resource($result)) {
			return $uid;
		}else{
			return false;
		}
	}

	public function userExists($uid) {
		return true;
	}
}
