<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Archive;

class Cache extends \OC\Files\Cache\Cache {
	/**
	 * @var string
	 */
	protected $archivePath;

	/**
	 * @var \OC\Files\Mount\Manager
	 */
	protected $mountManager;

	/**
	 * @param \OC\Files\Storage\Storage|string $storage
	 * @param \OC\Files\Mount\Manager $manager
	 * @param string $path
	 */
	public function __construct($storage, $manager, $path) {
		parent::__construct($storage);
		$this->mountManager = $manager;
		$this->archivePath = $path;
	}

	public function get($path) {
		$data = parent::get($path);
		if ($path === '/' or $path === '') {
			$mount = $this->mountManager->find(dirname($this->archivePath));
			$storage = $mount->getStorage();
			$internalPath = $mount->getInternalPath($this->archivePath);
			$data['size'] = $storage->filesize($internalPath);
		}
		return $data;
	}
}
