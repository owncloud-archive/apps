<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Gallery;

class Thumbnail {
	static private $writeHookCount;

	protected $image;
	protected $path;
	protected $useOriginal = false;

	public function __construct($imagePath, $square = false) {
		$this->useOriginal = (substr($imagePath, -4) === '.svg' or substr($imagePath, -5) === '.svgz');
		if ($this->useOriginal) {
			$this->path = $imagePath;
		} else {
			$user = \OCP\USER::getUser();
			$galleryDir = \OC_User::getHome($user) . '/gallery/';
			if (strrpos($imagePath, '.')) {
				$extension = substr($imagePath, strrpos($imagePath, '.') + 1);
				$image = substr($imagePath, 0, strrpos($imagePath, '.'));
			} else {
				$extension = '';
				$image = $imagePath;
			}
			if ($square) {
				$extension = 'square.' . $extension;
			}
			$this->path = $galleryDir . $image . '.' . $extension;
			if (!file_exists($this->path)) {
				self::create($imagePath, $square);
			}
		}
	}

	public function create($imagePath, $square) {
		$user = \OCP\USER::getUser();
		$galleryDir = \OC_User::getHome($user) . '/gallery/';
		$dir = dirname($imagePath);
		if (!is_dir($galleryDir . $dir)) {
			mkdir($galleryDir . $dir, 0755, true);
		}
		if (!\OC_Filesystem::file_exists($imagePath)) {
			return;
		}
		$this->image = new \OC_Image(\OC_Filesystem::getLocalFile($imagePath));
		if ($this->image->valid()) {
			$this->image->fixOrientation();
			if ($square) {
				$this->image->centerCrop(200);
			} else {
				$this->image->fitIn(400, 200);
			}
			$this->image->save($this->path);
		}
	}

	public function get() {
		if (is_null($this->image)) {
			$this->image = new \OC_Image($this->path);
		}
		return $this->image;
	}

	public function show() {
		if ($this->useOriginal) {
			$fp = @\OC_Filesystem::fopen($this->path, 'rb');
			$mtime = \OC_Filesystem::filemtime($this->path);
			$size = \OC_Filesystem::filesize($this->path);
			$mime = \OC_Filesystem::getMimetype($this->path);
		} else {
			$fp = @fopen($this->path, 'rb');
			$mtime = filemtime($this->path);
			$size = filesize($this->path);
			$mime = \OC_Helper::getMimetype($this->path);
		}
		if ($fp) {
			\OC_Response::enableCaching();
			\OC_Response::setLastModifiedHeader($mtime);
			header('Content-Length: ' . $size);
			header('Content-Type: ' . $mime);

			fpassthru($fp);
		} else {
			\OC_Response::setStatus(\OC_Response::STATUS_NOT_FOUND);
		}
	}

	static public function removeHook($params) {
		$path = $params['path'];
		$user = \OCP\USER::getUser();
		$galleryDir = \OC_User::getHome($user) . '/gallery/';
		$thumbPath = $galleryDir . $path;
		if (is_dir($thumbPath)) {
			if (file_exists($thumbPath . '.png')) {
				unlink($thumbPath . '.png');
			}
		} else {
			if (file_exists($thumbPath)) {
				unlink($thumbPath);
			}

			if (strrpos($path, '.')) {
				$extension = substr($path, strrpos($path, '.') + 1);
				$image = substr($path, 0, strrpos($path, '.'));
			} else {
				$extension = '';
				$image = $path;
			}
			$squareThumbPath = $galleryDir . $image . '.square.' . $extension;
			if (file_exists($squareThumbPath)) {
				unlink($squareThumbPath);
			}
		}

		$parent = dirname($path);
		if ($parent !== '/') {
			self::removeHook(array('path' => $parent));
		}
	}

	static public function writeHook($params) {
		self::removeHook($params);
		//only create 5 thumbnails max in one request to prevent locking up the request
		if (self::$writeHookCount < 5) {
			$path = $params['path'];
			$mime = \OC_Filesystem::getMimetype($path);
			if (substr($mime, 0, 6) === 'image/') {
				self::$writeHookCount++;
				new Thumbnail($path);
			}
		}
	}
}

class AlbumThumbnail extends Thumbnail {

	public function __construct($imagePath, $square = false) {
		$user = \OCP\USER::getUser();
		$galleryDir = \OC_User::getHome($user) . '/gallery/';
		$this->path = $galleryDir . $imagePath . '.png';
		if (!file_exists($this->path)) {
			self::create($imagePath, $square);
		}
	}

	public function create($albumPath, $square = false) {
		$user = \OCP\USER::getUser();
		$images = \OC_Filecache::searchByMime('image', null, '/' . $user . '/files' . $albumPath);

		$count = min(count($images), 10);
		$thumbnail = imagecreatetruecolor($count * 200, 200);
		for ($i = 0; $i < $count; $i++) {
			$thumb = new Thumbnail($albumPath . '/' . $images[$i], true);
			$image = $thumb->get();
			if ($image && $image->valid()) {
				imagecopy($thumbnail, $image->resource(), $i * 200, 0, 0, 0, 200, 200);
				$image->destroy();
			}
		}

		imagepng($thumbnail, $this->path);
		imagedestroy($thumbnail);
	}
}
