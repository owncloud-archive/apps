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

	/**
	 * @param array $get the $_GET array
	 * @param array $post the $_POST array
	 * @param array $files the $_FILES array
	 */
	public function __construct(array $get=array(), array $post=array(), 
								array $files=array()) {
		$this->get = $get;
		$this->post = $post;
		$this->files = $files;
	}


	/**
	 * Returns the merged GET and POST array
	 * @return array the merged array
	 */
	public function getGETAndPOST(){
		return array_merge($this->get, $this->post);
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

}
