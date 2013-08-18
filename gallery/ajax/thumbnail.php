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
$square = isset($_GET['square']) ? (bool)$_GET['square'] : false;
if ($owner !== OCP\User::getUser()) {
	list(, $img) = explode('/', $img, 2);
}

$image = new \OCA\Gallery\Thumbnail('/' . $img, $owner, $square);
$image->show();
