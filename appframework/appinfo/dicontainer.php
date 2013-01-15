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

namespace OCA\AppFramework;


class DIContainer extends \Pimple {


	public function __construct($appName){
		
		$this['AppName'] = $appName;

		$this['API'] = $this->share(function($c){
			return new API($c['AppName']);
		});

		$this['Request'] = $this->share(function($c){
			return new Request($_GET, $_POST, $_FILES);
		});


		/**
		 * Middleware
		 */
		$this['SecurityMiddleware'] = function($c){
			return new SecurityMiddleware($c['API']);
		};

		$this['MiddlewareDispatcher'] = function($c){
			$dispatcher = new MiddlewareDispatcher();
			$dispatcher->registerMiddleware($c['SecurityMiddleware']);
			return $dispatcher;
		};

	}

	
}

