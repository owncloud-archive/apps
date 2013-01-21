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


namespace OCA\AppFramework\Http;


/**
 * A renderer for JSON calls
 */
class JSONResponse extends Response {

	private $name;
	private $data;


	public function __construct() {
		parent::__construct();
		$this->data = array();
		$this->error = false;
		$this->data['status'] = 'success';
		$this->addHeader('Content-type: application/json');
	}


	/**
	 * @brief sets values in the data json array
	 * @param array $params: an array with key => value structure which will be
	 *                       transformed to JSON
	 */
	public function setParams(array $params){
		$this->data['data'] = $params;
	}


	/**
	 * @return the params
	 */
	public function getParams(){
		return $this->data['data'];
	}


	/**
	 * @brief in case we want to render an error message, also logs into the
	 *        owncloud log
	 * @param string $message: the error message
	 */
	public function setErrorMessage($msg){
		$this->error = true;
		$this->data['msg'] = $msg;
		$this->data['status'] = 'error';
	}


	/**
	 * Returns the rendered json
	 * @return the rendered json
	 */
	public function render(){
		return json_encode($this->data);
	}

}