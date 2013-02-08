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

use OCA\AppFramework\Controller\Controller;
use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Http\Response;
use OCA\AppFramework\Middleware\Middleware;
use OCA\AppFramework\Middleware\MiddlewareDispatcher;


require_once(__DIR__ . "/../classloader.php");


// needed to test ordering
class TestMiddleware extends Middleware {
	public static $beforeControllerCalled = 0;
	public static $afterControllerCalled = 0;
	public static $afterExceptionCalled = 0;
	public static $beforeOutputCalled = 0;

	public $beforeControllerOrder = 0;
	public $afterControllerOrder = 0;
	public $afterExceptionOrder = 0;
	public $beforeOutputOrder = 0;

	public $controller;
	public $methodName;
	public $exception;
	public $response;
	public $output;

	private $beforeControllerThrowsEx;

	public function __construct($beforeControllerThrowsEx) {
		self::$beforeControllerCalled = 0;
		self::$afterControllerCalled = 0;
		self::$afterExceptionCalled = 0;
		self::$beforeOutputCalled = 0;
		$this->beforeControllerThrowsEx = $beforeControllerThrowsEx;
	}

	public function beforeController($controller, $methodName){
		self::$beforeControllerCalled++;
		$this->beforeControllerOrder = self::$beforeControllerCalled;
		$this->controller = $controller;
		$this->methodName = $methodName;
		if($this->beforeControllerThrowsEx){
			throw new \Exception();
		}
	}

	public function afterException($controller, $methodName, \Exception $exception){
		self::$afterExceptionCalled++;
		$this->afterExceptionOrder = self::$afterExceptionCalled;
		$this->controller = $controller;
		$this->methodName = $methodName;
		$this->exception = $exception;
		return parent::afterException($controller, $methodName, $exception);
	}

	public function afterController($controller, $methodName, Response $response){
		self::$afterControllerCalled++;
		$this->afterControllerOrder = self::$afterControllerCalled;
		$this->controller = $controller;
		$this->methodName = $methodName;
		$this->response = $response;
		return parent::afterController($controller, $methodName, $response);
	}

	public function beforeOutput($controller, $methodName, $output){
		self::$beforeOutputCalled++;
		$this->beforeOutputOrder = self::$beforeOutputCalled;
		$this->controller = $controller;
		$this->methodName = $methodName;
		$this->output = $output;
		return parent::beforeOutput($controller, $methodName, $output);
	}
}


class MiddlewareDispatcherTest extends \PHPUnit_Framework_TestCase {

	private $dispatcher;


	public function setUp() {
		$this->dispatcher = new MiddlewareDispatcher();
		$this->controller = $this->getControllerMock();
		$this->method = 'method';
		$this->response = new Response();
		$this->output = 'hi';
		$this->exception = new \Exception();
	}


	private function getAPIMock(){
		return $this->getMock('OCA\AppFramework\Core\API',
					array('getAppName'), array('app'));
	}


	private function getControllerMock(){
		return $this->getMock('OCA\AppFramework\Controller\Controller', array('method'),
			array($this->getAPIMock(), new Request()));
	}


	private function getMiddleware($beforeControllerThrowsEx=false){
		$m1 = new TestMiddleware($beforeControllerThrowsEx);
		$this->dispatcher->registerMiddleware($m1);
		return $m1;
	}


	public function testAfterExceptionShouldReturnResponseOfMiddleware(){
		$response = new Response();
		$m1 = $this->getMock('\OCA\AppFramework\Middleware\Middleware',
				array('afterException', 'beforeController'));
		$m1->expects($this->never())
				->method('afterException');

		$m2 = $this->getMock('OCA\AppFramework\Middleware\Middleware',
				array('afterException', 'beforeController'));
		$m2->expects($this->once())
				->method('afterException')
				->will($this->returnValue($response));

		$this->dispatcher->registerMiddleware($m1);
		$this->dispatcher->registerMiddleware($m2);

		$this->dispatcher->beforeController($this->controller, $this->method);
		$this->assertEquals($response, $this->dispatcher->afterException($this->controller, $this->method, $this->exception));
	}


	public function testAfterExceptionShouldReturnNullIfNotHandled(){
		$m1 = $this->getMock('\OCA\AppFramework\Middleware\Middleware',
				array('afterException', 'beforeController'));
		$m1->expects($this->once())
				->method('afterException');

		$m2 = $this->getMock('OCA\AppFramework\Middleware\Middleware',
				array('afterException', 'beforeController'));
		$m2->expects($this->once())
				->method('afterException')
				->will($this->returnValue(null));

		$this->dispatcher->registerMiddleware($m1);
		$this->dispatcher->registerMiddleware($m2);

		$this->dispatcher->beforeController($this->controller, $this->method);
		$this->assertNull($this->dispatcher->afterException($this->controller, $this->method, $this->exception));
	}


	public function testBeforeControllerCorrectArguments(){
		$m1 = $this->getMiddleware();
		$this->dispatcher->beforeController($this->controller, $this->method);

		$this->assertEquals($this->controller, $m1->controller);
		$this->assertEquals($this->method, $m1->methodName);
	}


	public function testAfterControllerCorrectArguments(){
		$m1 = $this->getMiddleware();

		$this->dispatcher->afterController($this->controller, $this->method, $this->response);

		$this->assertEquals($this->controller, $m1->controller);
		$this->assertEquals($this->method, $m1->methodName);
		$this->assertEquals($this->response, $m1->response);
	}


	public function testAfterExceptionCorrectArguments(){
		$m1 = $this->getMiddleware();

		$this->setExpectedException('Exception');

		$this->dispatcher->beforeController($this->controller, $this->method);
		$this->dispatcher->afterException($this->controller, $this->method, $this->exception);

		$this->assertEquals($this->controller, $m1->controller);
		$this->assertEquals($this->method, $m1->methodName);
		$this->assertEquals($this->exception, $m1->exception);
	}


	public function testBeforeOutputCorrectArguments(){
		$m1 = $this->getMiddleware();

		$this->dispatcher->beforeOutput($this->controller, $this->method, $this->output);

		$this->assertEquals($this->controller, $m1->controller);
		$this->assertEquals($this->method, $m1->methodName);
		$this->assertEquals($this->output, $m1->output);
	}


	public function testBeforeControllerOrder(){
		$m1 = $this->getMiddleware();
		$m2 = $this->getMiddleware();

		$this->dispatcher->beforeController($this->controller, $this->method);

		$this->assertEquals(1, $m1->beforeControllerOrder);
		$this->assertEquals(2, $m2->beforeControllerOrder);
	}

	public function testAfterControllerOrder(){
		$m1 = $this->getMiddleware();
		$m2 = $this->getMiddleware();

		$this->dispatcher->afterController($this->controller, $this->method, $this->response);

		$this->assertEquals(2, $m1->afterControllerOrder);
		$this->assertEquals(1, $m2->afterControllerOrder);
	}


	public function testAfterExceptionOrder(){
		$m1 = $this->getMiddleware();
		$m2 = $this->getMiddleware();

		$this->setExpectedException('Exception');
		$this->dispatcher->beforeController($this->controller, $this->method);
		$this->dispatcher->afterException($this->controller, $this->method, $this->exception);

		$this->assertEquals(1, $m1->afterExceptionOrder);
		$this->assertEquals(1, $m2->afterExceptionOrder);
	}


	public function testBeforeOutputOrder(){
		$m1 = $this->getMiddleware();
		$m2 = $this->getMiddleware();

		$this->dispatcher->beforeOutput($this->controller, $this->method, $this->output);

		$this->assertEquals(2, $m1->beforeOutputOrder);
		$this->assertEquals(1, $m2->beforeOutputOrder);
	}


	public function testExceptionShouldRunAfterExceptionOfOnlyPreviouslyExecutedMiddlewares(){
		$m1 = $this->getMiddleware();
		$m2 = $this->getMiddleware(true);
		$m3 = $this->getMock('\OCA\AppFramework\Middleware\Middleware');
		$m3->expects($this->never())
				->method('afterException');
		$m3->expects($this->never())
				->method('beforeController');
		$m3->expects($this->never())
				->method('afterController');

		$this->dispatcher->registerMiddleware($m3);

		$this->dispatcher->beforeOutput($this->controller, $this->method, $this->output);

		$this->assertEquals(2, $m1->beforeOutputOrder);
		$this->assertEquals(1, $m2->beforeOutputOrder);
	}
}