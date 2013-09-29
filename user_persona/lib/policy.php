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

namespace OCA\User_persona;

class Policy {
	const CONFIG_KEY = 'persona_multiple_user';

	const MULTIPLE_USERS_DENY = 0;
	const MULTIPLE_USERS_FIRST = 10;
	const MULTIPLE_USERS_LIST = 20;

	/**
	 * Check if we have a user to login
	 * @param String $email 
	 * @param String $uid 
	 * @return String 
	 */
	public static function apply($email, $uid = '') {

		//Get list of matching users
		$list = array();

		$query = \OCP\DB::prepare('SELECT userid FROM *PREFIX*preferences WHERE appid = ? AND configkey = ? AND configvalue  = ?');
		$result = $query->execute(array('settings', 'email', $email));

		while ($userid = $result->fetchOne()) {
			$list[] = $userid;
		}
		$qtyUser = count($list);
		
		//No users found
		if (!$qtyUser) {
			\OCP\Util::writeLog(App::APP_ID, 'No users found. Deny login.', \OCP\Util::DEBUG);
			return false;
		}
		
		//One user found
		if ($qtyUser == 1) {
			\OCP\Util::writeLog(App::APP_ID, 'Single user found. Entering the open space.', \OCP\Util::DEBUG);
			return $list[0];
		}

		//Multiple users found
		$currentPolicy = self::getSystemPolicy();
		$isValidUid = in_array($uid, $list);

		if ($currentPolicy == self::MULTIPLE_USERS_LIST) {
			//Do we have correct uid?
			if ($isValidUid){
				\OCP\Util::writeLog(App::APP_ID, 'Multiple users found. Entering the open space.', \OCP\Util::DEBUG);
				return $uid;
			} else {
				\OCP\Util::writeLog(App::APP_ID, 'Multiple users found. List them all.', \OCP\Util::DEBUG);				
				\OCP\JSON::success(array('list' => $list));
				exit();
			}
		} elseif ($currentPolicy == self::MULTIPLE_USERS_FIRST) {
			\OCP\Util::writeLog(App::APP_ID, 'Multiple users found. Use first.', \OCP\Util::DEBUG);

			//not first but the best matching ;)
			$userid = $isValidUid ? $uid : $list[0];
			return $userid;
		}

		\OCP\Util::writeLog(App::APP_ID, 'Multiple users found. Deny login.', \OCP\Util::DEBUG);
		return Validator::setAmbigous();
	}

	/**
	 * Get all available policies
	 * @return array 
	 */
	public static function getAllPolicies() {
		return array(
			self::MULTIPLE_USERS_DENY => 'Login none of them',
			self::MULTIPLE_USERS_FIRST => 'Login first matching user',
			self::MULTIPLE_USERS_LIST => 'Show matches',
		);
	}

	/**
	 * Set system settings
	 * @param int $policy 
	 */
	public static function setSystemPolicy($policy) {
		$policy = self::_validatePolicy($policy);
		\OCP\Config::setAppValue(App::APP_ID, self::CONFIG_KEY, $policy);
	}

	/**
	 * Get system settings
	 * @return int
	 */
	public static function getSystemPolicy() {
		$policy = \OCP\Config::getAppValue(App::APP_ID, self::CONFIG_KEY);
		return self::_validatePolicy($policy);
	}

	/**
	 * Check if the value is allowed
	 * @param int $policy
	 * @return int
	 */
	protected static function _validatePolicy($policy) {
		$policies = self::getAllPolicies();
		if (!in_array($policy, array_keys($policies))) {
			$policy = self::MULTIPLE_USERS_DENY;
		}

		return $policy;
	}

}
