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
class RedirectResponse extends Response {

	private $redirectURL;

	/**
	 * Creates a response that redirects to a url
	 * @param string $redirectURL: the url to redirect to
	 */
	public function __construct($redirectURL){
		parent::__construct();
		$this->redirectURL = $redirectURL;
		$this->addHeader('Location: ' . $redirectURL);
	}


	/**
	 * @return: the url to redirect
	 */
	public function getRedirectURL(){
		return $this->redirectURL;
	}
	

}
