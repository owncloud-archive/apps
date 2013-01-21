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


namespace OCA\AppFramework\Utility;

use OCA\AppFramework\Http\Response as Response;


abstract class ControllerTestUtility extends \PHPUnit_Framework_TestCase {


	/**
	 * Boilerplate function for getting an API Mock class
	 * @param string $apiClass: the class inclusive namespace of the api that we
	 *                          want to use
	 * @param array $constructor: constructor parameters of the api class
	 */
	protected function getAPIMock($apiClass='OCA\AppFramework\Core\API', 
									array $constructor=array('appname')){
		$methods = get_class_methods($apiClass);
		return $this->getMock($apiClass, $methods, $constructor);
	}


	/**
	 * Checks if a controllermethod has the expected annotations
	 * @param Controller/string $controller: name or instance of the controller
	 * @param array $expected: an array containing the expected annotations
	 * @param array $valid: if you define your own annotations, pass them here
	 */
	protected function assertAnnotations($controller, $method, array $expected,
										array $valid=array()){
		$standard = array(
			'Ajax', 
			'CSRFExemption', 
			'IsAdminExemption', 
			'IsSubAdminExemption',
			'IsLoggedInExemption'
		);

		$possible = array_merge($standard, $valid);

		// check if expected annotations are valid
		foreach($expected as $annotation){
			$this->assertTrue(in_array($annotation, $possible));
		}

		$reader = new MethodAnnotationReader($controller, $method);
		foreach($expected as $annotation){
			$this->assertTrue($reader->hasAnnotation($annotation));
		}
	}


	/**
	 * @brief Shortcut for testing expected headers of a response
	 * @param array $expected: an array with the expected headers
	 * @param Response $response: the response which we want to test for headers
	 */
	protected function assertHeaders(array $expected=array(), Response $response){
		$headers = $reponse->getHeaders();
		foreach($expected as $header){
			$this->assertTrue(in_array($header, $headers));
		}
	}
	

}