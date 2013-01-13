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

abstract class Middleware {


        public function beforeController($controllerName, $methodName, \Pimple $container){

        }


        public function afterController($controllerName, $methodName, \Pimple $container, Response $response){
                return $response;
        }


        public function beforeOutput($controllerName, $methodName, \Pimple $container, $output){
                return $output;
        }


        public function afterException($controllerName, $methodName, \Pimple $container, $exception, $response){
                return $response;
        }

}