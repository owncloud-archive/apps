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


require_once \OC_App::getAppPath('apptemplate_advanced') . '/appinfo/bootstrap.php';


\OCP\App::registerAdmin('apptemplate_advanced', 'admin/settings');

\OCP\App::addNavigationEntry( array(
	
	// the string under which your app will be referenced
	// in owncloud, for instance: \OC_App::getAppPath('APP_ID')
	'id' => 'apptemplate_advanced',

	// sorting weight for the navigation. The higher the number, the higher
	// will it be listed in the navigation
	'order' => 74,
	
	// the route that will be shown on startup
	'href' => \OC_Helper::linkToRoute('apptemplate_advanced_index'),
	
	// the icon that will be shown in the navigation
	'icon' => \OCP\Util::imagePath('apptemplate_advanced', 'example.png' ),
	
	// the title of your application. This will be used in the
	// navigation or on the settings page of your app
	'name' => \OC_L10N::get('apptemplate_advanced')->t('Advanced App Template') 
	
));
