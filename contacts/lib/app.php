<?php
/**
 * Copyright (c) 2011 Bart Visscher bartv@thisnet.nl
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * This class manages our app actions
 */
OC_Contacts_App::$l10n = OC_L10N::get('contacts');
class OC_Contacts_App {
	/*
	 * @brief language object for calendar app
	 */

	public static $l10n;
	/*
	 * @brief categories of the user
	 */
	public static $categories = null;

	public static function getAddressbook($id) {
		// TODO: Throw an exception instead of returning json.
		$addressbook = OC_Contacts_Addressbook::find( $id );
		if($addressbook === false || $addressbook['userid'] != OCP\USER::getUser()) {
			if ($addressbook === false) {
				OCP\Util::writeLog('contacts',
					'Addressbook not found: '. $id,
					OCP\Util::ERROR);
				//throw new Exception('Addressbook not found: '. $id);
				OCP\JSON::error(
					array(
						'data' => array(
							'message' => self::$l10n->t('Addressbook not found: ' . $id)
						)
					)
				);
			} else {
				$sharedAddressbook = OCP\Share::getItemSharedWithBySource('addressbook', $id, OC_Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS);
				if ($sharedAddressbook) {
					return $sharedAddressbook[0];
				} else {
					OCP\Util::writeLog('contacts',
						'Addressbook('.$id.') is not from '.OCP\USER::getUser(),
						OCP\Util::ERROR);
					//throw new Exception('This is not your addressbook.');
					OCP\JSON::error(
						array(
							'data' => array(
								'message' => self::$l10n->t('This is not your addressbook.')
							)
						)
					);
				}
			}
		}
		return $addressbook;
	}

	public static function getContactObject($id) {
		$card = OC_Contacts_VCard::find( $id );
		if( $card === false ) {
			OCP\Util::writeLog('contacts',
				'Contact could not be found: '.$id,
				OCP\Util::ERROR);
			OCP\JSON::error(
				array(
					'data' => array(
						'message' => self::$l10n->t('Contact could not be found.')
							.' '.print_r($id, true)
					)
				)
			);
			exit();
		}

		self::getAddressbook( $card['addressbookid'] );//access check
		return $card;
	}

	/**
	 * @brief Gets the VCard as an OC_VObject
	 * @returns The card or null if the card could not be parsed.
	 */
	public static function getContactVCard($id) {
		$card = self::getContactObject( $id );

		$vcard = OC_VObject::parse($card['carddata']);
		if (!is_null($vcard) && !isset($vcard->REV)) {
			$rev = new DateTime('@'.$card['lastmodified']);
			$vcard->setString('REV', $rev->format(DateTime::W3C));
		}
		return $vcard;
	}

	public static function getPropertyLineByChecksum($vcard, $checksum) {
		$line = null;
		for($i=0;$i<count($vcard->children);$i++) {
			if(md5($vcard->children[$i]->serialize()) == $checksum ) {
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
					'protocol' => null,
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
				);
		}
	}

	/**
	 * @brief returns the vcategories object of the user
	 * @return (object) $vcategories
	 */
	public static function getVCategories() {
		if (is_null(self::$categories)) {
			if(OC_VCategories::isEmpty('contact')) {
				self::scanCategories();
			}
			self::$categories = new OC_VCategories('contact',
				null,
				self::getDefaultCategories());
		}
		return self::$categories;
	}

	/**
	 * @brief returns the categories for the user
	 * @return (Array) $categories
	 */
	public static function getCategories() {
		$categories = self::getVCategories()->categories();
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
			$vcaddressbooks = OC_Contacts_Addressbook::all(OCP\USER::getUser());
			if(count($vcaddressbooks) > 0) {
				$vcaddressbookids = array();
				foreach($vcaddressbooks as $vcaddressbook) {
					if($vcaddressbook['userid'] === OCP\User::getUser()) {
						$vcaddressbookids[] = $vcaddressbook['id'];
					}
				}
				$start = 0;
				$batchsize = 10;
				$categories = new OC_VCategories('contact');
				while($vccontacts =
					OC_Contacts_VCard::all($vcaddressbookids, $start, $batchsize)) {
					$cards = array();
					foreach($vccontacts as $vccontact) {
						$cards[] = array($vccontact['id'], $vccontact['carddata']);
					}
					OCP\Util::writeLog('contacts',
						__CLASS__.'::'.__METHOD__
							.', scanning: '.$batchsize.' starting from '.$start,
						OCP\Util::DEBUG);
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
	public static function loadCategoriesFromVCard($id, OC_VObject $contact) {
		self::getVCategories()->loadFromVObject($id, $contact, true);
	}

	/**
	 * @brief Get the last modification time.
	 * @param $vcard OC_VObject
	 * $return DateTime | null
	 */
	public static function lastModified($vcard) {
		$rev = $vcard->getAsString('REV');
		if ($rev) {
			return DateTime::createFromFormat(DateTime::W3C, $rev);
		}
	}

	public static function setLastModifiedHeader($contact) {
		$rev = $contact->getAsString('REV');
		if ($rev) {
			$rev = DateTime::createFromFormat(DateTime::W3C, $rev);
			OCP\Response::setLastModifiedHeader($rev);
		}
	}
}
