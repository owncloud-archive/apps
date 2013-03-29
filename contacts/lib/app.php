<?php
/**
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;

use Sabre\VObject;

/**
 * This class manages our app actions
 */
App::$l10n = \OC_L10N::get('contacts');

class App {

	/**
	* @brief Categories of the user
	* @var OC_VCategories
	*/
	public static $categories = null;

	/**
	 * @brief language object for calendar app
	 *
	 * @var OC_L10N
	 */
	public static $l10n;

	/**
	 * An array holding the current users address books.
	 * @var array
	 */
	protected static $addressBooks = array();
	/**
	* If backends are added to this map, they will be automatically mapped
	* to their respective classes, if constructed with the 'getBackend' method.
	*
	* @var array
	*/
	public static $backendClasses = array(
		'database' => 'OCA\Contacts\Backend\Database',
		'shared' => 'OCA\Contacts\Backend\Shared',
	);

	public function __construct(
		$user = null,
		$addressBooksTableName = '*PREFIX*addressbook',
		$backendsTableName = '*PREFIX*addressbooks_backend',
		$dbBackend = null
	) {
		$this->user = $user ? $user : \OCP\User::getUser();
		$this->addressBooksTableName = $addressBooksTableName;
		$this->backendsTableName = $backendsTableName;
		$this->dbBackend = $dbBackend
			? $dbBackend
			: new Backend\Database($user);
	}

	/**
	* Gets backend by name.
	*
	* @param string $name
	* @return \Backend\AbstractBackend
	*/
	static public function getBackend($name, $user = null) {
		$name = $name ? $name : 'database';
		if (isset(self::$backendClasses[$name])) {
			return new self::$backendClasses[$name]($user);
		} else {
			throw new \Exception('No backend for: ' . $name);
		}
	}

	/**
	 * Return all registered address books for current user.
	 * For now this is hard-coded to using the Database and
	 * Shared backends, but eventually admins will be able to
	 * register additional backends, and users will be able to
	 * subscribe to address books using those backends.
	 *
	 * @return AddressBook[]
	 */
	public function getAddressBooksForUser() {
		if(!self::$addressBooks) {
			foreach(array_keys(self::$backendClasses) as $backendName) {
				$backend = self::getBackend($backendName, $this->user);
				$addressBooks = $backend->getAddressBooksForUser();
				if($backendName === 'database' && count($addressBooks) === 0) {
					$id = $backend->createAddressBook(array('displayname' => 'Contacts'));
					if($id !== false) {
						$addressBook = $backend->getAddressBook($id);
						$addressBooks = array($addressBook);
					} else {
						// TODO: Write log
					}
				}
				foreach($addressBooks as $addressBook) {
					$addressBook['backend'] = $backendName;
					self::$addressBooks[] = new AddressBook($backend, $addressBook);
				}
			}
		}
		return self::$addressBooks;
	}

	/**
	 * Get an address book from a specific backend.
	 *
	 * @param string $backendName
	 * @param string $addressbookid
	 * @return AddressBook|null
	 */
	public function getAddressBook($backendName, $addressbookid) {
		foreach(self::$addressBooks as $addressBook) {
			if($addressBook->backend->name === $backendName
				&& $addressBook->getId() === $addressbookid
			) {
				return $addressBook;
			}
		}
		// TODO: Check for return values
		$backend = self::getBackend($backendName, $this->user);
		$info = $backend->getAddressBook($addressbookid);
		// FIXME: Backend name should be set by the backend.
		$info['backend'] = $backendName;
		$addressBook = new AddressBook($backend, $info);
		self::$addressBooks[] = $addressBook;
		return $addressBook;
	}

	/**
	 * Get a Contact from an address book from a specific backend.
	 *
	 * @param string $backendName
	 * @param string $addressbookid
	 * @param string $id - Contact id
	 * @return Contact|null
	 *
	 */
	public function getContact($backendName, $addressbookid, $id) {
		$addressBook = $this->getAddressBook($backendName, $addressbookid);
		// TODO: Check for return value
		return $addressBook->getChild($id);
	}

	/**
	* @brief returns the vcategories object of the user
	* @return (object) $vcategories
	*/
	public static function getVCategories() {
		if (is_null(self::$categories)) {
			if(\OC_VCategories::isEmpty('contact')) {
				self::scanCategories();
			}
			self::$categories = new \OC_VCategories('contact',
			null,
			self::getDefaultCategories());
		}
		return self::$categories;
	}
	/**
	 * @brief returns the categories for the user
	 * @return (Array) $categories
	 */
	public static function getCategories($format = null) {
		$categories = self::getVCategories()->categories($format);
		return ($categories ? $categories : self::getDefaultCategories());
	}

	/**
	 * scan vcards for categories.
	 * @param $vccontacts VCards to scan. null to check all vcards for the current user.
	 */
	public static function scanCategories($vccontacts = null) {
		if (is_null($vccontacts)) {
			$vcaddressbooks = Addressbook::all(\OCP\USER::getUser());
			if(count($vcaddressbooks) > 0) {
				$vcaddressbookids = array();
				foreach($vcaddressbooks as $vcaddressbook) {
					if($vcaddressbook['userid'] === \OCP\User::getUser()) {
						$vcaddressbookids[] = $vcaddressbook['id'];
					}
				}
				$start = 0;
				$batchsize = 10;
				$categories = new \OC_VCategories('contact');
				while($vccontacts =
					VCard::all($vcaddressbookids, $start, $batchsize)) {
					$cards = array();
					foreach($vccontacts as $vccontact) {
						$cards[] = array($vccontact['id'], $vccontact['carddata']);
					}
					\OCP\Util::writeLog('contacts',
						__CLASS__.'::'.__METHOD__
							.', scanning: '.$batchsize.' starting from '.$start,
						\OCP\Util::DEBUG);
					// only reset on first batch.
					$categories->rescan($cards,
						true,
						($start == 0 ? true : false));
					$start += $batchsize;
				}
			}
		}
	}

	/**
	 * check VCard for new categories.
	 * @see OC_VCategories::loadFromVObject
	 */
	public static function loadCategoriesFromVCard($id, $contact) {
		if(!$contact instanceof \OC_VObject) {
			$contact = new \OC_VObject($contact);
		}
		self::getVCategories()->loadFromVObject($id, $contact, true);
	}

}
