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
 * Should you find yourself in need for more methods, simply inherit from this
 * class and add your methods
 */
class API {

	private $appName;

	/**
	 * constructor
	 * @param string $appName the name of your application
	 */
	public function __construct($appName){
		$this->appName = $appName;
	}


	/**
	 * used to return the appname of the set application
	 * @return string the name of your application
	 */
	public function getAppName(){
		return $this->appName;
	}


	/**
	 * Gets the userid of the current user
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
	 * @param string $appName the name of the app, defaults to the current one
	 */
	public function addScript($scriptName, $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}
		\OCP\Util::addScript($appName, $scriptName);
	}


	/**
	 * Adds a new css file
	 * @param string $styleName the name of the css file in css/without the suffix
	 * @param string $appName the name of the app, defaults to the current one
	 */
	public function addStyle($styleName, $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}
		\OCP\Util::addStyle($appName, $styleName);
	}


	/**
	 * shorthand for addScript for files in the 3rdparty directory
	 * @param string $name the name of the file without the suffix
	 */
	public function add3rdPartyScript($name){
		\OCP\Util::addScript($this->appName . '/3rdparty', $name);
	}


	/**
	 * shorthand for addStyle for files in the 3rdparty directory
	 * @param string $name the name of the file without the suffix
	 */
	public function add3rdPartyStyle($name){
		\OCP\Util::addStyle($this->appName . '/3rdparty', $name);
	}

	/**
	 * Looks up a systemwide defined value from the config/config.php
	 * @param string $key the key of the value, under which it was saved
	 * @return string the saved value
	 */
	public function getSystemValue($key){
		return \OCP\Config::getSystemValue($key, '');
	}


	/**
	 * Writes a new systemwide value into the config/config.php
	 * @param string $key the key of the value, under which will be saved
	 * @param string $value the value that should be stored
	 */
	public function setSystemValue($key, $value){
		return \OCP\Config::setSystemValue($key, $value);
	}

	/**
	 * Looks up an appwide defined value
	 * @param string $key the key of the value, under which it was saved
	 * @return string the saved value
	 */
	public function getAppValue($key, $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}
		return \OCP\Config::getAppValue($appName, $key, '');
	}


	/**
	 * Writes a new appwide value
	 * @param string $key the key of the value, under which will be saved
	 * @param string $value the value that should be stored
	 */
	public function setAppValue($key, $value, $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}
		return \OCP\Config::setAppValue($appName, $key, $value);
	}


	/**
	 * Shortcut for setting a user defined value
	 * @param string $key the key under which the value is being stored
	 * @param string $value the value that you want to store
	 * @param string $userId the userId of the user that we want to store the value under, defaults to the current one
	 */
	public function setUserValue($key, $value, $userId=null){
		if($userId === null){
			$userId = $this->getUserId();
		}
		\OCP\Config::setUserValue($userId, $this->appName, $key, $value);
	}


	/**
	 * Shortcut for getting a user defined value
	 * @param string $key the key under which the value is being stored
	 * @param string $userId the userId of the user that we want to store the value under, defaults to the current one
	 */
	public function getUserValue($key, $userId=null){
		if($userId === null){
			$userId = $this->getUserId();
		}
		return \OCP\Config::getUserValue($userId, $this->appName, $key);
	}


	/**
	 * Returns the translation object
	 * @return \OC_L10N the translation object
	 */
	public function getTrans(){
		return \OC_L10N::get($this->appName);
	}


	/**
	 * Used to abstract the owncloud database access away
	 * @param string $sql the sql query with ? placeholder for params
	 * @param int $limit the maximum number of rows
	 * @param int $offset from which row we want to start
	 * @return \OCP\DB a query object
	 */
	public function prepareQuery($sql, $limit=null, $offset=null){
		return \OCP\DB::prepare($sql, $limit, $offset);
	}


	/**
	 * Used to get the id of the just inserted element
	 * @param string $tableName the name of the table where we inserted the item
	 * @return int the id of the inserted element
	 */
	public function getInsertId($tableName){
		return \OCP\DB::insertid($tableName);
	}


	/**
	 * Returns the URL for a route
	 * @param string $routeName the name of the route
	 * @param array $arguments an array with arguments which will be filled into the url
	 * @return string the url
	 */
	public function linkToRoute($routeName, $arguments=array()){
		return \OC_Helper::linkToRoute($routeName, $arguments);
	}


	/**
	 * Makes an URL absolute
	 * @param string $url the url
	 * @return string the absolute url
	 */
	public function getAbsoluteURL($url){
		return \OC_Helper::makeURLAbsolute($url);
	}


	/**
	 * links to a file
	 * @param string $file the name of the file
	 * @param string $appName the name of the app, defaults to the current one
	 * @deprecated replaced with linkToRoute()
	 * @return string the url
	 */
	public function linkToAbsolute($file, $appName=null){
		if($appName === null){
			$appName = $this->appName;
		}
		return \OC_Helper::linkToAbsolute($appName, $file);
	}


	/**
	 * Checks if the current user is logged in
	 * @return bool true if logged in
	 */
	public function isLoggedIn(){
		return \OC_User::isLoggedIn();
	}


	/**
	 * Checks if a user is an admin
	 * @param string $userId the id of the user
	 * @return bool true if admin
	 */
	public function isAdminUser($userId){
		return \OC_User::isAdminUser($userId);
	}


	/**
	 * Checks if a user is an subadmin
	 * @param string $userId the id of the user
	 * @return bool true if subadmin
	 */
	public function isSubAdminUser($userId){
		return \OC_SubAdmin::isSubAdmin($userId);
	}


	/**
	 * Checks if the CSRF check was correct
	 * @return bool true if CSRF check passed
	 */
	public function passesCSRFCheck(){
		return \OC_Util::isCallRegistered();
	}


	/**
	 * Checks if an app is enabled
	 * @param string $appName the name of an app
	 * @return bool true if app is enabled
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
	 * @return \OCP\Template a new template
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
	 * turns an owncloud path into a path on the filesystem
	 * @param string path the path to the file on the oc filesystem
	 * @return string the filepath in the filesystem
	 */
	public function getLocalFilePath($path){
		return \OC_Filesystem::getLocalFile($path);
	}


	/**
	 * used to return and open a new eventsource
	 * @return \OC_EventSource a new open EventSource class
	 */
	public function openEventSource(){
		return new \OC_EventSource();
	}


}