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


namespace OCA\AppFramework\Core;


/**
 * This is used to wrap the owncloud static api calls into an object to make the
 * code better abstractable for use in the dependency injection container
 *
 * Extend this to your needs
 */
class API {

	private $appName;

	/**
	 * @param string $appName the name of your application
	 */
	public function __construct($appName){
		$this->appName = $appName;
	}


	/**
	 * @return string the name of your application
	 */
	public function getAppName(){
		return $this->appName;
	}


	/**
	 * @return string the user id of the current user
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
	 * @param string $scriptName the name of the javascript in js/ without the suffix
	 */
	public function addScript($scriptName){
		\OCP\Util::addScript($this->appName, $scriptName);
	}


	/**
	 * Adds a new css file
	 * @param string $styleName the name of the css file in css/
	 * without the suffix
	 */
	public function addStyle($styleName){
		\OCP\Util::addStyle($this->appName, $styleName);
	}


	/**
	 * @brief shorthand for addScript for files in the 3rdparty directory
	 * @param string $name the name of the file without the suffix
	 */
	public function add3rdPartyScript($name){
		\OCP\Util::addScript($this->appName . '/3rdparty', $name);
	}


	/**
	 * @brief shorthand for addStyle for files in the 3rdparty directory
	 * @param string $name the name of the file without the suffix
	 */
	public function add3rdPartyStyle($name){
		\OCP\Util::addStyle($this->appName . '/3rdparty', $name);
	}

	/**
	 * Looks up a systemwide defined value
	 * @param string $key the key of the value, under which it was saved
	 * @return the saved value
	 */
	public function getSystemValue($key){
		return \OCP\Config::getSystemValue($key, '');
	}


	/**
	 * Sets a new systemwide value
	 * @param string $key the key of the value, under which will be saved
	 * @param $value the value that should be stored
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
	 * @param string $sql the sql query with ? placeholder for params
	 * @param int $limit the maximum number of rows
	 * @param int $offset from which row we want to start
	 * @return a query object
	 */
	public function prepareQuery($sql, $limit=null, $offset=null){
		return \OCP\DB::prepare($sql, $limit, $offset);
	}


	/**
	 * Used to get the id of the just inserted element
	 * @param string $tableName the name of the table where we inserted the item
	 * @return the id of the inserted element
	 */
	public function getInsertId($tableName){
		return \OCP\DB::insertid($tableName);
	}


	/**
	 * Returns the URL for a route
	 * @param string $routeName the name of the route
	 * @param array $arguments an array with arguments which will be filled into the url
	 * @return the url
	 */
	public function linkToRoute($routeName, $arguments=array()){
		return \OC_Helper::linkToRoute($routeName, $arguments);
	}


	/**
	 * @brief links to a file
	 * @deprecated
	 */
	public function linkToAbsolute($file, $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}
		return \OC_Helper::linkToAbsolute($appName, $file);
	}


	/**
	 * Checks if the current user is logged in
	 * @return bool
	 */
	public function isLoggedIn(){
		return \OC_User::isLoggedIn();
	}


	/**
	 * Checks if a user is an admin
	 * @param string $userId the id of the user
	 * @return bool
	 */
	public function isAdminUser($userId){
		return \OC_User::isAdminUser($userId);
	}


	/**
	 * Checks if a user is an subadmin
	 * @param string $userId the id of the user
	 * @return bool
	 */
	public function isSubAdminUser($userId){
		return \OC_SubAdmin::isSubAdmin($userId);
	}


	/**
	 * Checks if the CSRF check was correct
	 * @return bool
	 */
	public function passesCSRFCheck(){
		return \OC_Util::isCallRegistered();
	}


	/**
	 * Checks if an app is enabled
	 * @param string $appName the name of an app
	 * @return bool
	 */
	public function isAppEnabled($appName){
		\OC_App::isEnabled($appName);
	}


	/**
	 * Writes a function into the error log
	 * @param string $msg the error message to be logged
	 * @param int $level the error level
	 */
	public function log($msg, $level=null){
		if($level === null){
			$level = \OCP\Util::ERROR;
		}
		\OCP\Util::writeLog($this->appName, $msg, $level);
	}


	/**
	 * Returns a template
	 * @param string $templateName the name of the template
	 * @param string $renderAs how it should be rendered
	 * @param string $appName the name of the app
	 */
	public function getTemplate($templateName, $renderAs='user', $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}

		if($renderAs === 'blank'){
			return new \OCP\Template($appName, $templateName);
		} else {
			return new \OCP\Template($appName, $templateName, $renderAs);
		}
	}


	/**
	 * @param string path the path to the file on the oc filesystem
	 * @return the filepath in the filesystem
	 */
	public function getLocalFilePath($path){
		return \OC_Filesystem::getLocalFile($path);
	}


	/**
	 * @return returns a new open EventSource class
	 */
	public function openEventSource(){
		return new \OC_EventSource();
	}


}