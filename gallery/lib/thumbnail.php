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
}

class AlbumThumbnail extends Thumbnail {

	public function __construct($imagePath, $square = false) {
		$user = \OCP\USER::getUser();
		$galleryDir = \OC_User::getHome($user) . '/gallery/';
		$this->path = $galleryDir . $imagePath . '.jpg';
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
