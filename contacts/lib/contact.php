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

/**
 * Subclass this class or implement IPIMObject interface for PIM objects
 */

class Contact extends VObject\VCard implements IPIMObject {

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

		if(!is_null($data)) {
			if($data instanceof VObject\VCard) {
				foreach($obj->children as $child) {
					$this->add($child);
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
							break;
						case 'vcard':
							$this->props['vcard'] = $value;
							$this->retrieve();
							break;
						case 'displayname':
						case 'fullname':
							$this->props['displayname'] = $value;
							break;
					}
				}
			} else {
				throw new Exception(
					__METHOD__ . ' 3rd argument must either be an array or a subclass of \VObject\VCard'
				);
			}
		}
	}

	/**
	 * @return array|null
	 */
	public function getMetaData() {
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
		);
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
	public function update(array $data) {

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

	/**
	 * Delete the data from backend
	 *
	 * @return bool
	 */
	public function delete() {
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
	public function save() {
		if($this->getId()) {
			return $this->props['backend']->updateContact(
				$this->getParent()->getId(),
				$this->getId(),
				$this->serialize()
			);
		} else {
			$this->props['id'] = $this->props['backend']->createContact(
				$this->getParent()->getId(), $this->serialize()
			);
			if($this->props['id'] !== false) {
				$this->getParent()->setChildID($this);
			}
			return $this->getId() !== false;
		}
	}

	/**
	 * Get the data from the backend
	 */
	public function retrieve() {
		//\OCP\Util::writeLog('contacts', __METHOD__.' ' . print_r($this->props, true), \OCP\Util::DEBUG);
		if($this->children && is_null($data)) {
			return true;
		} else {
			if(isset($this->props['vcard'])
				&& $this->props['vcard'] instanceof VObject\VCard) {
				foreach($this->props['vcard']->children() as $child) {
					$this->add($child);
				}
				//$this->children = $this->props['vcard']->children();
				unset($this->props['vcard']);
				return true;
			} elseif(!isset($this->props['carddata']) && is_null($data)) {
				$result = $this->props['backend']->getContact(
					$this->parent->getId(),
					$this->id
				);
				if($result) {
					if(isset($result['vcard'])
						&& $result['vcard'] instanceof VObject\VCard) {
						foreach($result['vcard']->children() as $child) {
							$this->add($child);
						}
						return true;
					} elseif(isset($result['carddata'])) {
						// Save internal values
						$data = $result['carddata'];
						$this->props['carddata'] = $result['carddata'];
						$this->props['lastmodified'] = $result['lastmodified'];
						$this->props['displayname'] = $result['fullname'];
						$this->props['permissions'] = $result['permissions'];
					} else {
						return false;
					}
				}
			} elseif(isset($this->props['carddata'])) {
				$data = $this->props['carddata'];
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
				} else {
					return false;
				}
			} catch (\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__ .
					' Error parsing carddata: ' . $e->getMessage(),
						\OCP\Util::ERROR);
				return false;
			}
		}
		return true;
	}

	/**
	* Get a property by the checksum of its serialized value
	*
	* @param string $checksum An 8 char m5d checksum.
	* @return \Sabre\VObject\Propert|null
	*/
	public function getPropertyByChecksum($checksum) {
		$this->retrieve();
		$line = null;
		foreach($this->children as $i => $property) {
			if(substr(md5($property->serialize()), 0, 8) == $checksum ) {
				$line = $i;
				break;
			}
		}
		return isset($this->children[$line]) ? $this->children[$line] : null;
	}

	public function lastModified() {
		if(!isset($this->props['lastmodified'])) {
			$this->retrieve();
		}
		return isset($this->props['lastmodified'])
			? $this->props['lastmodified']
			: null;
	}
}
