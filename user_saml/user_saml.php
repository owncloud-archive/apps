<?php

/**
 * ownCloud - user_saml
 *
 * @author Sixto Martin <smartin@yaco.es>
 * @copyright 2012 Yaco Sistemas // CONFIA
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
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class OC_USER_SAML extends OC_User_Backend {

	// cached settings
	protected $sspPath;
	protected $spSource;
	public $forceLogin;
	public $autocreate;
	public $updateUserData;
	public $protectedGroups;
	public $defaultGroup;
	public $usernameMapping;
	public $mailMapping;
	public $displayNameMapping;
	public $quotaMapping;
	public $defaultQuota;
	public $groupMapping;
	public $auth;


	public function __construct() {
		$this->sspPath = OCP\Config::getAppValue('user_saml', 'saml_ssp_path', '');
		$this->spSource = OCP\Config::getAppValue('user_saml', 'saml_sp_source', '');
		$this->forceLogin = OCP\Config::getAppValue('user_saml', 'saml_force_saml_login', false);
		$this->autocreate = OCP\Config::getAppValue('user_saml', 'saml_autocreate', false);
		$this->updateUserData = OCP\Config::getAppValue('user_saml', 'saml_update_user_data', false);
		$this->defaultGroup = OCP\Config::getAppValue('user_saml', 'saml_default_group', '');
		$this->protectedGroups = explode (',', preg_replace('/\s+/', '', OCP\Config::getAppValue('user_saml', 'saml_protected_groups', '')));
		$this->usernameMapping = explode (',', preg_replace('/\s+/', '', OCP\Config::getAppValue('user_saml', 'saml_username_mapping', '')));
		$this->mailMapping = explode (',', preg_replace('/\s+/', '', OCP\Config::getAppValue('user_saml', 'saml_email_mapping', '')));
		$this->displayNameMapping = explode (',', preg_replace('/\s+/', '', OCP\Config::getAppValue('user_saml', 'saml_displayname_mapping', '')));
		$this->quotaMapping = explode (',', preg_replace('/\s+/', '', OCP\Config::getAppValue('user_saml', 'saml_quota_mapping', '')));
		$this->defaultQuota = OCP\Config::getAppValue('user_saml', 'saml_default_quota', '');
		$this->groupMapping = explode (',', preg_replace('/\s+/', '', OCP\Config::getAppValue('user_saml', 'saml_group_mapping', '')));

		if (!empty($this->sspPath) && !empty($this->spSource)) {
			include_once $this->sspPath."/lib/_autoload.php";

			$this->auth = new SimpleSAML_Auth_Simple($this->spSource);

			if (isset($_COOKIE["user_saml_logged_in"]) AND $_COOKIE["user_saml_logged_in"] AND !$this->auth->isAuthenticated()) {
				unset($_COOKIE["user_saml_logged_in"]);
				setcookie("user_saml_logged_in", null, -1);
				OCP\User::logout();
			}
		}
	}


	public function checkPassword($uid, $password) {

		if(!$this->auth->isAuthenticated()) {
			return false;
		}

		$attributes = $this->auth->getAttributes();

		foreach($this->usernameMapping as $usernameMapping) {
			if (array_key_exists($usernameMapping, $attributes) && !empty($attributes[$usernameMapping][0])) {
				$uid = $attributes[$usernameMapping][0];
				OCP\Util::writeLog('saml','Authenticated user '.$uid, OCP\Util::DEBUG);
				if(!OCP\User::userExists($uid) && $this->autocreate) {
					return $this->createUser($uid);
				}
				return $uid;
			}
		}

		OCP\Util::writeLog('saml','Not found attribute used to get the username at the requested saml attribute assertion', OCP\Util::DEBUG);
		$secure_cookie = OC_Config::getValue("forcessl", false);
		$expires = time() + OC_Config::getValue('remember_login_cookie_lifetime', 60*60*24*15);
		setcookie("user_saml_logged_in", "1", $expires, '', '', $secure_cookie);

		return false;
	}

	private function createUser($uid) {
                if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $uid)) {
                        OCP\Util::writeLog('saml','Invalid username "'.$uid.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OCP\Util::DEBUG);
                        return false;
                } else {
                        $random_password = OCP\Util::generateRandomBytes(64);
                        OCP\Util::writeLog('saml','Creating new user: '.$uid, OCP\Util::DEBUG);
                        \OC::$server->getUserManager()->createUser($uid, $random_password);
                        return $uid;
                }
        }
}
