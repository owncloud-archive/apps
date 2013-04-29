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

namespace OCA\Contacts;

// Firefox and Konqueror tries to download application/json for me.  --Arthur
\OCP\JSON::setContentTypeHeader('text/plain');
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('contacts');
require_once 'loghandler.php';

$contactid = isset($_GET['contactid']) ? $_GET['contactid'] : '';
$addressbookid = isset($_GET['addressbookid']) ? $_GET['addressbookid'] : '';
$backend = isset($_GET['backend']) ? $_GET['backend'] : '';

if(!$contactid) {
	bailOut('Missing contact id.');
}

if(!$addressbookid) {
	bailOut('Missing address book id.');
}

$app = new App();
// FIXME: Get backend and addressbookid
$contact = $app->getContact($backend, $addressbookid, $contactid);
if(!$contact) {
	\OC_Cache::remove($tmpkey);
	bailOut(App::$l10n
		->t('Error getting contact object.'));
}
// invalid vcard
if(!$contact) {
	bailOut(App::$l10n->t('Error reading contact photo.'));
} else {
	$image = new \OC_Image();
	if(!isset($contact->PHOTO) || !$image->loadFromBase64((string)$contact->PHOTO)) {
		if(isset($contact->LOGO)) {
			$image->loadFromBase64((string)$contact->LOGO);
		}
	}
	if($image->valid()) {
		$tmpkey = 'contact-photo-'.$contact->UID;
		if(\OC_Cache::set($tmpkey, $image->data(), 600)) {
			\OCP\JSON::success(array('data' => array('id'=>$_GET['id'], 'tmp'=>$tmpkey)));
			exit();
		} else {
			bailOut(App::$l10n->t('Error saving temporary file.'));
		}
	} else {
		bailOut(App::$l10n->t('The loading photo is not valid.'));
	}
}
