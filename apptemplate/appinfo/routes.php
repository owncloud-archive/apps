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
 * @param bool $disableAdminCheck: disables the check for adminuser rights
 * @param bool $isAjax: if the request is an ajax request
 */
function callController($controllerName, $methodName, $urlParams, $disableAdminCheck=true,
						$isAjax=false){
	$container = createDIContainer();
	
	// run security checks
	$security = $container['Security'];
	runSecurityChecks($security, $isAjax, $disableAdminCheck);

	// call the controller and render the page
	$controller = $container[$controllerName];
	$response = $controller->$methodName($urlParams);
	echo $response->render();
}


/**
 * Shortcut for calling an ajax controller method and printing the result
 * @param string $controllerName: the name of the controller under which it is
 *                                stored in the DI container
 * @param string $methodName: the method that you want to call
 * @param array $urlParams: an array with variables extracted from the routes
 * @param bool $disableAdminCheck: disables the check for adminuser rights
 */
function callAjaxController($controllerName, $methodName, $urlParams, $disableAdminCheck=true){
	callController($controllerName, $methodName, $urlParams, $disableAdminCheck, true);
}


/**
 * Runs the security checks and exits on error
 * @param Security $security: the security object
 * @param bool $isAjax: if true, the ajax checks will be run, otherwise the normal
 *                      checks
 * @param bool $disableAdminCheck: disables the check for adminuser rights
 */
function runSecurityChecks($security, $isAjax=false, $disableAdminCheck=true){
	if($disableAdminCheck){
		$security->setIsAdminCheck(false);	
	}

	if($isAjax){
		$security->runAJAXChecks();
	} else {
		$security->runChecks();
	}
}

/*************************
 * Define your routes here
 ************************/

/**
 * Normal Routes
 */
$this->create('apptemplate_index', '/')->action(
	function($params){		
		callController('IndexController', 'index', $params);
	}
);

/**
 * Ajax Routes
 */
$this->create('apptemplate_ajax_setsystemvalue', '/setsystemvalue')->post()->action(
	function($params){		
		callAjaxController('AjaxController', 'setSystemValue', $params);
	}
);
