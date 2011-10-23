<?php

/**
* ownCloud - Background Job
*
* @author Jakob Sack
* @copyright 2011 Jakob Sack owncloud@jakobsack.de
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
*
*/

/**
 * This class manages our reports.
 */
class OC_Backgroundjobs_Queue{
	/**
	 * @brief Gets one report
	 * @param $id ID of the report
	 * @return associative array
	 */
	public static function find( $id ){
		$stmt = OC_DB::prepare( 'SELECT * FROM *PREFIX*backgroundjobs_queue WHERE id = ?' );
		$result = $stmt->execute(array($id));
		return $result->fetchRow();
	}

	/**
	 * @brief Gets all reports
	 * @return array with associative arrays
	 */
	public static function all(){
		// Array for objects
		$return = array();

		// Get Data
		$stmt = OC_DB::prepare( 'SELECT * FROM *PREFIX*backgroundjobs_queue' );
		$result = $stmt->execute(array());
		while( $row = $result->fetchRow()){
			$return[] = $row;
		}

		// Und weg damit
		return $return;
	}

	/**
	 * @brief Gets all reports from one app
	 * @param $app app name
	 * @return array with associative arrays
	 */
	public static function whereAppIs( $app ){
		// Array for objects
		$return = array();

		// Get Data
		$stmt = OC_DB::prepare( 'SELECT * FROM *PREFIX*backgroundjobs_queue WHERE app = ?' );
		$result = $stmt->execute(array($app));
		while( $row = $result->fetchRow()){
			$return[] = $row;
		}

		// Und weg damit
		return $return;
	}

	/**
	 * @brief adds a report
	 * @param $app app name
	 * @param $task task name
	 * @param $report all useful data as text
	 *
	 * Adds a report. Should be used in case of failure only.
	 */
	public static function add( $task, $task, $parameters ){
		$stmt = OC_DB::prepare( 'INSERT INTO *PREFIX*backgroundjobs_queue (app, task, report, timestamp) VALUES(?,?,?,?)' );
		$result = $stmt->execute(array($app,$task,$parameters,time));

		return OC_DB::insertid();
	}

	/**
	 * @brief deletes a report
	 * @param $id id of report
	 * @return true/false
	 *
	 * Deletes a report
	 */
	public static function delete( $id ){
		$stmt = OC_DB::prepare( 'DELETE FROM *PREFIX*backgroundjobs_queue WHERE id = ?' );
		$result = $stmt->execute(array($id));

		return true;
	}
}
