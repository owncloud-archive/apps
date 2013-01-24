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
 * Baseclass for responses. Also used to just send headers
 */
class Response {

	private $headers;

	public function __construct(){
		$this->headers = array();
	}

	/**
	 * Adds a new header to the response that will be called before the render
	 * function
	 * @param string header the string that will be used in the header() function
	 */
	public function addHeader($header){
		array_push($this->headers, $header);
	}


	/**
	 * By default renders no output
	 * @return null
	 */
	public function render(){
		return null;
	}


	/**
	 * Returns the set headers
	 * @return array the headers
	 */
	public function getHeaders(){
		return $this->headers;
	}

}
