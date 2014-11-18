<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Archive;

class Manager {
	/**
	 * @var string[]
	 */
	protected $mounted = array();

	/**
	 * @var bool
	 */
	protected $enableAutomount = true;

	/**
	 * @var \OC\Files\View
	 */
	protected $rootView;

	/**
	 * @var \OC\Files\Mount\Manager
	 */
	protected $mountManager;

	/**
	 * @var \OC\Files\Storage\Loader
	 */
	protected $storageLoader;

	/**
	 * @param \OC\Files\View $view
	 * @param \OC\Files\Mount\Manager $mountManager
	 * @param \OC\Files\Storage\Loader $storageLoader
	 */
	public function __construct($view, $mountManager, $storageLoader) {
		$this->rootView = $view;
		$this->mountManager = $mountManager;
		$this->storageLoader = $storageLoader;
	}

	/**
	 * automount paths from file hooks
	 *
	 * @param array $params
	 */
	public function autoMount($params) {
		if (!$this->enableAutomount) {
			return;
		}
		$path = $params['path'];
		$this->enableAutomount = false; //prevent recursion
		$supported = array('zip', 'tar.gz', 'tar.bz2', 'tgz');
		foreach ($supported as $type) {
			$ext = '.' . $type . '/';
			if (($pos = strpos(strtolower($path), $ext)) !== false) {
				$archive = substr($path, 0, $pos + strlen($ext) - 1);
				if ($this->rootView->file_exists($archive) and array_search($archive, $this->mounted) === false) {
					$localArchive = $this->rootView->getLocalFile($archive);
					$storage = new Storage(array(
						'archive' => $localArchive,
						'archivePath' => $archive,
						'manager' => $this->mountManager,
					));
					$mount = new Mount($storage, $archive . '/', $this->rootView, $path, $this->mountManager, $this->storageLoader);
					$this->mountManager->addMount($mount);
					$this->mounted[] = $archive;
				}
			}
		}
		$this->enableAutomount = true;
	}
}
