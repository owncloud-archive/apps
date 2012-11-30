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
		$container['Security']->setIsAdminCheck(false);
		$container['Security']->setCSRFCheck(false);
	}

	runSecurityChecks($container['Security']);

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
 * @param Pimple $container: an instance of a pimple container. if not passed, a
 *                           new one will be instantiated. This can be used to
 *                           set different security values prehand or simply
 *                           swap or overwrite objects in the container.
 */
function callAjaxController($controllerName, $methodName, $urlParams, $container=null){

	// ajax requests come with csrf checks enabled. If you pass your own container
	// dont forget to enable the csrf check though if you need it. When in doubt
	// enable the csrf check
	if($container === null){
		$container = createDIContainer();
		$container['Security']->setCSRFCheck(true);
		$container['Security']->setIsAdminCheck(false);
	}

	callController($controllerName, $methodName, $urlParams, $container);
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
$this->create('apptemplate_advanced_index', '/')->action(
	function($params){		
		callController('IndexController', 'index', $params);
	}
);

/**
 * Ajax Routes
 */
$this->create('apptemplate_advanced_ajax_setsystemvalue', '/setsystemvalue')->post()->action(
	function($params){		
		callAjaxController('AjaxController', 'setSystemValue', $params);
	}
);
