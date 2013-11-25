<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('gallery');

$split = explode('/', $_GET['gallery'], 2);
$owner = $split[0];
$gallery = array_key_exists(1, $split) ? $split[1] : NULL;

$ownerView = new \OC\Files\View('/' . $owner . '/files');
if ($owner !== OCP\User::getUser()) {
	\OC\Files\Filesystem::initMountPoints($owner);
	list($shareId, , $gallery) = explode('/', $gallery, 3);
	if (OCP\Share::getItemSharedWith('file', $shareId)) {
		$sharedGallery = $ownerView->getPath($shareId);
		if ($gallery) {
			$gallery = $sharedGallery . '/' . $gallery;
		} else {
			$gallery = $sharedGallery;
		}
	} else {
		OCP\JSON::error(array( 'message' => 'no such file'));
	}
}
$meta = $ownerView->getFileInfo($gallery);
$data = array();
$data['fileid'] = $meta['fileid'];
$data['permissions'] = $meta['permissions'];

OCP\JSON::setContentTypeHeader();
echo json_encode($data);
