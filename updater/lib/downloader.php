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
			throw new \Exception('Unable to extract ' . $mime);
		}

		$extractDir = self::getPackageDir($version);
		if (!mkdir($extractDir, 0777, true)) {
			throw new \Exception("Unable to create temporary directory");
		}

		$archive = \OC_Archive::open($path);
		if ($archive) {
			$archive->extract($extractDir);
		} else {
			\OC_Helper::rmdirr($extractDir);
			@unlink($path);
			throw new \Exception("$path extraction error");
		}

		return $extractDir. DIRECTORY_SEPARATOR . self::PACKAGE_ROOT;
	}

	public static function getPackageDir($version) {
		return App::getBackupBase() . $version;
	}

}