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

	public static function mkdir($path, $isRecoursive = false) {
		if (!@mkdir($path, 0755, $isRecoursive)) {
			throw new \Exception("Unable to create $path");
		}
	}

	public static function removeIfExists($path) {
		if (!file_exists($path)){
			return;
		}

		if (is_dir($path)) {
			\OC_Helper::rmdirr($path);
		} else {
			@unlink($path);
		}
	}

	public static function getPreparedLocations() {
		$locations = App::getDirectories();
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
		$exclusions = App::getExcludeDirectories();
		$baseExclusions = array_values(App::getDirectories());
		foreach ($locations as $key => $location) {
			$fullPath = $basePath . '/' .$location;
			if (!is_dir($fullPath)){
				continue;
			}
			if (in_array($fullPath, $exclusions['full'])
				|| in_array($fullPath, $baseExclusions)
				|| in_array($location, $exclusions['relative']) ) {
				unset($locations[$key]);
			}
		}
		return $locations;
	}

	public static function scandir($path) {
		$content = @scandir($path);
		if (!is_array($content)) {
			throw new \Exception("Unable to list $path content");
		}
		return $content;
	}

}
