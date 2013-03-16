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
 * Encapsulates $_GET, $_FILES and $_POST arrays for better testability
 */
class Request {

	private $get;
	private $post;
	private $files;
	private $server;
	private $env;
	private $session;
	private $cookie;
	private $urlParams;

	/**
	 * @param array $get the $_GET array
	 * @param array $post the $_POST array
	 * @param array $files the $_FILES array
	 * @param array $server the $_SERVER array
	 * @param array $env the $_ENV array
	 * @param array $session the $_SESSION array
	 * @param array $cookie the $_COOKIE array
	 * @param array $urlParams the parameters which were matched from the URL
	 */
	public function __construct(array $get=array(), array $post=array(), 
								array $files=array(), array $server=array(),
								array $env=array(), array $session=array(),
								array $cookie=array(), 
								array $urlParams=array()) {
		$this->get = $get;
		$this->post = $post;
		$this->files = $files;
		$this->server = $server;
		$this->env = $env;
		$this->session = $session;
		$this->cookie = $cookie;
		$this->urlParams = $urlParams;
	}


	/**
	 * Returns the merged urlParams, GET and POST array
	 * @return array the merged array
	 */
	public function getRequestParams(){
		return array_merge($this->urlParams, $this->get, $this->post);
	}


	/**
	 * Returns the get value or the default if not found
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getGET($key, $default=null){
		if(array_key_exists($key, $this->get)){
			return $this->get[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the get value or the default if not found
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getPOST($key, $default=null){
		if(array_key_exists($key, $this->post)){
			return $this->post[$key];
		} else {
			return $default;
		}
	}

	/**
	 * Returns the get value of the files array
	 * @param string $key the array key that should be looked up
	 * @return mixed the value of the stored array or the default
	 */
	public function getFILES($key){
		if(array_key_exists($key, $this->files)){
			return $this->files[$key];
		} else {
			return null;
		}
	}


	/**
	 * Returns the get value of the server array
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getSERVER($key, $default=null){
		if(array_key_exists($key, $this->server)){
			return $this->server[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the get value of the env array
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getENV($key, $default=null){
		if(array_key_exists($key, $this->env)){
			return $this->env[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the get value of the session array
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getSESSION($key, $default=null){
		if(array_key_exists($key, $this->session)){
			return $this->session[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the get value of the cookie array
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getCOOKIE($key, $default=null){
		if(array_key_exists($key, $this->cookie)){
			return $this->cookie[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the get value of the urlParams array
	 * @param string $key the array key that should be looked up
	 * @param string $default if the key is not found, return this value
	 * @return mixed the value of the stored array or the default
	 */
	public function getURLParams($key, $default=null){
		if(array_key_exists($key, $this->urlParams)){
			return $this->urlParams[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the request method
	 * @return string request method of the server array
	 */
	public function getMethod(){
		return $this->getSERVER('REQUEST_METHOD');
	}


	/**
	 * Sets a session variable
	 * @param string $key the key of the session variable
	 * @param string $value the value of the session variable
	 */
	public function setSESSION($key, $value){
		$this->session[$key] = $value;
	}

}
