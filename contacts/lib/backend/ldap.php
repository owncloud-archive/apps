<?php
/**
 * ownCloud - Base class for Contacts backends
 *
 * @author Thomas Tanghus, Nicolas Mora
 * @copyright 2013 Thomas Tanghus (thomas@tanghus.net)
 * @copyright 2013 Nicolas Mora (mail@babelouest.org)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.	If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Contacts\Backend;

use OCA\Contacts\Contact;
use OCA\Contacts\VObject\VCard;
use Sabre\VObject\Reader;
use OCA\Contacts\LDAP\Connector;

/**
 * Subclass this class for Cantacts backends
 */

class Ldap extends AbstractBackend {

	/**
	 * The name of the backend.
	 * @var string
	 */
	public $name = 'ldap';
	static private $preparedQueries = array();
	
	/**
	* Sets up the backend
	*
	* @param string $addressBooksTableName
	* @param string $cardsTableName
	*/
	public function __construct(
		$userid = null,
		$addressBooksTableName = '*PREFIX*contacts_ldap_addressbooks'
	) {
		$this->userid = $userid ? $userid : \OCP\User::getUser();
		$this->addressBooksTableName = $addressBooksTableName;
		$this->addressbooks = array();
	}

	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * The returned arrays MUST contain a unique 'id' for the
	 * backend and a 'displayname', and it MAY contain a
	 * 'description'.
	 *
	 * @param string $principaluri
	 * @return array
	 */
	public function getAddressBooksForUser($userid = null) {
		$userid = $userid ? $userid : $this->userid;

		try {
			if(!isset(self::$preparedQueries['addressbooksforuser'])) {
				$sql = 'SELECT `id` FROM `'
					. $this->addressBooksTableName
					. '` WHERE `owner` = ? ';
				self::$preparedQueries['addressbooksforuser'] = \OCP\DB::prepare($sql);
			}
			$result = self::$preparedQueries['addressbooksforuser']->execute(array($userid));
			if (\OC_DB::isError($result)) {
				\OCP\Util::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return $this->addressbooks;
			}
		} catch(\Exception $e) {
			\OC_Log::write('contacts', __METHOD__.' exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			return $this->addressbooks;
		}

		$this->addressbooks = array();
		while( $row = $result->fetchRow()) {
			$this->addressbooks[] = self::getAddressBook($row['id']);
		}
		return $this->addressbooks;
	}

	/**
	 * Get an addressbook's properties
	 *
	 * The returned array MUST contain 'displayname' and an integer 'permissions'
	 * value using there ownCloud CRUDS constants (which MUST be at least
	 * \OCP\PERMISSION_READ).
	 * Currently the only ones supported are 'displayname' and
	 * 'description', but backends can implement additional.
	 *
	 * @param string $addressbookid
	 * @return array $properties
	 */
	public function getAddressBook($addressbookid) {
		//\OC_Log::write('contacts', __METHOD__.' id: '
		//	. $addressbookid, \OC_Log::DEBUG);
		if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
			//print(__METHOD__ . ' ' . __LINE__ .' addressBookInfo: ' . print_r($this->addressbooks[$addressbookid], true));
			return $this->addressbooks[$addressbookid];
		}
		// Hmm, not found. Lets query the db.
		try {
			$query = 'SELECT `id`, `displayname`, `description`, `owner`, `uri` FROM `'
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
			$row['permissions'] = \OCP\PERMISSION_READ;
			$row['lastmodified'] = self::lastModifiedAddressBook($addressbookid);
			return $row;
		} catch(\Exception $e) {
			\OC_Log::write('contacts', __METHOD__.' exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			return array();
		}
		return array();
	}

	/**
	 * Test if the address book exists
	 * @return bool
	 */
	public function hasAddressBook($addressbookid) {
		if($this->addressbooks && isset($this->addressbooks[$addressbookid])) {
			return true;
		}
		return count($this->getAddressBook($addressbookid)) > 0;
	}

	/**
	 * Updates an addressbook's properties
	 *
	 * The $properties array contains the changes to be made.
	 *
	 * Currently the only ones supported are 'displayname' and
	 * 'description', but backends can implement additional.
	 *
	 * @param string $addressbookid
	 * @param array $properties
	 * @return bool
	 */
	public function updateAddressBook($addressbookid, array $properties) {
		// Need these ones for checking uri
		$addressbook = self::getAddressBook($id);
		$name = $addressbook['name'];
		$description = $addressbook['description'];

		try {
			$stmt = \OCP\DB::prepare('UPDATE `'.$this->addressBooksTableName.'` SET `displayname`=?,`description`=?, `ldapurl`=?,`ldapbasedn`=?,`ldapuser`=?,`ldappass`=?,`ldapanonymous`=?,`ldapreadonly`=?	WHERE `id`=?');
			$result = $stmt->execute(array($name,$properties['description'],$properties['ldapurl'],$properties['ldapbasedn'],$properties['ldapuser'],$properties['ldappass'],$properties['ldapanonymous'],$properties['ldapreadonly'],$properties['id']));
		} catch(Exception $e) {
			OCP\Util::writeLog('contacts_ldap', __CLASS__.'::'.__METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			OCP\Util::writeLog('contacts_ldap', __CLASS__.'::'.__METHOD__.', id: '.$id, \OC_Log::DEBUG);
			throw new Exception(
				OC_Contacts_App_Ldap::$l10n->t(
					'There was an error updating the addressbook.'
				)
			);
		}

		return true;
	}

	/**
	 * Creates a new address book
	 *
	 * Currently the only ones supported are 'displayname' and
	 * 'description', but backends can implement additional.
	 * 'displayname' MUST be present.
	 *
	 * @param array $properties
	 * @return string|false The ID if the newly created AddressBook or false on error.
	 */
	public function createAddressBook(array $properties) {
		try {
			$stmt = \OCP\DB::prepare( 'SELECT `uri` FROM `'.$this->addressBooksTableName.'` WHERE `owner` = ? ' );
			$result = $stmt->execute(array($uid));
		} catch(Exception $e) {
			OCP\Util::writeLog('contacts_ldap', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), \OCP\Util::ERROR);
			OCP\Util::writeLog('contacts_ldap', __CLASS__.'::'.__METHOD__.' uid: '.$uid, \OC_Log::DEBUG);
			return false;
		}
		$uris = array();
		while($row = $result->fetchRow()) {
			$uris[] = $row['uri'];
		}

		$uri = self::createURI($name, $uris );
		try {
			$stmt = \OCP\DB::prepare( 'INSERT INTO `'.$this->addressBooksTableName.'` (`owner`,`displayname`,`uri`,`description`,`ldapurl`,`ldapbasedn`,`ldapuser`,`ldappass`,`ldapanonymous`,`ldapreadonly`) VALUES(?,?,?,?,?,?,?,?,?,?,?)' );
			$result = $stmt->execute(array($name,$properties['description'],$properties['ldapurl'],$properties['ldapbasedn'],$properties['ldapuser'],$properties['ldappass'],$properties['ldapanonymous'],$properties['ldapreadonly']));
		} catch(Exception $e) {
			OCP\Util::writeLog('contacts_ldap', __CLASS__.'::'.__METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			OCP\Util::writeLog('contacts_ldap', __CLASS__.'::'.__METHOD__.', uid: '.$uid, \OC_Log::DEBUG);
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
		$addressbook = self::getAddressBook($addressbookid);

		try {
			$stmt = \OCP\DB::prepare('DELETE FROM `'.$this->addressBooksTableName.'` WHERE `id` = ?');
			$stmt->execute(array($id));
		} catch(Exception $e) {
			OCP\Util::writeLog('contacts_ldap',
				__METHOD__.', exception for ' . $addressbookid . ': '
				. $e->getMessage(),
				OCP\Util::ERROR);
			throw new Exception(
				OC_Contacts_App_Ldap::$l10n->t(
					'There was an error deleting this addressbook.'
				)
			);
		}

		OCP\Share::unshareAll('addressbook_ldap', $addressbookid);

		return true;
	}

	/**
	 * @brief Get the last modification time for an address book.
	 *
	 * Must return a UNIX time stamp or null if the backend
	 * doesn't support it.
	 *
	 * TODO: Implement default methods get/set for backends that
	 * don't support.
	 * @param string $addressbookid
	 * @returns int | null
	 */
	public function lastModifiedAddressBook($addressbookid) {
		$datetime = new \DateTime('now');
		return $datetime->format(\DateTime::W3C);
		// TODO use ldap_sort and get the last element
	}

	/**
	 * Returns all contacts for a specific addressbook id.
	 *
	 * The returned array MUST contain the unique ID of the contact mapped to 'id', a
	 * displayname mapped to 'displayname' and an integer 'permissions' value using there
	 * ownCloud CRUDS constants (which MUST be at least \OCP\PERMISSION_READ), and SHOULD
	 * contain the properties of the contact formatted as a vCard 3.0
	 * https://tools.ietf.org/html/rfc2426 mapped to 'carddata' or as an
	 * \OCA\Contacts\VObject\VCard object mapped to 'vcard'.
	 *
	 * Example:
	 *
	 * array(
	 *	 0 => array('id' => '4e111fef5df', 'permissions' => 1, 'displayname' => 'John Q. Public', 'vcard' => $object),
	 *	 1 => array('id' => 'bbcca2d1535', 'permissions' => 32, 'displayname' => 'Jane Doe', 'carddata' => $data)
	 * );
	 *
	 * For contacts that contain loads of data, the 'carddata' or 'vcard' MAY be omitted
	 * as it can be fetched later.
	 *
	 * TODO: Some sort of ETag?
	 *
	 * @param string $addressbookid
	 * @param bool $omitdata Don't fetch the entire carddata or vcard.
	 * @return array
	 */
	public function getContacts($addressbookid, $limit = null, $offset = null, $omitdata = false) {
		$cards = array();
		if(is_array($addressbookid) && count($addressbookid)) {
			$id_array = $addressbookid;
		} elseif(is_int($addressbookid) || is_string($addressbookid)) {
			$id_array = array($addressbookid);
		} else {
			\OC_Log::write('contacts_ldap', __METHOD__.'. Addressbook id(s) argument is empty: '. print_r($id, true), \OC_Log::DEBUG);
			return false;
		}
		
		foreach ($id_array as $one_id) {
			$param_array = self::getLdapParams($one_id);
			if ($param_array) {
				$connector = new Connector($param_array['ldap_vcard_connector']);
				//OCP\Util::writeLog('contacts_ldap', __METHOD__.' Connector OK', \OC_Log::DEBUG);
				$cards = array_merge(
					$cards,
					self::getEntries($one_id,
						$connector,
						$param_array['ldapurl'],
						$param_array['ldapbasedn'],
						'(objectclass=inetOrgPerson)',
						$param_array['ldapuser'],
						$param_array['ldappass'],
						$param_array['ldapanonymous'],
						$param_array['ldapreadonly'],
						$offset,
						$limit
					)
				);
				//OCP\Util::writeLog('contacts_ldap', __METHOD__.' counts '.count($cards), \OC_Log::DEBUG);
			}
		}
		return $cards;
	}
	
	/**
	 * @brief Returns all vcards corresponding to the bindsearch
	 * @param string $ldapurl
	 * @param string $ldapbasedn
	 * @param string $bindsearch
	 * @param string $ldapuser
	 * @param string $ldappass
	 * @param boolean $ldapanonymous
	 * @param int $start
	 * @param int $num
	 * @return array|false
	 */
	public function getEntries($aid, $connector, $ldapurl, $ldapbasedn, $bindsearch, $ldapuser='', $ldappass='', $ldapanonymous=false, $ldapreadonly=false, $start=null, $num=null) {
		if ($start==null) {
			$start=0;
		}
		
		if ($num==null) {
			$num=PHP_INT_MAX;
		}

		// ldap connect
		$ldapconn = ldap_connect($ldapurl);
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		if ($ldapconn) {
			// ldap bind
			if ($ldapanonymous) {
				$ldapbind = ldap_bind($ldapconn);
				// \OC_Log::write('vcard_ldap', __METHOD__.', anonymous bind', \OC_Log::DEBUG);
			} else {
				$ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);
				\OC_Log::write('vcard_ldap', __METHOD__.", log in and bind as '$ldapuser'", \OC_Log::DEBUG);
				//error_log('vcard_ldap'.__METHOD__.", log in and bind as '$ldapuser', pass='$ldappass'");
			}
			
			if ($ldapbind) {
				// \OC_Log::write('vcard_ldap', __METHOD__.', bind OK', \OC_Log::DEBUG);
				// ldap search
				$ldap_results = ldap_search ($ldapconn, $ldapbasedn, $bindsearch, $connector->getLdapEntries(), 0, max(($start+$num), LDAP_OPT_SIZELIMIT));
				$info = ldap_get_entries($ldapconn, $ldap_results);
				
				//OCP\Util::writeLog('vcard_ldap', __METHOD__.', search size '.$info["count"], \OC_Log::DEBUG);
				// parse results
				$cards = array();
				for ($i=$start; $i<$info["count"] && $i<$num; $i++) {
					$a_card = $connector->ldapToVCard($info[$i]);
					$cards[] = self::getSabreFormatCard($aid, $a_card);
				}
				
				// ldap close
				ldap_unbind($ldapconn);
				
				return $cards;
			} else {
				\OC_Log::write('vcard_ldap', __METHOD__.', can not bind', \OC_Log::DEBUG);
				return false;
			}
		}
		\OC_Log::write('vcard_ldap', __METHOD__.', can not connect', \OC_Log::DEBUG);
		return false;
	}
	
	public function getLdapParams($aid) {
		//error_log(__METHOD__.', id: '.$id);
		\OC_Log::write('contacts_ldap', __METHOD__.', id: '.$aid, \OC_Log::DEBUG);
		$sql = 'SELECT * FROM `'.$this->addressBooksTableName.'` WHERE `id` = ? ';
		try {
			$stmt = \OCP\DB::prepare($sql);
			$result = $stmt->execute(array($aid));
		} catch(Exception $e) {
			\OC_Log::write('contacts_ldap', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OC_Log::write('contacts_ldap', __METHOD__.', id: '.$aid, \OC_Log::DEBUG);
			\OC_Log::write('contacts_ldap', __METHOD__.'SQL:'.$sql, \OC_Log::DEBUG);
			return false;
		}
		
		$cards = array();
		if(!is_null($result)) {
			while( $row = $result->fetchRow() ) {
				return array(
					'ldapurl' => $row['ldapurl'],
					'ldapbasedn' => $row['ldapbasedn'],
					'ldapuser' => $row['ldapuser'],
					'ldappass' => base64_decode($row['ldappass']),
					'ldapanonymous' => $row['ldapanonymous'],
					'ldapreadonly' => $row['ldapreadonly'],
					'ldap_vcard_connector' => $row['ldap_vcard_connector']
				);
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Returns a specfic contact.
	 *
	 * Same as getContacts except that either 'carddata' or 'vcard' is mandatory.
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @return array|bool
	 */
	public function getContact($addressbookid, $ids) {
		foreach ($ids as $id) {
			$cid = html_entity_decode(base64_decode(substr($id, 0, count($id)-4)));
			$param_array = self::getLdapParams($addressbookid);
			if ($param_array) {
				$connector = new Connector($param_array['ldap_vcard_connector']);
				\OC_Log::write('contacts_ldap', __METHOD__.' Connector OK', \OC_Log::DEBUG);
				$entry = self::getEntries(
					$addressbookid,
					$connector,
					$param_array['ldapurl'],
					$cid,
					'(objectclass=inetOrgPerson)',
					$param_array['ldapuser'],
					$param_array['ldappass'],
					$param_array['ldapanonymous'],
					$param_array['ldapreadonly']
				);
				if (count($entry)>0) {
					return $entry[0];
				}
			}
		}
		return false;
	}

	/**
	 * @brief construct a vcard in Sabre format
	 * @param integer $aid Addressbook Id
	 * @param OC_VObject $card VCard
	 * @return array
	 */
	public static function getSabreFormatCard($aid, $vcard) {
		/*
		 * array return format :
     * array( 'id' => 'bbcca2d1535', 
     *        'permissions' => 32, 
     *        'displayname' => 'Jane Doe', 
     *        'carddata' => $data)
		 */
		$FN = (string)$vcard->FN;
		$UID = (string)$vcard->UID;
		$REV = (string)$vcard->REV;
		return array('id' => $UID,
			'permissions' => \OCP\PERMISSION_READ,
			'displayname' => $FN,
			'carddata' => $vcard->serialize(),
			'uri' => base64_encode($UID) . '.vcf',
			'lastmodified' => $REV
		);
	}

	/**
	 * Creates a new contact
	 *
	 * @param string $addressbookid
	 * @param string $carddata
	 * @return string|bool The identifier for the new contact or false on error.
	 */
	public function createContact($addressbookid, $carddata) {
		// TODO
	}

	/**
	 * Updates a contact
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @param string $carddata
	 * @return bool
	 */
	public function updateContact($addressbookid, $id, $carddata) {
		// TODO
	}

	/**
	 * Deletes a contact
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @return bool
	 */
	public function deleteContact($addressbookid, $id) {
		// TODO
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
		$contact = getContact($addrebookid, $id);
		if ($contact != null) {
			return $contact['lastmodified'];
		} else {
			return null;
		}
	}
}
