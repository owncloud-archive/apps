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
* Class for reading json data input from mozilla sync service client
*/
class InputData implements \arrayaccess
{
	const NO_VALUE = 0;

	/**
	* Constructor, the default parameter should be used in app.
	* Changing json content is used for testing.
	*
	* @param string $file
	*/
	public function __construct($jsonInput = self::NO_VALUE) {
		$this->parseValidFlag = true;

		$input = '';

		if($jsonInput === self::NO_VALUE) {
			$input = file_get_contents('php://input');
		}
		else{
			$input = $jsonInput;
		}

		$parseResult = json_decode($input, true);

		if($parseResult == null) {
			$this->parseValidFlag = false;
		}
		else{
			$this->inputArray = $parseResult;
		}
	}

	/**
	* Return true if parsed json data are correct
	* otherwise false
	*
	* @return bool
	*/
	public function isValid() {
		return $this->parseValidFlag;
	}

	/**
	* @brief Check if there is value name in input data
	*
	* @param string $valueName
	* @return boolean
	*/
	public function hasValue($valueName) {
		return array_key_exists($valueName, $this->inputArray) && $this->inputArray[$valueName] != null;
	}

	/**
	* @brief Check if there are values in input data
	*
	* @param array $valuesNameArray
	* @return boolean
	*/
	public function hasValues($valuesNameArray) {
		foreach ($valuesNameArray as $valueName) {
			if(!$this->hasValue($valueName)) {
				return false;
			}

		}
		return true;
	}

	/**
	* @brief Return value by key
	*
	* @param string $valueName
	*/
	public function getValue($valueName) {
		return $this->inputArray[$valueName];
	}

	/**
	* @brief Return input data as array
	*/
	public function getInputArray() {
		return $this->inputArray;
	}

	/**
	* arrayaccess interface set function
	* No operation, input data is read only array
	*/
	public function offsetSet($offset, $value) {}

	/**
	* arrayaccess interface unset function
	* No operation, input data is read only array
	*/
	public function offsetUnset($offset) {}

	/**
	* arrayaccess interface exists function
	*/
	public function offsetExists($offset) {
		return $this->hasValue($offset);
	}

	/**
	* arrayaccess interface get function
	*/
	public function offsetGet($offset) {
		return $this->getValue($offset);
	}

	/**
	* Flag for checking parsing result
	*/
	private $parseValidFlag;

	private $inputArray;
}