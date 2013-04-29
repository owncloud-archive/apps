<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Storage;

class Archive extends Common {
	/**
	 * underlying local storage used for missing functions
	 *
	 * @var \OC_Archive
	 */
	private $archive;
	private $path;
	private static $mounted = array();
	private static $enableAutomount = true;
	private static $rootView;

	private function stripPath($path) { //files should never start with /
		if (!$path || $path[0] == '/') {
			$path = substr($path, 1);
		}
		return $path;
	}

	public function __construct($params) {
		$this->archive = \OC_Archive::open($params['archive']);
		$this->path = $params['archive'];
	}

	public function mkdir($path) {
		$path = $this->stripPath($path);
		return $this->archive->addFolder($path);
	}

	public function rmdir($path) {
		$path = $this->stripPath($path);
		return $this->archive->remove($path . '/');
	}

	public function opendir($path) {
		if (substr($path, -1) !== '/') {
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
		if ($path == '') {
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
		if ($path == '') {
			return 'dir';
		}
		if (substr($path, -1) == '/') {
			return $this->archive->fileExists($path) ? 'dir' : 'file';
		} else {
			return $this->archive->fileExists($path . '/') ? 'dir' : 'file';
		}
	}

	public function isReadable($path) {
		return is_readable($this->path);
	}

	public function isUpdatable($path) {
		return is_writable($this->path);
	}

	public function file_exists($path) {
		$path = $this->stripPath($path);
		if ($path == '') {
			return file_exists($this->path);
		}
		return $this->archive->fileExists($path);
	}

	public function unlink($path) {
		$path = $this->stripPath($path);
		return $this->archive->remove($path);
	}

	public function fopen($path, $mode) {
		$path = $this->stripPath($path);
		return $this->archive->getStream($path, $mode);
	}

	public function free_space($path) {
		return 0;
	}

	public function touch($path, $mtime = null) {
		if (is_null($mtime)) {
			$tmpFile = \OCP\Files::tmpFile();
			$this->archive->extractFile($path, $tmpFile);
			$this->archive->addfile($path, $tmpFile);
			return true;
		} else {
			return false; //not supported
		}
	}

	private function toTmpFile($path) {
		$tmpFile = \OC_Helper::tmpFile();
		$this->archive->extractFile($path, $tmpFile);
		return $tmpFile;
	}

	public function file_put_contents($path, $data) {
		$path = $this->stripPath($path);
		return $this->archive->addFile($path, $data);
	}

	public function file_get_contents($path) {
		$path = $this->stripPath($path);
		return $this->archive->getFile($path);
	}

	/**
	 * automount paths from file hooks
	 *
	 * @param array $params
	 */
	public static function autoMount($params) {
		if (!self::$enableAutomount) {
			return;
		}
		$path = $params['path'];
		if (!self::$rootView) {
			self::$rootView = new \OC_FilesystemView('');
		}
		self::$enableAutomount=false;//prevent recursion
		$supported = array('zip', 'tar.gz', 'tar.bz2', 'tgz');
		foreach ($supported as $type) {
			$ext = '.' . $type . '/';
			if (($pos = strpos(strtolower($path), $ext)) !== false) {
				$archive = substr($path, 0, $pos + strlen($ext) - 1);
				if (self::$rootView->file_exists($archive) and  array_search($archive, self::$mounted) === false) {
					$localArchive = self::$rootView->getLocalFile($archive);
					\OC\Files\Filesystem::mount('\OC\Files\Storage\Archive', array('archive' => $localArchive), $archive . '/');
					self::$mounted[] = $archive;
				}
			}
		}
		self::$enableAutomount = true;
	}

	public function rename($path1, $path2) {
		return $this->archive->rename($path1, $path2);
	}

	public function hasUpdated($path, $time) {
		return $this->filemtime($this->path) > $time;
	}

	public function getId() {
		return 'archive::' . md5($this->path);
	}
}
