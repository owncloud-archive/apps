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


class Controller {

	protected $api;
	protected $request;

	private $urlParams;

	/**
	 * @param API $api: an api wrapper instance
	 * @param Request $request: an instance of the request
	 */
        public function __construct(API $api, Request $request){
		$this->api = $api;
		$this->request = $request;
		$this->urlParams = array();
	}


	/**
	 * @brief URL params are passed to this method from the routes dispatcher to
	 * be available via the $this->params
	 * @param array $urlParams: the array with the params from the URL
	 */
        public function setURLParams(array $urlParams=array()){
		$this->urlParams = $urlParams;
	}


	/**
	 * @brief lets you access post and get parameters by the index
	 * @param string $key: the key which you want to access in the URL Parameter
	 *                     placeholder, $_POST or $_GET array. 
	 *                     The priority how they're returned is the following:
	 *                     1. URL parameters
	 *                     2. POST parameters
	 *                     3. GET parameters
	 * @param $default: If the key is not found, this value will be returned
	 * @return: the content of the array
	 */
	public function params($key, $default=null){
		$postValue = $this->request->getPOST($key);
		$getValue = $this->request->getGET($key);

		if(array_key_exists($key, $this->urlParams)){
			return $this->urlParams[$key];
		}

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
	public function getUploadedFile($key){
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
        protected function render($templateName, array $params=array(),
                                                                $renderAs='user', array $headers=array()){
		$response = new TemplateResponse($this->api->getAppName(), $templateName, $renderAs);
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
        protected function renderJSON(array $data=array(), $errorMsg=null, $file=''){
		$response = new JSONResponse($this->api->getAppName());
		$response->setParams($data);

		if($errorMsg !== null){
			$response->setErrorMessage($errorMsg, $file);
		}

		return $response;
	}

}