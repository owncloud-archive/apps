<?php

/**
 * ownCloud - App Framework
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


require_once(__DIR__ . "/../classloader.php");


class ChildController extends Controller {};

class ControllerTest extends \PHPUnit_Framework_TestCase {

	private $controller;
        private $api;

	protected function setUp(){
		$request = new Request(
			array('get'=>'getvalue'), 
			array('post'=>'postvalue'), 
			array('file'=>'filevalue')
		);

                $this->api = $this->getMock('OCA\AppFramework\API',
                                        array('getAppName'), array('test'));
                $this->api->expects($this->any())
			->method('getAppName')
			->will($this->returnValue('apptemplate_advanced'));

			  
                $this->controller = new ChildController($this->api, $request);
	}


	public function testSetURLParams() {
		$urlParams = array('post' => 'something');
		$this->controller->setURLParams($urlParams);

		$this->assertEquals($urlParams['post'], $this->controller->params('post'));
	}


	public function testParamsPreferPostOverGet(){
		$request = new Request(array('post'=>'getvalue'), array('post'=>'postvalue'));
                $this->controller = new ChildController($this->api, $request);

		$this->assertEquals('postvalue', $this->controller->params('post'));
	}


	public function testParamsPostDefault(){
		$this->assertEquals('default', $this->controller->params('posts', 'default'));
	}


	public function testParamsGet(){
		$this->assertEquals('getvalue', $this->controller->params('get', 'getvalue'));
	}


	public function testParamsGetDefault(){
		$this->assertEquals('default', $this->controller->params('gets', 'default'));
	}


	public function testGetUploadedFile(){
		$this->assertEquals('filevalue', $this->controller->params('file', 'filevalue'));
	}


	public function testGetUploadedFileDefault(){
		$this->assertEquals('default', $this->controller->params('files', 'default'));
	}


	public function testRender(){
		// TODO
	}


	public function testRenderJSON() {
		// TODO
	}


}