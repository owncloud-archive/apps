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
							$this->retrieve();
							break;
						case 'vcard':
							$this->props['vcard'] = $value;
							$this->retrieve();
							break;
						case 'displayname':
						case 'fullname':
							$this->props['displayname'] = $value;
							//$this->FN = $value;
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
		if(isset($this->FN)) {
			$this->props['displayname'] = (string)$this->FN;
		}
		if($this->getId()) {
			return $this->props['backend']->updateContact(
				$this->getParent()->getId(),
				$this->getId(),
				$this->serialize()
			);
		} else {
			//print(__METHOD__.' ' . print_r($this->getParent(), true));
			$this->props['id'] = $this->props['backend']->createContact(
				$this->getParent()->getId(), $this
			);
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
		if($this->children) {
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
				//$this->children = $this->props['vcard']->children();
				unset($this->props['vcard']);
				return true;
			} elseif(!isset($this->props['carddata'])) {
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
						$this->props['displayname'] = $result['displayname'];
						$this->props['permissions'] = $result['permissions'];
					} else {
						return false;
					}
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
				} else {
					\OCP\Util::writeLog('contacts', __METHOD__.' Error reading: ' . print_r($data, true), \OCP\Util::DEBUG);
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

	public function cacheThumbnail(\OC_Image $image = null) {
		$key = $this->getBackend()->name . '::' . $this->getParent()->getId() . '::' . $this->getId();
		if(\OC_Cache::hasKey(self::THUMBNAIL_PREFIX . $key) && $image === null) {
			return \OC_Cache::get(self::THUMBNAIL_PREFIX . $key);
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
		 // Cache for around a month
		\OC_Cache::set(self::THUMBNAIL_PREFIX . $key, $image->data(), 3000000);
		\OCP\Util::writeLog('contacts', 'Caching ' . $key, \OCP\Util::DEBUG);
		return \OC_Cache::get(self::THUMBNAIL_PREFIX . $key);
	}
}
