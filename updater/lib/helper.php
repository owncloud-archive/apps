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

class Helper {
	
	static function copyr($src, $dest) {
		if(is_dir($src)) {
			if(!is_dir($dest)) {
				self::mkdir($dest);
			}
			$files = scandir($src);
			foreach ($files as $file) {
				if ($file != "." && $file != "..") {
					self::copyr("$src/$file", "$dest/$file");
				}
			}
		}elseif(file_exists($src)) {
			if (!@copy($src, $dest)) {
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
		$locations = self::getDirectories();
		$preparedLocations  = array();
		foreach ($locations as $type => $path) {
			$content = self::scandir($path);
			$filtered = self::filterLocations($content, $path);
			foreach ($filtered as $dirName){
				$preparedLocations[$type][$dirName] = $path . '/' . $dirName;
			}
		}
		return $preparedLocations;
	}

	public static function filterLocations($locations, $basePath) {
		$exclusions = self::getExcludeDirectories();
		
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
	 * Get the list of directories to be replaced on update
	 * @return array
	 * 
	 */
	public static function getDirectories() {
		$dirs = array();
		$dirs['3rdparty'] = \OC::$THIRDPARTYROOT . '/3rdparty';
		
		//Long, long ago we had single app location
		if (isset(\OC::$APPSROOTS)) {
			foreach (\OC::$APPSROOTS as $i => $approot){
				$index = $i ? $i : '';
				$dirs['apps' . $index] = $approot['path'];
			}
		} else {
			$dirs['apps'] = \OC::$APPSROOT . '/apps';
		}
		
	    $dirs['core'] = \OC::$SERVERROOT;
		return $dirs;
	}

	/**
	 * Get the list of directories that should NOT be replaced
	 * @return array
	 */
	public static function getExcludeDirectories() {
		$fullPath = array_values(self::getDirectories());
		
		$fullPath[] = rtrim(App::getBackupBase(), '/');
		$fullPath[] = \OC_Config::getValue( "datadirectory", \OC::$SERVERROOT."/data" );
		
		return array(
			'full' => $fullPath,
			'relative' => array('.', '..')
		);
	}

}
