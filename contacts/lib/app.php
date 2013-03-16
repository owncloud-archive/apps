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
	const THUMBNAIL_PREFIX = 'contact-thumbnail-';
	const THUMBNAIL_SIZE = 28;

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
	* to their respective classes, if constructed with the 'createBackend' method.
	*
	* @var array
	*/
	public static $backendClasses = array(
		'database' => 'OCA\Contacts\Backend\Database',
		'shared' => 'OCA\Contacts\Backend\Shared',
	);

	public function __construct(
		$addressBooksTableName = '*PREFIX*addressbook',
		$backendsTableName = '*PREFIX*addressbooks_backend',
		$dbBackend = null
	) {
		$this->addressBooksTableName = $addressBooksTableName;
		$this->backendsTableName = $backendsTableName;
		$this->dbBackend = $dbBackend ? $dbBackend : new Backend\Database();
	}

	/**
	* Creates the new backend by name, but in addition will also see if
	* there's a class mapped to the property name.
	*
	* @param string $name
	* @return \Backend\AbstractBackend
	*/
	static public function createBackend($name) {
		$name = $name ? $name : 'database';
		if (isset(self::$backendClasses[$name])) {
			return new self::$backendClasses[$name]();
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
	public function getAllAddressBooksForUser() {
		if(!self::$addressBooks) {
			foreach(array_keys(self::$backendClasses) as $backendName) {
				$backend = self::createBackend($backendName);
				$addressBooks = $backend->getAddressBooksForUser();
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
		$backend = self::createBackend($backendName);
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

	public static function cacheThumbnail($id, \OC_Image $image = null) {
		if(\OC_Cache::hasKey(self::THUMBNAIL_PREFIX . $id) && $image === null) {
			return \OC_Cache::get(self::THUMBNAIL_PREFIX . $id);
		}
		if(is_null($image)) {
			$vcard = self::getContactVCard($id);

			// invalid vcard
			if(is_null($vcard)) {
				\OCP\Util::writeLog('contacts',
					__METHOD__.' The VCard for ID ' . $id . ' is not RFC compatible',
					\OCP\Util::ERROR);
				return false;
			}
			$image = new \OC_Image();
			if(!isset($vcard->PHOTO)) {
				return false;
			}
			if(!$image->loadFromBase64((string)$vcard->PHOTO)) {
				return false;
			}
		}
		if(!$image->centerCrop()) {
			\OCP\Util::writeLog('contacts',
				'thumbnail.php. Couldn\'t crop thumbnail for ID ' . $id,
				\OCP\Util::ERROR);
			return false;
		}
		if(!$image->resize(self::THUMBNAIL_SIZE)) {
			\OCP\Util::writeLog('contacts',
				'thumbnail.php. Couldn\'t resize thumbnail for ID ' . $id,
				\OCP\Util::ERROR);
			return false;
		}
		 // Cache for around a month
		\OC_Cache::set(self::THUMBNAIL_PREFIX . $id, $image->data(), 3000000);
		\OCP\Util::writeLog('contacts', 'Caching ' . $id, \OCP\Util::DEBUG);
		return \OC_Cache::get(self::THUMBNAIL_PREFIX . $id);
	}

}
