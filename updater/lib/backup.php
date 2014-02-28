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

class Backup {

	/**
	 * Path to the current Backup instance
	 * @var string
	 */
	protected static $path = '';

	/**
	 * Perform backup
	 * @return string
	 */
	public static function create() {
		try {
			$locations = Helper::getPreparedLocations();
			Helper::mkdir(self::getPath(), true);
			foreach ($locations as $type => $dirs) {
				$backupFullPath = self::getPath() . '/' . $type . '/';
				Helper::mkdir($backupFullPath, true);
				
				foreach ($dirs as $name => $path) {
					Helper::copyr($path, $backupFullPath . $name);
				}
			}
		} catch (\Exception $e){
			App::log('Backup creation failed. Check permissions.');
			self::cleanUp();
			throw $e;
		}

		return self::getPath();
	}

	/**
	 * Generate unique backup path
	 * or return existing one
	 * @return string
	 */
	public static function getPath() {
		if (!self::$path) {
			$backupBase = App::getBackupBase();
			$currentVersion = \OCP\Config::getSystemValue('version', '0.0.0');
			$path = $backupBase . $currentVersion . '-';

			do {
				$salt = substr(md5(time()), 0, 8);
			} while (file_exists($path . $salt));

			self::$path = $path . $salt;
		}
		return self::$path;
	}

	public static function cleanUp(){
		if (self::$path) {
			Helper::removeIfExists(self::$path);
		}
		Helper::removeIfExists(App::getTempBase());
	}

}
