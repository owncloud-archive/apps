<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkAppEnabled('gallery');

list($token, $img) = explode('/', $_GET['file'], 2);
$linkItem = \OCP\Share::getShareByToken($token);
if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
	// seems to be a valid share
	$rootLinkItem = \OCP\Share::resolveReShare($linkItem);
	$owner = $rootLinkItem['uid_owner'];
	OCP\JSON::checkUserExists($owner);
	OC_Util::tearDownFS();
	OC_Util::setupFS($owner);
	\OC_User::setIncognitoMode(true);
} else {
	OCP\JSON::checkLoggedIn();

	list($owner, $img) = explode('/', $_GET['file'], 2);
	if ($owner !== OCP\User::getUser()) {
		OCP\JSON::checkUserExists($owner);
		OC_Util::tearDownFS();
		OC_Util::setupFS($owner);
		$view = new \OC\Files\View('/' . $owner . '/files');
		// second part is the (duplicated) share name
		list($folderId, , $img) = explode('/', $img, 3);
		$shareInfo = \OCP\Share::getItemSharedWithBySource('file', $folderId);
		if ($shareInfo) {
			$sharedFolder = $view->getPath($folderId);
			if ($sharedFolder) {
				$img = $sharedFolder . '/' . $img;
			} else {
				\OC_Response::setStatus(404);
				exit;
			}
		} else {
			\OC_Response::setStatus(403);
			exit;
		}
	}
}

session_write_close();

$ownerView = new \OC\Files\View('/' . $owner . '/files');

if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
	// prepend path to share
	$path = $ownerView->getPath($linkItem['file_source']);
	$img = $path.'/'.$img;
}

$mime = $ownerView->getMimeType($img);
list($mimePart,) = explode('/', $mime);
if ($mimePart === 'image') {
	$local = $ownerView->getLocalFile($img);
	$rotate = false;
	if (is_callable('exif_read_data')) { //don't use OCP\Image here, using OCP\Image will always cause parsing the image file
		$exif = @exif_read_data($local, 'IFD0');
		if (isset($exif['Orientation'])) {
			$rotate = ($exif['Orientation'] > 1);
		}
	}
	if ($rotate) {
		$image = new OCP\Image($local);
		$image->fixOrientation();
		$image->show();
	} else { //use the original file if we dont need to rotate, saves having to re-encode the image
		header('Content-Type: ' . $mime);
		$ownerView->readfile($img);
	}
}
