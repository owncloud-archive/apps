<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * User authentication against a FTP/FTPS server
 *
 * @category Apps
 * @package  UserExternal
 * @author   Robin Appelman <icewind@owncloud.com>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
class OC_User_FTP extends \OCA\user_external\Base{
	private $host;
	private $secure;
	private $protocol;

	/**
	 * Create new FTP authentication provider
	 *
	 * @param string  $host   Hostname or IP of FTP server
	 * @param boolean $secure TRUE to enable SSL
	 */
	public function __construct($host,$secure=false) {
		$this->host=$host;
		$this->secure=$secure;
		$this->protocol='ftp';
		if($this->secure) {
			$this->protocol.='s';
		}
		parent::__construct($this->protocol . '://' . $this->host);
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
		if (false === array_search($this->protocol, stream_get_wrappers())) {
			OCP\Util::writeLog(
				'user_external',
				'ERROR: Stream wrapper not available: ' . $this->protocol, OCP\Util::ERROR
			);
			return false;
		}
		// opendir handles the as %-encoded string, but this is not true for usernames and passwords, encode them before passing them
		$url = sprintf('%s://%s:%s@%s/', $this->protocol, urlencode($uid), urlencode($password), $this->host);
		$result=@opendir($url);
		if(is_resource($result)) {
			$this->storeUser($uid);
			return $uid;
		}else{
			return false;
		}
	}
}
