<?php

/**
 * ownCloud - Updater plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA_Updater;

\OCP\JSON::checkAdminUser();

// Url to download package e.g. http://download.owncloud.org/releases/owncloud-4.0.5.tar.bz2
$packageUrl = 'http://owncloud.org/releases/owncloud-latest.zip';


//Package version e.g. 4.0.4	
$packageVersion = '';
$updateData = \OC_Updater::check();
if (isset($updateData['version'])) {
	$packageVersion = $updateData['version'];
}
if (isset($updateData['url']) && extension_loaded('bz2')) {
	$packageUrl = $updateData['url'];
}

if (!$packageVersion) {
	\OCP\JSON::error(array('msg' => 'Version not found'));
	exit();
}


$sourcePath = Downloader::getPackage($packageUrl, $packageVersion);
if (!$sourcePath) {
	\OCP\JSON::error(array('msg' => 'Unable to fetch package'));
	exit();
}

$backupPath = Backup::createBackup();
if ($backupPath) {
	Updater::update($sourcePath, $backupPath);
	\OCP\JSON::success(array());
} else {
	\OCP\JSON::error(array('msg' => 'Failed to create backup'));
}
