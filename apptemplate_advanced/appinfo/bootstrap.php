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

/**
 * Declare your classes and their include path so that they'll be automatically
 * loaded once you instantiate them
 */
\OC::$CLASSPATH['Pimple'] = 'apps/apptemplate_advanced/3rdparty/Pimple/Pimple.php';

\OC::$CLASSPATH['OCA\AppTemplateAdvanced\API'] = 'apps/apptemplate_advanced/lib/api.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\Request'] = 'apps/apptemplate_advanced/lib/request.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\Security'] = 'apps/apptemplate_advanced/lib/security.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\Controller'] = 'apps/apptemplate_advanced/lib/controller.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\Response'] = 'apps/apptemplate_advanced/lib/responses/response.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\TemplateResponse'] = 'apps/apptemplate_advanced/lib/responses/template.response.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\JSONResponse'] = 'apps/apptemplate_advanced/lib/responses/json.response.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\RedirectResponse'] = 'apps/apptemplate_advanced/lib/responses/redirect.response.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\TextDownloadResponse'] = 'apps/apptemplate_advanced/lib/responses/textdownload.response.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\Mapper'] = 'apps/apptemplate_advanced/lib/mapper.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\DoesNotExistException'] = 'apps/apptemplate_advanced/lib/doesnotexist.exception.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\MethodAnnotationReader'] = 'apps/apptemplate_advanced/lib/methodannotationreader.php';

\OC::$CLASSPATH['OCA\AppTemplateAdvanced\ItemMapper'] = 'apps/apptemplate_advanced/database/item.mapper.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\Item'] = 'apps/apptemplate_advanced/database/item.php';

\OC::$CLASSPATH['OCA\AppTemplateAdvanced\ItemController'] = 'apps/apptemplate_advanced/controllers/item.controller.php';
\OC::$CLASSPATH['OCA\AppTemplateAdvanced\SettingsController'] = 'apps/apptemplate_advanced/controllers/settings.controller.php';


/**
 * @return a new DI container with prefilled values for the news app
 */
function createDIContainer(){
	$container = new \Pimple();

	/** 
	 * BASE
	 */
	$container['API'] = $container->share(function($c){
		return new API('apptemplate_advanced');
	});

	$container['Security'] = $container->share(function($c){
		return new Security($c['API']->getAppName());
	});

	$container['Request'] = $container->share(function($c){
		return new Request($_GET, $_POST, $_FILES);
	});


	/** 
	 * CONTROLLERS
	 */
	$container['ItemController'] = function($c){
		return new ItemController($c['API'], $c['Request'], $c['ItemMapper']);
	};

	$container['SettingsController'] = function($c){
		return new SettingsController($c['API'], $c['Request']);
	};


	/**
	 * MAPPERS
	 */
	$container['ItemMapper'] = $container->share(function($c){
		return new ItemMapper($c['API']);
	});


	return $container;
}