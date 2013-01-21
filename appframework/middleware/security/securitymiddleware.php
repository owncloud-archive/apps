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


namespace OCA\AppFramework\Middleware\Security;

use OCA\AppFramework\Controller\Controller as Controller;
use OCA\AppFramework\Http\Response as Response;
use OCA\AppFramework\Http\JSONResponse as JSONResponse;
use OCA\AppFramework\Http\RedirectResponse as RedirectResponse;
use OCA\AppFramework\Utility\MethodAnnotationReader as MethodAnnotationReader;
use OCA\AppFramework\Middleware\Middleware as Middleware;
use OCA\AppFramework\Core\API as API;


/**
 * Used to do all the authentication and checking stuff for a controller method
 * It reads out the annotations of a controller method and checks which if
 * security things should be checked and also handles errors in case a security
 * check fails
 */
class SecurityMiddleware extends Middleware {

	private $security;
	private $api;

	/**
	 * @param API $api: an instance of the api
	 */
	public function __construct(API $api){
		$this->api = $api;
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
			$ajax = false;
		} else {
			$ajax = true;
		}

		$exceptionMessage = null;

		// security checks
		if(!$annotationReader->hasAnnotation('IsLoggedInExemption')){
			if(!$this->api->isLoggedIn()){
				$exceptionMessage = 'Current user is not logged in';
			}
		}

		if(!$annotationReader->hasAnnotation('CSRFExemption')){
			if(!$this->api->passesCSRFCheck()){
				$exceptionMessage = 'CSRF check failed';
			}
		}

		if(!$annotationReader->hasAnnotation('IsAdminExemption')){
			if(!$this->api->isAdminUser($this->api->getUserId())){
								$exceptionMessage = 'Logged in user must be an admin';
			}
		}

		if(!$annotationReader->hasAnnotation('IsSubAdminExemption')){
			if(!$this->api->isSubAdminUser($this->api->getUserId())){
								$exceptionMessage = 'Logged in user must be a subadmin';
			}
		}

		if($exceptionMessage !== null){
			throw new SecurityException($exceptionMessage, $ajax);
		}

	}


	/**
	 * If an SecurityException is being caught, ajax requests return a JSON error
	 * response and non ajax requests redirect to the index
	 */
	public function afterException($controller, $methodName, \Exception $exception){
		if($exception instanceof SecurityException){

			if($exception->isAjax()){
				
				// ajax responses get an ajax error message
				$response = new JSONResponse();
				$response->setErrorMessage($exception->getMessage(), 
						get_class($controller) . '->' . $methodName);
				$this->api->log($exception->getMessage());
				return $response;

			} else {

				// normal error messages link to the index page
				//$url = $this->api->linkToRoute('index')
				$url = $this->api->linkToAbsolute('index.php', ''); // TODO: replace with link to route
				$this->api->log($exception->getMessage());
				return new RedirectResponse($url);
			}
		} else  {
			return null;
		}
	}

}