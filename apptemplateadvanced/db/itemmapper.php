<?php
/**
* ownCloud - App Template Example
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

namespace OCA\AppTemplateAdvanced\Db;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Db\DoesNotExistException;


class ItemMapper extends Mapper {


	private $tableName;

	/**
	 * @param API $api: Instance of the API abstraction layer
	 */
	public function __construct($api){
		parent::__construct($api);
		$this->tableName = '*PREFIX*apptemplateadvanced_items';
	}


	/**
	 * Finds an item by id
	 * @throws DoesNotExistException: if the item does not exist
	 * @throws MultipleObjectsReturnedException if more than one item exist
	 * @return the item
	 */
	public function find($id){
		$row = $this->findQuery($this->tableName, $id);
		return new Item($row);
	}


	/**
	 * Finds an item by user id
	 * @param string $userId: the id of the user that we want to find
	 * @throws DoesNotExistException: if the item does not exist
	 * @return the item
	 */
	public function findByUserId($userId){
		$sql = 'SELECT * FROM `' . $this->tableName . '` WHERE `user` = ?';
		$params = array($userId);

		$result = $this->execute($sql, $params)->fetchRow();
		if($result){
			return new Item($result);
		} else {
			throw new DoesNotExistException('Item with user id ' . $userId . ' does not exist!');
		}
	}


	/**
	 * Finds all Items
	 * @return array containing all items
	 */
	public function findAll(){
		$result = $this->findAllQuery($this->tableName);

		$entityList = array();
		while($row = $result->fetchRow()){
			$entity = new Item($row);
			array_push($entityList, $entity);
		}

		return $entityList;
	}


	/**
	 * Saves an item into the database
	 * @param Item $item: the item to be saved
	 * @return the item with the filled in id
	 */
	public function save($item){
		$sql = 'INSERT INTO `'. $this->tableName . '`(`name`, `user`, `path`)'.
				' VALUES(?, ?, ?)';

		$params = array(
			$item->getName(),
			$item->getUser(),
			$item->getPath()
		);

		$this->execute($sql, $params);

		$item->setId($this->api->getInsertId($this->tableName));
	}


	/**
	 * Updates an item
	 * @param Item $item: the item to be updated
	 */
	public function update($item){
		$sql = 'UPDATE `'. $this->tableName . '` SET
				`name` = ?,
				`user` = ?,
				`path` = ?
				WHERE `id` = ?';

		$params = array(
			$item->getName(),
			$item->getUser(),
			$item->getPath(),
			$item->getId()
		);

		$this->execute($sql, $params);
	}


	/**
	 * Deletes an item
	 * @param int $id: the id of the item
	 */
	public function delete($id){
		$this->deleteQuery($this->tableName, $id);
	}


}