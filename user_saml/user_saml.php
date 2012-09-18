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
	public $autocreate;
	public $updateUserData;
	public $protectedGroups;
	public $defaultGroup;
	public $usernameMapping;
	public $mailMapping;
	public $groupMapping;
	public $auth;


	public function __construct() {
		$this->sspPath = OCP\Config::getAppValue('user_saml', 'saml_ssp_path', '');
		$this->spSource = OCP\Config::getAppValue('user_saml', 'saml_sp_source', '');
		$this->autocreate = OCP\Config::getAppValue('user_saml', 'saml_autocreate', false);
		$this->updateUserData = OCP\Config::getAppValue('user_saml', 'saml_update_user_data', false);
		$this->defaultGroup = OCP\Config::getAppValue('user_saml', 'saml_default_group', '');
		$this->protectedGroups = explode (',', str_replace(' ', '', OCP\Config::getAppValue('user_saml', 'saml_protected_groups', '')));
		$this->usernameMapping = OCP\Config::getAppValue('user_saml', 'saml_username_mapping', '');
		$this->mailMapping = OCP\Config::getAppValue('user_saml', 'saml_email_mapping', '');
		$this->groupMapping = OCP\Config::getAppValue('user_saml', 'saml_group_mapping', '');


		include_once($this->sspPath."/lib/_autoload.php");

		$this->auth = new SimpleSAML_Auth_Simple($this->spSource);
	}


	public function checkPassword($uid, $password) {

		if(!$this->auth->isAuthenticated()) {
			return false;
		}

		$attributes = $this->auth->getAttributes();

		if (array_key_exists($this->usernameMapping, $attributes)) {
			$uid = $attributes[$this->usernameMapping][0];
			OC_Log::write('saml','Authenticated user '.$uid,OC_Log::DEBUG);
		}
		else {
			OC_Log::write('saml','Not found attribute used to get the username ("'.$this->usernameMapping.'") at the requested saml attribute assertion',OC_Log::DEBUG);
		}

		return $uid;
	}
}
