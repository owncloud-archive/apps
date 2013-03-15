<?php
/**
 * ownCloud - Backend for Shared contacts
 *
 * @author Thomas Tanghus
 * @copyright 2013 Thomas Tanghus (thomas@tanghus.net)
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

namespace OCA\Contacts\Backend;

use OCA\Contacts;

/**
 * Subclass this class for Cantacts backends
 */

class Shared extends Database {

	public $name = 'shared';
	public $addressbooks = array();

	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * TODO: Create default if none exists.
	 *
	 * @param string $principaluri
	 * @return array
	 */
	public function getAddressBooksForUser($userid = null) {
		$userid = $userid ? $userid : $this->userid;

		$this->addressbooks = \OCP\Share::getItemsSharedWith(
			'addressbook',
			Contacts\Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS
		);

		return $this->addressbooks;
	}

	/**
	 * Returns a specific address book.
	 *
	 * @param string $addressbookid
	 * @param mixed $id Contact ID
	 * @return mixed
	 */
	public function getAddressBook($addressbookid) {
		$addressbook = \OCP\Share::getItemSharedWithBySource(
			'addressbook',
			$addressbookid,
			Contacts\Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS
		);
		// Not sure if I'm doing it wrongly, or if its supposed to return
		// the info in an array?
		return (isset($addressbook['permissions']) ? $addressbook : $addressbook[0]);
	}

	/**
	 * Returns all contacts for a specific addressbook id.
	 *
	 * TODO: Check for parent permissions
	 *
	 * @param string $addressbookid
	 * @param bool $omitdata Don't fetch the entire carddata or vcard.
	 * @return array
	 */
	public function getContacts($addressbookid, $limit = null, $offset = null, $omitdata = false) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' addressbookid: '
		//	. $addressbookid, \OCP\Util::DEBUG);
		$addressbook = \OCP\Share::getItemSharedWithBySource(
			'addressbook',
			$addressbookid,
			Contacts\Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS,
			null, // parameters
			true // includeCollection
		);
		\OCP\Util::writeLog('contacts', __METHOD__.' shared: '
			. print_r($addressbook, true), \OCP\Util::DEBUG);

		$addressbook = $this->getAddressBook($addressbookid);
		$permissions = $addressbook['permissions'];

		$cards = array();
		try {
			$qfields = $omitdata ? '`id`, `fullname` AS `displayname`, `lastmodified`' : '*';
			$query = 'SELECT ' . $qfields . ' FROM `' . $this->cardsTableName
				. '` WHERE `addressbookid` = ? ORDER BY `fullname`';
			$stmt = \OCP\DB::prepare($query, $limit, $offset);
			$result = $stmt->execute(array($addressbookid));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__. 'DB error: '
					. \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return $cards;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.', exception: '
				. $e->getMessage(), \OCP\Util::ERROR);
			return $cards;
		}

		if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				$row['permissions'] = $permissions;
				$cards[] = $row;
			}
		}

		return $cards;
	}

}
