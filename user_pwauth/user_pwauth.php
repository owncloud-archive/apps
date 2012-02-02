<?php

/**
 * ownCloud
 *
 * @author Clément Véret
 * @copyright 2011 Clément Véret veretcle+owncloud@mateu.be
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

class OC_USER_PWAUTH extends OC_User_Backend {
	protected $pwauth_bin_path = '/usr/sbin/pwauth';
	protected $pwauth_min_uid;
	protected $pwauth_max_uid;
	
	public function __construct() {
		$this->pwauth_min_uid = OC_Appconfig::getValue('user_pwauth', 'min_uid', OC_USER_BACKEND_PWAUTH_MIN_UID);
		$this->pwauth_max_uid = OC_Appconfig::getValue('user_pwauth', 'max_uid', OC_USER_BACKEND_PWAUTH_MAX_UID);
		$this->logger = new OC_Log;
	}
	
	public function createUser() {
		// Can't create user
		$this->logger->write('OC_USER_PWAUTH', 'Not possible to create local users from web frontend using unix user backend',3);
		return false;
	}

	public function deleteUser() {
		// Can't delete user
		$this->logger->write('OC_USER_PWAUTH', 'Not possible to delete local users from web frontend using unix user backend',3);
		return false;
	}

	public function setPassword ( $uid, $password ) {
		// We can't change user password
		$this->logger->write('OC_USER_PWAUTH', 'Not possible to change password for local users from web frontend using unix user backend',3);
		return false;
	}
	
	public function checkPassword( $uid, $password ) {
		$uid = strtolower($uid);
		
		$unix_user = posix_getpwnam($uid);
		
		// checks if the Unix UID number is allowed to connect
		if(empty($unix_user)) return false; //user does not exist
		if($unix_user['uid'] < $this->pwauth_min_uid || $unix_user['uid'] > $this->pwauth_max_uid) return false;
		
		$handle = popen($this->pwauth_bin_path, 'w');
                if ($handle === false) {
			// Can't open pwauth executable
					$this->logger->write('OC_USER_PWAUTH', 'Cannot open pwauth executable, check that it is installed on server.',3);
                        return false;
                }
 
                if (fwrite($handle, "$uid\n$password\n") === false) {
			// Can't pipe uid and password
                        return false;
                }
 
                # Is the password valid?
	        $result = pclose( $handle );
                if ($result == 0){
			// before returning the UID
			// we must ensure that there Principals
			// have been created for SabreDAV to function properly
			// unless CardDAV and CalDAV server will be unusable
			$myOC_Connector_Sabre_Principal = new OC_Connector_Sabre_Principal();
			$principals = $myOC_Connector_Sabre_Principal->getPrincipalsByPrefix('principals/'.$uid);
			if(empty($principals)) {
				$params['uid'] = $uid;
				$myOC_Connector_Sabre_Principal->addPrincipal($params);
			}
			unset($myOC_Connector_Sabre_Principal);
			return $uid;
		}
                return false;
	}
	
	public function userExists( $uid ){
		$user = posix_getpwnam( strtolower($uid) );
		return is_array($user);
	}
	/*
	* this is a tricky one : there is no way to list all users which UID > 1000 directly in PHP
	* so we just scan all UIDs from $pwauth_min_uid to $pwauth_max_uid
	*/
	public function getUsers(){
		$returnArray = array();
		for($f = $this->pwauth_min_uid; $f <= $this->pwauth_max_uid; $f++) {
			if(is_array($array = posix_getpwuid($f))) {
				$returnArray[] = $array['name'];
			}
		}
		
		return $returnArray;
	}
}

?>

