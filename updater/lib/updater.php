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
				$backupPath . '/lib' . PATH_SEPARATOR .
				$backupPath . '/config' . PATH_SEPARATOR .
				$backupPath . '/3rdparty' . PATH_SEPARATOR .
				$backupPath . '/apps' . PATH_SEPARATOR .
				get_include_path()
		);

		$tempPath = self::getTempDir();
		if  (!@mkdir($tempPath, 0755, true)) {
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
				$updateDir .= '/' . $type;
				$tempDir .= '/' . $type;
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
			$fullPath = $source . '/' . $file;
			if (is_dir($fullPath)) {
				if (in_array($file, self::$_skipDirs['relative'])
					|| in_array($fullPath, self::$_skipDirs['full'])
				) {
					continue;
				}
			}

			rename($fullPath, $destination . '/' . $file);
		}
		return true;
	}

	public static function cleanUp(){
		$tempPath = self::getTempDir();
		if (is_dir($tempPath)) {
			\OC_Helper::rmdirr($tempPath);
			@unlink($tempPath);
		}
	}

	public static function getTempDir(){
		return App::getBackupBase() . 'tmp';
	}

}
