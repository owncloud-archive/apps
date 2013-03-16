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


namespace OCA\AppFramework;


/**
 * Entry point for every request in your app. You can consider this as your 
 * public static void main() method
 * 
 * Handles all the dependency injection, controllers and output flow
 */
class App {


	/**
	 * Shortcut for calling a controller method and printing the result
	 * @param string $controllerName the name of the controller under which it is
	 *                               stored in the DI container
	 * @param string $methodName the method that you want to call
	 * @param array $urlParams an array with variables extracted from the routes
	 * @param Pimple $container an instance of a pimple container. 
	 */
	public static function main($controllerName, $methodName, array $urlParams, \Pimple $container){

		$container['urlParams'] = $urlParams;
		$controller = $container[$controllerName];
		
		// initialize the dispatcher and run all the middleware before the controller
		$middlewareDispatcher = $container['MiddlewareDispatcher'];

		// create response and run middleware that receives the response
		// if an exception appears, the middleware is checked to handle the exception
		// and to create a response. If no response is created, it is assumed that
		// theres no middleware who can handle it and the error is thrown again
		try {
			$middlewareDispatcher->beforeController($controller, $methodName);
			$response = $controller->$methodName();
		} catch(\Exception $exception){
			$response = $middlewareDispatcher->afterException($controller, $methodName, $exception);
			if($response === null){
				throw $exception;
			}
		}

		// this can be used to modify or exchange a response object
		$response = $middlewareDispatcher->afterController($controller, $methodName, $response);

		// get the output which should be printed and run the after output middleware
		// to modify the response
		$output = $response->render();
		$output = $middlewareDispatcher->beforeOutput($controller, $methodName, $output);

		// output headers and echo content
		foreach($response->getHeaders() as $header){
			header($header);
		}
		
		if($output !== null){
			echo $output;	
		}
		
	}


}