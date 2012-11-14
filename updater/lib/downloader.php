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

class Downloader {

	const PACKAGE_ROOT = 'owncloud';

	protected static $package = false;

	public static function getPackage($url, $version) {
		self::$package = \OC_Helper::tmpFile();
		try {
			if (!copy($url, self::$package)) {
				throw new \Exception("Failed to download $url package");
			}

			if (preg_match('/\.zip$/i', $url)) {
				rename(self::$package, self::$package . '.zip');
				self::$package .= '.zip';
			} elseif (preg_match('/(\.tgz|\.tar\.gz)$/i', $url)) {
				rename(self::$package, self::$package . '.tgz');
				self::$package .= '.tgz';
			} elseif (preg_match('/\.tar\.bz2$/i', $url)) {
				rename(self::$package, self::$package . '.tar.bz2');
				self::$package .= '.tar.bz2';
			} else {
				throw new \Exception('Unable to extract package');
			}

			$extractDir = self::getPackageDir($version);
			Helper::mkdir($extractDir, true);

			$archive = \OC_Archive::open(self::$package);
			if ($archive) {
				$archive->extract($extractDir);
			} else {
				throw new \Exception(self::$package . " extraction error");
			}
		} catch (\Exception $e){
			self::cleanUp($version);
			throw $e;
		}
		Helper::removeIfExists(self::$package);
		
		//  Prepare extracted data
		//  to have '3rdparty', 'apps' and 'core' subdirectories
		$sources = Helper::getSources($version);
		$baseDir = $extractDir. '/' . self::PACKAGE_ROOT;
		@rename($baseDir . '/' . Helper::THIRDPARTY_DIRNAME, $sources[Helper::THIRDPARTY_DIRNAME]);
		@rename($baseDir . '/' . Helper::APP_DIRNAME, $sources[Helper::APP_DIRNAME]);
		@rename($baseDir, $sources[Helper::CORE_DIRNAME]);
		
	}

	public static function cleanUp($version){
		if (self::$package) {
			Helper::removeIfExists(self::$package);
		}
		Helper::removeIfExists(self::getPackageDir($version));
	}
	
	public static function getPackageDir($version) {
		return App::getBackupBase() . $version;
	}

}