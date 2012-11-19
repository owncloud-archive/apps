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

/**
 * Declare your classes and their include path so that they'll be automatically
 * loaded once you instantiate them
 */
\OC::$CLASSPATH['Pimple'] = 'apps/apptemplate/3rdparty/Pimple/Pimple.php';

\OC::$CLASSPATH['OCA\AppTemplate\API'] = 'apps/apptemplate/lib/api.php';
\OC::$CLASSPATH['OCA\AppTemplate\Request'] = 'apps/apptemplate/lib/request.php';
\OC::$CLASSPATH['OCA\AppTemplate\Security'] = 'apps/apptemplate/lib/security.php';
\OC::$CLASSPATH['OCA\AppTemplate\Controller'] = 'apps/apptemplate/lib/controller.php';

\OC::$CLASSPATH['OCA\AppTemplate\IndexController'] = 'apps/apptemplate/controllers/index.controller.php';
\OC::$CLASSPATH['OCA\AppTemplate\SettingsController'] = 'apps/apptemplate/controllers/settings.controller.php';
\OC::$CLASSPATH['OCA\AppTemplate\AjaxController'] = 'apps/apptemplate/controllers/ajax.controller.php';


/**
 * @return a new DI container with prefilled values for the news app
 */
function createDIContainer(){
	$container = new \Pimple();

	/** 
	 * BASE
	 */
	$container['API'] = $container->share(function($c){
		return new API('apptemplate');
	});

	$container['Security'] = $container->share(function($c){
		return new Security($c['API']->getAppName());
	});

	$container['Request'] = $container->share(function($c){
		return new Request($c['API']->getUserId(), $_GET, $_POST);
	});


	/** 
	 * CONTROLLERS
	 */
	$container['IndexController'] = function($c){
		return new IndexController($c['API'], $c['Request']);
	};

	$container['SettingsController'] = function($c){
		return new SettingsController($c['API'], $c['Request']);
	};


	$container['AjaxController'] = function($c){
		return new AjaxController($c['API'], $c['Request']);
	};


	return $container;
}