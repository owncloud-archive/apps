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

	public static function getPackage($url, $version) {
		$path = \OC_Helper::tmpFile();

		if (!copy($url, $path)) {
			throw new \Exception("Failed to download $url package to $path");
		}

		if (preg_match('/\.zip$/i', $url)) {
			rename($path, $path . '.zip');
			$path.='.zip';
		} elseif (preg_match('/(\.tgz|\.tar\.gz)$/i', $url)) {
			rename($path, $path . '.tgz');
			$path.='.tgz';
		} elseif (preg_match('/\.tar\.bz2$/i', $url)) {
			rename($path, $path . '.tar.bz2');
			$path.='.tar.bz2';
		} else {
			throw new \Exception('Unable to extract package');
		}

		$extractDir = self::getPackageDir($version);
		if (!mkdir($extractDir, 0777, true)) {
			throw new \Exception("Unable to create temporary directory");
		}

		$archive = \OC_Archive::open($path);
		if ($archive) {
			$archive->extract($extractDir);
		} else {
			self::cleanUp($version);
			@unlink($path);
			throw new \Exception("$path extraction error");
		}

		return $extractDir. '/' . self::PACKAGE_ROOT;
	}
	
	public static function cleanUp($version){
		$location = self::getPackageDir($version);
		if (is_dir($location)) {
			\OC_Helper::rmdirr($location);
		}
	}

	public static function getPackageDir($version) {
		return App::getBackupBase() . $version;
	}

}