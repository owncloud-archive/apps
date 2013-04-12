<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * Copyright (c) 2011, 2012 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2011 Jakob Sack mail@jakobsack.de
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Init owncloud

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('contacts');

function getStandardImage() {
	//OCP\Response::setExpiresHeader('P10D');
	OCP\Response::enableCaching();
	OCP\Response::redirect(OCP\Util::imagePath('contacts', 'person_large.png'));
	exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$etag = null;
$caching = null;
$max_size = 170;

if(!$id || $id === 'new') {
	getStandardImage();
}

if(!extension_loaded('gd') || !function_exists('gd_info')) {
	OCP\Util::writeLog('contacts',
		'photo.php. GD module not installed', OCP\Util::DEBUG);
	getStandardImage();
}

$contact = OCA\Contacts\App::getContactVCard($id);
$image = new OC_Image();
if (!$image || !$contact) {
	getStandardImage();
}
// invalid vcard
if (is_null($contact)) {
	OCP\Util::writeLog('contacts',
		'photo.php. The VCard for ID ' . $id . ' is not RFC compatible',
		OCP\Util::ERROR);
} else {
	// Photo :-)
	if (isset($contact->PHOTO) && $image->loadFromBase64((string)$contact->PHOTO)) {
		// OK
		$etag = md5($contact->PHOTO);
	}
	else
	// Logo :-/
	if (isset($contact->LOGO) && $image->loadFromBase64((string)$contact->LOGO)) {
		// OK
		$etag = md5($contact->LOGO);
	}
	if ($image->valid()) {
		$modified = OCA\Contacts\App::lastModified($contact);
		// Force refresh if modified within the last minute.
		if(!is_null($modified)) {
			$caching = (time() - $modified->format('U') > 60) ? null : 0;
		}
		OCP\Response::enableCaching($caching);
		if(!is_null($modified)) {
			OCP\Response::setLastModifiedHeader($modified);
		}
		if($etag) {
			OCP\Response::setETagHeader($etag);
		}
		if ($image->width() > $max_size || $image->height() > $max_size) {
			$image->resize($max_size);
		}
	}
}
if (!$image->valid()) {
	// Not found :-(
	getStandardImage();
}
header('Content-Type: '.$image->mimeType());
$image->show();
