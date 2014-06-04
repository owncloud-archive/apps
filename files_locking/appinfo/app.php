<?php

// Set up flock
\OC\Files\Filesystem::addStorageWrapper('oc_flock', function ($mountPoint, $storage) {
	/**
	 * @var \OC\Files\Storage\Storage $storage
	 */
	if ($storage instanceof \OC\Files\Storage\Storage && $storage->isLocal()) {
		return new \OC\Files\Storage\Wrapper\LockingWrapper(array('storage' => $storage));
	} else {
		return $storage;
	}
});
