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


class SecurityMiddleware extends Middleware {

	private $security;
	private $api;

	/**
	 * @param API $api: an instance of the api
	 * @param Security $security: an instance of the security check object
	 */
	public function __construct(API $api, Security $security){
		$this->api = $api;
		$this->security = $security;
	}


	/**
	 * @brief this runs all the security checks before a method call. The
	 * security checks are determined by inspecting the controller method
	 * annotations
	 */
	public function beforeController($controller, $methodName){

		// get annotations from comments
		$annotationReader = new MethodAnnotationReader($controller, $methodName);

		// this will set the current navigation entry of the app, use this only
		// for normal HTML requests and not for AJAX requests
		if(!$annotationReader->hasAnnotation('Ajax')){
			$this->api->activateNavigationEntry();
		}

		// security checks
		if($annotationReader->hasAnnotation('CSRFExemption')){
			$this->security->setCSRFCheck(false);
		}

		if($annotationReader->hasAnnotation('IsAdminExemption')){
			$this->security->setIsAdminCheck(false);
		}

		if($annotationReader->hasAnnotation('AppEnabledExemption')){
			$this->security->setAppEnabledCheck(false);
		}

		if($annotationReader->hasAnnotation('IsLoggedInExemption')){
			$this->security->setLoggedInCheck(false);
		}

		if($annotationReader->hasAnnotation('IsSubAdminExemption')){
			$this->security->setIsSubAdminCheck(false);
		}

		$this->security->runChecks();

	}

}