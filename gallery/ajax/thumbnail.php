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

if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
	// prepend path to share
	$ownerView = new \OC\Files\View('/' . $owner . '/files');
	$path = $ownerView->getPath($linkItem['file_source']);
	$img = $path . '/' . $img;
}

$square = isset($_GET['square']) ? (bool)$_GET['square'] : false;

$image = new \OCA\Gallery\Thumbnail('/' . $img, $owner, $square);
$image->show();
