<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('gallery');

list($owner, $gallery) = explode('/', $_GET['gallery'], 2);

$ownerView = new \OC\Files\View('/' . $owner . '/files');
if ($owner !== OC_User::getUser()) {
	\OC\Files\Filesystem::initMountPoints($owner);
	list($shareId, , $gallery) = explode('/', $gallery, 3);
	if (OCP\Share::getItemSharedWith('gallery', $shareId)) {
		$sharedGallery = $ownerView->getPath($shareId);
		if ($gallery) {
			$gallery = $sharedGallery . '/' . $gallery;
		} else {
			$gallery = $sharedGallery;
		}
	} else {
		OC_JSON::error('no such file');
	}
}
$meta = $ownerView->getFileInfo($gallery);
$data = array();
$data['fileid'] = $meta['fileid'];
$data['permissions'] = $meta['permissions'];

OCP\JSON::setContentTypeHeader();
echo json_encode($data);
