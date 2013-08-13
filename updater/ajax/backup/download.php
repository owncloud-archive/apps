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

$file = basename(
	@$_GET["filename"]
);

// Prevent directory traversal
if (strlen($file)<3) {
	exit;
}

$filename = App::getBackupBase() . $file;

if(!@file_exists($filename)) {
	exit;
}

header('Content-Type:' . 
		\OCP\Files::getMimeType($filename)
);
if (preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"])) {
	header('Content-Disposition: attachment; filename="' . rawurlencode(basename($filename)) . '"');
} else {
	header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(basename($filename))
		 . '; filename="' . rawurlencode(basename($filename)) . '"');
}

\OCP\Response::disableCaching();
header('Content-Length: ' . filesize($filename));

\OC_Util::obEnd();
readfile($filename);
