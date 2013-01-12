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

namespace OCA\AppTemplateAdvanced;


class Controller {

	protected $api;
	protected $appName;
	protected $request;

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
	 * @param $default: If the key is not found, this value will be returned
	 * @return: the content of the array
	 */
	protected function params($key, $default=null){
		$postValue = $this->request->getPOST($key);
		$getValue = $this->request->getGET($key);
		
		if($postValue !== null){
			return $postValue;
		}

		if($getValue !== null){
			return $getValue;
		}

		return $default;
	}


	/**
	 * Shortcut for accessing an uploaded file through the $_FILES array
	 * @param string $key: the key that will be taken from the $_FILES array
	 * @return the file in the $_FILES element
	 */
	protected function getUploadedFile($key){
		return $this->request->getFILES($key);
	}


	/**
	 * Shortcut for rendering a template
	 * @param string $templateName: the name of the template
	 * @param array $params: the template parameters in key => value structure
	 * @param string $renderAs: user renders a full page, blank only your template
	 *                          admin an entry in the admin settings
	 * @param array $headers: set additional headers
	 * @return a TemplateResponse
	 */
	protected function render($templateName, $params=array(), $renderAs='user',
								$headers=array()){
		$response = new TemplateResponse($this->appName, $templateName, $renderAs);
		$response->setParams($params);
		$response->renderAs($renderAs);

		foreach($headers as $header){
			$response->addHeader($header);
		}

		return $response;
	}


	/**
	 * Shortcut for rendering a JSON response
	 * @param array $data: the PHP array that will be put into the JSON data index
	 * @param string $errorMsg: If you want to return an error message, pass one
	 * @param string $file: the file where the error message happened
	 * @return a JSONResponse
	 */
	protected function renderJSON($data=array(), $errorMsg=null, $file=''){
		$response = new JSONResponse($this->appName);
		$response->setParams($data);

		if($errorMsg !== null){
			$response->setErrorMessage($errorMsg, $file);
		}

		return $response;
	}

}