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

class Location_3rdparty extends Location {
	
	protected $type = '3rdparty';
	
	protected function filterOld($pathArray) {
		return $pathArray;
	}

	protected function filterNew($pathArray) {
		return $pathArray;
	}
	
}
