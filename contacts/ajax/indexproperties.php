<?php
/**
 * ownCloud - Contacts
 *
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

/**
 * @brief Index vCard properties for easier searching
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::callCheck();

require_once 'loghandler.php';

$addressbooks = OCA\Contacts\Addressbook::all(OCP\USER::getUser());

$ids = array();
foreach($addressbooks as $addressbook) {
	$ids[] = $addressbook['id'];
}

$user = OCP\User::getUser();
session_write_close();

$start = 0;
$batchsize = 10;
while($contacts = OCA\Contacts\VCard::all($ids, $start, $batchsize)) {
	OCP\Util::writeLog('contacts', 'Indexing contacts: '.$batchsize.' starting from '.$start, OCP\Util::DEBUG);
	foreach($contacts as $contact) {
		$vcard = OC_VObject::parse($contact['carddata']);
		OCA\Contacts\App::updateDBProperties($contact['id'], $vcard);
	}
	$start += $batchsize;
}

OCP\Config::setUserValue($user, 'contacts', 'contacts_indexed', 'yes');
