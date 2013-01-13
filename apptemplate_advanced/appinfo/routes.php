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
	$controller->setURLParams($urlParams);

	// initialize the dispatcher and run all the middleware before the controller
	$middlewareDispatcher = $container['MiddlewareDispatcher'];

	// create response and run middleware that receives the response
	// if an exception appears, the middleware is checked to handle the exception
	// and to create a response. If no response is created, it is assumed that
	// theres no middleware to handle it and the error is thrown again
	try {
		$middlewareDispatcher->beforeController($controllerName, $methodName, $container);
		$response = $controller->$methodName();
	} catch(Exception $exception){
		$response = $middlewareDispatcher->afterException($controllerName, $methodName, $container, $exception);
		if($response === null){
			throw $exception;
		}
	}

	// this can be used to modify or exchange a response object
	$reponse = $middlewareDispatcher->afterController($controllerName, $methodName, $container, $response);

	// get the output which should be printed and run the after output middleware
	// to modify the response
	$output = $response->render();
	$output = $middlewareDispatcher->beforeOutput($controllerName, $methodName, $container, $output);

	echo $output;
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

$this->create('apptemplate_advanced_index_param', '/test/{test}')->action(
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
