<?php
/**
 * Copyright (c) 2015 Huanjie Wu <wuhj@zjutv.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * Please paste your UCenter Application Configuration here instead of the
 * example.
 * It is recommended to define 'UC_CONNECT' as 'mysql'.
 */
// UCenter Application Configuration Example begin
define('UC_CONNECT', 'mysql');
define('UC_DBHOST', 'localhost');
define('UC_DBUSER', 'root');
define('UC_DBPW', 'password');
define('UC_DBNAME', 'ucenter_db');
define('UC_DBCHARSET', 'utf8');
define('UC_DBTABLEPRE', '`ucenter_db`.table_prefix_');
define('UC_DBCONNECT', '0');
define('UC_KEY', 'app_key_here_like_asdfasdfasdfasdfasd');
define('UC_API', 'http://your.domain.com/ucenter_server_path');
define('UC_CHARSET', 'utf-8');
define('UC_IP', '');
define('UC_APPID', '1');
define('UC_PPP', '20');
// UCenter Application Configuration Example end

include 'uc_client/client.php';

/**
 * User authentication against a UCenter server
 *
 * @category Apps
 * @package  UserExternal
 * @author   Huanjie Wu <wuhj@zjutv.com>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/AxelPanda/apps
 */
class OC_User_UC extends \OCA\user_external\Base {
	private $ucenter;

	/**
	 * Create new UCenter authentication provider
	 *
	 * @param string $ucenter user_external backend identifier, e.g.
	 *                        myUcBackend
	 */
	public function __construct($ucenter) {
		parent::__construct($ucenter);
		$this->ucenter=$ucenter;
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
		$result = uc_user_login($uid, $password);
		if($result[0] > 0) {
			$this->storeUser($uid);
			return $uid;
		} else {
			return false;
		}
	}
}
