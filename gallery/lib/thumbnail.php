<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Gallery;

use OC\Files\Filesystem;

class Thumbnail {
	static private $writeHookCount;

	protected $image;
	protected $path;
	protected $user;
	protected $useOriginal = false;

	/**
	 * @var \OC\Files\View $view
	 */
	protected $view;

	public function __construct($imagePath, $user = null, $square = false) {
		if (!\OC\Files\Filesystem::isValidPath($imagePath)) {
			return;
		}
		if (is_null($user)) {
			$this->view = \OC\Files\Filesystem::getView();
			$this->user = \OCP\USER::getUser();
		} else {
			$this->view = new \OC\Files\View('/' . $user . '/files');
			$this->user = $user;
		}
		$this->useOriginal = (substr($imagePath, -4) === '.svg' or substr($imagePath, -5) === '.svgz');
		if ($this->useOriginal) {
			$this->path = $imagePath;
		} else {
			$galleryDir = \OC_User::getHome($this->user) . '/gallery/' . $this->user . '/';
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
		$galleryDir = \OC_User::getHome($this->user) . '/gallery/';
		$dir = dirname($imagePath);
		if (!is_dir($galleryDir . $dir)) {
			mkdir($galleryDir . $dir, 0755, true);
		}
		if (!$this->view->file_exists($imagePath)) {
			return;
		}
		$this->image = new \OC_Image($this->view->getLocalFile($imagePath));
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
			$fp = @$this->view->fopen($this->path, 'rb');
			$mtime = $this->view->filemtime($this->path);
			$size = $this->view->filesize($this->path);
			$mime = $this->view->getMimetype($this->path);
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
		if ($parent !== DIRECTORY_SEPARATOR and $parent !== '') {
			self::removeHook(array('path' => $parent));
		}
	}

	static public function writeHook($params) {
		self::removeHook($params);
		//only create 5 thumbnails max in one request to prevent locking up the request
		if (self::$writeHookCount < 5) {
			$path = $params['path'];
			$mime = \OC\Files\Filesystem::getMimetype($path);
			if (substr($mime, 0, 6) === 'image/') {
				self::$writeHookCount++;
				new Thumbnail($path);
			}
		}
	}
}

class AlbumThumbnail extends Thumbnail {

	public function __construct($imagePath, $user = null, $square = false) {
		if (!\OC\Files\Filesystem::isValidPath($imagePath)) {
			return;
		}
		if (is_null($user)) {
			$this->view = \OC\Files\Filesystem::getView();
			$this->user = \OCP\USER::getUser();
		} else {
			$this->view = new \OC\Files\View('/' . $user . '/files');
			$this->user = $user;
		}
		$galleryDir = \OC_User::getHome($this->user) . '/gallery/' . $this->user . '/';
		$this->path = $galleryDir . $imagePath . '.png';
		if (!file_exists($this->path)) {
			self::create($imagePath, $square);
		}
	}

	public function create($albumPath, $square = false) {
		$albumView = new \OC\Files\View($this->view->getRoot() . $albumPath);
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
