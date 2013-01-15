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


namespace OCA\AppFramework;


/**
 * A renderer for JSON calls
 */
class JSONResponse extends Response {

	private $name;
	private $data;
	private $appName;

	/**
	 * @param string $appName: the name of your app
	 */
	public function __construct($appName) {
		parent::__construct();
		$this->appName = $appName;
		$this->data = array();
		$this->error = false;
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
	 * @param string $file: the file where the error occured, use __FILE__ in
	 *                      the file where you call it
	 */
	public function setErrorMessage($msg, $file){
		$this->error = true;
		$this->data['msg'] = $msg;
		\OCP\Util::writeLog($this->appName, $file . ': ' . $msg, \OCP\Util::ERROR);
	}


	/**
	 * Returns the rendered json
	 * @return the rendered json
	 */
	public function render(){

		ob_start();

		if($this->error){
		\OCP\JSON::error($this->data);
		} else {
		\OCP\JSON::success($this->data);
		}

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

}