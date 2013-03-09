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

	/**
	 * Properties there can be more than one of.
	 */
	public static $multi_properties = array('EMAIL', 'TEL', 'IMPP', 'ADR', 'URL');

	/**
	 * Properties to index.
	 */
	public static $index_properties = array('BDAY', 'UID', 'N', 'FN', 'TITLE', 'ROLE', 'NOTE', 'NICKNAME', 'ORG', 'CATEGORIES', 'EMAIL', 'TEL', 'IMPP', 'ADR', 'URL', 'GEO', 'PHOTO');

	const THUMBNAIL_PREFIX = 'contact-thumbnail-';
	const THUMBNAIL_SIZE = 28;

	/**
	 * @brief Gets the VCard as a \Sabre\VObject\Component
	 * @param integer $id
	 * @returns \Sabre\VObject\Component|null The card or null if the card could not be parsed.
	 */
	public static function getContactVCard($id) {
		$card = null;
		$vcard = null;
		try {
			$card = VCard::find($id);
		} catch(Exception $e) {
			return null;
		}

		try {
			$vcard = \Sabre\VObject\Reader::read($card['carddata']);
		} catch(Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: ' . $id, \OCP\Util::DEBUG);
			return null;
		}

		if (!is_null($vcard) && !isset($vcard->REV)) {
			$rev = new \DateTime('@'.$card['lastmodified']);
			$vcard->REV = $rev->format(\DateTime::W3C);
		}
		return $vcard;
	}

	public static function getPropertyLineByChecksum($vcard, $checksum) {
		$line = null;
		foreach($vcard->children as $i => $property) {
			if(substr(md5($property->serialize()), 0, 8) == $checksum ) {
				$line = $i;
				break;
			}
		}
		return $line;
	}

	/**
	 * @return array of vcard prop => label
	 */
	public static function getIMOptions($im = null) {
		$l10n = self::$l10n;
		$ims = array(
				'jabber' => array(
					'displayname' => (string)$l10n->t('Jabber'),
					'xname' => 'X-JABBER',
					'protocol' => 'xmpp',
				),
				'aim' => array(
					'displayname' => (string)$l10n->t('AIM'),
					'xname' => 'X-AIM',
					'protocol' => 'aim',
				),
				'msn' => array(
					'displayname' => (string)$l10n->t('MSN'),
					'xname' => 'X-MSN',
					'protocol' => 'msn',
				),
				'twitter' => array(
					'displayname' => (string)$l10n->t('Twitter'),
					'xname' => 'X-TWITTER',
					'protocol' => 'twitter',
				),
				'googletalk' => array(
					'displayname' => (string)$l10n->t('GoogleTalk'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'facebook' => array(
					'displayname' => (string)$l10n->t('Facebook'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'xmpp' => array(
					'displayname' => (string)$l10n->t('XMPP'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'icq' => array(
					'displayname' => (string)$l10n->t('ICQ'),
					'xname' => 'X-ICQ',
					'protocol' => 'icq',
				),
				'yahoo' => array(
					'displayname' => (string)$l10n->t('Yahoo'),
					'xname' => 'X-YAHOO',
					'protocol' => 'ymsgr',
				),
				'skype' => array(
					'displayname' => (string)$l10n->t('Skype'),
					'xname' => 'X-SKYPE',
					'protocol' => 'skype',
				),
				'qq' => array(
					'displayname' => (string)$l10n->t('QQ'),
					'xname' => 'X-SKYPE',
					'protocol' => 'x-apple',
				),
				'gadugadu' => array(
					'displayname' => (string)$l10n->t('GaduGadu'),
					'xname' => 'X-SKYPE',
					'protocol' => 'x-apple',
				),
		);
		if(is_null($im)) {
			return $ims;
		} else {
			$ims['ymsgr'] = $ims['yahoo'];
			$ims['gtalk'] = $ims['googletalk'];
			return isset($ims[$im]) ? $ims[$im] : null;
		}
	}

	/**
	 * @return types for property $prop
	 */
	public static function getTypesOfProperty($prop) {
		$l = self::$l10n;
		switch($prop) {
			case 'ADR':
			case 'IMPP':
				return array(
					'WORK' => $l->t('Work'),
					'HOME' => $l->t('Home'),
					'OTHER' =>  $l->t('Other'),
				);
			case 'TEL':
				return array(
					'HOME'  =>  $l->t('Home'),
					'CELL'  =>  $l->t('Mobile'),
					'WORK'  =>  $l->t('Work'),
					'TEXT'  =>  $l->t('Text'),
					'VOICE' =>  $l->t('Voice'),
					'MSG'   =>  $l->t('Message'),
					'FAX'   =>  $l->t('Fax'),
					'VIDEO' =>  $l->t('Video'),
					'PAGER' =>  $l->t('Pager'),
					'OTHER' =>  $l->t('Other'),
				);
			case 'EMAIL':
				return array(
					'WORK' => $l->t('Work'),
					'HOME' => $l->t('Home'),
					'INTERNET' => $l->t('Internet'),
					'OTHER' =>  $l->t('Other'),
				);
		}
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
	 * @brief returns the default categories of ownCloud
	 * @return (array) $categories
	 */
	public static function getDefaultCategories() {
		return array(
			(string)self::$l10n->t('Friends'),
			(string)self::$l10n->t('Family'),
			(string)self::$l10n->t('Work'),
			(string)self::$l10n->t('Other'),
		);
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

	/**
	 * @brief Get the last modification time.
	 * @param OC_VObject|Sabre\VObject\Component|integer|null $contact
	 * @returns DateTime | null
	 */
	public static function lastModified($contact = null) {
		if(is_null($contact)) {
			// FIXME: This doesn't take shared address books into account.
			$sql = 'SELECT MAX(`lastmodified`) FROM `*PREFIX*contacts_cards`, `*PREFIX*contacts_addressbooks` ' .
				'WHERE  `*PREFIX*contacts_cards`.`addressbookid` = `*PREFIX*contacts_addressbooks`.`id` AND ' .
				'`*PREFIX*contacts_addressbooks`.`userid` = ?';
			$stmt = \OCP\DB::prepare($sql);
			$result = $stmt->execute(array(\OCP\USER::getUser()));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				return null;
			}
			$lastModified = $result->fetchOne();
			if(!is_null($lastModified)) {
				return new \DateTime('@' . $lastModified);
			}
		} else if(is_numeric($contact)) {
			$card = VCard::find($contact, array('lastmodified'));
			return ($card ? new \DateTime('@' . $card['lastmodified']) : null);
		} elseif($contact instanceof \OC_VObject || $contact instanceof VObject\Component) {
			return isset($contact->REV) 
				? \DateTime::createFromFormat(\DateTime::W3C, $contact->REV)
				: null;
		}
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
