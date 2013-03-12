<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
 * @copyright 2012 Thomas Tanghus <thomas@tanghus.net>
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
/*
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE contacts_cards (
 * id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 * addressbookid INT(11) UNSIGNED NOT NULL,
 * fullname VARCHAR(255),
 * carddata TEXT,
 * uri VARCHAR(100),
 * lastmodified INT(11) UNSIGNED
 * );
 */

namespace OCA\Contacts;

use Sabre\VObject;

/**
 * This class manages our vCards
 */
class VCard {
	/**
	 * @brief Returns all cards of an address book
	 * @param integer $id
	 * @param integer $offset
	 * @param integer $limit
	 * @param array $fields An array of the fields to return. Defaults to all.
	 * @return array|false
	 *
	 * The cards are associative arrays. You'll find the original vCard in
	 * ['carddata']
	 */
	public static function all($id, $offset=null, $limit=null, $fields = array()) {
		$result = null;
		\OCP\Util::writeLog('contacts', __METHOD__.'count fields:' . count($fields), \OCP\Util::DEBUG);
		$qfields = count($fields) > 0
			? '`' . implode('`,`', $fields) . '`'
			: '*';
		if(is_array($id) && count($id)) {
			$id_sql = join(',', array_fill(0, count($id), '?'));
			$sql = 'SELECT ' . $qfields . ' FROM `*PREFIX*contacts_cards` WHERE `addressbookid` IN ('.$id_sql.') ORDER BY `fullname`';
			try {
				$stmt = \OCP\DB::prepare($sql, $limit, $offset);
				$result = $stmt->execute($id);
				if (\OC_DB::isError($result)) {
					\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
				\OCP\Util::writeLog('contacts', __METHOD__.', ids: ' . join(',', $id), \OCP\Util::DEBUG);
				\OCP\Util::writeLog('contacts', __METHOD__.'SQL:' . $sql, \OCP\Util::DEBUG);
				return false;
			}
		} elseif(is_int($id) || is_string($id)) {
			try {
				$sql = 'SELECT ' . $qfields . ' FROM `*PREFIX*contacts_cards` WHERE `addressbookid` = ? ORDER BY `fullname`';
				$stmt = \OCP\DB::prepare($sql, $limit, $offset);
				$result = $stmt->execute(array($id));
				if (\OC_DB::isError($result)) {
					\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				\OCP\Util::writeLog('contacts', __METHOD__.', ids: '. $id, \OCP\Util::DEBUG);
				return false;
			}
		} else {
			\OCP\Util::writeLog('contacts', __METHOD__
				. '. Addressbook id(s) argument is empty: '
				.  print_r($id, true), \OCP\Util::DEBUG);
			return false;
		}
		$cards = array();
		if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				$cards[] = $row;
			}
		}

		return $cards;
	}

	/**
	 * @brief Returns a card
	 * @param integer $id
	 * @param array $fields An array of the fields to return. Defaults to all.
	 * @return associative array or false.
	 */
	public static function find($id, $fields = array() ) {
		try {
			$qfields = count($fields) > 0
				? '`' . implode('`,`', $fields) . '`'
				: '*';
			$stmt = \OCP\DB::prepare( 'SELECT ' . $qfields . ' FROM `*PREFIX*contacts_cards` WHERE `id` = ?' );
			$result = $stmt->execute(array($id));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '. $id, \OCP\Util::DEBUG);
			return false;
		}

		return $result->fetchRow();
	}

	/**
	 * @brief finds a card by its DAV Data
	 * @param integer $aid Addressbook id
	 * @param string $uri the uri ('filename')
	 * @return associative array or false.
	 */
	public static function findWhereDAVDataIs($aid, $uri) {
		try {
			$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*contacts_cards` WHERE `addressbookid` = ? AND `uri` = ?' );
			$result = $stmt->execute(array($aid,$uri));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', aid: '.$aid.' uri'.$uri, \OCP\Util::DEBUG);
			return false;
		}

		return $result->fetchRow();
	}

	/**
	* @brief Format property TYPE parameters for upgrading from v. 2.1
	* @param $property Reference to a Sabre_VObject_Property.
	* In version 2.1 e.g. a phone can be formatted like: TEL;HOME;CELL:123456789
	* This has to be changed to either TEL;TYPE=HOME,CELL:123456789 or TEL;TYPE=HOME;TYPE=CELL:123456789 - both are valid.
	*/
	public static function formatPropertyTypes(&$property) {
		foreach($property->parameters as $key=>&$parameter) {
			$types = App::getTypesOfProperty($property->name);
			if(is_array($types) && in_array(strtoupper($parameter->name), array_keys($types)) || strtoupper($parameter->name) == 'PREF') {
				$property->parameters[] = new \Sabre\VObject\Parameter('TYPE', $parameter->name);
			}
			unset($property->parameters[$key]);
		}
	}

	/**
	* @brief Decode properties for upgrading from v. 2.1
	* @param $property Reference to a Sabre_VObject_Property.
	* The only encoding allowed in version 3.0 is 'b' for binary. All encoded strings
	* must therefor be decoded and the parameters removed.
	*/
	public static function decodeProperty(&$property) {
		// Check out for encoded string and decode them :-[
		foreach($property->parameters as $key=>&$parameter) {
			if(strtoupper($parameter->name) == 'ENCODING') {
				if(strtoupper($parameter->value) == 'QUOTED-PRINTABLE') { // what kind of other encodings could be used?
					// Decode quoted-printable and strip any control chars
					// except \n and \r
					$property->value = preg_replace(
								'/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/',
								'',
								quoted_printable_decode($property->value)
					);
					unset($property->parameters[$key]);
				}
			} elseif(strtoupper($parameter->name) == 'CHARSET') {
					unset($property->parameters[$key]);
			}
		}
	}

	/**
	* @brief Checks if a contact with the same UID already exist in the address book.
	* @param $aid Address book ID.
	* @param $uid UID (passed by reference).
	* @returns true if the UID has been changed.
	*/
	protected static function trueUID($aid, &$uid) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*contacts_cards` WHERE `addressbookid` = ? AND `uri` = ?' );
		$uri = $uid.'.vcf';
		try {
			$result = $stmt->execute(array($aid,$uri));
			if (\OC_DB::isError($result)) {
				\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', aid: '.$aid.' uid'.$uid, \OCP\Util::DEBUG);
			return false;
		}
		if($result->numRows() > 0) {
			while(true) {
				$tmpuid = substr(md5(rand().time()), 0, 10);
				$uri = $tmpuid.'.vcf';
				$result = $stmt->execute(array($aid, $uri));
				if($result->numRows() > 0) {
					continue;
				} else {
					$uid = $tmpuid;
					return true;
				}
			}
		} else {
			return false;
		}
	}

	/**
	* @brief Tries to update imported VCards to adhere to rfc2426 (VERSION: 3.0) and add mandatory fields if missing.
	* @param aid Address book id.
	* @param vcard A Sabre\VObject\Component of type VCARD (passed by reference).
	*/
	protected static function updateValuesFromAdd($aid, &$vcard) { // any suggestions for a better method name? ;-)
		$stringprops = array('N', 'FN', 'ORG', 'NICK', 'ADR', 'NOTE');
		$typeprops = array('ADR', 'TEL', 'EMAIL');
		$upgrade = false;
		$fn = $n = $uid = $email = $org = null;
		$version = isset($vcard->VERSION) ? $vcard->VERSION : null;
		// Add version if needed
		if($version && $version < '3.0') {
			$upgrade = true;
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Updating from version: '.$version, OCP\Util::DEBUG);
		}
		foreach($vcard->children as &$property) {
			// Decode string properties and remove obsolete properties.
			if($upgrade && in_array($property->name, $stringprops)) {
				self::decodeProperty($property);
			}
			if(function_exists('iconv')) {
				$property->value = str_replace("\r\n", "\n", iconv(mb_detect_encoding($property->value, 'UTF-8, ISO-8859-1'), 'utf-8', $property->value));
			} else {
				$property->value = str_replace("\r\n", "\n", mb_convert_encoding($property->value, 'UTF-8', mb_detect_encoding($property->value, 'UTF-8, ISO-8859-1'), $property->value));
			}
			if(in_array($property->name, $stringprops)) {
				$property->value = strip_tags($property->value);
			}
			// Fix format of type parameters.
			if($upgrade && in_array($property->name, $typeprops)) {
				//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. before: '.$property->serialize(), OCP\Util::DEBUG);
				self::formatPropertyTypes($property);
				//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. after: '.$property->serialize(), OCP\Util::DEBUG);
			}
			if($property->name == 'FN') {
				$fn = $property->value;
			}
			else if($property->name == 'N') {
				$n = $property->value;
			}
			else if($property->name == 'UID') {
				$uid = $property->value;
			}
			else if($property->name == 'ORG') {
				$org = $property->value;
			}
			else if($property->name == 'EMAIL' && is_null($email)) { // only use the first email as substitute for missing N or FN.
				$email = $property->value;
			}
		}
		// Check for missing 'N', 'FN' and 'UID' properties
		if(!$fn) {
			if($n && $n != ';;;;') {
				$fn = join(' ', array_reverse(array_slice(explode(';', $n), 0, 2)));
			} elseif($email) {
				$fn = $email;
			} elseif($org) {
				$fn = $org;
			} else {
				$fn = 'Unknown Name';
			}
			$vcard->FN = $fn;
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Added missing \'FN\' field: '.$fn, OCP\Util::DEBUG);
		}
		if(!$n || $n == ';;;;') { // Fix missing 'N' field. Ugly hack ahead ;-)
			$slice = array_reverse(array_slice(explode(' ', $fn), 0, 2)); // Take 2 first name parts of 'FN' and reverse.
			if(count($slice) < 2) { // If not enought, add one more...
				$slice[] = "";
			}
			$n = implode(';', $slice).';;;';
			$vcard->N = $n;
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Added missing \'N\' field: '.$n, OCP\Util::DEBUG);
		}
		if(!$uid) {
			$uid = substr(md5(rand().time()), 0, 10);
			$vcard->add('UID', $uid);
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Added missing \'UID\' field: '.$uid, OCP\Util::DEBUG);
		}
		if(self::trueUID($aid, $uid)) {
			$vcard->{'UID'} = $uid;
		}
		$now = new \DateTime;
		$vcard->{'REV'} = $now->format(\DateTime::W3C);
	}

	/**
	 * @brief Adds a card
	 * @param $aid integer Addressbook id
	 * @param $card Sabre\VObject\Component  vCard file
	 * @param $uri string the uri of the card, default based on the UID
	 * @param $isChecked boolean If the vCard should be checked for validity and version.
	 * @return insertid on success or false.
	 */
	public static function add($aid, VObject\Component $card, $uri=null, $isChecked=false) {
		if(is_null($card)) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ', No vCard supplied', \OCP\Util::ERROR);
			return null;
		};
		$addressbook = Addressbook::find($aid);
		if ($addressbook['userid'] != \OCP\User::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource('addressbook', $aid);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_CREATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to add contacts to this addressbook.'
					)
				);
			}
		}
		if(!$isChecked) {
			self::updateValuesFromAdd($aid, $card);
		}
		$card->{'VERSION'} = '3.0';
		// Add product ID is missing.
		//$prodid = trim($card->getAsString('PRODID'));
		//if(!$prodid) {
		if(!isset($card->PRODID)) {
			$appinfo = \OCP\App::getAppInfo('contacts');
			$appversion = \OCP\App::getAppVersion('contacts');
			$prodid = '-//ownCloud//NONSGML '.$appinfo['name'].' '.$appversion.'//EN';
			$card->add('PRODID', $prodid);
		}

		$fn = isset($card->FN) ? $card->FN : '';

		$uri = isset($uri) ? $uri : $card->UID . '.vcf';

		$data = $card->serialize();
		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*contacts_cards` (`addressbookid`,`fullname`,`carddata`,`uri`,`lastmodified`) VALUES(?,?,?,?,?)' );
		try {
			$result = $stmt->execute(array($aid, $fn, $data, $uri, time()));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', aid: '.$aid.' uri'.$uri, \OCP\Util::DEBUG);
			return false;
		}
		$newid = \OCP\DB::insertid('*PREFIX*contacts_cards');
		App::loadCategoriesFromVCard($newid, $card);
		App::updateDBProperties($newid, $card);
		App::cacheThumbnail($newid);

		Addressbook::touch($aid);
		\OC_Hook::emit('\OCA\Contacts\VCard', 'post_createVCard', $newid);
		return $newid;
	}

	/**
	 * @brief Adds a card with the data provided by sabredav
	 * @param integer $id Addressbook id
	 * @param string $uri   the uri the card will have
	 * @param string $data  vCard file
	 * @returns integer|false insertid or false on error
	 */
	public static function addFromDAVData($id, $uri, $data) {
		try {
			$vcard = \Sabre\VObject\Reader::read($data);
			return self::add($id, $vcard, $uri);
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
	}

	/**
	 * @brief Mass updates an array of cards
	 * @param array $objects  An array of [id, carddata].
	 */
	public static function updateDataByID($objects) {
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*contacts_cards` SET `carddata` = ?, `lastmodified` = ? WHERE `id` = ?' );
		$now = new \DateTime;
		foreach($objects as $object) {
			$vcard = null;
			try {
				$vcard = Sabre\VObject\Reader::read($contact['carddata']);
			} catch(\Exception $e) {
				\OC_Log::write('contacts', __METHOD__. $e->getMessage(), \OCP\Util::ERROR);
			}
			if(!is_null($vcard)) {
				$oldcard = self::find($object[0]);
				if (!$oldcard) {
					return false;
				}

				$addressbook = Addressbook::find($oldcard['addressbookid']);
				if ($addressbook['userid'] != \OCP\User::getUser()) {
					$sharedContact = \OCP\Share::getItemSharedWithBySource('contact', $object[0], \OCP\Share::FORMAT_NONE, null, true);
					if (!$sharedContact || !($sharedContact['permissions'] & \OCP\PERMISSION_UPDATE)) {
						return false;
					}
				}
				$vcard->{'REV'} = $now->format(\DateTime::W3C);
				$data = $vcard->serialize();
				try {
					$result = $stmt->execute(array($data,time(),$object[0]));
					if (\OC_DB::isError($result)) {
						\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
					}
					//OCP\Util::writeLog('contacts','OCA\Contacts\VCard::updateDataByID, id: '.$object[0].': '.$object[1],OCP\Util::DEBUG);
				} catch(\Exception $e) {
					\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
					\OCP\Util::writeLog('contacts', __METHOD__.', id: '.$object[0], \OCP\Util::DEBUG);
				}
				App::updateDBProperties($object[0], $vcard);
			}
		}
	}

	/**
	 * @brief edits a card
	 * @param integer $id id of card
	 * @param Sabre\VObject\Component $card  vCard file
	 * @return boolean true on success, otherwise an exception will be thrown
	 */
	public static function edit($id, VObject\Component $card) {
		$oldcard = self::find($id);
		if (!$oldcard) {
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '
				. $id . ' not found.', \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'Could not find the vCard with ID.' . $id
				)
			);
		}
		if(is_null($card)) {
			return false;
		}
		// NOTE: Owner checks are being made in the ajax files, which should be done
		// inside the lib files to prevent any redundancies with sharing checks
		$addressbook = Addressbook::find($oldcard['addressbookid']);
		if ($addressbook['userid'] != \OCP\User::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(
				'addressbook',
				$oldcard['addressbookid'],
				\OCP\Share::FORMAT_NONE, null, true);
			$sharedContact = \OCP\Share::getItemSharedWithBySource('contact', $id, \OCP\Share::FORMAT_NONE, null, true);
			$addressbook_permissions = 0;
			$contact_permissions = 0;
			if ($sharedAddressbook) {
				$addressbook_permissions = $sharedAddressbook['permissions'];
			}
			if ($sharedContact) {
				$contact_permissions = $sharedEvent['permissions'];
			}
			$permissions = max($addressbook_permissions, $contact_permissions);
			if (!($permissions & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to edit this contact.'
					)
				);
			}
		}
		App::loadCategoriesFromVCard($id, $card);

		$fn = isset($card->FN) ? $card->FN : '';

		$now = new \DateTime;
		$card->REV = $now->format(\DateTime::W3C);

		$data = $card->serialize();
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*contacts_cards` SET `fullname` = ?,`carddata` = ?, `lastmodified` = ? WHERE `id` = ?' );
		try {
			$result = $stmt->execute(array($fn, $data, time(), $id));
			if (\OC_DB::isError($result)) {
				\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id'.$id, \OCP\Util::DEBUG);
			return false;
		}

		App::cacheThumbnail($oldcard['id']);
		App::updateDBProperties($id, $card);
		Addressbook::touch($oldcard['addressbookid']);
		\OC_Hook::emit('\OCA\Contacts\VCard', 'post_updateVCard', $id);
		return true;
	}

	/**
	 * @brief edits a card with the data provided by sabredav
	 * @param integer $id Addressbook id
	 * @param string $uri   the uri of the card
	 * @param string $data  vCard file
	 * @return boolean
	 */
	public static function editFromDAVData($aid, $uri, $data) {
		$oldcard = self::findWhereDAVDataIs($aid, $uri);
		try {
			$vcard = \Sabre\VObject\Reader::read($data);
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', Unable to parse VCARD, : ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
		try {
			self::edit($oldcard['id'], $vcard);
			return true;
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '
				. $e->getMessage() . ', '
				. \OCP\USER::getUser(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', uri'
				. $uri, \OCP\Util::DEBUG);
			return false;
		}
	}

	/**
	 * @brief deletes a card
	 * @param integer $id id of card
	 * @return boolean true on success, otherwise an exception will be thrown
	 */
	public static function delete($id) {
		$contact = self::find($id);
		if (!$contact) {
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '
				. $id . ' not found.', \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'Could not find the vCard with ID: ' . $id, 404
				)
			);
		}
		$addressbook = Addressbook::find($contact['addressbookid']);
		if(!$addressbook) {
			throw new \Exception(
				App::$l10n->t(
					'Could not find the Addressbook with ID: '
					. $contact['addressbookid'], 404
				)
			);
		}

		if ($addressbook['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			\OCP\Util::writeLog('contacts', __METHOD__.', '
				. $addressbook['userid'] . ' != ' . \OCP\User::getUser(), \OCP\Util::DEBUG);
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(
				'addressbook',
				$contact['addressbookid'],
				\OCP\Share::FORMAT_NONE, null, true);
			$sharedContact = \OCP\Share::getItemSharedWithBySource(
				'contact',
				$id,
				\OCP\Share::FORMAT_NONE, null, true);
			$addressbook_permissions = 0;
			$contact_permissions = 0;
			if ($sharedAddressbook) {
				$addressbook_permissions = $sharedAddressbook['permissions'];
			}
			if ($sharedContact) {
				$contact_permissions = $sharedEvent['permissions'];
			}
			$permissions = max($addressbook_permissions, $contact_permissions);

			if (!($permissions & \OCP\PERMISSION_DELETE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to delete this contact.', 403
					)
				);
			}
		}
		$aid = $contact['addressbookid'];
		\OC_Hook::emit('\OCA\Contacts\VCard', 'pre_deleteVCard',
			array('aid' => null, 'id' => $id, 'uri' => null)
		);
		$stmt = \OCP\DB::prepare('DELETE FROM `*PREFIX*contacts_cards` WHERE `id` = ?');
		try {
			$stmt->execute(array($id));
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '
				. $id, \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'There was an error deleting this contact.'
				)
			);
		}

		App::updateDBProperties($id);
		App::getVCategories()->purgeObject($id);
		Addressbook::touch($addressbook['id']);

		\OCP\Share::unshareAll('contact', $id);
		return true;
	}

	/**
	 * @brief deletes a card with the data provided by sabredav
	 * @param integer $aid Addressbook id
	 * @param string $uri the uri of the card
	 * @return boolean
	 */
	public static function deleteFromDAVData($aid, $uri) {
		$contact = self::findWhereDAVDataIs($aid, $uri);
		if(!$contact) {
			\OCP\Util::writeLog('contacts', __METHOD__.', contact not found: '
				. $uri, \OCP\Util::DEBUG);
			throw new \Sabre_DAV_Exception_NotFound(
				App::$l10n->t(
					'Contact not found.'
				)
			);
		}
		$id = $contact['id'];
		try {
			return self::delete($id);
		} catch (\Exception $e) {
			switch($e->getCode()) {
				case 403:
					\OCP\Util::writeLog('contacts', __METHOD__.', forbidden: '
						. $uri, \OCP\Util::DEBUG);
					throw new \Sabre_DAV_Exception_Forbidden(
						App::$l10n->t(
							$e->getMessage()
						)
					);
					break;
				case 404:
					\OCP\Util::writeLog('contacts', __METHOD__.', contact not found: '
						. $uri, \OCP\Util::DEBUG);
					throw new \Sabre_DAV_Exception_NotFound(
						App::$l10n->t(
							$e->getMessage()
						)
					);
					break;
				default:
					throw $e;
					break;
			}
		}
		return true;
	}

	/**
	 * @brief Data structure of vCard
	 * @param Sabre\VObject\Component $property
	 * @return associative array
	 *
	 * look at code ...
	 */
	public static function structureContact($vcard) {
		$details = array();

		foreach($vcard->children as $property) {
			$pname = $property->name;
			$temp = self::structureProperty($property);
			if(!is_null($temp)) {
				// Get Apple X-ABLabels
				if(isset($vcard->{$property->group . '.X-ABLABEL'})) {
					$temp['label'] = $vcard->{$property->group . '.X-ABLABEL'}->value;
					if($temp['label'] == '_$!<Other>!$_') {
						$temp['label'] = App::$l10n->t('Other');
					}
					if($temp['label'] == '_$!<HomePage>!$_') {
						$temp['label'] = App::$l10n->t('HomePage');
					}
				}
				if(array_key_exists($pname, $details)) {
					$details[$pname][] = $temp;
				}
				else{
					$details[$pname] = array($temp);
				}
			}
		}
		return $details;
	}

	/**
	 * @brief Data structure of properties
	 * @param object $property
	 * @return associative array
	 *
	 * returns an associative array with
	 * ['name'] name of property
	 * ['value'] htmlspecialchars escaped value of property
	 * ['parameters'] associative array name=>value
	 * ['checksum'] checksum of whole property
	 * NOTE: $value is not escaped anymore. It shouldn't make any difference
	 * but we should look out for any problems.
	 */
	public static function structureProperty($property) {
		if(!in_array($property->name, App::$index_properties)) {
			return;
		}
		$value = $property->value;
		if($property->name == 'ADR' || $property->name == 'N' || $property->name == 'ORG' || $property->name == 'CATEGORIES') {
			$value = $property->getParts();
			$value = array_map('trim', $value);
		}
		elseif($property->name == 'BDAY') {
			if(strpos($value, '-') === false) {
				if(strlen($value) >= 8) {
					$value = substr($value, 0, 4).'-'.substr($value, 4, 2).'-'.substr($value, 6, 2);
				} else {
					return null; // Badly malformed :-(
				}
			}
		} elseif($property->name == 'PHOTO') {
			$value = true;
		}
		elseif($property->name == 'IMPP') {
			if(strpos($value, ':') !== false) {
				$value = explode(':', $value);
				$protocol = array_shift($value);
				if(!isset($property['X-SERVICE-TYPE'])) {
					$property['X-SERVICE-TYPE'] = strtoupper(\OCP\Util::sanitizeHTML($protocol));
				}
				$value = implode('', $value);
			}
		}
		if(is_string($value)) {
			$value = strtr($value, array('\,' => ',', '\;' => ';'));
		}
		$temp = array(
			//'name' => $property->name,
			'value' => \OCP\Util::sanitizeHTML($value),
			'parameters' => array()
		);

		// This cuts around a 3rd off of the json response size.
		if(in_array($property->name, App::$multi_properties)) {
			$temp['checksum'] = substr(md5($property->serialize()), 0, 8);
		}
		foreach($property->parameters as $parameter) {
			// Faulty entries by kaddressbook
			// Actually TYPE=PREF is correct according to RFC 2426
			// but this way is more handy in the UI. Tanghus.
			if($parameter->name == 'TYPE' && strtoupper($parameter->value) == 'PREF') {
				$parameter->name = 'PREF';
				$parameter->value = '1';
			}
			// NOTE: Apparently Sabre_VObject_Reader can't always deal with value list parameters
			// like TYPE=HOME,CELL,VOICE. Tanghus.
			// TODO: Check if parameter is has commas and split + merge if so.
			if ($parameter->name == 'TYPE') {
				$pvalue = $parameter->value;
				if(is_string($pvalue) && strpos($pvalue, ',') !== false) {
					$pvalue = array_map('trim', explode(',', $pvalue));
				}
				$pvalue = is_array($pvalue) ? $pvalue : array($pvalue);
				if (isset($temp['parameters'][$parameter->name])) {
					$temp['parameters'][$parameter->name][] = \OCP\Util::sanitizeHTML($pvalue);
				}
				else {
					$temp['parameters'][$parameter->name] = \OCP\Util::sanitizeHTML($pvalue);
				}
			}
			else{
				$temp['parameters'][$parameter->name] = \OCP\Util::sanitizeHTML($parameter->value);
			}
		}
		return $temp;
	}

	/**
	 * @brief Move card(s) to an address book
	 * @param integer $aid Address book id
	 * @param $id Array or integer of cards to be moved.
	 * @return boolean
	 *
	 */
	public static function moveToAddressBook($aid, $id, $isAddressbook = false) {
		Addressbook::find($aid);
		$addressbook = Addressbook::find($aid);
		if ($addressbook['userid'] != \OCP\User::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource('addressbook', $aid);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_CREATE)) {
				return false;
			}
		}
		if(is_array($id)) {
			foreach ($id as $index => $cardId) {
				$card = self::find($cardId);
				if (!$card) {
					unset($id[$index]);
				}
				$oldAddressbook = Addressbook::find($card['addressbookid']);
				if ($oldAddressbook['userid'] != \OCP\User::getUser()) {
					$sharedContact = \OCP\Share::getItemSharedWithBySource('contact', $cardId, \OCP\Share::FORMAT_NONE, null, true);
					if (!$sharedContact || !($sharedContact['permissions'] & \OCP\PERMISSION_DELETE)) {
						unset($id[$index]);
					}
				}
			}
			$id_sql = join(',', array_fill(0, count($id), '?'));
			$prep = 'UPDATE `*PREFIX*contacts_cards` SET `addressbookid` = ? WHERE `id` IN ('.$id_sql.')';
			try {
				$stmt = \OCP\DB::prepare( $prep );
				//$aid = array($aid);
				$vals = array_merge((array)$aid, $id);
				$result = $stmt->execute($vals);
				if (\OC_DB::isError($result)) {
					\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				\OCP\Util::writeLog('contacts', __METHOD__.', ids: '.join(',', $vals), \OCP\Util::DEBUG);
				\OCP\Util::writeLog('contacts', __METHOD__.', SQL:'.$prep, \OCP\Util::DEBUG);
				return false;
			}
		} else {
			$stmt = null;
			if($isAddressbook) {
				$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*contacts_cards` SET `addressbookid` = ? WHERE `addressbookid` = ?' );
			} else {
				$card = self::find($id);
				if (!$card) {
					return false;
				}
				$oldAddressbook = Addressbook::find($card['addressbookid']);
				if ($oldAddressbook['userid'] != \OCP\User::getUser()) {
					$sharedContact = \OCP\Share::getItemSharedWithBySource('contact', $id, \OCP\Share::FORMAT_NONE, null, true);
					if (!$sharedContact || !($sharedContact['permissions'] & \OCP\PERMISSION_DELETE)) {
						return false;
					}
				}
				$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*contacts_cards` SET `addressbookid` = ? WHERE `id` = ?' );
			}
			try {
				$result = $stmt->execute(array($aid, $id));
				if (\OC_DB::isError($result)) {
					\OC_Log::write('contacts', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::DEBUG);
				\OCP\Util::writeLog('contacts', __METHOD__.' id: '.$id, \OCP\Util::DEBUG);
				return false;
			}
		}
		\OC_Hook::emit('\OCA\Contacts\VCard', 'post_moveToAddressbook', array('aid' => $aid, 'id' => $id));
		Addressbook::touch($aid);
		return true;
	}
}
