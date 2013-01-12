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


require_once \OC_App::getAppPath('apptemplate_advanced') . '/appinfo/bootstrap.php';


/**
 * Shortcut for calling a controller method and printing the result
 * @param string $controllerName: the name of the controller under which it is
 *                                stored in the DI container
 * @param string $methodName: the method that you want to call
 * @param array $urlParams: an array with variables extracted from the routes
 * @param Pimple $container: an instance of a pimple container. if not passed, a
 *                           new one will be instantiated. This can be used to
 *                           set different security values prehand or simply
 *                           swap or overwrite objects in the container.
 */
function callController($controllerName, $methodName, $urlParams, $container=null){
	
	// assume a normal request and disable admin and csrf checks. To specifically
	// enable them, pass a container with changed security object
	if($container === null){
		$container = createDIContainer();
	}

	// call the controller
	$controller = $container[$controllerName];

	// run security checks other annotation specific stuff
	handleAnnotations($controller, $methodName, $container);

	// render page
    $response = $controller->$methodName($urlParams);
	echo $response->render();
}


/**
 * Runs the security checks and exits on error
 * @param Controller $controller: an instance of the controller to be checked
 * @param string $methodName: the name of the controller method that will be called
 * @param Pimple $container: an instance of the container for the security object
 */
function handleAnnotations($controller, $methodName, $container){
	// get annotations from comments
	$annotationReader = new MethodAnnotationReader($controller, $methodName);
	
	// this will set the current navigation entry of the app, use this only
	// for normal HTML requests and not for AJAX requests
	if(!$annotationReader->hasAnnotation('Ajax')){
		$container['API']->activateNavigationEntry();
	}

	// security checks
	$security = $container['Security'];
	if($annotationReader->hasAnnotation('CSRFExemption')){
		$security->setCSRFCheck(false);
	}

	if($annotationReader->hasAnnotation('IsAdminExemption')){
		$security->setIsAdminCheck(false);	
	}

	if($annotationReader->hasAnnotation('AppEnabledExemption')){
		$security->setAppEnabledCheck(false);	
	}

	if($annotationReader->hasAnnotation('IsLoggedInExemption')){
		$security->setLoggedInCheck(false);
	}

	if($annotationReader->hasAnnotation('IsSubAdminExemption')){
		$security->setIsSubAdminCheck(false);
	}

	$security->runChecks();

}



/*************************
 * Define your routes here
 ************************/

/**
 * Normal Routes
 */
$this->create('apptemplate_advanced_index', '/')->action(
	function($params){
		callController('ItemController', 'index', $params);
	}
);

$this->create('apptemplate_advanced_index_redirect', '/redirect')->action(
	function($params){
		callController('ItemController', 'redirectToIndex', $params);
	}
);

/**
 * Ajax Routes
 */
$this->create('apptemplate_advanced_ajax_setsystemvalue', '/setsystemvalue')->post()->action(
	function($params){
		callController('ItemController', 'setSystemValue', $params);
	}
);
