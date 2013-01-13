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


namespace OCA\AppFramework;

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
	 * Adds a new css file
	 * @param string $styleName: the name of the css file in css/
	 * without the suffix
	 */
	public function addStyle($styleName){
		\OCP\Util::addStyle($this->appName, $styleName);
	}


	/**
	 * @brief shorthand for addScript for files in the 3rdparty directory
	 * @param string $name: the name of the file without the suffix
	 */
	public function add3rdPartyScript($name){
		\OCP\Util::addScript($this->appName . '/3rdparty', $name);
	}


	/**
	 * @brief shorthand for addStyle for files in the 3rdparty directory
	 * @param string $name: the name of the file without the suffix
	 */
	public function add3rdPartyStyle($name){
		\OCP\Util::addStyle($this->appName . '/3rdparty', $name);
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


	/**
	 * Shortcut for setting a user defined value
	 * @param $key the key under which the value is being stored
	 * @param $value the value that you want to store
	 */
	public function setUserValue($key, $value, $user=null){
		if($user === null){
				$user = $this->getUserId();
		}
		\OCP\Config::setUserValue($user, $this->appName, $key, $value);
	}


	/**
	 * Shortcut for getting a user defined value
	 * @param $key the key under which the value is being stored
	 */
	public function getUserValue($key, $user=null){
		if($user === null){
				$user = $this->getUserId();
		}
		return \OCP\Config::getUserValue($user, $this->appName, $key);
	}


	/**
	 * Returns the translation object
	 * @return the translation object
	 */
	public function getTrans(){
		return \OC_L10N::get($this->appName);
	}


	/**
	 * Used to abstract the owncloud database access away
	 * @param string $sql: the sql query with ? placeholder for params
	 * @param int $limit: the maximum number of rows
	 * @param int $offset: from which row we want to start
	 * @return a query object
	 */
	public function prepareQuery($sql, $limit=null, $offset=null){
		return \OCP\DB::prepare($sql, $limit, $offset);
	}


	/**
	 * Used to get the id of the just inserted element
	 * @param string $tableName: the name of the table where we inserted the item
	 * @return the id of the inserted element
	 */
	public function getInsertId($tableName){
		return \OCP\DB::insertid($tableName);
	}

	/**
	 * Returns the URL for a route
	 * @return the url
	 */
	public function linkToRoute($routeName){
		return \OC_Helper::linkToRoute($routeName);
	}

}