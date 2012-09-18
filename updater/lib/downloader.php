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

namespace OCA_Updater;

class Downloader {

	const PACKAGE_ROOT = 'owncloud';
		
	public static function getPackage($url, $version) {
		$path = \OC_Helper::tmpFile();

		if (!copy($url, $path)) {
			\OC_Log::write(App::APP_ID, "Failed to download $url package to $path", \OC_Log::ERROR);
			return false;
		}

		//Mimetype bug workaround
		$mime = rtrim(\OC_Helper::getMimeType($path), ';');

		if ($mime == 'application/zip') {
			rename($path, $path . '.zip');
			$path.='.zip';
		} elseif ($mime == 'application/x-gzip') {
			rename($path, $path . '.tgz');
			$path.='.tgz';
		} elseif ($mime == 'application/x-bzip2') {
			rename($path, $path . '.tar.bz2');
			$path.='.tar.bz2';
		} else {
			\OC_Log::write(App::APP_ID, 'Archives of type ' . $mime . ' are not supported', \OC_Log::ERROR);
			return false;
		}

		$extractDir = self::getPackageDir($version);
		if (!mkdir($extractDir, 0777, true)) {
			\OC_Log::write(App::APP_ID, 'Unable to create temporary directory', \OC_Log::ERROR);
			return false;
		}

		$archive = \OC_Archive::open($path);
		if ($archive) {
			$archive->extract($extractDir);
		} else {
			\OC_Log::write(App::APP_ID, "Failed to open package $path", \OC_Log::ERROR);
			\OC_Helper::rmdirr($extractDir);
			@unlink($path);
			return false;
		}
		
		return $extractDir. DIRECTORY_SEPARATOR . self::PACKAGE_ROOT;
	}

	public static function getPackageDir($version) {
		return App::getBackupBase() . $version;
	}
	
}