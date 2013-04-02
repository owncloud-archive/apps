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
	const LAST_BACKUP_PATH = 'last_backup_path';

	public static function init() {
		\OC::$CLASSPATH['OCA\Updater\Backup'] = self::APP_ID . '/lib/backup.php';
		\OC::$CLASSPATH['OCA\Updater\Downloader'] = self::APP_ID . '/lib/downloader.php';
		\OC::$CLASSPATH['OCA\Updater\Updater'] = self::APP_ID . '/lib/updater.php';
		\OC::$CLASSPATH['OCA\Updater\Helper'] = self::APP_ID . '/lib/helper.php';
		//Allow config page
		\OC_APP::registerAdmin(self::APP_ID, 'admin');
	}

	/**
	 * Get app working directory
	 * @return string
	 */
	public static function getBackupBase() {
		return \OC::$SERVERROOT . '/backup/';
	}

	public static function getSource($url, $version) {
		return \OCP\Config::getAppValue(self::APP_ID, md5($version . $url), false);
	}

	public static function setSource($url, $version, $isDownloaded) {
		\OCP\Config::setAppValue(self::APP_ID, md5($version . $url), $isDownloaded);
	}
	
	public static function getRecentBackupPath() {
		return \OCP\Config::getAppValue(self::APP_ID, self::LAST_BACKUP_PATH, '');
	}

	public static function setRecentBackupPath($path) {
		\OCP\Config::setAppValue(self::APP_ID, self::LAST_BACKUP_PATH, $path);
	}
	
	public static function log($message, $level= \OC_Log::ERROR) {
		\OC_Log::write(self::APP_ID, $message, $level);
	}
}

//Startup
if (\OCP\App::isEnabled(App::APP_ID)) {
	App::init();
}
