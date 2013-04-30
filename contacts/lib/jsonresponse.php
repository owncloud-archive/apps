<?php
/**
 * @author Thomas Tanghus, Bart Visscher
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;
use OCA\AppFramework\Http\JSONResponse as OriginalResponse;


/**
 * A renderer for JSON calls
 */
class JSONResponse extends OriginalResponse {

	public function __construct($params = array()) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' request: '.print_r($request, true), \OCP\Util::DEBUG);
		parent::__construct();
		$this->data['data'] = $params;
	}

	/**
	 * in case we want to render an error message, also logs into the owncloud log
	 * @param string $message the error message
	 */
	public function setErrorMessage($message){
		$this->error = true;
		$this->data['data']['message'] = $message;
		$this->data['status'] = 'error';
	}

	function bailOut($msg, $tracelevel = 1, $debuglevel = \OCP\Util::ERROR) {
		$this->setErrorMessage($msg);
		$this->debug($msg, $tracelevel, $debuglevel);
	}

	function debug($msg, $tracelevel = 0, $debuglevel = \OCP\Util::DEBUG) {
		if(!is_numeric($tracelevel)) {
			return;
		}

		if(PHP_VERSION >= "5.4") {
			$call = debug_backtrace(false, $tracelevel + 1);
		} else {
			$call = debug_backtrace(false);
		}

		$call = $call[$tracelevel];
		if($debuglevel !== false) {
			\OCP\Util::writeLog('contacts',
				$call['file'].'. Line: '.$call['line'].': '.$msg,
				$debuglevel);
		}
	}

}