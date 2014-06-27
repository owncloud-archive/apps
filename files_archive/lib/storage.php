<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Archive;

use OC\Files\Cache\Updater;
use OC\Files\Filesystem;
use OC\Files\Storage\Common;
use OC\Files\View;

class Storage extends Common {
	/**
	 * underlying local storage used for missing functions
	 *
	 * @var \OC_Archive
	 */
	private $archive;

	/**
	 * @var string absolute path to the archive on the local fs
	 */
	private $path;

	/**
	 * @var string path to the archive in ownCloud's filesystem
	 */
	private $archivePath;

	/**
	 * @var \OC\Files\Mount\Manager $mountManager
	 */
	private $mountManager;

	private function stripPath($path) { //files should never start with /
		if (!$path || $path[0] == '/') {
			$path = substr($path, 1);
		}
		return $path;
	}

	public function __construct($params) {
		$this->archive = \OC_Archive::open($params['archive']);
		$this->path = $params['archive'];
		$this->mountManager = $params['manager'];
		$this->archivePath = $params['archivePath'];
	}

	public function mkdir($path) {
		return false;
	}

	public function rmdir($path) {
		$this->unlink($path);
	}

	public function opendir($path) {
		if (!$path) {
			$path = '/';
		} else if (substr($path, -1) !== '/') {
			$path .= '/';
		}
		$path = $this->stripPath($path);
		$files = $this->archive->getFolder($path);
		$content = array();
		foreach ($files as $file) {
			if (substr($file, -1) == '/') {
				$file = substr($file, 0, -1);
			}
			if ($file and strpos($file, '/') === false) {
				$content[] = $file;
			}
		}
		$id = md5($this->path . $path);
		\OC\Files\Stream\Dir::register($id, $content);
		return opendir('fakedir://' . $id);
	}

	public function stat($path) {
		$ctime = -1;
		$path = $this->stripPath($path);
		if (!$path or $path === '/') {
			$stat = stat($this->path);
			$stat['size'] = 0;
		} else {
			if ($this->is_dir($path)) {
				$stat = array('size' => 0);
				$stat['mtime'] = filemtime($this->path);
			} else {
				$stat = array();
				$stat['mtime'] = $this->archive->mtime($path);
				$stat['size'] = $this->archive->filesize($path);
				if (!$stat['mtime']) {
					$stat['mtime'] = time();
				}
			}
		}
		$stat['ctime'] = $ctime;
		return $stat;
	}

	public function filetype($path) {
		$path = $this->stripPath($path);
		if (!$path or $path === '/') {
			return 'dir';
		}
		if (substr($path, -1) == '/') {
			return $this->archive->fileExists($path) ? 'dir' : 'file';
		} else {
			return $this->archive->fileExists($path . '/') ? 'dir' : 'file';
		}
	}

	public function isReadable($path) {
		return true;
	}

	public function isUpdatable($path) {
		return false;
	}

	public function isSharable($path) {
		return $path === '' or $path === '/';
	}

	public function file_exists($path) {
		$path = $this->stripPath($path);
		if ($path == '') {
			return true;
		}
		return $this->archive->fileExists($path);
	}

	public function unlink($path) {
		return false;
	}

	public function fopen($path, $mode) {
		if ($mode !== 'r' and $mode !== 'rb') {
			return false;
		}
		$path = $this->stripPath($path);
		return $this->archive->getStream($path, $mode);
	}

	public function free_space($path) {
		return 0;
	}

	public function touch($path, $mtime = null) {
		return false;
	}

	protected function toTmpFile($path) {
		$tmpFile = \OCP\Files::tmpFile();
		$path = $this->stripPath($path);
		$this->archive->extractFile($path, $tmpFile);
		return $tmpFile;
	}

	public function file_put_contents($path, $data) {
		return false;
	}

	public function file_get_contents($path) {
		$path = $this->stripPath($path);
		return $this->archive->getFile($path);
	}

	public function getMimeType($path) {
		if (!$path or $path === '/') {
			return \OC_Helper::getFileNameMimeType($this->path);
		} else {
			return parent::getMimeType($path);
		}
	}

	public function rename($path1, $path2) {
		return false;
	}

	public function hasUpdated($path, $time) {
		return false;
	}

	public function getId() {
		return 'archive::' . md5($this->path);
	}

	public function getCache($path = '', $storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		return new Cache($storage, $this->mountManager, $this->archivePath);
	}
}
