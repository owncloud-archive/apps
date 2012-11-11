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

	public static function move($src, $dest) {
		if (!@rename($src, $dest)){
			throw new \Exception("Unable copy $src to $dest");
		}
	}
	
	public static function copyr($src, $dest) {
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
