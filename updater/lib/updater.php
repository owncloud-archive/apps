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

class Updater {

	protected static $processed = array();

	public static function update($version, $backupBase){
		if (!is_dir($backupBase)){
			throw new \Exception("Backup directory $backupBase is not found");
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

		$installed = Helper::getDirectories();
		$sources = Helper::getSources($version);
		
		try{
				$thirdPartyUpdater = new Location_3rdparty(
						$installed[Helper::THIRDPARTY_DIRNAME],
						$sources[Helper::THIRDPARTY_DIRNAME]
				);
				$thirdPartyUpdater->update($tempDir . '/' . Helper::THIRDPARTY_DIRNAME);
				self::$processed[] = $thirdPartyUpdater;
				
				$coreUpdater = new Location_Core(
						$installed[Helper::CORE_DIRNAME],
						$sources[Helper::CORE_DIRNAME]
				);
				$coreUpdater->update($tempDir . '/' . Helper::CORE_DIRNAME);
				self::$processed[] = $coreUpdater;
				
				$appsUpdater = new Location_Apps(
						'', //TODO: put smth really helpful here ;)
						$sources[Helper::APP_DIRNAME]
				);
				$appsUpdater->update($tempDir . '/' . Helper::APP_DIRNAME);
				self::$processed[] = $appsUpdater;
		} catch (\Exception $e){
			self::rollBack();
			self::cleanUp();
			throw $e;
		}

		// zip backup 
		$zip = new \ZipArchive();
		if ($zip->open($backupBase . ".zip", \ZIPARCHIVE::CREATE) === true){
			Helper::addDirectoryToZip($zip, $backupBase, $backupBase);
			$zip->close();
			\OCP\Files::rmdirr($backupBase);
		}

		return true;
	}

	public static function rollBack(){
		foreach (self::$processed as $item){
			$item->rollback();
		}
	}

	public static function cleanUp(){
		Helper::removeIfExists(self::getTempDir());
		Helper::removeIfExists(App::getTempBase());
	}
	
	public static function isClean(){
		return !@file_exists(self::getTempDir());
	}

	public static function getTempDir(){
		return App::getTempBase() . 'tmp';
	}

}
