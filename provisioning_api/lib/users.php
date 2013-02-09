<?php

/**
* ownCloud - Provisioning API
*
* @author Tom Needham
* @copyright 2012 Tom Needham tom@owncloud.com
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
* You should have received a copy of the GNU Lesser General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

class OC_Provisioning_API_Users {

	/**
	 * returns a list of users
	 */
	public static function getUsers($parameters){
		$search = !empty($_GET['search']) ? $_GET['search'] : '';
		$limit = !empty($_GET['limit']) ? $_GET['limit'] : null;
		$offset = !empty($_GET['offset']) ? $_GET['offset'] : null;
		return new OC_OCS_Result(array('users' => OC_User::getUsers($search, $limit, $offset)));
	}

	public static function addUser(){
		$userid = isset($_POST['userid']) ? $_POST['userid'] : null;
		$password = isset($_POST['password']) ? $_POST['password'] : null;
		try {
			OC_User::createUser($userid, $password);
			return new OC_OCS_Result(null, 100);
		} catch (Exception $e) {
			switch($e->getMessage()){
				case 'Only the following characters are allowed in a username: "a-z", "A-Z", "0-9", and "_.@-"':
				case 'A valid username must be provided':
				case 'A valid password must be provided':
					return new OC_OCS_Result(null, 101);
					break;
				case 'The username is already being used';
					return new OC_OCS_Result(null, 102);
					break;
				default:
					return new OC_OCS_Result(null, 103);
					break;
			}
		}
	}

	/**
	 * gets user info
	 */
	public static function getUser($parameters){
		$userid = $parameters['userid'];
		$return = array();
		// Check if they are viewing information on themself
		if($userid === OC_User::getUser()){
			// Self lookup
			$return['email'] = OC_Preferences::getValue($userid, 'settings', 'email', '');			
			// Todo add quota info
		} else {
			// Looking up someone else
			if(OC_User::isAdminUser(OC_User::getUser()) 
			|| OC_SubAdmin::isUserAccessible(OC_User::getUser(), $userid)) {
				// Check the user exists
				if(!OC_User::userExists($parameters['userid'])){
					return new OC_OCS_Result(null, 101);
				}
				// If an admin, return if the user is enabled or not
				if(OC_User::isAdminUser($userid)){
					$return['enabled'] = OC_Preferences::getValue($userid, 'core', 'enabled', 'true');
				}
			} else {
				// No permission to view this user data
				return new OC_OCS_Result(null, 997);
			}
		}
		$return['displayname'] = OC_User::getDisplayName($userid);
		return new OC_OCS_Result($return);
	}

	/** 
	 * edit users
	 */
	public static function editUser($parameters){
		$userid = $parameters['userid'];
		if($userid === OC_User::getUser()) {
			// Editing self (diaply, email)
			$permittedfields[] = 'display';
			$permittedfields[] = 'email';
			$permittedfields[] = 'password';
		} else {
			// Check if admin / subadmin
			if(OC_SubAdmin::isUserAccessible(OC_User::getUser(), $userid) 
			|| OC_User::isAdminUser(OC_User::getUser())) {
				// They have permissions over the user
				$permittedfields[] = 'display';
				$permittedfields[] = 'quota';
				$permittedfields[] = 'password';
			} else {
				// No rights
				return new OC_OCS_Result(null, 997);
			}
		}
		// Check if permitted to edit this field
		if(!in_array($parameters['_put']['key'], $permittedfields)) {
			return new OC_OCS_Result(null, 997);
		}
		// Process the edit
		switch($parameters['_put']['key']){
			case 'display':
				OC_User::setDisplayName($userid, $parameters['_put']['value']);
				break;
			case 'quota':
				if(!is_numeric($parameters['_put']['value'])) {
					return new OC_OCS_Result(null, 101);
				}
				$quota = $parameters['_put']['value'];
				if($quota !== 'none' and $quota !== 'default') {
					$quota = OC_Helper::computerFileSize($quota);
					if($quota == 0) {
						$quota = 'default';
					}else if($quota == -1){
						$quota = 'none';
					} else {
						$quota = OC_Helper::humanFileSize($quota);
					}
				}
				OC_Preferences::setValue($userid, 'files', 'quota', $quota);
				break;
			case 'password':
				OC_User::setPassword($userid, $parameters['_put']['value']);
				break;
			case 'email':
				if(filter_var($parameters['_put']['value'], FILTER_VALIDATE_EMAIL)) {
					OC_Preferences::setValue(OC_User::getUser(), 'settings', 'email', $parameters['_put']['value']);
				} else {
					return new OC_OCS_Result(null, 102);
				}
				break;
			default:
				return new OC_OCS_Result(null, 103);
				break;
		}
		return new OC_OCS_Result(null, 100);
	}

	public static function deleteUser($parameters){
		if(!OC_User::userExists($parameters['userid']) 
		|| $parameters['userid'] === OC_User::getUser()) {
			return new OC_OCS_Result(null, 101);
		}
		// If not an admin
		if(!OC_User::isAdminUser(OC_User::getUser())) {
			if(!OC_SubAdmin::isUserAccessible(OC_User::getUser(), $parameters['userid'])) {
				return new OC_OCS_Result(null, 997);
			}
		}
		// Go ahead with the delete
		if(OC_User::deleteUser($parameters['userid'])) {
			return new OC_OCS_Result(null, 100);
		} else {
			return new OC_OCS_Result(null, 101);
		}
	}

	public static function getUsersGroups($parameters){
		if($parameters['userid'] === OC_User::getUser() || OC_User::isAdminUser(OC_User::getUser())) {
			// Self lookup or admin lookup
			return new OC_OCS_Result(array('groups' => OC_Group::getUserGroups($parameters['userid'])));
		} else {
			// Looking up someone else
			if(OC_SubAdmin::isUserAccessible(OC_User::getUser(), $parameters['userid'])) {
				// Return the group that the method caller is subadmin of for the user in question
				$groups = array_intersect(OC_SubAdmin::getSubAdminsGroups(OC_User::getUser()), OC_Group::getUserGroups($parameters['userid']));
				return new OC_OCS_Result(array('groups' => $groups));
			} else {
				// Not permitted
				return new OC_OCS_Result(null, 997);
			}
		}
		
	}

	public static function addToGroup($parameters){
		$group = !empty($_POST['groupid']) ? $_POST['groupid'] : null;
		if(is_null($group)){
			return new OC_OCS_Result(null, 101);
		}
		// Check they are a subadmin, if not an admin
		if(!OC_Group::inGroup(OC_User::getUser(), 'admin') && !OC_SubAdmin::isSubAdminofGroup(OC_User::getUser(), $group)){
			// This subadmin doesn't have rights to add a user to this group
			return new OC_OCS_Result(null, 104);
		}
		// Check if the group exists
		if(!OC_Group::groupExists($group)){
			return new OC_OCS_Result(null, 102);
		}
		// Check if the user exists
		if(!OC_User::userExists($parameters['userid'])){
			return new OC_OCS_Result(null, 103);
		}
		// Add user to group
		return OC_Group::addToGroup($parameters['userid'], $group) ? new OC_OCS_Result(null, 100) : new OC_OCS_Result(null, 105);
	}

	public static function removeFromGroup($parameters){
		$group = !empty($parameters['_delete']['groupid']) ? $parameters['_delete']['groupid'] : null;
		if(is_null($group)){
			return new OC_OCS_Result(null, 101);
		}
		// If they're not an admin, check they are a subadmin of the group in question
		if(!OC_Group::inGroup(OC_User::getUser(), 'admin') && !OC_SubAdmin::isSubAdminofGroup(OC_User::getUser(), $group)){
			return new OC_OCS_Result(null, 104);
		}
		// Check they aren't removing themselves from 'admin' or their 'subadmin; group
		if($parameters['userid'] === OC_User::getUser()){
			if(OC_Group::inGroup(OC_User::getUser(), 'admin')){
				if($group === 'admin'){
					return new OC_OCS_Result(null, 105, 'Cannot remove yourself from the admin group');
				}
			} else {
				// Not an admin, check they are not removing themself from their subadmin group
				if(in_array($group, OC_SubAdmin::getSubAdminsGroups(OC_User::getUser()))){
					return new OC_OCS_Result(null, 105, 'Cannot remove yourself from this group as you are a SubAdmin');
				}
			}
		}
		// Check if the group exists
		if(!OC_Group::groupExists($group)){
			return new OC_OCS_Result(null, 102);
		}
		// Check if the user exists
		if(!OC_User::userExists($parameters['userid'])){
			return new OC_OCS_Result(null, 103);
		}
		// Remove user from group
		return OC_Group::removeFromGroup($parameters['userid'], $group) ? new OC_OCS_Result(null, 100) : new OC_OCS_Result(null, 105);
	}

}