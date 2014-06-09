<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Archive;

use OC\Files\Cache\Scanner;
use OC\Files\Cache\Updater;
use OC\Files\Mount\MoveableMount;

class Mount extends \OC\Files\Mount\Mount implements MoveableMount {
	/**
	 * @var \OC\Files\Mount\Manager
	 */
	protected $mountManager;

	/**
	 * @var \OC\Files\View
	 */
	protected $rootView;

	/**
	 * @var string
	 */
	protected $archivePath;

	public function __construct($storage, $mountpoint, $view, $path, $mountManager, $loader = null) {
		$this->rootView = $view;
		$this->archivePath = $path;
		$this->mountManager = $mountManager;
		parent::__construct($storage, $mountpoint, null, $loader);
	}

	/**
	 * Move the mount point to $target
	 *
	 * @param string $target the target mount point
	 * @return bool
	 */
	public function moveMount($target) {
		$sourceMount = $this->mountManager->find(dirname($this->archivePath));
		$targetMount = $this->mountManager->find($target);

		$sourceInternalPath = trim($sourceMount->getInternalPath($this->archivePath), '/');
		$targetInternalPath = $sourceMount->getInternalPath($target);

		$sourceStorage = $sourceMount->getStorage();
		$targetStorage = $targetMount->getStorage();

		if ($sourceMount->getMountPoint() === $targetMount->getMountPoint()) {
			$result = $sourceStorage->rename($sourceInternalPath, $targetInternalPath);
		} else {
			$source = $sourceStorage->fopen($sourceInternalPath, 'rb');
			$target = $targetStorage->fopen($targetInternalPath, 'wb+');
			stream_copy_to_stream($source, $target);
			$result = $sourceStorage->unlink($sourceInternalPath);
		}

		if ($result) {
			$this->archivePath = $target;
			$sourceStorage->getScanner()->scan(dirname($sourceInternalPath), Scanner::SCAN_SHALLOW, Scanner::REUSE_ETAG | Scanner::REUSE_SIZE);
			$targetStorage->getScanner()->scan(dirname($targetInternalPath), Scanner::SCAN_SHALLOW, Scanner::REUSE_ETAG | Scanner::REUSE_SIZE);
		}
		return $result;
	}

	/**
	 * Remove the mount points
	 *
	 * @return mixed
	 * @return bool
	 */
	public function removeMount() {
		$mount = $this->mountManager->find(dirname($this->archivePath));
		$internalPath = trim($mount->getInternalPath($this->archivePath), '/');
		$storage = $mount->getStorage();

		return $storage->unlink($internalPath);
	}
}
