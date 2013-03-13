<?php
/**
 * Copyright (c) 2011 Bart Visscher bartv@thisnet.nl
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
	/*
	 * @brief language object for calendar app
	 */

	public static $l10n;
	/*
	 * @brief categories of the user
	 */
	public static $categories = null;

	const THUMBNAIL_PREFIX = 'contact-thumbnail-';
	const THUMBNAIL_SIZE = 28;

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

	public static function updateDBProperties($contactid, $vcard = null) {
		$stmt = \OCP\DB::prepare('DELETE FROM `*PREFIX*contacts_cards_properties` WHERE `contactid` = ?');
		try {
			$stmt->execute(array($contactid));
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '
				. $id, \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'There was an error deleting properties for this contact.'
				)
			);
		}

		if(is_null($vcard)) {
			return;
		}

		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*contacts_cards_properties` '
			. '(`userid`, `contactid`,`name`,`value`,`preferred`) VALUES(?,?,?,?,?)' );
		foreach($vcard->children as $property) {
			if(!in_array($property->name, self::$index_properties)) {
				continue;
			}
			$preferred = 0;
			foreach($property->parameters as $parameter) {
				if($parameter->name == 'TYPE' && strtoupper($parameter->value) == 'PREF') {
					$preferred = 1;
					break;
				}
			}
			try {
				$result = $stmt->execute(
					array(
						\OCP\User::getUser(), 
						$contactid, 
						$property->name, 
						$property->value, 
						$preferred,
					)
				);
				if (\OC_DB::isError($result)) {
					\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: ' 
						. \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}
	}
}
