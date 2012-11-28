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

namespace OCA\AppTemplate;


class Controller {

	protected $api;
	protected $appName;

	private $request;

	/**
	 * @param API $api: an api wrapper instance
	 * @param Request $request: an instance of the request
	 */
	public function __construct($api, $request){
		$this->api = $api;
		$this->request = $request;
				$this->appName = $api->getAppName();
	}


	/**
	 * @brief lets you access post and get parameters by the index
	 * @param string $key: the key which you want to access in the $_POST or
	 *                     $_GET array. If both arrays store things under the same
	 *                     key, return the value in $_POST
	 * @return: the content of the array
	 */
	protected function params($key){
		$postValue = $this->request->getPOST($key);
		$getValue = $this->request->getGET($key);
		
		if($postValue !== null){
			return $postValue;
		}

		if($getValue !== null){
			return $getValue;
		}

	}

}