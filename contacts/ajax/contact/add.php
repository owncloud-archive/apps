<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
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

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::callCheck();

require_once __DIR__.'/../loghandler.php';

$aid = isset($_POST['aid']) ? $_POST['aid'] : null;
if(!$aid) {
	$aid = min(OCA\Contacts\Addressbook::activeIds()); // first active addressbook.
}

debug('Adding new contact to: ' . $aid);

$isnew = isset($_POST['isnew']) ? $_POST['isnew'] : false;
$fn = trim($_POST['fn']);
$n = trim($_POST['n']);

$vobject = Sabre\VObject\Component::create('VCARD');
debug('vobject: ', print_r($vobject->serialize(), true));
$vcard = new OC_VObject($vobject);
$vcard->setUID();
$vcard->setString('FN', $fn);

$id = null;
try {
	$id = OCA\Contacts\VCard::add($aid, $vcard, null, $isnew);
} catch(Exception $e) {
	bailOut($e->getMessage());
}

if(!$id) {
	bailOut('There was an error adding the contact.');
}

$lastmodified = OCA\Contacts\App::lastModified($vcard);
if(!$lastmodified) {
	$lastmodified = new DateTime();
}
OCP\JSON::success(array(
	'data' => array(
		'id' => $id,
		'aid' => $aid,
		'details' => OCA\Contacts\VCard::structureContact($vcard),
		'lastmodified' => $lastmodified->format('U')
	)
));
