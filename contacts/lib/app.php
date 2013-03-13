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
	const THUMBNAIL_PREFIX = 'contact-thumbnail-';
	const THUMBNAIL_SIZE = 28;

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
