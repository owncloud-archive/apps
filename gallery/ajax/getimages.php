<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkAppEnabled('gallery');

if (isset($_GET['token'])) {
	$token = $_GET['token'];
	$linkItem = \OCP\Share::getShareByToken($token);
	if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
		// seems to be a valid share
		$type = $linkItem['item_type'];
		$fileSource = $linkItem['file_source'];
		$shareOwner = $linkItem['uid_owner'];
		$path = null;
		$rootLinkItem = \OCP\Share::resolveReShare($linkItem);
		$fileOwner = $rootLinkItem['uid_owner'];

		// Setup FS with owner
		OCP\JSON::checkUserExists($fileOwner);
		OC_Util::tearDownFS();
		OC_Util::setupFS($fileOwner);

		// The token defines the target directory (security reasons)
		$path = \OC\Files\Filesystem::getPath($linkItem['file_source']);

		$view = new \OC\Files\View(\OC\Files\Filesystem::getView()->getAbsolutePath($path));
		$images = $view->searchByMime('image');

		foreach ($images as &$image) {
			$image['path'] = $token . $image['path'];
		}

		OCP\JSON::setContentTypeHeader();
		echo json_encode(array('images' => $images, 'users' => array(), 'displayNames' => array()));

		exit;
	}
}

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('gallery');

$images = \OCP\Files::searchByMime('image');
$user = \OCP\User::getUser();

foreach ($images as &$image) {
	$path = $user . $image['path'];
	if (strpos($path, DIRECTORY_SEPARATOR . ".")) {
		continue;
	}
	$image['path'] = $user . $image['path'];
}

$shared = array();
$sharedSources = OCP\Share::getItemsSharedWith('file');
$users = array();
foreach ($sharedSources as $sharedSource) {
	$owner = $sharedSource['uid_owner'];
	if (array_search($owner, $users) === false) {
		$users[] = $owner;
	}
	\OC\Files\Filesystem::initMountPoints($owner);
	$ownerView = new \OC\Files\View('/' . $owner . '/files');
	$path = $ownerView->getPath($sharedSource['item_source']);
	if ($path) {
		$shareName = basename($path);
		$shareView = new \OC\Files\View('/' . $owner . '/files' . $path);
		$sharedImages = $shareView->searchByMime('image');
		foreach ($sharedImages as $sharedImage) {
			// set the file_source in the path so we can get the original shared folder later
			$sharedImage['path'] = $owner . '/' . $sharedSource['file_source'] . '/' . $shareName . $sharedImage['path'];
			$images[] = $sharedImage;
		}
	}
}

$displayNames = array();
foreach ($users as $user) {
	$displayNames[$user] = \OCP\User::getDisplayName($user);
}

function startsWith($haystack, $needle) {
	return !strncmp($haystack, $needle, strlen($needle));
}

OCP\JSON::setContentTypeHeader();
echo json_encode(array('images' => $images, 'users' => $users, 'displayNames' => $displayNames));
