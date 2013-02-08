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


namespace OCA\AppFramework\Middleware\Security;

use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Http\RedirectResponse;
use OCA\AppFramework\Http\JSONResponse;
use OCA\AppFramework\Middleware\Middleware;


require_once(__DIR__ . "/../../classloader.php");


class SecurityMiddlewareTest extends \PHPUnit_Framework_TestCase {

	private $middleware;
	private $controller;
	private $secException;
	private $secAjaxException;

	public function setUp() {
		$api = $this->getMock('OCA\AppFramework\Core\API', array(), array('test'));
		$this->controller = $this->getMock('OCA\AppFramework\Controller\Controller',
				array(), array($api, new Request()));

		$this->middleware = new SecurityMiddleware($api);
		$this->secException = new SecurityException('hey', false);
		$this->secAjaxException = new SecurityException('hey', true);
	}


	private function getAPI(){
		return $this->getMock('OCA\AppFramework\Core\API',
					array('isLoggedIn', 'passesCSRFCheck', 'isAdminUser',
							'isSubAdminUser', 'activateNavigationEntry',
							'getUserId'),
					array('app'));
	}


	private function checkNavEntry($method, $shouldBeActivated=false){
		$api = $this->getAPI();

		if($shouldBeActivated){
			$api->expects($this->once())
				->method('activateNavigationEntry');
		} else {
			$api->expects($this->never())
				->method('activateNavigationEntry');
		}

		$sec = new SecurityMiddleware($api);
		$sec->beforeController('\OCA\AppFramework\Middleware\Security\SecurityMiddlewareTest', $method);
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testSetNavigationEntry(){
		$this->checkNavEntry('testSetNavigationEntry', true);
	}


	/**
	 * @Ajax
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testSetNoNavigationEntry(){
		$this->checkNavEntry('testSetNoNavigationEntry');
	}


	private function ajaxExceptionCheck($method, $shouldBeAjax=false){
		$api = $this->getAPI();
		$api->expects($this->any())
				->method('passesCSRFCheck')
				->will($this->returnValue(false));

		$sec = new SecurityMiddleware($api);

		try {
			$sec->beforeController('\OCA\AppFramework\Middleware\Security\SecurityMiddlewareTest',
					$method);
		} catch (SecurityException $ex){
			if($shouldBeAjax){
				$this->assertTrue($ex->isAjax());
			} else {
				$this->assertFalse($ex->isAjax());
			}

		}
	}


	/**
	 * @Ajax
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testAjaxException(){
		$this->ajaxExceptionCheck('testAjaxException');
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testNoAjaxException(){
		$this->ajaxExceptionCheck('testNoAjaxException');
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testNoChecks(){
		$api = $this->getAPI();
		$api->expects($this->never())
				->method('passesCSRFCheck')
				->will($this->returnValue(true));
		$api->expects($this->never())
				->method('isAdminUser')
				->will($this->returnValue(true));
		$api->expects($this->never())
				->method('isSubAdminUser')
				->will($this->returnValue(true));
		$api->expects($this->never())
				->method('isLoggedIn')
				->will($this->returnValue(true));

		$sec = new SecurityMiddleware($api);
		$sec->beforeController('\OCA\AppFramework\Middleware\Security\SecurityMiddlewareTest',
				'testNoChecks');
	}


	private function securityCheck($method, $expects, $shouldFail=false){
		$api = $this->getAPI();
		$api->expects($this->once())
				->method($expects)
				->will($this->returnValue(!$shouldFail));

		$sec = new SecurityMiddleware($api);

		if($shouldFail){
			$this->setExpectedException('\OCA\AppFramework\Middleware\Security\SecurityException');
		}

		$sec->beforeController('\OCA\AppFramework\Middleware\Security\SecurityMiddlewareTest', $method);
	}


	/**
	 * @IsLoggedInExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testCsrfCheck(){
		$this->securityCheck('testCsrfCheck', 'passesCSRFCheck');
	}


	/**
	 * @IsLoggedInExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testFailCsrfCheck(){
		$this->securityCheck('testFailCsrfCheck', 'passesCSRFCheck', true);
	}


	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testLoggedInCheck(){
		$this->securityCheck('testLoggedInCheck', 'isLoggedIn');
	}


	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function testFailLoggedInCheck(){
		$this->securityCheck('testFailLoggedInCheck', 'isLoggedIn', true);
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsSubAdminExemption
	 */
	public function testIsAdminCheck(){
		$this->securityCheck('testIsAdminCheck', 'isAdminUser');
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsSubAdminExemption
	 */
	public function testFailIsAdminCheck(){
		$this->securityCheck('testFailIsAdminCheck', 'isAdminUser', true);
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 */
	public function testIsSubAdminCheck(){
		$this->securityCheck('testIsSubAdminCheck', 'isSubAdminUser');
	}


	/**
	 * @IsLoggedInExemption
	 * @CSRFExemption
	 * @IsAdminExemption
	 */
	public function testFailIsSubAdminCheck(){
		$this->securityCheck('testFailIsSubAdminCheck', 'isSubAdminUser', true);
	}



	public function testAfterExceptionNotCaughtReturnsNull(){
		$ex = new \Exception();

		$this->assertEquals(null,
				$this->middleware->afterException($this->controller, 'test', $ex));
	}


	public function testAfterExceptionReturnsRedirect(){
		$response = $this->middleware->afterException($this->controller, 'test',
				$this->secException);

		$this->assertTrue($response instanceof RedirectResponse);
	}


	public function testAfterAjaxExceptionReturnsJSONError(){
		$response = $this->middleware->afterException($this->controller, 'test',
				$this->secAjaxException);

		$this->assertTrue($response instanceof JSONResponse);
	}


}