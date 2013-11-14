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
	$type = $linkItem['item_type'];
	$fileSource = $linkItem['file_source'];
	$shareOwner = $linkItem['uid_owner'];
	$path = null;
	$rootLinkItem = \OCP\Share::resolveReShare($linkItem);
	$owner = $rootLinkItem['uid_owner'];
} else {
	OCP\JSON::checkLoggedIn();

	list($owner, $img) = explode('/', $_GET['file'], 2);
	if ($owner !== OCP\User::getUser()) {
		list(, $img) = explode('/', $img, 2);
	}
}

session_write_close();

$square = isset($_GET['square']) ? (bool)$_GET['square'] : false;

$image = new \OCA\Gallery\Thumbnail('/' . $img, $owner, $square);
$image->show();
