<?php

// Set up flock
\OC\Files\Filesystem::addStorageWrapper('oc_flock', function ($mountPoint, $storage) {
	/**
	 * @var \OC\Files\Storage\Storage $storage
	 */
	if ($storage instanceof \OC\Files\Storage\Storage && $storage->isLocal()) {
		return new \OCA\Files_Locking\LockingWrapper(array('storage' => $storage));
	} else {
		return $storage;
	}
});
