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

	/**
	 * Get the list of directories to be replaced on update
	 * @return array
	 * 
	 */
	public static function getDirectories() {
		$dirs = array();
		$dirs['3rdparty'] = \OC::$THIRDPARTYROOT . '/3rdparty';
		
		//Long, long ago we had single app location
		if (isset(\OC::$APPSROOTS)) {
			foreach (\OC::$APPSROOTS as $i => $approot){
				$index = $i ? $i : '';
				$dirs['apps' . $index] = $approot['path'];
			}
		} else {
			$dirs['apps'] = \OC::$APPSROOT . '/apps';
		}
		
	    $dirs['core'] = \OC::$SERVERROOT;
		return $dirs;
	}

	/**
	 * Get the list of directories that should NOT be replaced
	 * @return array
	 */
	public static function getExcludeDirectories() {
		return array(
			'full' => array(
				rtrim(self::getBackupBase(), '/'),
				\OC_Config::getValue( "datadirectory", \OC::$SERVERROOT."/data" )
				),
			'relative' => array('.', '..')
		);
	}
	
	public static function getSourcePath($version, $url) {
		return \OCP\Config::getAppValue(self::APP_ID, md5($version . $url), '');
	}

	public static function setSourcePath($version, $url, $path) {
		\OCP\Config::setAppValue(self::APP_ID, md5($version . $url), $path);
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
