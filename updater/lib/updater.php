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
	protected static $locations = array();
	protected static $appsToRemove = array();

	public static function getAppsToRemove(){
		return self::$appsToRemove;
	}

	public static function prepare($version){
		$tempDir = self::getTempDir();

		$sources = Helper::getSources($version);
		$destinations = Helper::getDirectories();

		if (preg_match('/^\d+\.\d+/', $version, $ver)){
			$ver = $ver[0];
		} else {
			$ver = $version;
		}
		//  read the list of shipped apps
		$appLocation = $sources[Helper::APP_DIRNAME];
		$shippedApps = array_keys(Helper::getFilteredContent($appLocation));

		self::$appsToRemove = array();
		try{
			$locations = Helper::getPreparedLocations();
			foreach ($locations as $type => $dirs){
				if (isset($sources[$type])){
					$sourceBaseDir = $sources[$type];
				} else {
					//  Extra app directories
					$sourceBaseDir = false;
				}

				$tempBaseDir = $tempDir . '/' . $type;
				Helper::mkdir($tempBaseDir, true);

				// Collect old sources
				foreach ($dirs as $name => $path){
					//skip compatible, not shipped apps
					if (strpos($type, Helper::APP_DIRNAME) === 0 && !in_array($name, $shippedApps)
					){
						//Read compatibility info
						$info = \OC_App::getAppInfo($name);
						if (isset($info['require']) && version_compare($ver, $info['require']) >= 0){
							continue;
						}
						self::$appsToRemove[] = $name;
					}
					self::$locations[] = array(
						'src' => $path,
						'dst' => $tempBaseDir . '/' . $name
					);
				}
				//Collect new sources
				if (!$sourceBaseDir){
					continue;
				}
				foreach (Helper::getFilteredContent($sourceBaseDir) as $basename => $path){
					self::$locations[] = array(
						'src' => $path,
						'dst' => $destinations[$type] . '/' . $basename
					);
				}
			}
		} catch (\Exception $e){
			App::log('Apps check was interrupted. Upgrade cancelled.');
			throw $e;
		}

		return self::$locations;
	}

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

		try{
			foreach (self::prepare($version) as $location){
				Helper::move($location['src'], $location['dst']);
				self::$processed[] = array(
					'src' => $location['dst'],
					'dst' => $location['src']
				);
			}
		} catch (\Exception $e){
			App::log('Something went wrong. Rolling back.');
			self::rollBack();
			self::cleanUp();
			throw $e;
		}

		// move old config files
		$backupConfigPath = $backupBase . "/" . Helper::CORE_DIRNAME . "/config/";
		foreach (glob($backupConfigPath . "*.php") as $configFile){
			$target = \OC::$SERVERROOT . "/config/" . basename($configFile);
			if (!file_exists($target)){
				copy($configFile, $target);
			}
		}

		// zip backup 
		$zip = new \ZipArchive();
		if ($zip->open($backupBase . ".zip", \ZIPARCHIVE::CREATE) === true){
			Helper::addDirectoryToZip($zip, $backupBase, $backupBase);
			$zip->close();
			\OC_Helper::rmdirr($backupBase);
		}

		// Disable removed apps
		foreach (self::getAppsToRemove() as $appId){
			\OC_App::disable($appId);
		}

		return true;
	}

	public static function rollBack(){
		foreach (self::$processed as $item){
			Helper::copyr($item['src'], $item['dst'], false);
		}
	}

	public static function cleanUp(){
		Helper::removeIfExists(self::getTempDir());
	}

	public static function getTempDir(){
		return App::getBackupBase() . 'tmp';
	}

}
