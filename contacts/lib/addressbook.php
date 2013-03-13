<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
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
/*
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE contacts_addressbooks (
 * id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 * userid VARCHAR(255) NOT NULL,
 * displayname VARCHAR(255),
 * uri VARCHAR(100),
 * description TEXT,
 * ctag INT(11) UNSIGNED NOT NULL DEFAULT '1'
 * );
 *
 */

namespace OCA\Contacts;

/**
 * This class manages our addressbooks.
 */
class Addressbook {
	/**
	 * @brief Returns the list of addressbooks for a specific user.
	 * @param string $uid
	 * @param boolean $active Only return addressbooks with this $active state, default(=false) is don't care
	 * @param boolean $shared Whether to also return shared addressbook. Defaults to true.
	 * @return array or false.
	 */
	public static function all($uid, $active = false, $shared = true) {
		$values = array($uid);
		$active_where = '';
		if ($active) {
			$active_where = ' AND `active` = ?';
			$values[] = 1;
		}
		try {
			$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*contacts_addressbooks` WHERE `userid` = ? ' . $active_where . ' ORDER BY `displayname`' );
			$result = $stmt->execute($values);
			if (\OC_DB::isError($result)) {
				\OCP\Util::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.' exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.' uid: '.$uid, \OCP\Util::DEBUG);
			return false;
		}

		$addressbooks = array();
		while( $row = $result->fetchRow()) {
			$row['permissions'] = \OCP\PERMISSION_ALL;
			$addressbooks[] = $row;
		}

		if($shared === true) {
			$addressbooks = array_merge($addressbooks, \OCP\Share::getItemsSharedWith('addressbook', Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS));
		}
		if(!$active && !count($addressbooks)) {
			$id = self::addDefault($uid);
			return array(self::find($id),);
		}
		return $addressbooks;
	}

	/**
	 * @brief Get active addressbook IDs for a user.
	 * @param integer $uid User id. If null current user will be used.
	 * @return array
	 */
	public static function activeIds($uid = null) {
		if(is_null($uid)) {
			$uid = \OCP\USER::getUser();
		}

		// query all addressbooks to force creation of default if it desn't exist.
		$activeaddressbooks = self::all($uid);
		$ids = array();
		foreach($activeaddressbooks as $addressbook) {
			if($addressbook['active']) {
				$ids[] = $addressbook['id'];
			}
		}
		return $ids;
	}

	/**
	 * @brief Returns the list of active addressbooks for a specific user.
	 * @param string $uid
	 * @return array
	 */
	public static function active($uid) {
		return self::all($uid, true);
	}

	/**
	 * @brief Returns the list of addressbooks for a principal (DAV term of user)
	 * @param string $principaluri
	 * @return array
	 */
	public static function allWherePrincipalURIIs($principaluri) {
		$uid = self::extractUserID($principaluri);
		return self::all($uid);
	}

	/**
	 * @brief Gets the data of one address book
	 * @param integer $id
	 * @return associative array or false.
	 */
	public static function find($id) {
		try {
			$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*contacts_addressbooks` WHERE `id` = ?' );
			$result = $stmt->execute(array($id));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: ' . $id, \OCP\Util::DEBUG);
			return false;
		}
		$row = $result->fetchRow();

		if($row['userid'] != \OCP\USER::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource('addressbook', $id);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_READ)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to read this addressbook.'
					)
				);
			}
			$row['permissions'] = $sharedAddressbook['permissions'];
		} else {
			$row['permissions'] = \OCP\PERMISSION_ALL;
		}
		return $row;
	}

	/**
	 * @brief Adds default address book
	 * @return $id ID of the newly created addressbook or false on error.
	 */
	public static function addDefault($uid = null) {
		if(is_null($uid)) {
			$uid = \OCP\USER::getUser();
		}
		$id = self::add($uid, 'Contacts', 'Default Address Book');
		if($id !== false) {
			self::setActive($id, true);
		}
		return $id;
	}

	/**
	 * @brief Creates a new address book
	 * @param string $userid
	 * @param string $name
	 * @param string $description
	 * @return insertid
	 */
	public static function add($uid,$name,$description='') {
		try {
			$stmt = \OCP\DB::prepare( 'SELECT `uri` FROM `*PREFIX*contacts_addressbooks` WHERE `userid` = ? ' );
			$result = $stmt->execute(array($uid));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ' exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__ . ' uid: ' . $uid, \OCP\Util::DEBUG);
			return false;
		}
		$uris = array();
		while($row = $result->fetchRow()) {
			$uris[] = $row['uri'];
		}

		$uri = self::createURI($name, $uris );
		try {
			$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*contacts_addressbooks` (`userid`,`displayname`,`uri`,`description`,`ctag`) VALUES(?,?,?,?,?)' );
			$result = $stmt->execute(array($uid,$name,$uri,$description,1));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', uid: '.$uid, \OCP\Util::DEBUG);
			return false;
		}

		return \OCP\DB::insertid('*PREFIX*contacts_addressbooks');
	}

	/**
	 * @brief Creates a new address book from the data sabredav provides
	 * @param string $principaluri
	 * @param string $uri
	 * @param string $name
	 * @param string $description
	 * @return insertid or false
	 */
	public static function addFromDAVData($principaluri, $uri, $name, $description) {
		$uid = self::extractUserID($principaluri);

		try {
			$stmt = \OCP\DB::prepare('INSERT INTO `*PREFIX*contacts_addressbooks` '
				. '(`userid`,`displayname`,`uri`,`description`,`ctag`) VALUES(?,?,?,?,?)');
			$result = $stmt->execute(array($uid, $name, $uri, $description, 1));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', uid: ' . $uid, \OCP\Util::DEBUG);
			\OCP\Util::writeLog('contacts', __METHOD__.', uri: ' . $uri, \OCP\Util::DEBUG);
			return false;
		}

		return \OCP\DB::insertid('*PREFIX*contacts_addressbooks');
	}

	/**
	 * @brief Edits an addressbook
	 * @param integer $id
	 * @param string $name
	 * @param string $description
	 * @return boolean
	 */
	public static function edit($id,$name,$description) {
		// Need these ones for checking uri
		$addressbook = self::find($id);
		if ($addressbook['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(OCP\User::getUser(), 'admin')) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource('addressbook', $id);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to update this addressbook.'
					)
				);
			}
		}
		if(is_null($name)) {
			$name = $addressbook['name'];
		}
		if(is_null($description)) {
			$description = $addressbook['description'];
		}

		try {
			$stmt = \OCP\DB::prepare('UPDATE `*PREFIX*contacts_addressbooks` SET `displayname`=?,`description`=?, `ctag`=`ctag`+1 WHERE `id`=?');
			$result = $stmt->execute(array($name,$description,$id));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				throw new \Exception(
					App::$l10n->t(
						'There was an error updating the addressbook.'
					)
				);
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__ . ', id: ' . $id, \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'There was an error updating the addressbook.'
				)
			);
		}

		return true;
	}

	/**
	 * @brief Activates an addressbook
	 * @param integer $id
	 * @param boolean $active
	 * @return boolean
	 */
	public static function setActive($id,$active) {
		$sql = 'UPDATE `*PREFIX*contacts_addressbooks` SET `active` = ? WHERE `id` = ?';

		try {
			$stmt = \OCP\DB::prepare($sql);
			$stmt->execute(array(intval($active), $id));
			return true;
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ', exception for ' . $id.': ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
	}

	/**
	 * @brief Checks if an addressbook is active.
	 * @param integer $id ID of the address book.
	 * @return boolean
	 */
	public static function isActive($id) {
		$sql = 'SELECT `active` FROM `*PREFIX*contacts_addressbooks` WHERE `id` = ?';
		try {
			$stmt = \OCP\DB::prepare( $sql );
			$result = $stmt->execute(array($id));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
			$row = $result->fetchRow();
			return (bool)$row['active'];
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
	}

	/**
	 * @brief removes an address book
	 * @param integer $id
	 * @return boolean true on success, otherwise an exception will be thrown
	 */
	public static function delete($id) {
		$addressbook = self::find($id);

		if ($addressbook['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource('addressbook', $id);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_DELETE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to delete this addressbook.'
					)
				);
			}
		}

		// First delete cards belonging to this addressbook.
		$cards = VCard::all($id);
		foreach($cards as $card) {
			try {
				VCard::delete($card['id']);
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts',
					__METHOD__.', exception deleting vCard '.$card['id'].': '
					. $e->getMessage(),
					\OCP\Util::ERROR);
			}
		}

		try {
			$stmt = \OCP\DB::prepare('DELETE FROM `*PREFIX*contacts_addressbooks` WHERE `id` = ?');
			$stmt->execute(array($id));
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts',
				__METHOD__.', exception for ' . $id . ': '
				. $e->getMessage(),
				\OCP\Util::ERROR);
			throw new \Exception(
				App::$l10n->t(
					'There was an error deleting this addressbook.'
				)
			);
		}

		\OCP\Share::unshareAll('addressbook', $id);

		if(count(self::all(\OCP\User::getUser())) == 0) {
			self::addDefault();
		}

		return true;
	}

	/**
	 * @brief Updates ctag for addressbook
	 * @param integer $id
	 * @return boolean
	 */
	public static function touch($id) {
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*contacts_addressbooks` SET `ctag` = `ctag` + 1 WHERE `id` = ?' );
		$stmt->execute(array($id));

		return true;
	}

	/**
	 * @brief Creates a URI for Addressbook
	 * @param string $name name of the addressbook
	 * @param array  $existing existing addressbook URIs
	 * @return string new name
	 */
	public static function createURI($name,$existing) {
		$name = str_replace(' ', '_', strtolower($name));
		$newname = $name;
		$i = 1;
		while(in_array($newname, $existing)) {
			$newname = $name.$i;
			$i = $i + 1;
		}
		return $newname;
	}

	/**
	 * @brief gets the userid from a principal path
	 * @return string
	 */
	public static function extractUserID($principaluri) {
		list($prefix, $userid) = \Sabre_DAV_URLUtil::splitPath($principaluri);
		return $userid;
	}
}
