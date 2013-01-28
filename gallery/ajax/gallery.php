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
$meta = $ownerView->getFileInfo($gallery);

OCP\JSON::setContentTypeHeader();
echo json_encode($meta);
