<?php
/**
 * Copyright (c) 2014 Christian Weiske <cweiske@cweiske.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\user_external;
use \OC_DB;

/**
 * Base class for external auth implementations that stores users
 * on their first login in a local table.
 * This is required for making many of the user-related ownCloud functions
 * work, including sharing files with them.
 *
 * @category Apps
 * @package  UserExternal
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
abstract class Base extends \OC_User_Backend{
	protected $backend = '';

	/**
	 * Create new instance, set backend name
	 *
	 * @param string $backend Identifier of the backend
	 */
	public function __construct($backend) {
		$this->backend = $backend;
	}

	/**
	 * Delete a user
	 *
	 * @param string $uid The username of the user to delete
	 *
	 * @return bool
	 */
	public function deleteUser($uid) {
		OC_DB::executeAudited(
			'DELETE FROM `*PREFIX*users_external` WHERE `uid` = ? AND `backend` = ?',
			array($uid, $this->backend)
		);
		return true;
	}

	/**
	 * Get display name of the user
	 *
	 * @param string $uid user ID of the user
	 *
	 * @return string display name
	 */
	public function getDisplayName($uid) {
		$user = OC_DB::executeAudited(
			'SELECT `displayname` FROM `*PREFIX*users_external`'
			. ' WHERE `uid` = ? AND `backend` = ?',
			array($uid, $this->backend)
		)->fetchRow();
		$displayName = trim($user['displayname'], ' ');
		if (!empty($displayName)) {
			return $displayName;
		} else {
			return $uid;
		}
	}

	/**
	 * Get a list of all display names and user ids.
	 *
	 * @return array with all displayNames (value) and the corresponding uids (key)
	 */
	public function getDisplayNames($search = '', $limit = null, $offset = null) {
		$result = OC_DB::executeAudited(
			array(
				'sql' => 'SELECT `uid`, `displayname` FROM `*PREFIX*users_external`'
					. ' WHERE (LOWER(`displayname`) LIKE LOWER(?) '
					. ' OR LOWER(`uid`) LIKE LOWER(?)) AND `backend` = ?',
				'limit'  => $limit,
				'offset' => $offset
			),
			array($search . '%', $search . '%', $this->backend)
		);

		$displayNames = array();
		while ($row = $result->fetchRow()) {
			$displayNames[$row['uid']] = $row['displayname'];
		}

		return $displayNames;
	}

	/**
	* Get a list of all users
	*
	* @return array with all uids
	*/
	public function getUsers($search = '', $limit = null, $offset = null) {
		$result = OC_DB::executeAudited(
			array(
				'sql' => 'SELECT `uid` FROM `*PREFIX*users_external`'
					. ' WHERE LOWER(`uid`) LIKE LOWER(?) AND `backend` = ?',
				'limit' => $limit,
				'offset' => $offset
			),
			array($search . '%', $this->backend)
		);
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['uid'];
		}
		return $users;
	}

	/**
	 * Determines if the backend can enlist users
	 *
	 * @return bool
	 */
	public function hasUserListings() {
		return true;
	}

	/**
	 * Change the display name of a user
	 *
	 * @param string $uid         The username
	 * @param string $displayName The new display name
	 *
	 * @return true/false
	 */
	public function setDisplayName($uid, $displayName) {
		if (!$this->userExists($uid)) {
			return false;
		}
		OC_DB::executeAudited(
			'UPDATE `*PREFIX*users_external` SET `displayname` = ?'
			. ' WHERE LOWER(`uid`) = ? AND `backend` = ?',
			array($displayName, $uid, $this->backend)
		);
		return true;
	}

	/**
	 * Create user record in database
	 *
	 * @param string $uid The username
	 *
	 * @return void
	 */
	protected function storeUser($uid)
	{
		if (!$this->userExists($uid)) {
			OC_DB::executeAudited(
				'INSERT INTO `*PREFIX*users_external` ( `uid`, `backend` )'
				. ' VALUES( ?, ? )',
				array($uid, $this->backend)
			);
		}
	}

	/**
	 * Check if a user exists
	 *
	 * @param string $uid the username
	 *
	 * @return boolean
	 */
	public function userExists($uid) {
		$result = OC_DB::executeAudited(
			'SELECT COUNT(*) FROM `*PREFIX*users_external`'
			. ' WHERE LOWER(`uid`) = LOWER(?) AND `backend` = ?',
			array($uid, $this->backend)
		);
		return $result->fetchOne() > 0;
	}
}
