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

use \OCA\AppFramework\App as App;

use \OCA\AppTemplateAdvanced\DependencyInjection\DIContainer as DIContainer;


/*************************
 * Define your routes here
 ************************/

/**
 * Normal Routes
 */
$this->create('apptemplate_advanced_index', '/')->action(
	function($params){
		App::main('ItemController', 'index', $params, new DIContainer());
	}
);

$this->create('apptemplate_advanced_index_param', '/test/{test}')->action(
	function($params){
		App::main('ItemController', 'index', $params, new DIContainer());
	}
);

$this->create('apptemplate_advanced_index_redirect', '/redirect')->action(
	function($params){
		App::main('ItemController', 'redirectToIndex', $params, new DIContainer());
	}
);

/**
 * Ajax Routes
 */
$this->create('apptemplate_advanced_ajax_setsystemvalue', '/setsystemvalue')->post()->action(
	function($params){
		App::main('ItemController', 'setSystemValue', $params, new DIContainer());
	}
);
