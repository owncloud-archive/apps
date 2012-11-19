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

/**
 * This is used to wrap the owncloud static api calls into an object to make the
 * code better abstractable for use in the dependency injection container
 *
 * Extend this to your needs
 */
class API {

	private $appName;

	/**
	 * @param string $appName: the name of your application
	 */
	public function __construct($appName){
		$this->appName = $appName;
	}


	/**
	 * @return the name of your application
	 */
	public function getAppName(){
		return $this->appName;
	}


	/**
	 * @return: the user id of the current user
	 */
	public function getUserId(){
		return \OCP\USER::getUser();
	}


	/**
	 * Sets the current navigation entry to the currently running app
	 */
	public function activateNavigationEntry(){
		\OCP\App::setActiveNavigationEntry($this->appName);
	}


	/**
	 * Adds a new javascript file
	 * @param string $scriptName: the name of the javascript in js/ 
	 *                            without the suffix
	 */
	public function addScript($scriptName){
		\OCP\Util::addScript($this->appName, $scriptName);
	}


	/**
	 * Looks up a systemwide defined value
	 * @param string $key: the key of the value, under which it was saved
	 * @return the saved value
	 */
	public function getSystemValue($key){
		return \OCP\Config::getSystemValue($key, '');
	}
	

	/**
	 * Sets a new systemwide value
	 * @param string $key: the key of the value, under which will be saved
	 * @param $value: the value that should be stored
	 */
	public function setSystemValue($key, $value){
		return \OCP\Config::setSystemValue($key, $value);
	}
	

}