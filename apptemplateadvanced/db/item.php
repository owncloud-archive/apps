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


class Item {

	private $id;
	private $name;
	private $path;
	private $user;

	public function __construct($fromRow=null){
		if($fromRow){
			$this->fromRow($fromRow);
		}
	}

	public function fromRow($row){
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->path = $row['path'];
		$this->user = $row['user'];
	}


	public function getId(){
		return $this->id;
	}

	public function getName(){
		return $this->name;
	}

	public function getUser(){
		return $this->user;
	}

	public function getPath(){
		return $this->path;
	}


	public function setId($id){
		$this->id = $id;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function setUser($user){
		$this->user = $user;
	}

	public function setPath($path){
		$this->path = $path;
	}

}