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

use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Core\API;
use OCA\AppFramework\Middleware\MiddlewareDispatcher;


require_once(__DIR__ . "/../3rdparty/Pimple/Pimple.php");
require_once(__DIR__ . "/classloader.php");


class AppTest extends \PHPUnit_Framework_TestCase {

	private $dicontainer;
	private $api;
	private $controller;
	private $dispatcher;
	private $params;
	private $controllerName;
	private $controllerMethod;

	protected function setUp() {
		$this->dicontainer = new \Pimple();
		$this->api = $this->getMock('OCA\AppFramework\Core\API', null, array('hi'));
		$this->controller = $this->getMock('OCA\AppFramework\Controller\Controller', 
				array('setURLParams', 'method'), array($this->api, new Request()));
		$this->dispatcher = $this->getMock('OCA\AppFramework\Middleware\MiddlewareDispatcher', 
				array('beforeController', 'afterController', 'afterException', 'beforeOutput'));
		$this->dicontainer['Controller'] = $this->controller;
		$this->dicontainer['MiddlewareDispatcher'] = $this->dispatcher;
		$this->params = array('hi', 'ho');
		$this->controllerName = 'Controller';
		$this->controllerMethod = 'method';
		$this->response = $this->getMock('OCA\AppFramework\Http\Response', array('render'));
		$this->output = 'hi';
	}


	private function expectControllerMethodCall(){
		$this->controller->expects($this->once())
				->method('method')
				->will($this->returnValue($this->response));
	}


	private function callMain(){
		App::main($this->controllerName, $this->controllerMethod, $this->params, $this->dicontainer);
	}


	public function testBeforeControllerWillBeCalled(){
		$this->expectControllerMethodCall();
		$this->dispatcher->expects($this->once())
				->method('beforeController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod));
		$this->dispatcher->expects($this->once())
				->method('afterController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->response))
				->will($this->returnValue($this->response));
		$this->callMain();
	}


	public function testExceptionInBeforeControllerWillNotCallController(){
		$this->setExpectedException('Exception');
		$this->controller->expects($this->never())
				->method('method');
		$this->dispatcher->expects($this->once())
				->method('beforeController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod))
				->will($this->throwException(new \Exception()));
		$this->callMain();		
	}


	public function testUncaughtExceptionInControllerMethodWillNotBeCaught(){
		$this->setExpectedException('Exception');
		$this->controller->expects($this->once())
				->method('method')
				->will($this->throwException(new \Exception()));
		$this->callMain();
	}


	public function testCaughtExceptionWillCallAfterController(){
		$ex = new \Exception();
		$this->controller->expects($this->once())
				->method('method')
				->will($this->throwException($ex));
		
		$this->dispatcher->expects($this->once())
				->method('afterException')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($ex))
				->will($this->returnValue($this->response));

		$this->dispatcher->expects($this->once())
				->method('afterController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->response))
				->will($this->returnValue($this->response));

		$this->callMain();
	}


	public function testRenderWillBeCalled(){
		$this->controller->expects($this->once())
				->method('method')
				->will($this->returnValue($this->response));
		$this->dispatcher->expects($this->once())
				->method('afterController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->response))
				->will($this->returnValue($this->response));
		$this->response->expects($this->once())->method('render');

		$this->callMain();
	}


	public function testBeforeOutputWillBeCalled(){
		$this->expectOutputString($this->output);

		$this->response->expects($this->once())
				->method('render')
				->will($this->returnValue($this->output));

		$this->controller->expects($this->once())
				->method('method')
				->will($this->returnValue($this->response));

		$this->dispatcher->expects($this->once())
				->method('afterController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->response))
				->will($this->returnValue($this->response));

		$this->dispatcher->expects($this->once())
				->method('beforeOutput')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->output))
				->will($this->returnValue($this->output));

		$this->callMain();		
	}


	public function testNoPrintWithNullOutput(){
		$this->expectOutputString('');

		$this->response->expects($this->once())
				->method('render')
				->will($this->returnValue($this->output));

		$this->controller->expects($this->once())
				->method('method')
				->will($this->returnValue($this->response));

		$this->dispatcher->expects($this->once())
				->method('afterController')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->response))
				->will($this->returnValue($this->response));

		$this->dispatcher->expects($this->once())
				->method('beforeOutput')
				->with($this->equalTo($this->controller), $this->equalTo($this->controllerMethod), $this->equalTo($this->output))
				->will($this->returnValue(null));

		$this->callMain();	
	}


}