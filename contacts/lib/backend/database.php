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
		$cardsTableName = '*PREFIX*contacts_cards'
	) {
		$this->userid = $userid ? $userid : \OCP\User::getUser();
		$this->addressBooksTableName = $addressBooksTableName;
		$this->cardsTableName = $cardsTableName;
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
				self::$preparedQueries['addressbooksforuser'] = \OCP\DB::prepare( 'SELECT * FROM `'
					. $this->addressBooksTableName
					. '` WHERE `userid` = ? ORDER BY `displayname`' );
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
			$this->addressbooks[] = $row;
		}
		return $this->addressbooks;
	}

	public function getAddressBook($addressbookid) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' id: '
		//	. $addressbookid, \OCP\Util::DEBUG);
		if($this->addressbooks) {
			foreach($this->addressbooks as $addressbook) {
				if($addressbook['id'] === $addressbookid) {
					return $addressbook;
				}
			}
			// Hmm, not found. Lets query the db.
		}
		try {
			$query = 'SELECT * FROM `' . $this->addressBooksTableName
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
			return $result->fetchRow();
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.' exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			return array();
		}
		return array();
	}

	public function hasAddressBook($addressbookid) {
		if($this->addressbooks) {
			foreach($this->addressbooks as $addressbook) {
				if($addressbook['id'] === $addressbookid) {
					return true;
				}
			}
			return false;
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
		}

		if(isset($changes['description'])) {
			$query .= '`description` = ?, ';
			$updates[] = $changes['description'];
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
		if(count($properties) === 0) {
			return false;
		}

		$query = 'INSERT INTO `' . $this->addressBooksTableName . '` '
			. '(`userid`,`displayname`,`uri`,`description`,`ctag`) VALUES(?,?,?,?,?)';

		$updates = array($userid, $properties['displayname']);
		$updates[] = isset($properties['uri'])
			? $properties['uri']
			: $this->createAddressBookURI($properties['displayname']);
		$updates[] = isset($properties['description']) ? $properties['description'] : '';
		$updates[] = time();

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

		return \OCP\DB::insertid($this->addressBooksTableName);
	}

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * @param string $addressbookid
	 * @return bool
	 */
	public function deleteAddressBook($addressbookid) {
		\OC_Hook::emit('\OCA\Contacts', 'pre_deleteAddressBook',
			array('id' => $addressbookid)
		);

		if(!isset(self::$preparedQueries['deleteaddressbookcontacts'])) {
			self::$preparedQueries['deleteaddressbookcontacts'] =
				\OCP\DB::prepare('DELETE FROM `'
					. $this->cardsTableName
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
			while( $row = $result->fetchRow()) {
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
	 *
	 * @param string $addressbookid
	 * @param mixed $id Contact ID
	 * @return mixed
	 */
	public function getContact($addressbookid, $id) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' identifier: '
		//	. print_r($id, true), \OCP\Util::DEBUG);

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
		try {
			$stmt = \OCP\DB::prepare( 'SELECT * FROM `' . $this->cardsTableName . '` WHERE ' . $where_query );
			$result = $stmt->execute(array($id));
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

	/**
	 * Creates a new contact
	 *
	 * In the Database and Shared backends contact be either a Contact object or a string
	 * with carddata to be able to play seamlessly with the CardDAV backend.
	 * If this method is called by the CardDAV backend, the carddata is already validated.
	 *
	 * @param string $addressbookid
	 * @param mixed $contact
	 * @return bool
	 */
	public function createContact($addressbookid, $contact, $uri = null) {
		$uri = is_null($uri) ? $contact->UID . '.vcf' : $uri;

		if(!$contact instanceof Contact) {
			try {
				$contact = Reader::read($contact);
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}

		$now = new \DateTime;
		$contact->REV = $now->format(\DateTime::W3C);

		$data = $contact->serialize();
		if(!isset(self::$preparedQueries['createaddressbook'])) {
		self::$preparedQueries['createaddressbook'] = \OCP\DB::prepare('INSERT INTO `'
			. $this->cardsTableName
			. '` (`addressbookid`,`fullname`,`carddata`,`uri`,`lastmodified`) VALUES(?,?,?,?,?)' );
		}
		try {
			$result = self::$preparedQueries['createaddressbook']
				->execute(array($addressbookid, (string)$contact->FN, $contact->serialize(), $uri, time()));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
		$newid = \OCP\DB::insertid($this->cardsTableName);

		$this->touchAddressBook(addressbookid);
		\OC_Hook::emit('\OCA\Contacts', 'post_createContact',
			array('id' => $id, 'contact' => $contact)
		);
		return $newid;
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
	public function updateContact($addressbookid, $id, $contact) {
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

		$data = $card->serialize();
		$query = 'UPDATE `' . $this->cardsTableName
				. '` SET `fullname` = ?,`carddata` = ?, `lastmodified` = ? WHERE ' . $where_query;
		if(!isset(self::$preparedQueries[$qname])) {
			self::$preparedQueries[$qname] = \OCP\DB::prepare($query);
		}
		try {
			$result = self::$preparedQueries[$qname]->execute(array($contact->FN, $data, time(), $id));
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

		$this->touchAddressBook(addressbookid);
		\OC_Hook::emit('\OCA\Contacts', 'post_updateContact',
			array('id' => $id, 'contact' => $contact)
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
		\OC_Hook::emit('\OCA\Contacts', 'pre_deleteContact',
			array('id' => $id)
		);
		if(!isset(self::$preparedQueries[$qname])) {
			self::$preparedQueries[$qname] = \OCP\DB::prepare('DELETE FROM `'
				. $this->cardsTableName
				. '` WHERE ' . $where_query . ' AND `addressbookid` = ?');
		}
		try {
			self::$preparedQueries[$qname]->execute(array($id, $addressbookid));
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
