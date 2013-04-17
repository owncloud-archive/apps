<?php
/**
 * ownCloud - Base class for Contacts backends
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

/**
 * Subclass this class for address book backends
 *
 * The following methods MUST be implemented:
 * @method array getAddressBooksForUser(string $userid) FIXME: I'm not sure about this one.
 * @method array getAddressBook(string $addressbookid)
 * @method array getContacts(string $addressbookid)
 * @method array getContact(string $addressbookid, mixed $id)
 * The following methods MAY be implemented:
 * @method bool hasAddressBook(string $addressbookid)
 * @method bool updateAddressBook(string $addressbookid, array $updates)
 * @method string createAddressBook(string $addressbookid, array $properties)
 * @method bool deleteAddressBook(string $addressbookid)
 * @method int lastModifiedAddressBook(string $addressbookid)
 * @method array numContacts(string $addressbookid)
 * @method bool updateContact(string $addressbookid, string $id, array $updates)
 * @method string createContact(string $addressbookid, string $id, array $properties)
 * @method bool deleteContact(string $addressbookid, string $id)
 * @method int lastModifiedContact(string $addressbookid)
 */

abstract class AbstractBackend {

	/**
	 * The name of the backend.
	 * @var string
	 */
	public $name;

	protected $possibleContactPermissions = array(
		\OCP\PERMISSION_CREATE 	=> 'createContact',
		\OCP\PERMISSION_READ	=> 'getContact',
		\OCP\PERMISSION_UPDATE	=> 'updateContact',
		\OCP\PERMISSION_DELETE 	=> 'deleteContact',
	);

	protected $possibleAddressBookPermissions = array(
		\OCP\PERMISSION_CREATE 	=> 'createAddressBook',
		\OCP\PERMISSION_READ		=> 'getAddressBook',
		\OCP\PERMISSION_UPDATE	=> 'updateAddressBook',
		\OCP\PERMISSION_DELETE 	=> 'deleteAddressBook',
	);

	/**
	* @brief Get all permissions for contacts
	* @returns bitwise-or'ed actions
	*
	* Returns the supported actions as int to be
	* compared with \OCP\PERMISSION_CREATE etc.
	* TODO: When getting the permissions we also have to check for
	* configured permissions and return min() of the two values.
	* A user can for example configure an address book with a backend
	* that implements deleteContact() but wants to set it read-only.
	*/
	public function getContactPermissions() {
		$permissions = 0;
		foreach($this->possibleContactPermissions AS $permission => $methodName) {
			if(method_exists($this, $methodName)) {
				$permissions |= $permission;
			}
		}

		return $permissions;
	}

	/**
	* @brief Get all permissions for address book.
	* @returns bitwise-or'ed actions
	*
	* Returns the supported actions as int to be
	* compared with \OCP\PERMISSION_CREATE etc.
	*/
	public function getAddressBookPermissions() {
		$permissions = 0;
		foreach($this->possibleAddressBookPermissions AS $permission => $methodName) {
			if(method_exists($this, $methodName)) {
				$permissions |= $permission;
			}
		}

		return $permissions;
	}

	/**
	* @brief Check if backend implements action for contacts
	* @param $actions bitwise-or'ed actions
	* @returns boolean
	*
	* Returns the supported actions as int to be
	* compared with \OCP\PERMISSION_CREATE etc.
	*/
	public function hasContactPermission($permission) {
		return (bool)($this->getContactPermissions() & $permission);
	}

	/**
	* @brief Check if backend implements action for contacts
	* @param $actions bitwise-or'ed actions
	* @returns boolean
	*
	* Returns the supported actions as int to be
	* compared with \OCP\PERMISSION_CREATE etc.
	*/
	public function hasAddressBooksPermission($permission) {
		return (bool)($this->getAddressBooksPermissions() & $permission);
	}

	/**
	 * Check if the backend has the address book
	 *
	 * @param string $addressbookid
	 * @return bool
	 */
	public function hasAddressBook($addressbookid) {
		return count($this->getAddressBook($addressbookid)) > 0;
	}

	/**
	 * Returns the number of contacts in an address book.
	 * Implementations can choose to override this method to either
	 * get the result more effectively or to return null if the backend
	 * cannot determine the number.
	 *
	 * @param string $addressbookid
	 * @return integer|null
	 */
	public function numContacts($addressbookid) {
		return count($this->getContacts($addressbookid));
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
	public function getAddressBooksForUser($userid) {
	}
	 */

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
	public abstract function getAddressBook($addressbookid);

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
	public function updateAddressBook($addressbookid, array $properties) {
	}
	 */

	/**
	 * Creates a new address book
	 *
	 * Currently the only ones supported are 'displayname' and
	 * 'description', but backends can implement additional.
	 * 'displayname' MUST be present.
	 *
	 * @param array $properties
	 * @return string|false The ID if the newly created AddressBook or false on error.
	public function createAddressBook(array $properties) {
	}
	 */

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * @param string $addressbookid
	 * @return bool
	public function deleteAddressBook($addressbookid) {
	}
	 */

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
	 *   0 => array('id' => '4e111fef5df', 'permissions' => 1, 'displayname' => 'John Q. Public', 'vcard' => $object),
	 *   1 => array('id' => 'bbcca2d1535', 'permissions' => 32, 'displayname' => 'Jane Doe', 'carddata' => $data)
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
	public abstract function getContacts($addressbookid, $limit = null, $offset = null, $omitdata = false);

	/**
	 * Returns a specfic contact.
	 *
	 * Same as getContacts except that either 'carddata' or 'vcard' is mandatory.
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @return array|bool
	 */
	public abstract function getContact($addressbookid, $id);

	/**
	 * Creates a new contact
	 *
	 * @param string $addressbookid
	 * @param string $carddata
	 * @return string|bool The identifier for the new contact or false on error.
	public function createContact($addressbookid, $carddata) {
	}
	 */

	/**
	 * Updates a contact
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @param string $carddata
	 * @return bool
	public function updateContact($addressbookid, $id, $carddata) {
	}
	 */

	/**
	 * Deletes a contact
	 *
	 * @param string $addressbookid
	 * @param mixed $id
	 * @return bool
	public function deleteContact($addressbookid, $id) {
	}
	 */

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
	}
}
