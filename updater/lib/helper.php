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

class Helper {
	const APP_DIRNAME = 'apps';
	const THIRDPARTY_DIRNAME = '3rdparty';
	const CORE_DIRNAME = 'core';
	
	/**
	 * Moves file/directory
	 * @param string $src  - source path
	 * @param string $dest - destination path
	 * @throws \Exception on error
	 */
	public static function move($src, $dest) {
		if (!@rename($src, $dest)) {
			throw new \Exception("Unable to move $src to $dest");
		}
	}
	
	/**
	 * Copy recoursive 
	 * @param string $src  - source path
	 * @param string $dest - destination path
	 * @throws \Exception on error
	 */
	public static function copyr($src, $dest, $stopOnError = true) {
		if(is_dir($src)) {
			if(!is_dir($dest)) {
				try {
					self::mkdir($dest);
				} catch (\Exception $e){
					if ($stopOnError){
						throw $e;
					}
				}
			}
			$files = scandir($src);
			foreach ($files as $file) {
				if ($file != "." && $file != "..") {
					self::copyr("$src/$file", "$dest/$file", $stopOnError);
				}
			}
		}elseif(file_exists($src)) {
			if (!@copy($src, $dest) && $stopOnError) {
				throw new \Exception("Unable copy $src to $dest");
			}
		}
	}

	/**
	 * Wrapper for mkdir
	 * @param string $path
	 * @param bool $isRecoursive
	 * @throws \Exception on error
	 */
	public static function mkdir($path, $isRecoursive = false) {
		if (!@mkdir($path, 0755, $isRecoursive)) {
			throw new \Exception("Unable to create $path");
		}
	}
	
	/**
	 * Get directory content as array
	 * @param string $path
	 * @return array 
	 * @throws \Exception on error
	 */
	public static function scandir($path) {
		$content = @scandir($path);
		if (!is_array($content)) {
			throw new \Exception("Unable to list $path content");
		}
		return $content;
	}

	/**
	 * Silently remove the filesystem item
	 * Used for cleanup
	 * @param string $path
	 */
	public static function removeIfExists($path) {
		if (!file_exists($path)) {
			return;
		}

		if (is_dir($path)) {
			\OC_Helper::rmdirr($path);
		} else {
			@unlink($path);
		}
	}

	/**
	 * Get the final list of files/directories to be replaced
	 * e.g. ['core']['lib'] = '/path/to/lib'
	 * @return array
	 */
	public static function getPreparedLocations() {
		$preparedLocations  = array();
		foreach (self::getDirectories() as $type => $path) {
			$preparedLocations[$type] = self::getFilteredContent($path);
		}
		return $preparedLocations;
	}
	
	/**
	 * Lists directory content as an array
	 * ['basename']=>'full path' 
	 * e.g.['lib'] = '/path/to/lib'
	 * @param string $path
	 * @return array
	 */
	public static function getFilteredContent($path){
		$result = array();
		$filtered =  self::filterLocations(self::scandir($path), $path);
		foreach ($filtered as $dirName){
			$result [$dirName] = $path . '/' . $dirName;
		}
		return $result;
	}

	public static function filterLocations($locations, $basePath) {
		$fullPath = array_values(self::getDirectories());
		$fullPath[] = rtrim(App::getBackupBase(), '/');
		$fullPath[] = \OC_Config::getValue( "datadirectory", \OC::$SERVERROOT."/data" );
		
		$exclusions = array(
			'full' => $fullPath,
			'relative' => array('.', '..')
		);
		
		foreach ($locations as $key => $location) {
			$fullPath = $basePath . '/' .$location;
			if (!is_dir($fullPath)) {
				continue;
			}
			if (in_array($fullPath, $exclusions['full'])
				|| in_array($location, $exclusions['relative'])
			) {
				unset($locations[$key]);
			}
		}
		return $locations;
	}
	
	/**
	 * Get the list of directories to be replaced on update
	 * @return array
	 */
	public static function getDirectories() {
		$dirs = array();
		$dirs[self::THIRDPARTY_DIRNAME] = \OC::$THIRDPARTYROOT . '/' . self::THIRDPARTY_DIRNAME;
		
		//Long, long ago we had single app location
		if (isset(\OC::$APPSROOTS)) {
			foreach (\OC::$APPSROOTS as $i => $approot){
				$index = $i ? $i : '';
				$dirs[self::APP_DIRNAME . $index] = $approot['path'];
			}
		} else {
			$dirs[self::APP_DIRNAME] = \OC::$APPSROOT . '/' . self::APP_DIRNAME;
		}
		
	    $dirs[self::CORE_DIRNAME] = \OC::$SERVERROOT;
		return $dirs;
	}
	
	public static function getSources($version) {
		$base = Downloader::getPackageDir($version);
		return array (
			self::APP_DIRNAME => $base . '/' . self::APP_DIRNAME,
			self::THIRDPARTY_DIRNAME => $base . '/' . self::THIRDPARTY_DIRNAME,
			self::CORE_DIRNAME => $base . '/' . self::CORE_DIRNAME,	
		);
	}
	
	public static function addDirectoryToZip($zip, $dir, $base) {
		$newFolder = str_replace($base, '', $dir);
		$zip->addEmptyDir($newFolder);
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file)) {
				$zip = self::addDirectoryToZip($zip, $file, $base);
			} else {
				$newFile = str_replace($base, '', $file);
				$zip->addFile($file, $newFile);
			}
		}
		return $zip;
	}
}
