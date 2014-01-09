<?php
namespace OCA\user_external;
/**
 * Copyright (c) 2014 Christian Weiske <cweiske@cweiske.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
use \OC_DB;

/**
 * Base class for external auth implementations that stores users
 * on their first login in a local table.
 * This is required for making many of the user-related owncloud functions
 * work, including sharing files with them.
 */
abstract class Base extends \OC_User_Backend{
	protected $backend = '';

	public function __construct($backend) {
		$this->backend = $backend;
	}

	/**
	 * @brief delete a user
	 * @param string $uid The username of the user to delete
	 * @return bool
	 *
	 * Deletes a user
	 */
	public function deleteUser($uid) {
		$query = OC_DB::prepare('DELETE FROM `*PREFIX*users_external` WHERE `uid` = ? AND backend = ?');
		$query->execute(array($uid, $this->backend));
		return true;
	}

	/**
	 * @brief get display name of the user
	 * @param $uid user ID of the user
	 * @return string display name
	 */
	public function getDisplayName($uid) {
		$query = OC_DB::prepare('SELECT `displayname` FROM `*PREFIX*users_external` WHERE `uid` = ? AND backend = ?');
		$result = $query->execute(array($uid, $this->backend))->fetchAll();
		$displayName = trim($result[0]['displayname'], ' ');
		if (!empty($displayName)) {
			return $displayName;
		} else {
			return $uid;
		}
	}

	/**
	 * @brief Get a list of all display names
	 * @returns array with  all displayNames (value) and the correspondig uids (key)
	 *
	 * Get a list of all display names and user ids.
	 */
	public function getDisplayNames($search = '', $limit = null, $offset = null) {
		$displayNames = array();
		$query = OC_DB::prepare('SELECT `uid`, `displayname` FROM `*PREFIX*users_external`'
			. ' WHERE (LOWER(`displayname`) LIKE LOWER(?) OR '
			. 'LOWER(`uid`) LIKE LOWER(?)) AND backend = ?', $limit, $offset);
		$result = $query->execute(array($search . '%', $search . '%', $this->backend));
		$users = array();
		while ($row = $result->fetchRow()) {
			$displayNames[$row['uid']] = $row['displayname'];
		}

		return $displayNames;
	}

	/**
	* @brief Get a list of all users
	* @returns array with all uids
	*
	* Get a list of all users.
	*/
	public function getUsers($search = '', $limit = null, $offset = null) {
		$query = OC_DB::prepare('SELECT `uid` FROM `*PREFIX*users_external` WHERE LOWER(`uid`) LIKE LOWER(?) AND backend = ?', $limit, $offset);
		$result = $query->execute(array($search . '%', $this->backend));
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['uid'];
		}
		return $users;
	}

	/**
	 * @return bool
	 */
	public function hasUserListings() {
		return true;
	}

	/**
	 * @brief Set display name
	 * @param $uid The username
	 * @param $displayName The new display name
	 * @returns true/false
	 *
	 * Change the display name of a user
	 */
	public function setDisplayName($uid, $displayName) {
		if ($this->userExists($uid)) {
			$query = OC_DB::prepare('UPDATE `*PREFIX*users_external` SET `displayname` = ? WHERE LOWER(`uid`) = ? AND backend = ?');
			$query->execute(array($displayName, $uid, $this->backend));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief Create user record in database
	 * @param $uid The username
	 * @returns void
	 */
	protected function storeUser($uid)
	{
		if (!$this->userExists($uid)) {
			$query = OC_DB::prepare('INSERT INTO `*PREFIX*users_external` ( `uid`, `backend` ) VALUES( ?, ? )');
			$result = $query->execute(array($uid, $this->backend));
		}
	}

	/**
	 * @brief check if a user exists
	 * @param string $uid the username
	 * @return boolean
	 */
	public function userExists($uid) {
		$query = OC_DB::prepare('SELECT COUNT(*) FROM `*PREFIX*users_external` WHERE LOWER(`uid`) = LOWER(?) AND backend = ?');
		$result = $query->execute(array($uid, $this->backend));
		if (OC_DB::isError($result)) {
			OC_Log::write('user_external', OC_DB::getErrorMessage($result), OC_Log::ERROR);
			return false;
		}
		return $result->fetchOne() > 0;
	}
}
?>
