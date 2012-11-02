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

	protected static $processed = array();

	public static function update($updateBase, $backupBase) {
		if (!is_dir($backupBase)) {
			throw new \Exception('Backup directory is not found');
		}

		set_include_path(
				$backupBase . PATH_SEPARATOR .
				$backupBase . '/lib' . PATH_SEPARATOR .
				$backupBase . '/config' . PATH_SEPARATOR .
				$backupBase . '/3rdparty' . PATH_SEPARATOR .
				$backupBase . '/apps' . PATH_SEPARATOR .
				get_include_path()
		);

		$tempBase = self::getTempDir();
		Helper::mkdir($tempBase, true);
		
		try {
			$locations = Helper::getPreparedLocations();
			foreach ($locations as $type => $dirs) {
				$tempPath = $tempBase . '/';
				$updatePath = $updateBase . '/';
			
				if ($type != 'core') {
					$tempPath .= $type . '/';
					$updatePath .= $type . '/';
				}
			
				foreach ($dirs as $name => $path) {
					//TODO: Add Check/Rollback here
					self::moveTriple($path, $updatePath . $name, $tempPath . $name);
				}
			}
		} catch (\Exception $e){
			self::cleanUp();
			throw $e;
		}

		$config = "/config/config.php";
		copy($tempBase . $config, \OC::$SERVERROOT . $config);

		return true;
	}

	public static function moveTriple($old, $new, $temp) {
		rename($old, $temp);
		rename($new, $old);
		return true;
	}

	public static function cleanUp(){
		Helper::removeIfExists(self::getTempDir());
	}

	public static function getTempDir(){
		return App::getBackupBase() . 'tmp';
	}

}
