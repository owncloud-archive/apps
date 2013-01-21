<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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


namespace OCA\AppFramework\Db;

use OCA\AppFramework\Core\API as API;


abstract class Mapper {

	/**
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct(API $api){
		$this->api = $api;
	}


	/**
	 * Returns an db result by id
	 * @param string $tableName: the name of the table to query
	 * @param int $id: the id of the item
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the result
	 */
	protected function findQuery($tableName, $id){
		$sql = 'SELECT * FROM `' . $tableName . '` WHERE `id` = ?';
		$params = array($id);

		$result = $this->execute($sql, $params)->fetchRow();
		if($result){
			return $result;
		} else {
			throw new DoesNotExistException('Item with id ' . $id . ' does not exist!');
		}

	}


	/**
	 * Returns all entries of a table
	 * @param string $tableName: the name of the table to query
	 * @return the result
	 */
	protected function findAllQuery($tableName){
		$sql = 'SELECT * FROM `' . $tableName . '`';
		return $this->execute($sql);
	}


	/**
	 * Deletes a row in a table by id
	 * @param string $tableName: the name of the table to query
	 * @param int $id: the id of the item
	 */
	protected function deleteQuery($tableName, $id){
		$sql = 'DELETE FROM `' . $tableName . '` WHERE `id` = ?';
		$params = array($id);
		$this->execute($sql, $params);
	}


	/**
	 * Runs an sql query
	 * @param string $sql: the prepare string
	 * @param array $params: the params which should replace the ? in the sql query
	 * @param int $limit: the maximum number of rows
	 * @param int $offset: from which row we want to start
	 * @return the database query result
	 */
	protected function execute($sql, array $params=array(), $limit=null, $offset=null){
		$query = $this->api->prepareQuery($sql);
		return $query->execute($params);
	}

}