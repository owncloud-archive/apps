<?php
/**
 * ownCloud - Database backend for Contacts
 *
 * @author Thomas Tanghus
 * @copyright 2013 Thomas Tanghus (thomas@tanghus.net)
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

namespace OCA\Contacts\Backend;

use OCA\Contacts\Contact;
use OCA\Contacts\VObject\VCard;
use Sabre\VObject\Reader;

/**
 * Subclass this class for Cantacts backends
 */

class Database extends AbstractBackend {

	public $name = 'database';
	static private $preparedQueries = array();

	/**
	* Sets up the backend
	*
	* @param string $addressBooksTableName
	* @param string $cardsTableName
	*/
	public function __construct(
		$userid = null,
		$addressBooksTableName = '*PREFIX*contacts_addressbooks',
		$cardsTableName = '*PREFIX*contacts_cards',
		$indexTableName = '*PREFIX*contacts_cards_properties'
	) {
		$this->userid = $userid ? $userid : \OCP\User::getUser();
		$this->addressBooksTableName = $addressBooksTableName;
		$this->cardsTableName = $cardsTableName;
		$this->indexTableName = $indexTableName;
		$this->addressbooks = array();
	}

	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * TODO: Create default if none exists.
	 *
	 * @param string $principaluri
	 * @return array
	 */
	public function getAddressBooksForUser($userid = null) {
		$userid = $userid ? $userid : $this->userid;

		try {
			if(!isset(self::$preparedQueries['addressbooksforuser'])) {
				$sql = 'SELECT `id`, `displayname`, `description`, `ctag` AS `lastmodified`, `userid` AS `owner` FROM `'
					. $this->addressBooksTableName
					. '` WHERE `userid` = ? ORDER BY `displayname`';
				self::$preparedQueries['addressbooksforuser'] = \OCP\DB::prepare($sql);
			}
			$result = self::$preparedQueries['addressbooksforuser']->execute(array($userid));
			if (\OC_DB::isError($result)) {
				\OCP\Util::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return $this->addressbooks;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.' exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return $this->addressbooks;
		}

		while( $row = $result->fetchRow()) {
			$row['permissions'] = \OCP\PERMISSION_ALL;
			$this->addressbooks[$row['id']] = $row;
		}
		return $this->addressbooks;
	}

	public function getAddressBook($addressbookid) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' id: '
		//	. $addressbookid, \OCP\Util::DEBUG);
		if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
			//print(__METHOD__ . ' ' . __LINE__ .' addressBookInfo: ' . print_r($this->addressbooks[$addressbookid], true));
			return $this->addressbooks[$addressbookid];
		}
		// Hmm, not found. Lets query the db.
		try {
			$query = 'SELECT `id`, `displayname`, `description`, `userid` AS `owner`, `ctag` AS `lastmodified` FROM `'
				. $this->addressBooksTableName
				. '` WHERE `id` = ?';
			if(!isset(self::$preparedQueries['getaddressbook'])) {
				self::$preparedQueries['getaddressbook'] = \OCP\DB::prepare($query);
			}
			$result = self::$preparedQueries['getaddressbook']->execute(array($addressbookid));
			if (\OC_DB::isError($result)) {
				\OCP\Util::write('contacts', __METHOD__. 'DB error: '
					. \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return array();
			}
			$row = $result->fetchRow();
			$row['permissions'] = \OCP\PERMISSION_ALL;
			return $row;
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.' exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			return array();
		}
		return array();
	}

	public function hasAddressBook($addressbookid) {
		if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
			return true;
		}
		return count($this->getAddressBook($addressbookid)) > 0;
	}

	/**
	 * Updates an addressbook's properties
	 *
	 * @param string $addressbookid
	 * @param array $changes
	 * @return bool
	 */
	public function updateAddressBook($addressbookid, array $changes) {
		if(count($changes) === 0) {
			return false;
		}

		$query = 'UPDATE `' . $this->addressBooksTableName . '` SET ';

		$updates = array();

		if(isset($changes['displayname'])) {
			$query .= '`displayname` = ?, ';
			$updates[] = $changes['displayname'];
			if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
				$this->addressbooks[$addressbookid]['displayname'] = $changes['displayname'];
			}
		}

		if(isset($changes['description'])) {
			$query .= '`description` = ?, ';
			$updates[] = $changes['description'];
			if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
				$this->addressbooks[$addressbookid]['description'] = $changes['description'];
			}
		}

		$query .= '`ctag` = `ctag` + 1 WHERE `id` = ?';
		$updates[] = $addressbookid;

		try {
			$stmt = \OCP\DB::prepare($query);
			$result = $stmt->execute($updates);
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts',
					__METHOD__. 'DB error: '
					. \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
		} catch(Exception $e) {
			\OCP\Util::writeLog('contacts',
				__METHOD__ . ', exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}

		return true;
	}

	/**
	 * Creates a new address book
	 *
	 * Supported properties are 'displayname', 'description' and 'uri'.
	 * 'uri' is supported to allow to add from CardDAV requests, and MUST
	 * be used for the 'uri' database field if present.
	 * 'displayname' MUST be present.
	 *
	 * @param array $properties
	 * @return string|false The ID if the newly created AddressBook or false on error.
	 */
	public function createAddressBook(array $properties, $userid = null) {
		$userid = $userid ? $userid : $this->userid;
		if(count($properties) === 0 || !isset($properties['displayname'])) {
			return false;
		}

		$query = 'INSERT INTO `' . $this->addressBooksTableName . '` '
			. '(`userid`,`displayname`,`uri`,`description`,`ctag`) VALUES(?,?,?,?,?)';

		$updates = array($userid, $properties['displayname']);
		$updates[] = isset($properties['uri'])
			? $properties['uri']
			: $this->createAddressBookURI($properties['displayname']);
		$updates[] = isset($properties['description']) ? $properties['description'] : '';
		$ctag = time();
		$updates[] = $ctag;

		try {
			if(!isset(self::$preparedQueries['createaddressbook'])) {
				self::$preparedQueries['createaddressbook'] = \OCP\DB::prepare($query);
			}
			$result = self::$preparedQueries['createaddressbook']->execute($updates);
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return false;
			}
		} catch(Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}

		$newid = \OCP\DB::insertid($this->addressBooksTableName);
		if($this->addressbooks) {
			$updates['id'] = $newid;
			$updates['ctag'] = $ctag;
			$updates['lastmodified'] = $ctag;
			$this->addressbooks[$addressbookid] = $updates;
		}
		return $newid;
	}

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * TODO: Delete contacts as well.
	 * @param string $addressbookid
	 * @return bool
	 */
	public function deleteAddressBook($addressbookid) {
		\OC_Hook::emit('OCA\Contacts', 'pre_deleteAddressBook',
			array('id' => $addressbookid)
		);

		// Clean up sharing
		\OCP\Share::unshareAll('addressbook', $addressbookid);

		// Get all contact ids for this address book
		$ids = array();
		$result = null;
		$stmt = \OCP\DB::prepare('SELECT `id` FROM `' . $this->cardsTableName . '`'
					. ' WHERE `addressbookid` = ?');
		try {
			$result = $stmt->execute(array($addressbookid));
			if (\OC_DB::isError($result)) {
				\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: '
					. \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return;
		}

		if(!is_null($result)) {
			while($id = $result->fetchOne()) {
				$ids[] = $id;
			}
		}

		// Purge contact property indexes
		$stmt = \OCP\DB::prepare('DELETE FROM `' . $this->indexTableName
			.'` WHERE `contactid` IN ('.str_repeat('?,', count($ids)-1).'?)');
		try {
			$stmt->execute($ids);
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
		}

		// Purge categories
		$catctrl = new \OC_VCategories('contact');
		$catctrl->purgeObjects($ids);

		if(!isset(self::$preparedQueries['deleteaddressbookcontacts'])) {
			self::$preparedQueries['deleteaddressbookcontacts'] =
				\OCP\DB::prepare('DELETE FROM `' . $this->cardsTableName
					. '` WHERE `addressbookid` = ?');
		}
		try {
			self::$preparedQueries['deleteaddressbookcontacts']
				->execute(array($addressbookid));
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}

		if(!isset(self::$preparedQueries['deleteaddressbook'])) {
			self::$preparedQueries['deleteaddressbook'] =
				\OCP\DB::prepare('DELETE FROM `'
					. $this->addressBooksTableName . '` WHERE `id` = ?');
		}
		try {
			self::$preparedQueries['deleteaddressbook']
				->execute(array($addressbookid));
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}

		if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
			unset($this->addressbooks[$addressbookid]);
		}

		return true;
	}

	/**
	 * @brief Updates ctag for addressbook
	 * @param integer $id
	 * @return boolean
	 */
	public function touchAddressBook($id) {
		$query = 'UPDATE `' . $this->addressBooksTableName
			. '` SET `ctag` = `ctag` + 1 WHERE `id` = ?';
		if(!isset(self::$preparedQueries['touchaddressbook'])) {
			self::$preparedQueries['touchaddressbook'] = \OCP\DB::prepare($query);
		}
		self::$preparedQueries['touchaddressbook']->execute(array($id));

		return true;
	}

	public function lastModifiedAddressBook($addressbookid) {
		if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
			return $this->addressbooks[$addressbookid]['lastmodified'];
		}
		$sql = 'SELECT MAX(`lastmodified`) FROM `' . $this->cardsTableName . '`, `' . $this->addressBooksTableName . '` ' .
			'WHERE  `' . $this->cardsTableName . '`.`addressbookid` = `*PREFIX*contacts_addressbooks`.`id` AND ' .
			'`' . $this->addressBooksTableName . '`.`userid` = ? AND `' . $this->addressBooksTableName . '`.`id` = ?';
		if(!isset(self::$preparedQueries['lastmodifiedaddressbook'])) {
			self::$preparedQueries['lastmodifiedaddressbook'] = \OCP\DB::prepare($sql);
		}
		$result = self::$preparedQueries['lastmodifiedaddressbook']->execute(array($this->userid, $addressbookid));
		if (\OC_DB::isError($result)) {
			\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
			return null;
		}
		return $result->fetchOne();
	}

	/**
	 * Returns the number of contacts in a specific address book.
	 *
	 * @param string $addressbookid
	 * @param bool $omitdata Don't fetch the entire carddata or vcard.
	 * @return array
	 */
	public function numContacts($addressbookid) {
		$query = 'SELECT COUNT(*) AS `count` FROM `' . $this->cardsTableName . '` WHERE '
			. '`addressbookid` = ?';

		if(!isset(self::$preparedQueries['count'])) {
			self::$preparedQueries['count'] = \OCP\DB::prepare($query);
		}
		$result = self::$preparedQueries['count']->execute(array($addressbookid));
		if (\OC_DB::isError($result)) {
			\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
			return null;
		}
		return (int)$result->fetchOne();
	}

	/**
	 * Returns all contacts for a specific addressbook id.
	 *
	 * @param string $addressbookid
	 * @param bool $omitdata Don't fetch the entire carddata or vcard.
	 * @return array
	 */
	public function getContacts($addressbookid, $limit = null, $offset = null, $omitdata = false) {
		$cards = array();
		try {
			$qfields = $omitdata ? '`id`, `fullname` AS `displayname`' : '*';
			$query = 'SELECT ' . $qfields . ' FROM `' . $this->cardsTableName
				. '` WHERE `addressbookid` = ? ORDER BY `fullname`';
			$stmt = \OCP\DB::prepare($query, $limit, $offset);
			$result = $stmt->execute(array($addressbookid));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return $cards;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return $cards;
		}

		if(!is_null($result)) {
			while($row = $result->fetchRow()) {
				$row['permissions'] = \OCP\PERMISSION_ALL;
				$cards[] = $row;
			}
		}

		return $cards;
	}

	/**
	 * Returns a specfic contact.
	 *
	 * The $id for Database and Shared backends can be an array containing
	 * either 'id' or 'uri' to be able to play seamlessly with the
	 * CardDAV backend.
	 * FIXME: $addressbookid isn't used in the query, so there's no access control.
	 * 	OTOH the groups backend - OC_VCategories - doesn't no about parent collections
	 * 	only object IDs. Hmm.
	 * 	I could make a hack and add an optional, not documented 'nostrict' argument
	 * 	so it doesn't look for addressbookid.
	 *
	 * @param string $addressbookid
	 * @param mixed $id Contact ID
	 * @return array|false
	 */
	public function getContact($addressbookid, $id, $noCollection = false) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' identifier: '
		//	. print_r($id, true), \OCP\Util::DEBUG);

		$ids = array($id);
		$where_query = '`id` = ?';
		if(is_array($id)) {
			$where_query = '';
			if(isset($id['id'])) {
				$id = $id['id'];
			} elseif(isset($id['uri'])) {
				$where_query = '`uri` = ?';
				$id = $id['uri'];
			} else {
				throw new \Exception(
					__METHOD__ . ' If second argument is an array, either \'id\' or \'uri\' has to be set.'
				);
			}
		}

		if(!$noCollection) {
			$where_query .= ' AND `addressbookid` = ?';
			$ids[] = $addressbookid;
		}

		try {
			$query =  'SELECT `id`, `carddata`, `lastmodified`, `fullname` AS `displayname` FROM `'
				. $this->cardsTableName . '` WHERE ' . $where_query;
			$stmt = \OCP\DB::prepare($query);
			$result = $stmt->execute($ids);
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '. $id, \OCP\Util::DEBUG);
			return false;
		}

		$row = $result->fetchRow();
		$row['permissions'] = \OCP\PERMISSION_ALL;
		return $row;
	}

	public function hasContact($addressbookid, $id) {
		return $this->getContact($addressbookid, $id) !== false;
	}

	/**
	 * Creates a new contact
	 *
	 * In the Database and Shared backends contact be either a Contact object or a string
	 * with carddata to be able to play seamlessly with the CardDAV backend.
	 * If this method is called by the CardDAV backend, the carddata is already validated.
	 * NOTE: It's assumed that this method is called either from the CardDAV backend, the
	 * import script, or from the ownCloud web UI in which case either the uri parameter is
	 * set, or the contact has a UID. If neither is set, it will fail.
	 *
	 * @param string $addressbookid
	 * @param mixed $contact
	 * @return string|bool The identifier for the new contact or false on error.
	 */
	public function createContact($addressbookid, $contact, $uri = null) {

		if(!$contact instanceof Contact) {
			try {
				$contact = Reader::read($contact);
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}

		try {
			$contact->validate(VCard::REPAIR|VCard::UPGRADE);
		} catch (\Exception $e) {
			OCP\Util::writeLog('contacts', __METHOD__ . ' ' .
				'Error validating vcard: ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}

		$uri = is_null($uri) ? $contact->UID . '.vcf' : $uri;
		$now = new \DateTime;
		$contact->REV = $now->format(\DateTime::W3C);

		$appinfo = \OCP\App::getAppInfo('contacts');
		$appversion = \OCP\App::getAppVersion('contacts');
		$prodid = '-//ownCloud//NONSGML '.$appinfo['name'].' '.$appversion.'//EN';
		$contact->PRODID = $prodid;

		$data = $contact->serialize();
		if(!isset(self::$preparedQueries['createcontact'])) {
		self::$preparedQueries['createcontact'] = \OCP\DB::prepare('INSERT INTO `'
			. $this->cardsTableName
			. '` (`addressbookid`,`fullname`,`carddata`,`uri`,`lastmodified`) VALUES(?,?,?,?,?)' );
		}
		try {
			$result = self::$preparedQueries['createcontact']
				->execute(
					array(
						$addressbookid,
						(string)$contact->FN,
						$contact->serialize(),
						$uri,
						time()
					)
				);
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
		$newid = \OCP\DB::insertid($this->cardsTableName);

		$this->touchAddressBook($addressbookid);
		\OC_Hook::emit('OCA\Contacts', 'post_createContact',
			array('id' => $newid, 'parent' => $addressbookid, 'contact' => $contact)
		);
		return (string)$newid;
	}

	/**
	 * Updates a contact
	 *
	 * @param string $addressbookid
	 * @param mixed $id Contact ID
	 * @param mixed $contact
	 * @see getContact
	 * @return bool
	 */
	public function updateContact($addressbookid, $id, $contact, $noCollection = false) {
		if(!$contact instanceof Contact) {
			try {
				$contact = Reader::read($contact);
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}
		$where_query = '`id` = ?';
		if(is_array($id)) {
			$where_query = '';
			if(isset($id['id'])) {
				$id = $id['id'];
				$qname = 'createcontactbyid';
			} elseif(isset($id['uri'])) {
				$where_query = '`id` = ?';
				$id = $id['uri'];
				$qname = 'createcontactbyuri';
			} else {
				throw new Exception(
					__METHOD__ . ' If second argument is an array, either \'id\' or \'uri\' has to be set.'
				);
			}
		} else {
			$qname = 'createcontactbyid';
		}

		$now = new \DateTime;
		$contact->REV = $now->format(\DateTime::W3C);

		$data = $contact->serialize();

		$updates = array($contact->FN, $data, time(), $id);
		if(!$noCollection) {
			$where_query .= ' AND `addressbookid` = ?';
			$updates[] = $addressbookid;
		}

		$query = 'UPDATE `' . $this->cardsTableName
				. '` SET `fullname` = ?,`carddata` = ?, `lastmodified` = ? WHERE ' . $where_query;
		if(!isset(self::$preparedQueries[$qname])) {
			self::$preparedQueries[$qname] = \OCP\DB::prepare($query);
		}
		try {
			$result = self::$preparedQueries[$qname]->execute($updates);
			if (\OC_DB::isError($result)) {
				\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id' . $id, \OCP\Util::DEBUG);
			return false;
		}

		$this->touchAddressBook($addressbookid);
		\OC_Hook::emit('OCA\Contacts', 'post_updateContact',
			array('id' => $id, 'parent' => $addressbookid, 'contact' => $contact)
		);
		return true;
	}

	/**
	 * Deletes a contact
	 *
	 * @param string $addressbookid
	 * @param string $id
	 * @see getContact
	 * @return bool
	 */
	public function deleteContact($addressbookid, $id) {
		$where_query = '`id` = ?';
		if(is_array($id)) {
			$where_query = '';
			if(isset($id['id'])) {
				$id = $id['id'];
				$qname = 'deletecontactsbyid';
			} elseif(isset($id['uri'])) {
				$where_query = '`id` = ?';
				$id = $id['uri'];
				$qname = 'deletecontactsbyuri';
			} else {
				throw new Exception(
					__METHOD__ . ' If second argument is an array, either \'id\' or \'uri\' has to be set.'
				);
			}
		} else {
			$qname = 'deletecontactsbyid';
		}
		\OC_Hook::emit('OCA\Contacts', 'pre_deleteContact',
			array('id' => $id)
		);
		if(!isset(self::$preparedQueries[$qname])) {
			self::$preparedQueries[$qname] = \OCP\DB::prepare('DELETE FROM `'
				. $this->cardsTableName
				. '` WHERE ' . $where_query . ' AND `addressbookid` = ?');
		}
		try {
			$result = self::$preparedQueries[$qname]->execute(array($id, $addressbookid));
			if (\OC_DB::isError($result)) {
				\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: '
					. \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '
				. $id, \OCP\Util::DEBUG);
			return false;
		}
		return true;
	}

	/**
	 * @brief Get the last modification time for a contact.
	 *
	 * Must return a UNIX time stamp or null if the backend
	 * doesn't support it.
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @returns int | null
	 */
	public function lastModifiedContact($addressbookid, $id) {
		$contact = $this->getContact($addressbookid, $id);
		return ($contact ? $contact['lastmodified'] : null);
	}

	private function createAddressBookURI($displayname, $userid = null) {
		$userid = $userid ? $userid : \OCP\User::getUser();
		$name = str_replace(' ', '_', strtolower($displayname));
		try {
			$stmt = \OCP\DB::prepare('SELECT `uri` FROM `' . $this->addressBooksTableName . '` WHERE `userid` = ? ');
			$result = $stmt->execute(array($userid));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return $name;
			}
		} catch(Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ' exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return $name;
		}
		$uris = array();
		while($row = $result->fetchRow()) {
			$uris[] = $row['uri'];
		}

		$newname = $name;
		$i = 1;
		while(in_array($newname, $uris)) {
			$newname = $name.$i;
			$i = $i + 1;
		}
		return $newname;
	}

}
