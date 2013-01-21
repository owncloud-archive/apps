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


require_once(__DIR__ . "/../classloader.php");


class ExampleMapper extends Mapper {
	public function __construct(API $api){ parent::__construct($api); }
	public function find($table, $id){ return $this->findQuery($table, $id); }
	public function findAll($table){ return $this->findAllQuery($table); }
	public function delete($table, $id){ $this->deleteQuery($table, $id); }
}


class MapperTest extends \PHPUnit_Framework_TestCase {

	private $api;

	public function setUp(){
		$this->api = $this->getMock('OCA\AppFramework\Core\API',
							array('getAppName', 'prepareQuery'), 
							array('test'));
	}



	private function find($doesNotExist=false){
		$sql = 'SELECT * FROM `hihi` WHERE `id` = ?';
		$params = array(1);

		$cursor = $this->getMock('cursor', array('fetchRow'));
		$cursor->expects($this->once())
				->method('fetchRow')
				->will($this->returnValue(!$doesNotExist));

		$query = $this->getMock('query', array('execute'));
		$query->expects($this->once())
				->method('execute')
				->with($this->equalTo($params))
				->will($this->returnValue($cursor));

		$this->api->expects($this->once())
				->method('prepareQuery')
				->with($this->equalTo($sql))
				->will($this->returnValue($query));

		$mapper = new ExampleMapper($this->api);

		if($doesNotExist){
			$this->setExpectedException('\OCA\AppFramework\Db\DoesNotExistException');
		}

		$result = $mapper->find('hihi', $params[0]);

		if($doesNotExist){
			$this->assertFalse($result);
		} else {
			$this->assertTrue($result);
		}
		
	}


	public function testFind(){
		$this->find();
	}


	public function testFindDoesNotExist(){
		$this->find(true);
	}


	private function query($method, $sql, $params=array()){

		$query = $this->getMock('query', array('execute'));
		
		$query->expects($this->once())
				->method('execute')
				->with($this->equalTo($params));

		$this->api->expects($this->once())
				->method('prepareQuery')
				->with($this->equalTo($sql))
				->will($this->returnValue($query));

		$mapper = new ExampleMapper($this->api);

		if(count($params) > 0){
			$mapper->$method('hihi', $params[0]);
		} else {
			$mapper->$method('hihi');
		}
	}


	public function testFindAll(){
		$this->query('findAll', 'SELECT * FROM `hihi`');
	}


	public function testDelete(){
		$this->query('delete', 'DELETE FROM `hihi` WHERE `id` = ?', array(1));
	}

}