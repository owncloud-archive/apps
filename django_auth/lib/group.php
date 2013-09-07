<?php

/**
 * ownCloud
 *
 * @author Florian Reinhard
 * @copyright 2012 Florian Reinhard <florian.reinhard@googlemail.com>
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

/**
* @brief Class providing django groups to ownCloud
* @see http://www.djangoproject.com
*
* Authentification backend to authenticate agains a django webapplication using
* django.contrib.auth.
*/
class OC_GROUP_DJANGO extends OC_Group_Backend {
	static $staff_is_admin;
	static $superuser_is_admin;

	public function __construct() {
		self::$staff_is_admin     = OCP\Config::getAppValue('django_auth', 'staff_is_admin',     OC_GROUP_BACKEND_DJANGO_STAFF_IS_ADMIN);
		self::$superuser_is_admin = OCP\Config::getAppValue('django_auth', 'superuser_is_admin', OC_GROUP_BACKEND_DJANGO_SUPERUSER_IS_ADMIN);
	}
	static private $userGroupCache=array();

	/**
	* @brief Try to create a new group
	* @param $gid The name of the group to create
	* @returns true/false
	*
	* Trys to create a new group. If the group name already exists, false will
	* be returned.
	*/
	public static function createGroup( $gid ) {
		OCP\Util::writeLog('OC_Group_Django', 'Use the django webinterface to create groups',3);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief delete a group
	* @param $gid gid of the group to delete
	* @returns true/false
	*
	* Deletes a group and removes it from the group_user-table
	*/
	public function deleteGroup( $gid ) {
		OCP\Util::writeLog('OC_Group_Django', 'Use the django webinterface to delete groups',3);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief is user in group?
	* @param $uid uid of the user
	* @param $gid gid of the group
	* @returns true/false
	*
	* Checks whether the user is member of a group or not.
	*/
	public function inGroup( $uid, $gid ) {
		// Special case for the admin group
		if ($gid =='admin') {
			$sql = 'SELECT `auth_user`.`username`
  FROM `auth_user`
 WHERE `auth_user`.`username`  = ?
   AND `auth_user`.`is_active` = 1';
			if (self::$superuser_is_admin or self::$staff_is_admin) {
				$sql.="\nAND (";
				if (self::$superuser_is_admin) {
					$sql.='`auth_user`.`is_superuser` = 1';
					if (self::$staff_is_admin) {
						$sql.=' OR ';
					}
				}
				if (self::$staff_is_admin) {
					$sql.='`auth_user`.`is_staff` = 1';
				}
				$sql.=')';
			}
			$query  = OCP\DB::prepare($sql);
			$result = $query->execute( array( $uid ));
		}
		else {
			$sql = 'SELECT `auth_user`.`username`
  FROM `auth_user`
 INNER JOIN `auth_user_groups`
         ON (`auth_user`.`id` = `auth_user_groups`.`user_id`)
 INNER JOIN `auth_group`
         ON (`auth_group`.`id` = `auth_user_groups`.`group_id`)
 WHERE `auth_group`.`name` = ?
   AND `auth_user`.`username`  = ?
   AND `auth_user`.`is_active` = 1';
			$query  = OCP\DB::prepare($sql);
			$result = $query->execute( array( $gid, $uid ));
		}

		return $result->fetchRow() ? true : false;
	}

	/**
	* @brief Add a user to a group
	* @param $uid Name of the user to add to group
	* @param $gid Name of the group in which add the user
	* @returns true/false
	*
	* Adds a user to a group.
	*/
	public function addToGroup( $uid, $gid ) {
		OCP\Util::writeLog('OC_Group_Django', 'Use the django webinterface to add users to groups',3);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief Removes a user from a group
	* @param $uid Name of the user to remove from group
	* @param $gid Name of the group from which remove the user
	* @returns true/false
	*
	* removes the user from a group.
	*/
	public function removeFromGroup( $uid, $gid ) {
		OCP\Util::writeLog('OC_Group_Django', 'Use the django webinterface to remove users from groups',3);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief Get all groups a user belongs to
	* @param $uid Name of the user
	* @returns array with group names
	*
	* This function fetches all groups a user belongs to. It does not check
	* if the user exists at all.
	*/
	public function getUserGroups( $uid ) {
		$query = OCP\DB::prepare( 'SELECT  `auth_group`.`name`
FROM  `auth_group`
INNER JOIN  `auth_user_groups` ON (  `auth_group`.`id` =  `auth_user_groups`.`group_id` )
INNER JOIN  `auth_user`        ON (  `auth_user`.`id` =  `auth_user_groups`.`user_id` )
 WHERE `auth_user`.`username`  = ?
   AND `auth_user`.`is_active` = 1' );
		$result = $query->execute( array( $uid ));
		$groups = array();
		while ( $row = $result->fetchRow()) {
			$groups[] = $row["name"];
		}
		return $groups;
	}

	/**
	* @brief get a list of all groups
	* @returns array with group names
	*
	* Returns a list with all groups
	*/
	public function getGroups($search = '', $limit = -1, $offset = 0) {
		$query  = OCP\DB::prepare( "SELECT id, name FROM auth_group ORDER BY name" );
		$result = $query->execute();
		$groups = array();
		while ( $row = $result->fetchRow()) {
			$groups[] = $row["name"];
		}

		return $groups;
	}

	/**
	* @brief get a list of all users in a group
	* @returns array with user ids
	*/
	public function usersInGroup($gid, $search = '', $limit = -1, $offset = 0) {
		$query = OCP\DB::prepare('SELECT `auth_user`.`username`
  FROM `auth_user`
 INNER JOIN `auth_user_groups`
         ON (`auth_user`.`id` = `auth_user_groups`.`user_id`)
 INNER JOIN `auth_group`
         ON (`auth_group`.`id` = `auth_user_groups`.`group_id`)
 WHERE `auth_group`.`name` = ?
   AND `auth_user`.`is_active` = 1');
		$users  = array();
		$result = $query->execute(array($gid));
		while ($row=$result->fetchRow()) {
			$users[]=$row['username'];
		}
		return $users;
	}
}
