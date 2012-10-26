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
* Class for writing output for mozilla sync service client
*
* It can be used to write simple output like:
*  - '0'
*  - '1'
*  - 'success'
*  Or can be used to write json formated output. In this case argument of function write should be an array.
*
* This class has ability to test output. In normal mode output is written to browser.
* When $outputFlag is set to ConstOutputBuffer, output is written to $outputBuffer.
*/
class OutputData
{
	const NewLineFormat         = 0;
	const LengthFormat          = 1;

	const ConstOutputNormal     = 0;
	const ConstOutputBuffer     = 1;

	static public $outputFlag   = self::ConstOutputNormal;
	static public $outputBuffer = '';

	/**
	* @brief Function for writing output
	*
	* Srray will be encoded to json format and written,
	* other type of arguments will be simple written to browser,
	*
	* @param any $output
	*/
	static public function write($output) {

		// write simple output
		if(gettype($output) != 'array') {
			self::writeOutput($output);
		}
		// write json encoded output
		else{
			if(OutputData::getOutputFormat() == self::NewLineFormat) {
				self::writeNewLineFormat($output);
			}
			else{
				self::writeLengthFormat($output);
			}
		}
	}

	/**
	* @brief Get output format
	*
	*  Two alternate output formats are available for multiple record GET requests.
	*  They are triggered by the presence of the appropriate format in the
	*  Accept header (with application/whoisi taking precedence):
	*
	*  - application/whoisi:     each record consists of a 32-bit integer,
	*                            defining the length of the record, followed by the json record for a WBO
	*  - application/newlines:   each record is a separate json object on its own line.
	*                            Newlines in the body of the json object are replaced by ‘u000a’
	*/
	static private function getOutputFormat() {
		if( isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/whoisi') ) {
			return self::LengthFormat;
		}
		return self::NewLineFormat;
	}

	static private function writeNewLineFormat($outputArray) {
		self::writeOutput(json_encode($outputArray));
		self::writeOutput("\n");
	}

	static private function writeLengthFormat($outputArray) {
		//TODO: application/whoisi ouput format
	}

	static private function writeOutput($outputString) {
		if(self::$outputFlag == self::ConstOutputNormal) {
			print $outputString;
		}
		else{
			self::$outputBuffer .= $outputString;
		}
	}
}