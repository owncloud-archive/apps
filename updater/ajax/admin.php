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

namespace OCA\Updater;

\OCP\JSON::checkAdminUser();
\OCP\JSON::callCheck();

// Url to download package e.g. http://download.owncloud.org/releases/owncloud-4.0.5.tar.bz2
$packageUrl = 'https://download.owncloud.com/download/community/owncloud-latest.zip';

//Package version e.g. 4.0.4
$packageVersion = '';
$updateData = \OC_Updater::check();

if (isset($updateData['version'])) {
	$packageVersion = $updateData['version'];
}
if (isset($updateData['url']) && extension_loaded('bz2')) {
	$packageUrl = $updateData['url'];
}
if (!strlen($packageVersion) || !strlen($packageUrl)) {
	\OC_Log::write(App::APP_ID, 'Invalid response from update feed.', \OC_Log::ERROR);
	\OCP\JSON::error(array('msg' => 'Version not found'));
	exit();
}

//Step 1 - fetch & extract
if (!App::getSource($packageUrl, $packageVersion)){
	try {
		//Do we have any remains of the previous update attempt?
		Downloader::cleanUp($packageVersion);
		
		Downloader::getPackage($packageUrl, $packageVersion);
		App::setSource($packageUrl, $packageVersion, true);
		\OCP\JSON::success(array());
	} catch (\Exception $e){
		\OC_Log::write(App::APP_ID, $e->getMessage(), \OC_Log::ERROR);
		\OCP\JSON::error(array('msg' => 'Unable to fetch package'));
	}
	exit();
}

//Step 2 - backup & update

//Do we have any remains of the previous update attempt?
Updater::cleanUp();

try {
	$backupPath = Backup::create();
	App::setSource($packageUrl, $packageVersion, false);
	Updater::update($packageVersion, $backupPath);
	//Cleanup
	Downloader::cleanUp($packageVersion);
	Updater::cleanUp();
	\OCP\JSON::success(array());
} catch (\Exception $e){
	\OC_Log::write(App::APP_ID, $e->getMessage(), \OC_Log::ERROR);
	App::setSource($packageUrl, $packageVersion, false);
	\OCP\JSON::error(array('msg' => 'Failed to create backup'));
}

exit();