<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Updater;

class Location_Apps extends Location {

	protected $type = 'apps';
	protected $appsToDisable = array();
	protected $appsToUpdate = array();

	protected function filterOld($pathArray) {
		return $pathArray;
	}
	
	public function check() {
		$errors = array();
		if ($this->oldBase && !is_writable($this->oldBase)) {
			$errors[] = $this->oldBase;
		}
		
		$this->collect(true);
		foreach ($this->appsToUpdate as $item) {
			$path = \OC_App::getAppPath($item);
			if (!is_writable($path)) {
				$errors[] = $path;
			}
		}

		return $errors;
	}

	public function update($tmpDir = '') {
		Helper::mkdir($tmpDir, true);
		$this->collect(true);
		try {
			foreach ($this->appsToUpdate as $appId) {
				$path = \OC_App::getAppPath($appId);
				if ($path) {
					if (!@file_exists($this->newBase . '/' . $appId)){
						$this->appsToDisable[$appId] = $appId;
					} else {
						Helper::move($path, $tmpDir . '/' . $appId);
					
						// ! reverted intentionally
						$this->done [] = array(
							'dst' => $path,
							'src' => $tmpDir . '/' . $appId
						);
					
						Helper::move($this->newBase . '/' . $appId, $path);
					}
				}
			}
			$this->finalize();
		} catch (\Exception $e) {
			$this->rollback(true);
			throw $e;
		}
	}

	protected function finalize() {
		foreach ($this->appsToDisable as $appId) {
			\OC_App::disable($appId);
		}
	}

	protected function filterNew($pathArray) {
		return $pathArray;
	}

	public function collect($dryRun = false) {
		foreach (\OC_App::getAllApps() as $appId) {
			if (\OC_App::isShipped($appId)) {
				if ($dryRun || @file_exists($this->newBase . '/' . $appId)) {
					$this->appsToUpdate[$appId] = $appId;
				} else {
					$this->appsToDisable[$appId] = $appId;
				}
			} else {
				$this->appsToDisable[$appId] = $appId;
			}
		}
	}

}
