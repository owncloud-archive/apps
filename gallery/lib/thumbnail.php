<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Gallery;

class Thumbnail {
	protected $image;
	protected $path;

	public function __construct($imagePath, $square = false) {
		$user = \OCP\USER::getUser();
		$galleryDir = \OC_User::getHome($user) . '/gallery/';
		$this->path = $galleryDir . $imagePath;
		if (!file_exists($this->path)) {
			self::create($imagePath, $square);
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
				$this->image->save($this->path);
			}
		}
	}

	public function get() {
		if (is_null($this->image)) {
			$this->image = new \OC_Image($this->path);
		}
		return $this->image;
	}

	public function show() {
		$fp = @fopen($this->path, 'rb');
		if ($fp) {
			\OC_Response::enableCaching();
			\OC_Response::setLastModifiedHeader(filemtime($this->path));
			header('Content-Length: ' . filesize($this->path));
			header('Content-Type: ' . \OC_Helper::getMimetype($this->path));

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
			unlink($thumbPath . '.png');
		} else {
			unlink($thumbPath);
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
		$thumbnail = imagecreatetruecolor(200, 200);
		imagesavealpha($thumbnail, true);
		imagefill($thumbnail, 0, 0, 0x7fff0000);
		imagealphablending($thumbnail, true);
		for ($i = 0; $i < $count; $i++) {
			$thumb = new Thumbnail($albumPath . '/' . $images[$i]);
			$image = $thumb->get();
			$image->fitIn(80, 80);
			if ($image && $image->valid()) {
				$h = $image->height();
				$w = $image->width();
				$x = (($i % 2) * 100) + (100 - $w) / 2;
				$y = (floor($i / 2) * 100) + (100 - $h) / 2;
				imagecopy($thumbnail, $image->resource(), $x, $y, 0, 0, $w, $h);
				$image->destroy();
			}
		}

		imagepng($thumbnail, $this->path);
		imagedestroy($thumbnail);
	}
}
