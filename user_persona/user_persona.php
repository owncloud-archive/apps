<?php

/**
 * ownCloud - Persona plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

class OC_USER_PERSONA extends OC_User_Backend {

	protected $_isPersonaRequest;

	public function __construct() {
		$this->_isPersonaRequest = @$_POST['authService'] == 'MozillaPersona';
	}

	public function createUser($uid, $password) {
		//We can't create user
		return false;
	}

	public function deleteUser($uid) {
		//We can't delete user
		return false;
	}

	public function setPassword($uid, $password) {
		// We can't change user password
		return false;
	}

	public function checkPassword($uid, $assertion) {
		if ($this->_isPersonaRequest) {
			$email = OCA\User_persona\Validator::Validate($assertion);
			if ($email) {
				return OCA\User_persona\Policy::apply($email, $uid);
			}
			
			//we've got incorrect assertion
			OCP\Util::writeLog('OC_USER_PERSONA', 'Validation failed. Incorrect Assertion.', OCP\Util::DEBUG);
			OCP\JSON::error(array('msg'=>'Incorrect Assertion'));
			exit();
		}
		
		return false;
	}

	public function userExists($uid) {
		// We dunno
		return false;
	}

	public function getUsers($search = '', $limit = null, $offset = null){
		// We don't support user listing
		return array();
	}
}
