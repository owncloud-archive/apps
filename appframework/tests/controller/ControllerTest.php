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


namespace OCA\AppFramework\Controller;

use OCA\AppFramework\Http\Request as Request;
use OCA\AppFramework\Http\JSONResponse as JSONResponse;
use OCA\AppFramework\Http\TemplateResponse as TemplateResponse;


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

		$this->api = $this->getMock('OCA\AppFramework\Core\API',
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


	public function testParamsFile(){
		$this->assertEquals('filevalue', $this->controller->params('file', 'filevalue'));
	}


	public function testGetUploadedFile(){
		$this->assertEquals('filevalue', $this->controller->getUploadedFile('file'));
	}



	public function testGetUploadedFileDefault(){
		$this->assertEquals('default', $this->controller->params('files', 'default'));
	}


	public function testRender(){
		$this->assertTrue($this->controller->render('') instanceof TemplateResponse);
	}


	public function testSetParams(){
		$params = array('john' => 'foo');
		$response = $this->controller->render('home', $params);

		$this->assertEquals($params, $response->getParams());
	}


	public function testRenderRenderAs(){
		$ocTpl = $this->getMock('Template', array('fetchPage'));
		$ocTpl->expects($this->once())
				->method('fetchPage');

		$api = $this->getMock('OCA\AppFramework\Core\API',
					array('getAppName', 'getTemplate'), array('app'));
		$api->expects($this->any())
				->method('getAppName')
				->will($this->returnValue('app'));
		$api->expects($this->once())
				->method('getTemplate')
				->with($this->equalTo('home'), $this->equalTo('admin'), $this->equalTo('app'))
				->will($this->returnValue($ocTpl));

		$this->controller = new Controller($api, new Request());
		$this->controller->render('home', array(), 'admin')->render();
	}


	public function testRenderHeaders(){
		$headers = array('one', 'two');
		$response = $this->controller->render('', array(), '', $headers);

		$this->assertTrue(in_array($headers[0], $response->getHeaders()));
		$this->assertTrue(in_array($headers[1], $response->getHeaders()));
	}


	public function testRenderJSON() {
		$params = array('hi' => 'ho');
		$json = new JSONResponse();
		$json->setParams($params);

		$this->assertEquals($json->render(), 
				$this->controller->renderJSON($params)->render());
	}

	public function testRenderJSONError() {
		$params = array('hi' => 'ho');
		$error = 'not good';
		$json = new JSONResponse();
		$json->setParams($params);
		$json->setErrorMessage($error);

		$this->assertEquals($json->render(), 
				$this->controller->renderJSON($params, $error)->render());	
	}


}