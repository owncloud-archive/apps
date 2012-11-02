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

class Backup {

	/**
	 * Path to the current Backup instance
	 * @var string
	 */
	protected static $_backupPath = '';

	/**
	 * Perform backup
	 * @return string
	 */
	public static function createBackup() {
		try {
			$locations = Helper::getPreparedLocations();
			Helper::mkdir(self::getBackupPath(), true);
			foreach ($locations as $type => $dirs) {
				$backupFullPath = self::getBackupPath() . '/';

				// 3rd party and apps might have different location
				if ($type != 'core') {
					$backupFullPath .= $type . '/';
					Helper::mkdir($backupFullPath, true);
				}
				foreach ($dirs as $name => $path) {
					//TODO: reimplement copy with Exception on error
					\OC_Helper::copyr($path, $backupFullPath . $name);
				}
			}
		} catch (\Exception $e){
			self::cleanUp();
			throw $e;
		}

		return self::getBackupPath();
	}

	/**
	 * Generate unique backup path
	 * or return existing one
	 * @return string
	 */
	public static function getBackupPath() {
		if (!self::$_backupPath) {
			$backupBase = App::getBackupBase();
			$currentVersion = \OC_Config::getValue('version', '0.0.0');
			$backupPath = $backupBase . $currentVersion . '-';

			do {
				$salt = substr(md5(time()), 0, 8);
			} while (file_exists($backupPath . $salt));

			self::$_backupPath = $backupPath . $salt;
		}
		return self::$_backupPath;
	}

	public static function cleanUp(){
		if (self::$_backupPath){
			Helper::removeIfExists(self::$_backupPath);
		}
	}

}
