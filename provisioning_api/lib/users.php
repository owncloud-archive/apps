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
		// Check the user exists
		if(!OC_User::userExists($parameters['userid'])){
			return new OC_OCS_Result(null, 101);
		}
		$userid = $parameters['userid'];
		$return = array();
		$return['email'] = OC_Preferences::getValue($userid, 'settings', 'email', '');
		// Calcuate quota values
		$user_dir = '/'.$user.'/files';
		OC_Filesystem::init($user_dir);
		$rootInfo=OC_FileCache::get('');
		$sharedInfo=OC_FileCache::get('/Shared');
		$used=$rootInfo['size']-$sharedInfo['size'];
		$free=OC_Filesystem::free_space();
		$total=$free+$used;
		if($total==0) $total=1;  // prevent division by zero
		$relative=round(($used/$total)*10000)/100;
		$return['quota']=$total;
		$return['freespace']=$free;
		$return['usedspace']=$used;
		$return['relativespaceused']=$relative;
		$return['enabled'] = OC_Preferences::getValue($userid, 'core', 'enabled', 'true');
		return new OC_OCS_Result($return);
	}

	public static function editUser($parameters){
		// TODO
	}

	public static function deleteUser($parameters){
		// Do they exist?
		if(!OC_User::userExists($parameters['userid'])){
			return new OC_OCS_Result(null, 101);
		}
		// Can't delete yourself.
		if($parameters['userid'] === OC_User::getUser() || !OC_User::deleteUser($parameters['userid'])){
			return new OC_OCS_Result(null, 102);
		} else {
			return new OC_OCS_Result(null, 100);
		}
	}

	public static function getUsersGroups($parameters){
		$userid = $parameters['userid'];
		return new OC_OCS_Result(array('groups' => OC_Group::getUserGroups($userid)));
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
		// If they're not an adamin, check they are a subadmin of the group in question
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
		// Add user to group
		return OC_Group::removeFromGroup($parameters['userid'], $group) ? new OC_OCS_Result(null, 100) : new OC_OCS_Result(null, 105);
	}

}