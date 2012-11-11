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

	public static function update($version, $backupBase) {
		if (!is_dir($backupBase)) {
			throw new \Exception('Backup directory is not found');
		}

		set_include_path(
				$backupBase . PATH_SEPARATOR .
				$backupBase . '/core/lib' . PATH_SEPARATOR .
				$backupBase . '/core/config' . PATH_SEPARATOR .
				$backupBase . '/3rdparty' . PATH_SEPARATOR .
				$backupBase . '/apps' . PATH_SEPARATOR .
				get_include_path()
		);

		$tempDir = self::getTempDir();
		Helper::mkdir($tempDir, true);
		
		$destinations = Helper::getDirectories();
		try {
			$locations = Helper::getPreparedLocations();
			foreach ($locations as $type => $dirs) {
				switch ($type){
					case Helper::CORE_DIRNAME:
						$sourceBaseDir = Downloader::getCoreDir($version);
						break;
					case Helper::THIRDPARTY_DIRNAME:
						$sourceBaseDir = Downloader::getThirdPartyDir($version);
						break;
					case Helper::APP_DIRNAME:
						$sourceBaseDir = Downloader::getAppDir($version);
						break;
					default:
						//  Extra app directories
						$sourceBaseDir  = false;
						break;
				}
				$tempBaseDir = $tempDir . '/' . $type;		
				Helper::mkdir($tempBaseDir, true);
				
				// Purge old sources
				foreach ($dirs as $name => $path) {
					Helper::move($path, $tempBaseDir . '/' . $name);
					self::$processed[] = array (
						'src' => $tempBaseDir . '/' . $name,
						'dst' => $path
					);
				}
				//Put new sources
				if (!$sourceBaseDir){
					continue;
				}
				foreach (Helper::getFilteredContent($sourceBaseDir) as $basename=>$path){
					Helper::move($path, $destinations[$type] . '/' . $basename);
				}
			}
		} catch (\Exception $e){
			self::rollBack();
			self::cleanUp();
			throw $e;
		}

		$config = "/config/config.php";
		copy($backupBase . $config, \OC::$SERVERROOT . $config);
		
        //TODO: disable removed apps
		
		return true;
	}

	public static function rollBack(){
		foreach (self::$processed as $item){
			\OC_Helper::copyrr($item['src'], $item['dst']);
		}
	}

	public static function cleanUp(){
		Helper::removeIfExists(self::getTempDir());
	}

	public static function getTempDir(){
		return App::getBackupBase() . 'tmp';
	}

}
