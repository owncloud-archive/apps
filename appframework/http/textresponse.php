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
 * Prompts the user to download the a textfile
 */
class TextResponse extends Response {

	private $content;

	/**
	 * @brief Creates a response that just outputs text
	 * @param string $content: the content that should be written into the file
	 */
	public function __construct($content){
		parent::__construct();
		$this->content = $content;
	}


	/**
	 * Simply sets the headers and returns the file contents
	 * @return the file contents
	 */
	public function render(){
		return $this->content;
	}


}
