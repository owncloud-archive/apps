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

use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Http\JSONResponse;
use OCA\AppFramework\Http\TemplateResponse;


require_once(__DIR__ . "/../classloader.php");


class ChildController extends Controller {};

class ControllerTest extends \PHPUnit_Framework_TestCase {

	private $controller;
	private $api;

	protected function setUp(){
		$request = new Request(
			array('get'=>'getvalue'), 
			array('post'=>'postvalue'), 
			array('file'=>'filevalue'),
			array('REQUEST_METHOD' => 'hi'),
			array('PATH' => 'daheim'),
			array('sezession' => 'kein'),
			array('cooken' => 'warm'),
			array('url' => 'something')
		);

		$this->api = $this->getMock('OCA\AppFramework\Core\API',
									array('getAppName'), array('test'));
		$this->api->expects($this->any())
				->method('getAppName')
				->will($this->returnValue('apptemplate_advanced'));
	  
		$this->controller = new ChildController($this->api, $request);
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


	public function testGetParams(){
		$urlParams = array('url' => 'something');
		$get = array('get'=>'getvalue');
		$post = array('post'=>'postvalue');

		$this->assertEquals(array_merge($urlParams, $get, $post), $this->controller->getParams());
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

                $this->controller = new ChildController($api, new Request());
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


	public function testGetRequestMethod(){
		$this->assertEquals('hi', $this->controller->method());
	}


	public function testGetEnvVariable(){
		$this->assertEquals('daheim', $this->controller->env('PATH'));
	}

	public function testGetSessionVariable(){
		$this->assertEquals('kein', $this->controller->session('sezession'));
	}


	public function testSetCookieVariable(){
		$this->controller->session('test', 'problem');
		$this->assertEquals('problem', $this->controller->session('test'));	
	}


	public function testGetCookieVariable(){
		$this->assertEquals('warm', $this->controller->cookie('cooken'));
	}

}