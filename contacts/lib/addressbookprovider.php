<?php
/**
 * ownCloud - AddressbookProvider
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus (thomas@tanghus.net)
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

namespace OCA\Contacts;

/**
 * This class manages our addressbooks.
 */
class AddressbookProvider implements \OCP\IAddressBook {

	const CONTACT_TABLE = '*PREFIX*contacts_cards';
	const PROPERTY_TABLE = '*PREFIX*contacts_cards_properties';

	/**
	 * Addressbook id
	 * @var integer
	 */
	public $id;
	
	/**
	 * Addressbook info array
	 * @var array
	 */
	public $addressbook;

	/**
	 * Constructor
	 * @param integer $id
	 */
	public function __construct($id) {
		$this->id = $id;
		\Sabre\VObject\Property::$classMap['GEO'] = 'Sabre\\VObject\\Property\\Compound';
	}
	
	public function getAddressbook() {
		if(!$this->addressbook) {
			$this->addressbook = Addressbook::find($this->id);
		}
		return $this->addressbook;
	}
	
	/**
	* @return string defining the technical unique key
	*/
	public function getKey() {
		$book = $this->getAddressbook();
		return $book['uri'];
	}

	/**
	* In comparison to getKey() this function returns a human readable (maybe translated) name
	* @return mixed
	*/
	public function getDisplayName() {
		$book = $this->getAddressbook();
		return $book['displayname'];
	}

	/**
	* @return mixed
	*/
	public function getPermissions() {
		$book = $this->getAddressbook();
		return $book['permissions'];
	}

	private function getProperty(&$results, $row) {
		if(!$row['name'] || !$row['value']) {
			return false;
		}

		$value = null;

		switch($row['name']) {
			case 'PHOTO':
				$value = 'VALUE=uri:' . \OCP\Util::linkToAbsolute('contacts', 'photo.php') . '?id=' . $row['contactid'];
				break;
			case 'N':
			case 'ORG':
			case 'ADR':
			case 'GEO':
			case 'CATEGORIES':
				$property = \Sabre\VObject\Property::create($row['name'], $row['value']);
				$value = $property->getParts();
				break;
			default:
				$value = $value = strtr($row['value'], array('\,' => ',', '\;' => ';'));
				break;
		}
		
		if(in_array($row['name'], App::$multi_properties)) {
			if(!isset($results[$row['contactid']])) {
				$results[$row['contactid']] = array('id' => $row['contactid'], $row['name'] => array($value));
			} elseif(!isset($results[$row['contactid']][$row['name']])) {
				$results[$row['contactid']][$row['name']] = array($value);
			} else {
				$results[$row['contactid']][$row['name']][] = $value;
			}
		} else {
			if(!isset($results[$row['contactid']])) {
				$results[$row['contactid']] = array('id' => $row['contactid'], $row['name'] => $value);
			} elseif(!isset($results[$row['contactid']][$row['name']])) {
				$results[$row['contactid']][$row['name']] = $value;
			}
		}
	}
	
	/**
	* @param $pattern
	* @param $searchProperties
	* @param $options
	* @return array|false
	*/
	public function search($pattern, $searchProperties, $options) {
		$ids = array();
		$results = array();
		$query = 'SELECT DISTINCT `contactid` FROM `' . self::PROPERTY_TABLE . '` WHERE (';
		$params = array();
		foreach($searchProperties as $property) {
			$params[] = $property;
			$params[] = '%' . $pattern . '%';
			$query .= '(`name` = ? AND `value` LIKE ?) OR ';
		}
		$query = substr($query, 0, strlen($query) - 4);
		$query .= ')';

		$stmt = \OCP\DB::prepare($query);
		$result = $stmt->execute($params);
		if (\OC_DB::isError($result)) {
			\OC_Log::write('contacts', __METHOD__ . 'DB error: ' . \OC_DB::getErrorMessage($result), 
				\OCP\Util::ERROR);
			return false;
		}
		while( $row = $result->fetchRow()) {
			$ids[] = $row['contactid'];
		}

		if(count($ids) > 0) {
			$query = 'SELECT `' . self::CONTACT_TABLE . '`.`addressbookid`, `' . self::PROPERTY_TABLE . '`.`contactid`, `' 
				. self::PROPERTY_TABLE . '`.`name`, `' . self::PROPERTY_TABLE . '`.`value` FROM `' 
				. self::PROPERTY_TABLE . '`,`' . self::CONTACT_TABLE . '` WHERE `'
				. self::CONTACT_TABLE . '`.`addressbookid` = \'' . $this->id . '\' AND `'
				. self::PROPERTY_TABLE . '`.`contactid` = `' . self::CONTACT_TABLE . '`.`id` AND `' 
				. self::PROPERTY_TABLE . '`.`contactid` IN (' . join(',', array_fill(0, count($ids), '?')) . ')';

			//\OC_Log::write('contacts', __METHOD__ . 'DB query: ' . $query, \OCP\Util::DEBUG);
			$stmt = \OCP\DB::prepare($query);
			$result = $stmt->execute($ids);
		}
		while( $row = $result->fetchRow()) {
			$this->getProperty($results, $row);
		}
		
		return $results;
	}

	/**
	* @param $properties
	* @return mixed
	*/
	public function createOrUpdate($properties) {
		$id = null;
		$vcard = null;
		if(array_key_exists('id', $properties)) {
			// TODO: test if $id belongs to this addressbook
			$id = $properties['id'];
			// TODO: Test $vcard
			$vcard = App::getContactVCard($properties['id']);
			foreach(array_keys($properties) as $name) {
				if(isset($vcard->{$name})) {
					unset($vcard->{$name});
				}
			}
		} else {
			$vcard = \Sabre\VObject\Component::create('VCARD');
			$uid = substr(md5(rand().time()), 0, 10);
			$vcard->add('UID', $uid);
			try {
				$id = VCard::add($this->id, $vcard, null, true);
			} catch(Exception $e) {
				\OC_Log::write('contacts', __METHOD__ . ' ' . $e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}

		foreach($properties as $name => $value) {
			switch($name) {
				case 'ADR':
				case 'N':
					if(is_array($value)) {
						$property = \Sabre\VObject\Property::create($name);
						$property->setParts($value);
						$vcard->add($property);
					} else {
						$vcard->{$name} = $value;
					}
					break;
				case 'BDAY':
					// TODO: try/catch
					$date = New \DateTime($value);
					$vcard->BDAY = $date->format('Y-m-d');
					$vcard->BDAY->VALUE = 'DATE';
					break;
				case 'EMAIL':
				case 'TEL':
				case 'IMPP': // NOTE: We don't know if it's GTalk, Jabber etc. only the protocol
				case 'URL':
					if(is_array($value)) {
						foreach($value as $val) {
							$vcard->add($name, strip_tags($val));
						}
					} else {
						$vcard->add($name, strip_tags($value));
					}
				default:
					$vcard->{$name} = $value;
					break;
			}
		}

		try {
			VCard::edit($id, $vcard);
		} catch(Exception $e) {
			\OC_Log::write('contacts', __METHOD__ . ' ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
		
		$asarray = VCard::structureContact($vcard);
		$asarray['id'] = $id;
		return $asarray;
	}

	/**
	* @param $id
	* @return mixed
	*/
	public function delete($id) {
		try {
			$query = 'SELECT * FROM `*PREFIX*contacts_cards` WHERE `id` = ? AND `addressbookid` = ?';
			$stmt = \OCP\DB::prepare($query);
			$result = $stmt->execute(array($id, $this->id));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__ . 'DB error: ' . \OC_DB::getErrorMessage($result), 
					\OCP\Util::ERROR);
				return false;
			}
			if($result->numRows() === 0) {
				\OC_Log::write('contacts', __METHOD__ 
					. 'Contact with id ' . $id . 'doesn\'t belong to addressbook with id ' . $this->id, 
					\OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ', exception: ' . $e->getMessage(), 
				\OCP\Util::ERROR);
			return false;
		}
		return VCard::delete($id);
	}
}
