<?php
/**
 * ownCloud - Contact object
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

use Sabre\VObject\Property;

/**
 * Subclass this class or implement IPIMObject interface for PIM objects
 */

class Contact extends VObject\VCard implements IPIMObject {

	const THUMBNAIL_PREFIX = 'contact-thumbnail-';
	const THUMBNAIL_SIZE = 28;

	/**
	 * The name of the object type in this case VCARD.
	 *
	 * This is used when serializing the object.
	 *
	 * @var string
	 */
	public $name = 'VCARD';

	protected $props = array();

	/**
	 * Create a new Contact object
	 *
	 * @param AddressBook $parent
	 * @param AbstractBackend $backend
	 * @param mixed $data
	 */
	public function __construct($parent, $backend, $data = null) {
		//\OCP\Util::writeLog('contacts', __METHOD__ . ' ' . print_r($data, true), \OCP\Util::DEBUG);
		//\OCP\Util::writeLog('contacts', __METHOD__, \OCP\Util::DEBUG);
		$this->props['parent'] = $parent;
		$this->props['backend'] = $backend;
		$this->props['retrieved'] = false;
		$this->props['saved'] = false;

		if(!is_null($data)) {
			if($data instanceof VObject\VCard) {
				foreach($data->children as $child) {
					$this->add($child);
					$this->setRetrieved(true);
				}
			} elseif(is_array($data)) {
				foreach($data as $key => $value) {
					switch($key) {
						case 'id':
							$this->props['id'] = $value;
							break;
						case 'lastmodified':
							$this->props['lastmodified'] = $value;
							break;
						case 'uri':
							$this->props['uri'] = $value;
							break;
						case 'carddata':
							$this->props['carddata'] = $value;
							$this->retrieve();
							break;
						case 'vcard':
							$this->props['vcard'] = $value;
							$this->retrieve();
							break;
						case 'displayname':
						case 'fullname':
							$this->props['displayname'] = $value;
							$this->FN = $value;
							break;
					}
				}
			}
		}
	}

	/**
	 * @return array|null
	 */
	public function getMetaData() {
		if(!$this->hasPermission(\OCP\PERMISSION_READ)) {
			throw new \Exception('Access denied');
		}
		if(!isset($this->props['displayname'])) {
			if(!$this->retrieve()) {
				\OCP\Util::writeLog('contacts', __METHOD__.' error reading: '.print_r($this->props, true), \OCP\Util::ERROR);
				return null;
			}
		}
		return array(
			'id' => $this->getId(),
			'displayname' => $this->getDisplayName(),
			'permissions' => $this->getPermissions(),
			'lastmodified' => $this->lastModified(),
			'owner' => $this->getOwner(),
			'parent' => $this->getParent()->getId(),
			'backend' => $this->getBackend()->name,
		);
	}

	/**
	 * Get a unique key combined of backend name, address book id and contact id.
	 *
	 * @return string
	 */
	public function combinedKey() {
		return $this->getBackend()->name . '::' . $this->getParent()->getId() . '::' . $this->getId();
	}

	/**
	 * @return string|null
	 */
	public function getOwner() {
		return $this->props['parent']->getOwner();
	}

	/**
	 * @return string|null
	 */
	public function getId() {
		return isset($this->props['id']) ? $this->props['id'] : null;
	}

	/**
	 * @return string|null
	 */
	function getDisplayName() {
		if(!$this->hasPermission(\OCP\PERMISSION_READ)) {
			throw new \Exception('Access denied');
		}
		return isset($this->props['displayname']) ? $this->props['displayname'] : null;
	}

	/**
	 * @return string|null
	 */
	public function getURI() {
		return isset($this->props['uri']) ? $this->props['uri'] : null;
	}

	/**
	 * If this object is part of a collection return a reference
	 * to the parent object, otherwise return null.
	 * @return IPIMObject|null
	 */
	function getParent() {
		return $this->props['parent'];
	}

	function getBackend() {
		return $this->props['backend'];
	}

	/** CRUDS permissions (Create, Read, Update, Delete, Share)
	 *
	 * @return integer
	 */
	function getPermissions() {
		return $this->props['parent']->getPermissions();
	}

	/**
	 * @param integer $permission
	 * @return bool
	 */
	function hasPermission($permission) {
		return $this->getPermissions() & $permission;
	}

	/**
	 * Save the address book data to backend
	 * FIXME
	 *
	 * @param array $data
	 * @return bool
	 */
/*	public function update(array $data) {

		foreach($data as $key => $value) {
			switch($key) {
				case 'displayname':
					$this->addressBookInfo['displayname'] = $value;
					break;
				case 'description':
					$this->addressBookInfo['description'] = $value;
					break;
			}
		}
		return $this->props['backend']->updateContact(
			$this->getParent()->getId(),
			$this->getId(),
			$this
		);
	}
*/
	/**
	 * Delete the data from backend
	 *
	 * @return bool
	 */
	public function delete() {
		if(!$this->hasPermission(\OCP\PERMISSION_DELETE)) {
			throw new \Exception('Access denied');
		}
		return $this->props['backend']->deleteContact(
			$this->getParent()->getId(),
			$this->getId()
		);
	}

	/**
	 * Save the contact data to backend
	 *
	 * @return bool
	 */
	public function save($force = false) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		if($this->isSaved() && !$force) {
			\OCP\Util::writeLog('contacts', __METHOD__.' Already saved: ' . print_r($this->props, true), \OCP\Util::DEBUG);
			return true;
		}
		if(isset($this->FN)) {
			$this->props['displayname'] = (string)$this->FN;
		}
		if($this->getId()) {
			if($this->props['backend']
				->updateContact(
					$this->getParent()->getId(),
					$this->getId(),
					$this->serialize()
				)
			) {
				$this->props['lastmodified'] = time();
				$this->setSaved(true);
				return true;
			} else {
				return false;
			}
		} else {
			//print(__METHOD__.' ' . print_r($this->getParent(), true));
			$this->props['id'] = $this->props['backend']->createContact(
				$this->getParent()->getId(), $this
			);
			$this->setSaved(true);
			return $this->getId() !== false;
		}
	}

	/**
	 * Get the data from the backend
	 * FIXME: Clean this up and make sure the logic is OK.
	 */
	public function retrieve() {
		//error_log(__METHOD__);
		//\OCP\Util::writeLog('contacts', __METHOD__.' ' . print_r($this->props, true), \OCP\Util::DEBUG);
		if($this->isRetrieved()) {
			//\OCP\Util::writeLog('contacts', __METHOD__. ' children', \OCP\Util::DEBUG);
			return true;
		} else {
			$data = null;
			if(isset($this->props['vcard'])
				&& $this->props['vcard'] instanceof VObject\VCard) {
				foreach($this->props['vcard']->children() as $child) {
					$this->add($child);
					if($child->name === 'FN') {
						$this->props['displayname']
							= strtr($child->value, array('\,' => ',', '\;' => ';', '\\\\' => '\\'));
					}
				}
				$this->setRetrieved(true);
				//$this->children = $this->props['vcard']->children();
				unset($this->props['vcard']);
				return true;
			} elseif(!isset($this->props['carddata'])) {
				$result = $this->props['backend']->getContact(
					$this->getParent()->getId(),
					$this->id
				);
				if($result) {
					if(isset($result['vcard'])
						&& $result['vcard'] instanceof VObject\VCard) {
						foreach($result['vcard']->children() as $child) {
							$this->add($child);
						}
						$this->setRetrieved(true);
						return true;
					} elseif(isset($result['carddata'])) {
						// Save internal values
						$data = $result['carddata'];
						$this->props['carddata'] = $result['carddata'];
						$this->props['lastmodified'] = $result['lastmodified'];
						$this->props['displayname'] = $result['displayname'];
						$this->props['permissions'] = $result['permissions'];
					} else {
						\OCP\Util::writeLog('contacts', __METHOD__
							. ' Could not get vcard or carddata: '
							. $this->getId()
							. print_r($result, true), \OCP\Util::DEBUG);
						return false;
					}
				} else {
					\OCP\Util::writeLog('contacts', __METHOD__.' Error getting contact: ' . $this->getId(), \OCP\Util::DEBUG);
				}
			} elseif(isset($this->props['carddata'])) {
				$data = $this->props['carddata'];
				//error_log(__METHOD__.' data: '.print_r($data, true));
			}
			try {
				$obj = \Sabre\VObject\Reader::read(
					$data,
					\Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES
				);
				if($obj) {
					foreach($obj->children as $child) {
						$this->add($child);
					}
					$this->setRetrieved(true);
				} else {
					\OCP\Util::writeLog('contacts', __METHOD__.' Error reading: ' . print_r($data, true), \OCP\Util::DEBUG);
					return false;
				}
			} catch (\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__ .
					' Error parsing carddata  for: ' . $this->getId() . ' ' . $e->getMessage(),
						\OCP\Util::ERROR);
				return false;
			}
		}
		return true;
	}

	/**
	* Get a property index in the contact by the checksum of its serialized value
	*
	* @param string $checksum An 8 char m5d checksum.
	* @return \Sabre\VObject\Property Property by reference
	* @throws An exception with error code 404 if the property is not found.
	*/
	public function getPropertyIndexByChecksum($checksum) {
		$this->retrieve();
		$idx = 0;
		foreach($this->children as $i => &$property) {
			if(substr(md5($property->serialize()), 0, 8) == $checksum ) {
				return $idx;
			}
			$idx += 1;
		}
		throw new Exception('Property not found', 404);
	}

	/**
	* Get a property by the checksum of its serialized value
	*
	* @param string $checksum An 8 char m5d checksum.
	* @return \Sabre\VObject\Property Property by reference
	* @throws An exception with error code 404 if the property is not found.
	*/
	public function getPropertyByChecksum($checksum) {
		$this->retrieve();
		foreach($this->children as $i => &$property) {
			if(substr(md5($property->serialize()), 0, 8) == $checksum ) {
				return $property;
			}
		}
		throw new \Exception('Property not found', 404);
	}

	/**
	* Delete a property by the checksum of its serialized value
	* It is up to the caller to call ->save()
	*
	* @param string $checksum An 8 char m5d checksum.
	* @throws @see getPropertyByChecksum
	*/
	public function unsetPropertyByChecksum($checksum) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		$idx = $this->getPropertyIndexByChecksum($checksum);
		unset($this->children[$idx]);
		$this->setSaved(false);
	}

	/**
	* Set a property by the checksum of its serialized value
	* It is up to the caller to call ->save()
	*
	* @param string $checksum An 8 char m5d checksum.
	* @param string $name Property name
	* @param mixed $value
	* @param array $parameters
	* @throws @see getPropertyByChecksum
	* @return string new checksum
	*/
	public function setPropertyByChecksum($checksum, $name, $value, $parameters=array()) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		if($checksum === 'new') {
			$property = Property::create($name);
			$this->add($property);
		} else {
			$property = $this->getPropertyByChecksum($checksum);
		}
		switch($name) {
			case 'EMAIL':
				$value = strtolower($value);
				$property->setValue($value);
				break;
			case 'ADR':
				if(is_array($value)) {
					$property->setParts($value);
				} else {
					$property->setValue($value);
				}
				break;
			case 'IMPP':
				if(is_null($parameters) || !isset($parameters['X-SERVICE-TYPE'])) {
					throw new \InvalidArgumentException(__METHOD__.' Missing IM parameter for: '.$name. ' ' . $value);
				}
				$serviceType = $parameters['X-SERVICE-TYPE'];
				if(is_array($serviceType)) {
					$serviceType = $serviceType[0];
				}
				$impp = Utils\Properties::getIMOptions($serviceType);
				if(is_null($impp)) {
					throw new \UnexpectedValueException(__METHOD__.' Unknown IM: ' . $serviceType);
				}
				$value = $impp['protocol'] . ':' . $value;
				$property->setValue($value);
				break;
			default:
				\OCP\Util::writeLog('contacts', __METHOD__.' adding: '.$name. ' ' . $value, \OCP\Util::DEBUG);
				$property->setValue($value);
				break;
		}
		$this->setParameters($property, $parameters, true);
		$this->setSaved(false);
		return substr(md5($property->serialize()), 0, 8);
	}

	/**
	* Set a property by the property name.
	* It is up to the caller to call ->save()
	*
	* @param string $name Property name
	* @param mixed $value
	* @param array $parameters
	* @return bool
	*/
	public function setPropertyByName($name, $value, $parameters=array()) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		// TODO: parameters are ignored for now.
		switch($name) {
			case 'BDAY':
				try {
					$date = New \DateTime($value);
				} catch(\Exception $e) {
					\OCP\Util::writeLog('contacts',
						__METHOD__.' DateTime exception: ' . $e->getMessage(),
						\OCP\Util::ERROR
					);
					return false;
				}
				$value = $date->format('Y-m-d');
				$this->BDAY = $value;
				$this->BDAY->add('VALUE', 'DATE');
				//\OCP\Util::writeLog('contacts', __METHOD__.' BDAY: '.$this->BDAY->serialize(), \OCP\Util::DEBUG);
				break;
			case 'CATEGORIES':
			case 'N':
			case 'ORG':
				$property = $this->select($name);
				if(count($property) === 0) {
					$property = \Sabre\VObject\Property::create($name);
					$this->add($property);
				} else {
					// Actually no idea why this works
					$property = array_shift($property);
				}
				if(is_array($value)) {
					$property->setParts($value);
				} else {
					$this->{$name} = $value;
				}
				break;
			default:
				\OCP\Util::writeLog('contacts', __METHOD__.' adding: '.$name. ' ' . $value, \OCP\Util::DEBUG);
				$this->{$name} = $value;
				break;
		}
		$this->setSaved(false);
		return true;
	}

	protected function setParameters($property, $parameters, $reset = false) {
		if(!$parameters) {
			return;
		}

		if($reset) {
			$property->parameters = array();
		}
		//debug('Setting parameters: ' . print_r($parameters, true));
		foreach($parameters as $key => $parameter) {
			//debug('Adding parameter: ' . $key);
			if(is_array($parameter)) {
				foreach($parameter as $val) {
					if(is_array($val)) {
						foreach($val as $val2) {
							if(trim($key) && trim($val2)) {
								//debug('Adding parameter: '.$key.'=>'.print_r($val2, true));
								$property->add($key, strip_tags($val2));
							}
						}
					} else {
						if(trim($key) && trim($val)) {
							//debug('Adding parameter: '.$key.'=>'.print_r($val, true));
							$property->add($key, strip_tags($val));
						}
					}
				}
			} else {
				if(trim($key) && trim($parameter)) {
					//debug('Adding parameter: '.$key.'=>'.print_r($parameter, true));
					$property->add($key, strip_tags($parameter));
				}
			}
		}
	}

	public function lastModified() {
		if(!isset($this->props['lastmodified'])) {
			$this->retrieve();
		}
		return isset($this->props['lastmodified'])
			? $this->props['lastmodified']
			: null;
	}

	/**
	 * Merge in data from a multi-dimentional array
	 *
	 * NOTE: This is *NOT* tested!
	 * The data array has this structure:
	 *
	 * array(
	 * 	'EMAIL' => array(array('value' => 'johndoe@example.com', 'parameters' = array('TYPE' => array('HOME','VOICE'))))
	 * );
	 * @param array $data
	 */
	public function mergeFromArray(array $data) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		foreach($data as $name => $properties) {
			if(in_array($name, array('PHOTO', 'UID'))) {
				continue;
			}
			if(!is_array($properties)) {
				\OCP\Util::writeLog('contacts', __METHOD__.' not an array?: ' .$name. ' '.print_r($properties, true), \OCP\Util::DEBUG);
			}
			if(in_array($name, Utils\Properties::$multi_properties)) {
				unset($this->{$name});
			}
			foreach($properties as $parray) {
				\OCP\Util::writeLog('contacts', __METHOD__.' adding: ' .$name. ' '.print_r($parray['value'], true) . ' ' . print_r($parray['parameters'], true), \OCP\Util::DEBUG);
				if(in_array($name, Utils\Properties::$multi_properties)) {
					// TODO: wrap in try/catch, check return value
					$this->setPropertyByChecksum('new', $name, $parray['value'], $parray['parameters']);
				} else {
					// TODO: Check return value
					if(!isset($this->{$name})) {
						$this->setPropertyByName($name, $parray['value'], $parray['parameters']);
					}
				}
			}
		}
		$this->setSaved(false);
		return true;
	}

	public function cacheThumbnail(\OC_Image $image = null) {
		$key = self::THUMBNAIL_PREFIX . $this->combinedKey();
		//\OC_Cache::remove($key);
		if(\OC_Cache::hasKey($key) && $image === null) {
			return \OC_Cache::get($key);
		}
		if(is_null($image)) {
			$this->retrieve();
			$image = new \OC_Image();
			if(!isset($this->PHOTO) && !isset($this->LOGO)) {
				return false;
			}
			if(!$image->loadFromBase64((string)$this->PHOTO)) {
				if(!$image->loadFromBase64((string)$this->LOGO)) {
					return false;
				}
			}
		}
		if(!$image->centerCrop()) {
			\OCP\Util::writeLog('contacts',
				'thumbnail.php. Couldn\'t crop thumbnail for ID ' . $key,
				\OCP\Util::ERROR);
			return false;
		}
		if(!$image->resize(self::THUMBNAIL_SIZE)) {
			\OCP\Util::writeLog('contacts',
				'thumbnail.php. Couldn\'t resize thumbnail for ID ' . $key,
				\OCP\Util::ERROR);
			return false;
		}
		 // Cache as base64 for around a month
		\OC_Cache::set($key, strval($image), 3000000);
		\OCP\Util::writeLog('contacts', 'Caching ' . $key, \OCP\Util::DEBUG);
		return \OC_Cache::get($key);
	}

	public function __set($key, $value) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		parent::__set($key, $value);
		$this->setSaved(false);
	}

	public function __unset($key) {
		if(!$this->hasPermission(\OCP\PERMISSION_UPDATE)) {
			throw new \Exception('Access denied');
		}
		parent::__unset($key);
		$this->setSaved(false);
	}

	protected function setRetrieved($state) {
		$this->props['retrieved'] = $state;
	}

	protected function isRetrieved() {
		return $this->props['retrieved'];
	}

	protected function setSaved($state) {
		$this->props['saved'] = $state;
	}

	protected function isSaved() {
		return $this->props['saved'];
	}

}
