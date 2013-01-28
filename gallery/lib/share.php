<?php
/**
 * ownCloud
 *
 * @author Michael Gapczynski
 * @copyright 2012 Michael Gapczynski mtgap@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Gallery\Share;

class Gallery implements \OCP\Share_Backend {

	public function isValidSource($itemSource, $uidOwner) {
		return is_array(\OC\Files\Cache\Cache::getById($itemSource));
	}

	public function generateTarget($itemSource, $shareWith, $exclude = null) {
		return $itemSource;
	}

	public function formatItems($items, $format, $parameters = null) {
		return $items;
	}
}
