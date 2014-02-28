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

class Location_Core extends Location {

	protected $type = 'core';
	
	public function check() {
		$errors = parent::check();
		$specialCases = array(
			$this->oldBase . '/' . 'config',
			$this->oldBase . '/' . 'themes',
		);

		foreach ($specialCases as $item) {
			if (!is_writable($item)) {
				$errors[] = $item;
			}
		}

		return $errors;
	}

	protected function filterOld($pathArray) {
		$skip = array_values(Helper::getDirectories());
		$skip[] = rtrim(App::getBackupBase(), '/');
		$skip[] = \OCP\Config::getSystemValue("datadirectory", \OC::$SERVERROOT . "/data");
		$skip[] = rtrim(App::getTempBase(), '/');

		// Skip 3rdparty | apps | backup | datadir | config | themes
		foreach ($pathArray as $key => $path) {
			if ($path === 'config' || $path === 'themes') {
				unset($pathArray[$key]);
			}
			if (in_array($this->oldBase . '/' . $path, $skip)) {
				unset($pathArray[$key]);
			}
		}
		return $pathArray;
	}

	protected function filterNew($pathArray) {
		// Skip config | themes
		foreach ($pathArray as $key => $path) {
			if ($path === 'config' || $path === 'themes') {
				unset($pathArray[$key]);
			}
		}
		return $pathArray;
	}

	protected function finalize() {
		// overwrite config.sample.php
		Helper::removeIfExists($this->oldBase . '/config/config.sample.php');
		Helper::move($this->newBase . '/config/config.sample.php', $this->oldBase . '/config/config.sample.php');

		// overwrite themes content with new files only
		$themes = $this->toAbsolute(
				$this->newBase . '/themes', Helper::scandir($this->newBase . '/themes')
		);

		foreach ($themes as $name => $location) {
			Helper::removeIfExists($this->oldBase . '/themes/' . $name);
			Helper::move($location, $this->oldBase . '/themes/' . $name);
		}

		parent::finalize();
	}

}
