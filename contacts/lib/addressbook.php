<?php
/**
 * ownCloud - Addressbook
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

namespace OCA\Contacts;

/**
 * This class manages our addressbooks.
 */
class Addressbook extends AbstractPIMCollection {

	protected $_count;
	/**
	 * @var Backend\AbstractBackend
	 */
	protected $backend;

	/**
	 * An array containing the mandatory:
	 * 	'displayname'
	 * 	'discription'
	 * 	'permissions'
	 *
	 * And the optional:
	 * 	'Etag'
	 * 	'lastModified'
	 *
	 * @var array
	 */
	protected $addressBookInfo;

	/**
	 * @param AbstractBackend $backend The storage backend
	 * @param array $addressBookInfo
	 */
	public function __construct(Backend\AbstractBackend $backend, array $addressBookInfo) {
		$this->backend = $backend;
		$this->addressBookInfo = $addressBookInfo;
		if(is_null($this->getId())) {
			$id = $this->backend->createAddressBook($addressBookInfo);
			if($id === false) {
				throw new \Exception('Error creating address book.');
			}
			$this->addressBookInfo = $this->backend->getAddressBook($id);
			//print(__METHOD__. ' '. __LINE__ . ' addressBookInfo: ' . print_r($this->backend->getAddressBook($id), true));
		}
		//\OCP\Util::writeLog('contacts', __METHOD__.' backend: ' . print_r($this->backend, true), \OCP\Util::DEBUG);
	}

	/**
	 * @return string|null
	 */
	public function getId() {
		return isset($this->addressBookInfo['id'])
			? $this->addressBookInfo['id']
			: null;
	}

	/**
	 * @return array
	 */
	public function getMetaData() {
		$metadata = $this->addressBookInfo;
		$metadata['lastmodified'] = $this->lastModified();
		$metadata['backend'] = $this->getBackend()->name;
		return $metadata;
	}

	/**
	 * @return string
	 */
	public function getDisplayName() {
		return $this->addressBookInfo['displayname'];
	}

	/**
	 * @return string
	 */
	public function getURI() {
		return $this->addressBookInfo['uri'];
	}

	/**
	 * @return string
	 */
	public function getOwner() {
		return $this->addressBookInfo['owner'];
	}

	/**
	 * Returns the lowest permission of what the backend allows and what it supports.
	 * @return int
	 */
	public function getPermissions() {
		return min($this->addressBookInfo['permissions'], $this->backend->getAddressBookPermissions());
	}

	function getBackend() {
		return $this->backend;
	}

	/**
	* Returns a specific child node, referenced by its id
	*
	* @param string $id
	* @return Contact|null
	*/
	function getChild($id) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' id: '.$id, \OCP\Util::DEBUG);
		if(!$this->hasPermission(\OCP\PERMISSION_READ)) {
			throw new \Exception('Access denied');
		}
		if(!isset($this->objects[$id])) {
			$contact = $this->backend->getContact($this->getId(), $id);
			if($contact) {
				$this->objects[$id] = new Contact($this, $this->backend, $contact);
			}
		}
		// When requesting a single contact we preparse it
		if(isset($this->objects[$id])) {
			$this->objects[$id]->retrieve();
			return $this->objects[$id];
		}
	}

	/**
	* Checks if a child-node with the specified id exists
	*
	* @param string $id
	* @return bool
	*/
	function childExists($id) {
		return ($this->getChild($id) !== null);
	}

	/**
	* Returns an array with all the child nodes
	*
	* @return Contact[]
	*/
	function getChildren($limit = null, $offset = null, $omitdata = false) {
		if(!$this->hasPermission(\OCP\PERMISSION_READ)) {
			throw new \Exception('Access denied');
		}
		//\OCP\Util::writeLog('contacts', __METHOD__.' backend: ' . print_r($this->backend, true), \OCP\Util::DEBUG);
		$contacts = array();

		foreach($this->backend->getContacts($this->getId(), $limit, $offset, $omitdata) as $contact) {
			//\OCP\Util::writeLog('contacts', __METHOD__.' id: '.$contact['id'], \OCP\Util::DEBUG);
			if(!isset($this->objects[$contact['id']])) {
				$this->objects[$contact['id']] = new Contact($this, $this->backend, $contact);
			}
			$contacts[] = $this->objects[$contact['id']];
		}
		//\OCP\Util::writeLog('contacts', __METHOD__.' children: '.count($contacts), \OCP\Util::DEBUG);
		return $contacts;
	}

	/**
	 * Add a contact to the address book
	 * This takes an array or a VCard|Contact and return
	 * the ID or false.
	 *
	 * @param array|VObject\VCard $data
	 * @return int|bool
	 */
	public function addChild($data = null) {
		if(!$this->hasPermission(\OCP\PERMISSION_CREATE)) {
			throw new \Exception('Access denied');
		}
		$contact = new Contact($this, $this->backend, $data);
		if($contact->save() === false) {
			return false;
		}
		$id = $contact->getId();
		if($this->count() !== null) {
			$this->_count += 1;
		}
		\OCP\Util::writeLog('contacts', __METHOD__.' id: '.$id, \OCP\Util::DEBUG);
		return $id;
	}

	/**
	 * Delete a contact from the address book
	 *
	 * @param string $id
	 * @return bool
	 */
	public function deleteChild($id) {
		if(!$this->hasPermission(\OCP\PERMISSION_READ)) {
			throw new \Exception('Access denied');
		}
		if($this->backend->deleteContact($this->getId(), $id)) {
			if(isset($this->objects[$id])) {
				unset($this->objects[$id]);
			}
			if($this->count() !== null) {
				$this->_count -= 1;
			}
			return true;
		}
		return false;
	}

	/**
	 * @internal implements Countable
	 * @return int|null
	 */
	public function count() {
		if(!isset($this->_count)) {
			$this->_count = $this->backend->numContacts($this->getId());
		}
		return $this->_count;
	}

	/**
	 * Update and save the address book data to backend
	 * NOTE: @see IPIMObject::update for consistency considerations.
	 *
	 * @param array $data
	 * @return bool
	 */
	public function update(array $data) {
		if(count($data) === 0) {
			return false;
		}
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
		return $this->backend->updateAddressBook($this->getId(), $data);
	}

	/**
	 * Save the address book data to backend
	 * NOTE: @see IPIMObject::update for consistency considerations.
	 *
	 * @return bool
	 */
	public function save() {
		if(!$this->hasPermission(OCP\PERMISSION_UPDATE)) {
			throw new Exception('You don\'t have permissions to update the address book.');
		}
	}

	/**
	 * Delete the address book from backend
	 *
	 * @return bool
	 */
	public function delete() {
		if(!$this->hasPermission(OCP\PERMISSION_DELETE)) {
			throw new Exception('You don\'t have permissions to delete the address book.');
		}
		return $this->backend->deleteAddressBook($this->getId());
	}

	/**
	 * @brief Get the last modification time for the object.
	 *
	 * Must return a UNIX time stamp or null if the backend
	 * doesn't support it.
	 *
	 * @returns int | null
	 */
	public function lastModified() {
		return $this->backend->lastModifiedAddressBook($this->getId());
	}

}
