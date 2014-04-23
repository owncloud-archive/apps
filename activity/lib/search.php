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


/**
* @brief Class for provide search results for the system search
*/
class Search extends \OC_Search_Provider{
	
	/**
	* @brief Search in the activities and return search results
	* @param $query
	* @return search results
	*/
	function search($query){

		$data = Data::search($query, 100);

		$results = array();
		foreach($data as $d){
			$file = $d['file'];
			$results[] = new \OC_Search_Result(
				basename($file),
				$d['subject'].' ('.\OCP\Util::formatDate($d['timestamp']).')',
				\OC_Helper::linkTo( 'activity', 'index.php' ), 'Activity', dirname($file));
		}

		return $results;
	}

}
