<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
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

$id = isset($_POST['id']) ? $_POST['id'] : null;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$checksum = isset($_POST['checksum']) ? $_POST['checksum'] : null;
$l10n = OCA\Contacts\App::$l10n;

$multi_properties = array('EMAIL', 'TEL', 'IMPP', 'ADR', 'URL');

if(!$id) {
	bailOut(OCA\Contacts\App::$l10n->t('id is not set.'));
}

if(!$name) {
	bailOut(OCA\Contacts\App::$l10n->t('element name is not set.'));
}

if(!$checksum && in_array($name, $multi_properties)) {
	bailOut(OCA\Contacts\App::$l10n->t('checksum is not set.'));
}

$vcard = OCA\Contacts\App::getContactVCard( $id );

if(!is_null($checksum)) {
	$line = OCA\Contacts\App::getPropertyLineByChecksum($vcard, $checksum);
	if(is_null($line)) {
		bailOut($l10n->t('Information about vCard is incorrect. Please reload the page.'));
		exit();
	}
	unset($vcard->children[$line]);
} else {
	unset($vcard->{$name});
}

try {
	OCA\Contacts\VCard::edit($id, $vcard);
} catch(Exception $e) {
	bailOut($e->getMessage());
}

OCP\JSON::success(array(
	'data' => array(
		'id' => $id,
		'lastmodified' => OCA\Contacts\App::lastModified($vcard)->format('U'),
	)
));
