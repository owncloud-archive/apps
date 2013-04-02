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
* Base class for user and storage service
*
*/
abstract class Service
{
	abstract public function run();

	protected function getInputData() {
		if($this->inputData == null) {
			$this->inputData = new InputData();
		}
		return $this->inputData;
	}

	protected $urlParser;
	protected $inputData;
}
