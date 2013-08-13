<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Updater;

\OCP\JSON::checkAdminUser();
\OCP\JSON::callCheck();

// Prevent directory traversal
$file = basename(
	@$_GET["filename"]
);
if (strlen($file)<3) {
	exit;
}

$filename = App::getBackupBase() . $file;

Helper::removeIfExists($filename);