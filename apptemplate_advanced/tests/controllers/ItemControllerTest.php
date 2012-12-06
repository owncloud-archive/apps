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

namespace OCA\AppTemplateAdvanced;

// get abspath of file directory
$path = realpath( dirname( __FILE__ ) ) . '/';

require_once($path . "../../lib/request.php");
require_once($path . "../../lib/responses/response.php");
require_once($path . "../../lib/responses/json.response.php");
require_once($path . "../../lib/controller.php");
require_once($path . "../../controllers/item.controller.php");


class ItemControllerTest extends \PHPUnit_Framework_TestCase {


        public function testSetSystemValue(){
                $post = array('somesetting' => 'this is a test');
                $request = new Request(null, $post);

                // create an api mock object
                $api = $this->getMock('API', array('setSystemValue', 'getAppName'));

                // expects to be called once with the method
                // setSystemValue('somesetting', 'this is a test')
                $api->expects($this->once())
                                        ->method('setSystemValue')
                                        ->with(	$this->equalTo('somesetting'),
                                                        $this->equalTo('this is a test'));

                // we want to return the appname apptemplate_advanced when this method
                // is being called
                $api->expects($this->any())
                                        ->method('getAppName')
                                        ->will($this->returnValue('apptemplate_advanced'));

                $controller = new ItemController($api, $request, null);
                $controller->setSystemValue(null);


        }


}
