<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
 * @copyright 2012-2013 Thomas Tanghus <thomas@tanghus.net>
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
	 * @brief deletes a card
	 * @param integer $id id of card
	 * @return boolean true on success, otherwise an exception will be thrown
	 */
	public static function delete($id) {

		App::updateDBProperties($id);
		App::getVCategories()->purgeObject($id);
		Addressbook::touch($addressbook['id']);

		\OCP\Share::unshareAll('contact', $id);
		return true;
	}

}
