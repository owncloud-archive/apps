<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
 * @copyright 2012-13 Thomas Tanghus <thomas@tanghus.net>
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

// Check if we are a user
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('contacts');
\OCP\JSON::callCheck();

require_once 'loghandler.php';

$image = null;

$x1 = (isset($_POST['x1']) && $_POST['x1']) ? $_POST['x1'] : 0;
//$x2 = isset($_POST['x2']) ? $_POST['x2'] : -1;
$y1 = (isset($_POST['y1']) && $_POST['y1']) ? $_POST['y1'] : 0;
//$y2 = isset($_POST['y2']) ? $_POST['y2'] : -1;
$w = (isset($_POST['w']) && $_POST['w']) ? $_POST['w'] : -1;
$h = (isset($_POST['h']) && $_POST['h']) ? $_POST['h'] : -1;
$tmpkey = isset($_POST['tmpkey']) ? $_POST['tmpkey'] : '';

$contactid = isset($_POST['contactid']) ? $_POST['contactid'] : '';
$addressbookid = isset($_POST['addressbookid']) ? $_POST['addressbookid'] : '';
$backend = isset($_POST['backend']) ? $_POST['backend'] : '';

if($tmpkey == '') {
	bailOut('Missing key to temporary file.');
}

if($contactid == '') {
	bailOut('Missing contact id.');
}

if($addressbookid == '') {
	bailOut('Missing address book id.');
}

\OCP\Util::writeLog('contacts', 'savecrop.php: key: '.$tmpkey, \OCP\Util::DEBUG);

$app = new App();
// FIXME: Get backend and addressbookid
$contact = $app->getContact($backend, $addressbookid, $contactid);
if(!$contact) {
	\OC_Cache::remove($tmpkey);
	bailOut(App::$l10n
		->t('Error getting contact object.'));
}

$data = \OC_Cache::get($tmpkey);
if($data) {
	$image = new \OC_Image();
	if($image->loadFromData($data)) {
		$w = ($w != -1 ? $w : $image->width());
		$h = ($h != -1 ? $h : $image->height());
		\OCP\Util::writeLog('contacts',
			'savecrop.php, x: '.$x1.' y: '.$y1.' w: '.$w.' h: '.$h,
			\OCP\Util::DEBUG);
		if($image->crop($x1, $y1, $w, $h)) {
			if(($image->width() <= 200 && $image->height() <= 200)
				|| $image->resize(200)) {

				// For vCard 3.0 the type must be e.g. JPEG or PNG
				// For version 4.0 the full mimetype should be used.
				// https://tools.ietf.org/html/rfc2426#section-3.1.4
				$type = strval($contact->VERSION) === '4.0'
					? $image->mimeType()
					: strtoupper(array_pop(explode('/', $image->mimeType())));
				if(isset($contact->PHOTO)) {
					\OCP\Util::writeLog('contacts',
						'savecrop.php: PHOTO property exists.',
						\OCP\Util::DEBUG);
					$property = $contact->PHOTO;
					if(!$property) {
						\OC_Cache::remove($tmpkey);
						bailOut(App::$l10n
							->t('Error getting PHOTO property.'));
					}
					$property->setValue(strval($image));
					$property->parameters = [];
					/*$property->ENCODING = 'b';
					$property->TYPE = $type;*/
					$property->parameters[]
						= new \Sabre\VObject\Parameter('ENCODING', 'b');
					$property->parameters[]
						= new \Sabre\VObject\Parameter('TYPE', $image->mimeType());
					$contact->PHOTO = $property;
				} else {
					\OCP\Util::writeLog('contacts',
						'savecrop.php: files: Adding PHOTO property.',
						\OCP\Util::DEBUG);
					$contact->add('PHOTO',
						strval($image), array('ENCODING' => 'b',
						'TYPE' => $type));
				}
				if(!$contact->save()) {
					bailOut(App::$l10n->t('Error saving contact.'));
				}
				$thumbnail = $contact->cacheThumbnail($image);
				\OCP\JSON::success(array(
					'data' => array(
						'id' => $contactid,
						'thumbnail' => $thumbnail,
					)
				));
			} else {
				bailOut(App::$l10n->t('Error resizing image'));
			}
		} else {
			bailOut(App::$l10n->t('Error cropping image'));
		}
	} else {
		bailOut(App::$l10n->t('Error creating temporary image'));
	}
} else {
	bailOut(App::$l10n->t('Error finding image: ').$tmpkey);
}

\OC_Cache::remove($tmpkey);
