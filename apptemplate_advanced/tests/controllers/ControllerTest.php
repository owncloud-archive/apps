<?php

/**
* ownCloud - App Template plugin
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


// get abspath of file directory
$path = realpath( dirname( __FILE__ ) ) . '/';

require_once($path . "../../lib/methodannotationreader.php");


abstract class ControllerTest extends \PHPUnit_Framework_TestCase {


	/**
	 * Returns a minimal API mock
	 * @param array $methods: additional methods for the mock
	 * @return minimal API mock object
	 */
	protected function getAPIMock($methods=array()){
		array_push($methods, 'getAppName');
		$api = $this->getMock('API', $methods);
		$api->expects($this->any())
					->method('getAppName')
					->will($this->returnValue('apptemplate_advanced'));
		return $api;
	}


	/**
	 * Asserts annotations of a controller
	 * @param Controller $controller: the controller instance
	 * @param string $methodName: the name of the method to inspect
	 * @param array $annotations: an array with expected annotations
	 */
	protected function assertAnnotations($controller, $methodName, 
										$annotations=array()){

		$reader = new MethodAnnotationReader($controller, $methodName);

		$possibleAnnotations = array(
			'Ajax', 
			'CSRFExemption', 
			'IsAdminExemption', 
			'IsSubAdminExemption',
			'IsLoggedInExemption'
		);

		// check for valid annotations parameters
		foreach($annotations as $annotation){
			$isPossible = in_array($annotation, $possibleAnnotations);
			if(!$isPossible){
				throw new \Exception('Annotation "' . $annotation . '" does not exist');
			}
			$this->assertTrue($isPossible);
		}

		// check if annotations exist in the controller
		foreach($possibleAnnotations as $possible){
			if(in_array($possible, $annotations)){
				if(!$reader->hasAnnotation($possible)){
					throw new \Exception('Annotation "' . $possible . '" does not appear in the controllermethod ' . $methodName);
				}
			} else {
				if($reader->hasAnnotation($possible)){
					throw new \Exception('Unexcpected annotation "' . $possible . '" in the controllermethod ' . $methodName);
				}
			}
		}
		
	}

}