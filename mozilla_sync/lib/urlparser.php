<?php

/**
* ownCloud
*
* @author Michal Jaskurzynski
* @copyright 2012 Michal Jaskurzynski mjaskurzynski@gmail.com
*
*/

namespace OCA_mozilla_sync;

/**
* Class for parsing Mozilla URL Semantics
*
* For example:
* 	<version>/<username>/<further instruction>
*
*/
class UrlParser {

	/**
	* Constructor, parse given url
	*
	* @param String $url ; Mozilla storage URL example /1.0/username/storage/history
	*/
	public function __construct($url) {

		// parser is valid at the begining
		$this->parseValidFlag = true;

		// remove from begin and end '/' characters
		$url = trim($url, '/');

		$urlArray = explode('/', $url);

		// there should be at least 2 arguments
		// version, username
		if( count($urlArray) < 2 ) {
			$this->parseValidFlag = false;
			return;
		}

		// version
		$this->version = array_shift($urlArray);
		if( ($this->version != '1.0') &&
			($this->version != '1.1') &&
			($this->version != '2.0') ) {
			$parseValidFlag = false;
			return;
		}

		// username
		$this->username = array_shift($urlArray);

		// commands
		$this->commandsArray = $urlArray;
	}

	/**
	* @brief Return true if parsed url is correct
	* otherwise false
	*
	* @return bool
	*/
	public function isValid() {
		return $this->parseValidFlag;
	}

	/**
	* @brief Return version
	*
	* @return string
	*/
	public function getVersion() {
		return $this->version;
	}

	/**
	* @brief Return username
	*
	* @return string
	*/
	public function getUserName() {
		return $this->username;
	}

	/**
	* @brief Return command by number, starting from 0
	*
	* @param integer $commandNumber
	*/
	public function getCommand($commandNumber) {

		$commandArray = explode('?', $this->commandsArray[$commandNumber]);

		return $commandArray[0];
	}

	/**
	* @brief Return modifiers array form given command
	*
	* Example:
	*   tabs?full=1&ids=1,2,3
	*
	* @param integer $commandNumber
	*/
	public function getCommandModifiers($commandNumber) {

		$resultArray = array();

		$commandArray = explode('?', $this->commandsArray[$commandNumber]);
		if(count($commandArray) != 2) {
			return $resultArray;
		}

		$modifiersArray = explode('&', $commandArray[1]);
		foreach($modifiersArray as $value) {
			$tmpArray = explode('=', $value);
			if(count($tmpArray) !=2 ) {
				continue;
			}

			$key = $tmpArray[0];

			//split argument list
			if(strpos($tmpArray[1], ',') == false) {
				$value = $tmpArray[1];
			}
			else{
				$value = explode(',', $tmpArray[1]);
			}

			$resultArray[$key] = $value;
		}

		return $resultArray;
	}

	/**
	* @brief Return command array
	*
	* @return array;
	*/
	public function getCommands() {
		return $this->commandsArray;
	}

	/**
	* @brief Return number of sub commands
	*
	* @return integer
	*/
	public function commandCount() {
		return count($this->commandsArray);
	}

	/**
	* @brief Check if command string match given pattern
	*
	* @param string $pattern
	* @return boolean
	*/
	public function commandMatch($pattern) {
		$commandString = implode('/', $this->commandsArray);
		return preg_match($pattern, $commandString);
	}

	/**
	* Flag for checking parsing result
	*/
	private $parseValidFlag;

	/**
	* Mozilla storage api version
	*/
	private $version;

	/**
	* User name hash
	*/
	private $username;

	/**
	* Further commands array
	*/
	private $commandsArray;
}
