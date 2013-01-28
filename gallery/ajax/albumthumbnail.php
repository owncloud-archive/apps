<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('gallery');
session_write_close();

list($owner, $img) = explode('/', $_GET['file'], 2);
\OC\Files\Filesystem::initMountPoints($owner);

$image = new \OCA\Gallery\AlbumThumbnail('/' . $img, $owner);
$image->show();
