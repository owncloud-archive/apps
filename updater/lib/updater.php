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

class Updater {

	protected static $_skipDirs = array();
	protected static $_updateDirs = array();

	public static function update($sourcePath, $backupPath) {
		if (!is_dir($backupPath)) {
			throw new \Exception('Backup directory is not found');
		}

		self::$_updateDirs = App::getDirectories();
		self::$_skipDirs = App::getExcludeDirectories();

		set_include_path(
				$backupPath . PATH_SEPARATOR .
				$backupPath . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR .
				$backupPath . DIRECTORY_SEPARATOR . 'config' . PATH_SEPARATOR .
				$backupPath . DIRECTORY_SEPARATOR . '3rdparty' . PATH_SEPARATOR .
				$backupPath . '/apps' . PATH_SEPARATOR .
				get_include_path()
		);

		$tempPath = App::getBackupBase() . 'tmp';
		if  (!@mkdir($tempPath, 0777, true)) {
			throw new \Exception('failed to create ' . $tempPath);
		}

		//TODO: Add Check/Rollback here
		self::moveDirectories($sourcePath, $tempPath);

		//TODO: Add Check/Rollback here
		$config = "/config/config.php";
		copy($tempPath . $config, self::$_updateDirs['core'] . $config);

		return true;
	}

	public static function moveDirectories($updatePath, $tempPath) {
		foreach (self::$_updateDirs as $type => $path) {
			$currentDir = $path;
			$updateDir = $updatePath;
			$tempDir = $tempPath;
			if ($type != 'core') {
				$updateDir .= DIRECTORY_SEPARATOR . $type;
				$tempDir .= DIRECTORY_SEPARATOR . $type;
				rename($currentDir, $tempDir);
				rename($updateDir, $currentDir);
			} else {
				self::moveDirectoryContent($currentDir, $tempDir);
				self::moveDirectoryContent($updateDir, $currentDir);
			}
		}
		return true;
	}

	public static function moveDirectoryContent($source, $destination) {
		$dh = opendir($source);
		while (($file = readdir($dh)) !== false) {
			$fullPath = $source . DIRECTORY_SEPARATOR . $file;
			if (is_dir($fullPath)) {
				if (in_array($file, self::$_skipDirs['relative'])
					|| in_array($fullPath, self::$_skipDirs['full'])
				) {
					continue;
				}
			}

			rename($fullPath, $destination . DIRECTORY_SEPARATOR . $file);
		}
		return true;
	}

}
