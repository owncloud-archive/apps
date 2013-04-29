<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
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

use OCA\Contacts;

class Backend extends \Sabre_CardDAV_Backend_Abstract {

	public function __construct($backends) {
		//\OCP\Util::writeLog('contacts', __METHOD__, \OCP\Util::DEBUG);
		$this->backends = $backends;
	}

	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * @param string $principaluri
	 * @return array
	 */
	public function getAddressBooksForUser($principaluri) {
		$userid = $this->userIDByPrincipal($principaluri);
		$userAddressBooks = array();
		foreach($this->backends as $backend) {
			$addressBooks = $backend->getAddressBooksForUser($userid);

			foreach($addressBooks as $addressBook) {
				if($addressBook['owner'] != \OCP\USER::getUser()) {
					$addressBook['uri'] = $addressBook['uri'] . '_shared_by_' . $addressBook['owner'];
					$addressBook['displayname'] = $addressBook['displayname'];
				}
				$userAddressbooks[] = array(
					'id'  => $backend->name . '::' . $addressBook['id'],
					'uri' => $addressBook['uri'],
					'principaluri' => 'principals/'.$addressBook['owner'],
					'{DAV:}displayname' => $addressBook['displayname'],
					'{' . \Sabre_CardDAV_Plugin::NS_CARDDAV . '}addressbook-description'
							=> $addressBook['description'],
					'{http://calendarserver.org/ns/}getctag' => $addressBook['lastmodified'],
				);
			}
		}

		return $userAddressbooks;
	}


	/**
	 * Updates an addressbook's properties
	 *
	 * See Sabre_DAV_IProperties for a description of the mutations array, as
	 * well as the return value.
	 *
	 * @param mixed $addressbookid
	 * @param array $mutations
	 * @see Sabre_DAV_IProperties::updateProperties
	 * @return bool|array
	 */
	public function updateAddressBook($addressbookid, array $mutations) {
		$name = null;
		$description = null;
		$changes = array();

		foreach($mutations as $property=>$newvalue) {
			switch($property) {
				case '{DAV:}displayname' :
					$changes['name'] = $newvalue;
					break;
				case '{' . \Sabre_CardDAV_Plugin::NS_CARDDAV
						. '}addressbook-description' :
					$changes['description'] = $newvalue;
					break;
				default :
					// If any unsupported values were being updated, we must
					// let the entire request fail.
					return false;
			}
		}

		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		return $backend->updateAddressBook($id, $changes);

	}

	/**
	 * Creates a new address book
	 *
	 * @param string $principaluri
	 * @param string $uri Just the 'basename' of the url.
	 * @param array $properties
	 * @return void
	 */
	public function createAddressBook($principaluri, $uri, array $properties) {

		$properties = array();
		$userid = $this->userIDByPrincipal($principaluri);

		foreach($properties as $property=>$newvalue) {

			switch($property) {
				case '{DAV:}displayname' :
					$properties['displayname'] = $newvalue;
					break;
				case '{' . \Sabre_CardDAV_Plugin::NS_CARDDAV
						. '}addressbook-description' :
					$properties['description'] = $newvalue;
					break;
				default :
					throw new \Sabre_DAV_Exception_BadRequest('Unknown property: '
						. $property);
			}

		}

		$properties['uri'] = $uri;

		list(,$backend) = $this->getBackendForAddressBook($addressbookid);
		$backend->createAddressBook($properties, $userid);
	}

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * @param int $addressbookid
	 * @return void
	 */
	public function deleteAddressBook($addressbookid) {
		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		$backend->deleteAddressBook($id);
	}

	/**
	 * Returns all cards for a specific addressbook id.
	 *
	 * @param mixed $addressbookid
	 * @return array
	 */
	public function getCards($addressbookid) {
		$contacts = array();
		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		$contacts = $backend->getContacts($id);

		$cards = array();
		foreach($contacts as $contact) {
			//OCP\Util::writeLog('contacts', __METHOD__.', uri: ' . $i['uri'], OCP\Util::DEBUG);
			$cards[] = array(
				'id' => $contact['id'],
				//'carddata' => $i['carddata'],
				'size' => strlen($contact['carddata']),
				'etag' => '"' . md5($contact['carddata']) . '"',
				'uri' => $contact['uri'],
				'lastmodified' => $contact['lastmodified'] );
		}

		return $cards;
	}

	/**
	 * Returns a specfic card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @return array
	 */
	public function getCard($addressbookid, $carduri) {
		\OCP\Util::writeLog('contacts', __METHOD__.' identifier: ' . $carduri . ' ' . print_r($addressbookid, true), \OCP\Util::DEBUG);
		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		$contact = $backend->getContact($id, array('uri' => $carduri));
		return ($contact ? $contact : false);

	}

	/**
	 * Creates a new card
	 *
	 * We don't return an Etag as the carddata can have been modified
	 * by Plugin::validate()
	 *
	 * @see Plugin::validate()
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @param string $carddata
	 * @return string|null
	 */
	public function createCard($addressbookid, $carduri, $carddata) {
		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		$backend->createContact($id, $carddata, $carduri);
	}

	/**
	 * Updates a card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @param string $carddata
	 * @return null
	 */
	public function updateCard($addressbookid, $carduri, $carddata) {
		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		$backend->updateContact($id, array('uri' => $carduri,), $carddata);
	}

	/**
	 * Deletes a card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @return bool
	 */
	public function deleteCard($addressbookid, $carduri) {
		list($id, $backend) = $this->getBackendForAddressBook($addressbookid);
		return $backend->deleteContact($id);
	}

	/**
	 * @brief gets the userid from a principal path
	 * @return string
	 */
	public function userIDByPrincipal($principaluri) {
		list(, $userid) = \Sabre_DAV_URLUtil::splitPath($principaluri);
		return $userid;
	}

	/**
	 * Get the backend for an address book
	 *
	 * @param mixed $addressbookid
	 * @return array(string, \OCA\Contacts\Backend\AbstractBackend)
	 */
	public function getBackendForAddressBook($addressbookid) {
		list($backendName, $id) = explode('::', $addressbookid);
		foreach($this->backends as $backend) {
			if($backend->name === $backendName && $backend->hasAddressBook($id)) {
				return array($id, $backend);
			}
		}
		throw new \Sabre_DAV_Exception_NotFound('Backend not found: ' . $addressbookid);
	}
}
