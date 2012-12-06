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

namespace OCA\AppTemplateAdvanced;


/**
 * This class is a simple object with getters and setters and allows
 * finegrained controll over security checks
 * All security checks are enabled by default
 */
class Security {

	private $csrfCheck;
	private $loggedInCheck;
	private $appEnabledCheck;
	private $isAdminCheck;
	private $isSubAdminCheck;
	private $appName;

	/**
	 * @param string $appName: the name of the app
	 */
	public function __construct($appName){
		$this->appName = $appName;

		// enable all checks by default
		$this->csrfCheck = true;
		$this->loggedInCheck = true;
		$this->appEnabledCheck = true;
		$this->isAdminCheck = true;
				$this->isSubAdminCheck = true;
	}


	public function setCSRFCheck($csrfCheck){
		$this->csrfCheck = $csrfCheck;
	}

	public function setLoggedInCheck($loggedInCheck){
		$this->loggedInCheck = $loggedInCheck;
	}

	public function setAppEnabledCheck($appEnabledCheck){
		$this->appEnabledCheck = $appEnabledCheck;
	}

	public function setIsAdminCheck($isAdminCheck){
		$this->isAdminCheck = $isAdminCheck;
	}


	public function setIsSubAdminCheck($isSubAdminCheck){
		$this->isSubAdminCheck = $isSubAdminCheck;
	}


	/**
	 * Runs all security checks
	 */
	public function runChecks() {

		if($this->csrfCheck){
			\OCP\JSON::callCheck();
		}

		if($this->loggedInCheck){
			\OCP\JSON::checkLoggedIn();
		}

		if($this->appEnabledCheck){
			\OCP\JSON::checkAppEnabled($this->appName);
		}

		if($this->isAdminCheck){
			\OCP\JSON::checkAdminUser();
		}

		if($this->isSubAdminCheck){
			\OC_JSON::checkSubAdminUser();
		}

	}


}