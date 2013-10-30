<?php

/**
 * ownCloud - Activities App
 *
 * @author Frank Karlitschek
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
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
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Activity;

use \OC_OCS_Result;

/**
 * @brief The class to handle the filesystem hooks
 */
class OCS {

	/**
	* @brief Registers the filesystem hooks for basic filesystem operations. All other events has to be triggered by the apps.
	*/
	public static function getActivities() {

		$start = isset($_GET['start']) ? $_GET['start'] : 0;
		$count = isset($_GET['count']) ? $_GET['count'] : 30;

		$data = Data::read($start,$count);
		$activities = array();

		foreach($data as $d) {
			$activity = array();
			$activity['id'] = $d['activity_id'];
			$activity['subject'] = $d['subject'];
			$activity['message'] = $d['message'];
			$activity['file'] = $d['file'];
			$activity['link'] = $d['link'];
			$activity['date'] = date('c', $d['timestamp']);

			$activities[] = $activity;
		}

		return new OC_OCS_Result($activities);

	}


}
