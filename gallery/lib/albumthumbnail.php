<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Gallery;

use OC\Files\Filesystem;
use OC\Files\View;

class AlbumThumbnail extends Thumbnail {

	public function __construct($imagePath, $user = null, $square = false) {
		if (!Filesystem::isValidPath($imagePath)) {
			return;
		}
		if (is_null($user)) {
			$this->view = Filesystem::getView();
			$this->user = \OCP\USER::getUser();
		} else {
			$this->view = new View('/' . $user . '/files');
			$this->user = $user;
		}
		$galleryDir = \OC_User::getHome($this->user) . '/gallery/' . $this->user . '/';
		$this->path = $galleryDir . $imagePath . '.png';
		if (!file_exists($this->path)) {
			self::create($imagePath, $square);
		}
	}

	public function create($albumPath, $square = false) {
		$albumView = new View($this->view->getRoot() . $albumPath);
		$images = $albumView->searchByMime('image', 10);

		$count = min(count($images), 10);
		$thumbnail = imagecreatetruecolor($count * 200, 200);
		for ($i = 0; $i < $count; $i++) {
			$thumb = new Thumbnail($albumPath . $images[$i]['path'], $this->user, true);
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
