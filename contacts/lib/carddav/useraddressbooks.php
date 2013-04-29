<?php
/**
 * ownCloud - Addressbook
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

namespace OCA\Contacts\CardDAV;

/**
 * This class overrides Sabre_CardDAV_UserAddressBooks::getChildren()
 * to instantiate \OCA\Contacts\CardDAV\AddressBooks.
*/
class UserAddressBooks extends \Sabre_CardDAV_UserAddressBooks {

	/**
	* Returns a list of addressbooks
	*
	* @return array
	*/
	public function getChildren() {

		$addressbooks = $this->carddavBackend->getAddressbooksForUser($this->principalUri);
		$objs = array();
		foreach($addressbooks as $addressbook) {
			$objs[] = new AddressBook($this->carddavBackend, $addressbook);
		}
		return $objs;

	}

}