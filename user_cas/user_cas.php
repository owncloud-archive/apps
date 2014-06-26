<?php

/**
 * ownCloud - user_cas
 *
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @copyright Sixto Martin Garcia. 2012
 * @copyright Leonis. 2014 <devteam@leonis.at>
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

class OC_USER_CAS extends OC_User_Backend {

	// cached settings
	public $autocreate;
	public $updateUserData;
	public $protectedGroups;
	public $defaultGroup;
	public $displayNameMapping;
	public $mailMapping;
	public $groupMapping;
	//public $initialized = false;
	protected static $instance = null;

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new OC_USER_CAS();
		}
		return self::$instance;
	}

	public function __construct() {
		// These are default values for the first login and should be changed via GUI
		$CAS_HOSTNAME = 'your.domain.org';
		$CAS_PORT = '443';
		$CAS_PATH = '/cas';

		$this->autocreate = OCP\Config::getAppValue('user_cas', 'cas_autocreate', true);
		$this->updateUserData = OCP\Config::getAppValue('user_cas', 'cas_update_user_data', true);
		$this->defaultGroup = OCP\Config::getAppValue('user_cas', 'cas_default_group', '');
		$this->protectedGroups = explode (',', str_replace(' ', '', OCP\Config::getAppValue('user_cas', 'cas_protected_groups', '')));
		$this->mailMapping = OCP\Config::getAppValue('user_cas', 'cas_email_mapping', '');
		$this->displayNameMapping = OCP\Config::getAppValue('user_cas', 'cas_displayName_mapping', '');
		$this->groupMapping = OCP\Config::getAppValue('user_cas', 'cas_group_mapping', '');

		$casVersion = OCP\Config::getAppValue('user_cas', 'cas_server_version', '2.0');
		$casHostname = OCP\Config::getAppValue('user_cas', 'cas_server_hostname', $CAS_HOSTNAME);
		$casPort = OCP\Config::getAppValue('user_cas', 'cas_server_port', $CAS_PORT);
		$casPath = OCP\Config::getAppValue('user_cas', 'cas_server_path', $CAS_PATH);
		$casCertPath = OCP\Config::getAppValue('user_cas', 'cas_cert_path', '');

		global $initialized_cas;

		if(!$initialized_cas) {
			phpCAS::client($casVersion,$casHostname,(int)$casPort,$casPath,false);
			if(!empty($casCertPath)) {
				phpCAS::setCasServerCACert($casCertPath);
			}
			else {
				phpCAS::setNoCasServerValidation();
			}
			$initialized_cas = true;
		}
	}

	public function checkPassword($uid, $password) {

		if(!phpCAS::isAuthenticated()) {
			return false;
		}

		$uid = phpCAS::getUser();
		return $uid;
	}

	
	public function getDisplayName($uid) {
		$udb = new OC_User_Database;
		return $udb->getDisplayName($uid);
	}

	/**
	* Sets the display name for by using the CAS attribute specified in the mapping
	*
	*/
	public function setDisplayName($uid,$displayName) {
		$udb = new OC_User_Database;
		$udb->setDisplayName($uid,$displayName);
	}

}

