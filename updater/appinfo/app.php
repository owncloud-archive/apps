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

class App {

	const APP_ID = 'updater';
	const APP_PATH = 'apps/updater/';
	const LAST_BACKUP_PATH = 'last_backup_path';

	public static function init() {
		//Allow config page
		\OC::$CLASSPATH['OCA_Updater\Backup'] = self::APP_PATH . 'lib/backup.php';
		\OC::$CLASSPATH['OCA_Updater\Downloader'] = self::APP_PATH . 'lib/downloader.php';
		\OC::$CLASSPATH['OCA_Updater\Updater'] = self::APP_PATH . 'lib/updater.php';
		\OC_APP::registerAdmin(self::APP_ID, 'admin');
	}

	public static function getBackupBase() {
		return \OC::$SERVERROOT . \DIRECTORY_SEPARATOR 
				. 'backup' . \DIRECTORY_SEPARATOR;
	}
	
	public static function getDirectories() {
		return array(
			'3rdparty' => \OC::$THIRDPARTYROOT . DIRECTORY_SEPARATOR . '3rdparty',
			'apps' => \OC::$APPSROOT . DIRECTORY_SEPARATOR . 'apps',
			'core' => \OC::$SERVERROOT
		);
	}

	public static function getExcludeDirectories() {
		return array(
			'full' => array(
				rtrim(self::getBackupBase(), DIRECTORY_SEPARATOR),
				\OC_Config::getValue( "datadirectory", \OC::$SERVERROOT."/data" )
				),
			'relative' => array('.', '..')
		);
	}

	public static function getRecentBackupPath() {
		return \OCP\Config::getAppValue(self::APP_ID, self::LAST_BACKUP_PATH, '');
	}

	public static function setRecentBackupPath($path) {
		\OCP\Config::setAppValue(self::APP_ID, self::LAST_BACKUP_PATH, $path);
	}

}

//Startup
if (\OCP\App::isEnabled(App::APP_ID)) {
	App::init();
}
