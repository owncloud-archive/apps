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

class Downloader {

	const PACKAGE_ROOT = 'owncloud';

	protected static $package = false;

	public static function getPackage($url, $version) {
		self::$package = \OCP\Files::tmpFile();
		if (!self::$package){
			throw new \Exception('Unable to create a temporary file');
		}
		try {
			
			if (self::fetch($url)===false) {
				throw new \Exception("Error storing package content");
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
			if (!$archive || !$archive->extract($extractDir)) {
				throw new \Exception(self::$package . " extraction error");
			}
			
		} catch (\Exception $e){
			App::log('Retrieving ' . $url);
			self::cleanUp($version);
			throw $e;
		}
		Helper::removeIfExists(self::$package);
		
		//  Prepare extracted data
		//  to have '3rdparty', 'apps' and 'core' subdirectories
		$sources = Helper::getSources($version);
		$baseDir = $extractDir. '/' . self::PACKAGE_ROOT;
		rename($baseDir . '/' . Helper::THIRDPARTY_DIRNAME, $sources[Helper::THIRDPARTY_DIRNAME]);
		rename($baseDir . '/' . Helper::APP_DIRNAME, $sources[Helper::APP_DIRNAME]);
		rename($baseDir, $sources[Helper::CORE_DIRNAME]);
	}
	
	/* To be replaced with OC_Util::getUrlContent for 5.x */
	public static function fetch($url){
		
		$urlFopen = ini_get('allow_url_fopen');
		$allowed = array('on', 'yes', 'true', 1);
		
		if (\in_array($urlFopen, $allowed)){
			$result = @file_put_contents(self::$package, fopen($url, 'r'));
		} elseif  (function_exists('curl_init')) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_USERAGENT, "ownCloud Server Crawler");
			
			$result = @file_put_contents(self::$package, curl_exec($curl));
			
			curl_close($curl);
		} else {
			$ctx = stream_context_create(
				array(
					'http' => array('timeout' => 32000)
				     )
				);
			
			$result = @file_put_contents(self::$package, @file_get_contents($url, 0, $ctx));
		}
		return $result;
	}

	public static function cleanUp($version){
		if (self::$package) {
			Helper::removeIfExists(self::$package);
		}
		Helper::removeIfExists(self::getPackageDir($version));
		Helper::removeIfExists(App::getTempBase());
	}
	
	public static function isClean($version){
		return !@file_exists(self::getPackageDir($version));
	}
	
	public static function getPackageDir($version) {
		return App::getTempBase() . $version;
	}
}
