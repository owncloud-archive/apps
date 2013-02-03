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

use OCA\AppFramework\Core\API;


/**
 * Simple parent class for inheriting your data access layer from. This class
 * may be subject to change in the future
 */
abstract class Mapper {

	/**
	 * @param API $api Instance of the API abstraction layer
	 */
	public function __construct(API $api){
		$this->api = $api;
	}


	/**
	 * Returns an db result by id
	 * @param string $tableName the name of the table to query
	 * @param int $id the id of the item
	 * @throws DoesNotExistException if the item does not exist
	 * @throws MultipleObjectsReturnedException if more than one item exist
	 * @return array the result as row
	 */
	protected function findQuery($tableName, $id){
		$sql = 'SELECT * FROM `' . $tableName . '` WHERE `id` = ?';
		$params = array($id);

		$result = $this->execute($sql, $params);
		$row = $result->fetchRow();
		if($row === null){
			throw new DoesNotExistException('Item with id ' . $id . ' does not exist!');
		} elseif($result->fetchRow() !== null) {
			throw new MultipleObjectsReturnedException('More than one result for Item with id ' . $id . '!');
		}

		return $row;
	}


	/**
	 * Returns all entries of a table
	 * @param string $tableName the name of the table to query
	 * @return \PDOStatement the result
	 */
	protected function findAllQuery($tableName){
		$sql = 'SELECT * FROM `' . $tableName . '`';
		return $this->execute($sql);
	}


	/**
	 * Deletes a row in a table by id
	 * @param string $tableName the name of the table to query
	 * @param int $id the id of the item
	 */
	protected function deleteQuery($tableName, $id){
		$sql = 'DELETE FROM `' . $tableName . '` WHERE `id` = ?';
		$params = array($id);
		$this->execute($sql, $params);
	}


	/**
	 * Runs an sql query
	 * @param string $sql the prepare string
	 * @param array $params the params which should replace the ? in the sql query
	 * @param int $limit the maximum number of rows
	 * @param int $offset from which row we want to start
	 * @return \PDOStatement the database query result
	 */
	protected function execute($sql, array $params=array(), $limit=null, $offset=null){
		$query = $this->api->prepareQuery($sql);
		return $query->execute($params);
	}

}