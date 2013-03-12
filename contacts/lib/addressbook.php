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
class Addressbook extends PIMCollectionAbstract {

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
	 * @var string
	 */
	protected $addressBookInfo;

	/**
	 * @param AbstractBackend $backend The storage backend
	 * @param array $addressBookInfo
	 */
	public function __construct(AbstractBackend $backend, array $addressBookInfo) {
		$this->backend = $backend;
		$this->addressBookInfo = $addressBookInfo;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->addressBookInfo['id'];
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
		return $this->addressBookInfo['userid'];
	}

	/**
	 * @return string
	 */
	public function getPermissions() {
		return $this->addressBookInfo['permissions'];
	}

	/**
	* Returns a specific child node, referenced by its id
	*
	* @param string $id
	* @return IPIMObject
	*/
	function getChild($id) {
		if(!isset($this->objects[$id])) {
			$contact = $this->$backend->getContact($id);
			if($contact) {
				$this->objects[$id] = new Contact($this, $this->backend, $contact);
			}
		}
		return isset($this->objects[$id]) ? $this->objects[$id] : null;
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
	* @return IPIMObject[]
	*/
	function getChildren($limit = null, $offset = null, $omitdata = false) {
		$contacts = array();

		foreach($this->backend->getContacts($this->getId(), $limit, $offset, $omitdata) as $contact) {
			if(!isset($this->objects[$contact['id']])) {
				$this->objects[$contact['id']] = new Contact($this, $this->backend, $contact);
			}
			$contacts[] = $this->objects[$contact['id']];
		}
		return $contacts;
	}

	/**
	 * Save the address book data to backend
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
		$this->backend->updateAddressBook($this->getId(), $data);
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

}
