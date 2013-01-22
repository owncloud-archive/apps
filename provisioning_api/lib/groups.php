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

class OC_Provisioning_API_Groups{

	/**
	 * returns a list of groups
	 */
	public static function getGroups($parameters){
		$search = !empty($_GET['search']) ? $_GET['search'] : '';
		$limit = !empty($_GET['limit']) ? $_GET['limit'] : -1;
		$offset = !empty($_GET['offset']) ? $_GET['offset'] : 0;
		return new OC_OCS_Result(array('groups' => OC_Group::getGroups($search, $limit, $offset)));
	}

	/**
	 * returns an array of users in the group specified
	 */
	public static function getGroup($parameters){
		// Check the group exists
		if(!OC_Group::groupExists($parameters['groupid'])){
			return new OC_OCS_Result(null, 101);
		}
		return new OC_OCS_Result(array('users' => OC_Group::usersInGroup($parameters['groupid'])));
	}

	/**
	 * creates a new group
	 */
	public static function addGroup($parameters){
		// Validate name
		$groupid = isset($_POST['groupid']) ? $_POST['groupid'] : '';
		if( preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $groupid ) || empty($groupid)){
			return new OC_OCS_Result(null, 101);
		}
		// Check if it exists
		if(OC_Group::groupExists($groupid)){
			return new OC_OCS_Result(null, 102);
		}
		if(OC_Group::createGroup($groupid)){
			return new OC_OCS_Result(null, 100);
		} else {
			return new OC_OCS_Result(null, 103);
		}
	}

	public static function deleteGroup($parameters){
		// Check it exists
		if(!OC_Group::groupExists($parameters['groupid'])){
			return new OC_OCS_Result(null, 101);
		} else if($parameters['groupid'] == 'admin' || !OC_Group::deleteGroup($parameters['groupid'])){
			// Cannot delete admin group
			return new OC_OCS_Result(null, 102);
		} else {
			return new OC_OCS_Result(null, 100);
		}
	}

}
