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

		if (!self::createBackupDirectory()) {
			throw new \Exception('Failed to create backup directory');
		}

		$locations = App::getDirectories();
		$exclusions = App::getExcludeDirectories();
		foreach ($locations as $type => $path) {
			if (!self::copyPath($path, $type, $exclusions)) {
				//TODO: Rollback here
				throw new \Exception('Failed to copy ' . $type);
			}
		}
		return self::getBackupPath();
	}

	/**
	 * Copy directory content skipping certain items
	 * @param string $path
	 * @param string $type
	 * @param array $exclusions
	 * @return bool
	 */
	public static function copyPath($path, $type, $exclusions) {
		$backupFullPath = self::getBackupPath() . '/';

		// 3rd party and apps might have different location
		if ($type != 'core') {
			$backupFullPath .= $type . '/';
			if (!@mkdir($backupFullPath, 0755, true)) {
				\OC_Log::write(App::APP_ID, 'Unable to create ' . $backupFullPath, \OC_Log::ERROR);
				return false;
			}
		}

		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
			$fullPath = $path . '/' . $file;
			if (is_dir($fullPath)) {
				if (in_array($file, $exclusions['relative'])
					|| in_array($fullPath, $exclusions['full'])
				) {
					continue;
				}
			}

			\OC_Helper::copyr($fullPath, $backupFullPath . $file);
		}
		return true;
	}

	/**
	 * Create directory to store backup
	 * @return string Path to directory or false
	 */
	public static function createBackupDirectory() {
		$backupPath = self::getBackupPath();
		if (@mkdir($backupPath, 0755, true)) {
			return $backupPath;
		}

		return false;
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

}
