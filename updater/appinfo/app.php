<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Updater;

class App {

	const APP_ID = 'updater';

	public static $l10n;
	
	public static function init() {
		self::$l10n = \OC_L10N::get(self::APP_ID);
		\OC::$CLASSPATH['OCA\Updater\Backup'] = self::APP_ID . '/lib/backup.php';
		\OC::$CLASSPATH['OCA\Updater\Downloader'] = self::APP_ID . '/lib/downloader.php';
		\OC::$CLASSPATH['OCA\Updater\Updater'] = self::APP_ID . '/lib/updater.php';
		\OC::$CLASSPATH['OCA\Updater\Helper'] = self::APP_ID . '/lib/helper.php';
		\OC::$CLASSPATH['OCA\Updater\Location'] = self::APP_ID . '/lib/location.php';
		\OC::$CLASSPATH['OCA\Updater\Location_3rdparty'] = self::APP_ID . '/lib/location/3rdparty.php';
		\OC::$CLASSPATH['OCA\Updater\Location_Apps'] = self::APP_ID . '/lib/location/apps.php';
		\OC::$CLASSPATH['OCA\Updater\Location_Core'] = self::APP_ID . '/lib/location/core.php';
		
		//Allow config page
		\OC_APP::registerAdmin(self::APP_ID, 'admin');
	}

	/**
	 * Get app working directory
	 * @return string
	 */
	public static function getBackupBase() {
		return \OC_Config::getValue("datadirectory", \OC::$SERVERROOT . "/data") . '/updater_backup/';
	}
	
	public static function getLegacyBackupBase() {
		return \OC::$SERVERROOT . '/backup/';
	}
	
	public static function log($message, $level= \OC_Log::ERROR) {
		\OC_Log::write(self::APP_ID, $message, $level);
	}
}

//Startup
if (\OCP\App::isEnabled(App::APP_ID)) {
	App::init();
}
