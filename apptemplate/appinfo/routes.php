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

namespace OCA\AppTemplate;


require_once \OC_App::getAppPath('apptemplate') . '/appinfo/bootstrap.php';

/**
 * Shortcut for calling a controller method and printing the result
 * @param string $controllerName: the name of the controller under which it is
 *                                stored in the DI container
 * @param string $methodName: the method that you want to call
 * @param array $urlParams: an array with variables extracted from the routes
 * @param bool $disableCSRF: disables the csrf check, defaults to false
 */
function callController($controllerName, $methodName, $urlParams, 
						$disableCSRF=false, $disableAdminCheck=true){
	$container = createDIContainer();
	
	// run security checks
	$security = $container['Security'];
	if($disableCSRF){
		$security->setCSRFCheck(false);	
	}
	if($disableAdminCheck){
		$security->setIsAdminCheck(false);	
	}

	$security->runChecks();

	// call the controller and render the page
	$controller = $container[$controllerName];
	$page = $controller->$methodName($urlParams);
	$page->printPage();
}


/*************************
 * Define your routes here
 ************************/

/**
 * Normal Routes
 */
$this->create(APP_NAME. '_index', '/')->action(
	function($params){		
		callController('IndexController', 'index', $params, true);
	}
);

/**
 * Ajax Routes
 */
$this->create(APP_NAME . '_ajax_setsystemvalue', '/setsystemvalue')->post()->action(
	function($params){		
		$container = createDIContainer();

		$security = $container['Security'];
		$security->runChecks();

		$controller = $container[$controllerName];
		$container->setsystemvalue($params);
	}
);