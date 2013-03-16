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


namespace OCA\AppFramework\Http;


require_once(__DIR__ . "/../classloader.php");



class RequestTest extends \PHPUnit_Framework_TestCase {


	public function testGetPOST(){
		$post = array('test' => 'somevalue');
		$request = new Request(array(), $post);

		$this->assertEquals('somevalue', $request->getPOST('test'));
	}


	public function testGetPOSTEmpty(){
		$post = array();
		$request = new Request(array(), $post);

		$this->assertEquals('', $request->getPOST('test'));
	}


	public function testGetPOSTDefault(){
		$post = array();
		$request = new Request(array(), $post);

		$this->assertEquals('default', $request->getPOST('test', 'default'));
	}


	public function testGetGET(){
		$get = array('test' => 'somevalue');
		$request = new Request($get);

		$this->assertEquals('somevalue', $request->getGET('test'));
	}


	public function testGetGETEmpty(){
		$get = array();
		$request = new Request($get);

		$this->assertEquals('', $request->getGET('test'));
	}


	public function testGetGETDefault(){
		$get = array();
		$request = new Request($get);

		$this->assertEquals('default', $request->getGET('test', 'default'));
	}


	public function testGetFILE(){
		$files = array('test' => 'somevalue');
		$request = new Request(array(), array(), $files);

		$this->assertEquals('somevalue', $request->getFILES('test'));
	}


	public function testGetFILEEmpty(){
		$request = new Request();

		$this->assertNull($request->getFILES('test'));
	}


	public function testRequestParams(){
		$get = array('johnny' => 'begood');
		$post = array('also' => 'rockit');
		$urlParams = array('aaa' => 'bbb');
		$request = new Request($get, $post, array(), array(),
					array(), array(), array(), $urlParams);

		$this->assertEquals(array_merge($get, $post, $urlParams), 
				$request->getRequestParams());
	}

	// server
	public function testGetSERVER(){
		$server = array('test' => 'somevalue');
		$request = new Request(array(), array(), array(), $server);

		$this->assertEquals('somevalue', $request->getSERVER('test'));
	}


	public function testGetSERVEREmpty(){
		$server = array();
		$request = new Request(array(), array(), array(), $server);

		$this->assertEquals('', $request->getSERVER('test'));
	}


	public function testGetSERVERDefault(){
		$server = array();
		$request = new Request(array(), array(), array(), $server);

		$this->assertEquals('default', $request->getSERVER('test', 'default'));
	}


	// env
	public function testGetENV(){
		$ENV = array('test' => 'somevalue');
		$request = new Request(array(), array(), array(), array(), $ENV);

		$this->assertEquals('somevalue', $request->getENV('test'));
	}


	public function testGetENVEmpty(){
		$ENV = array();
		$request = new Request(array(), array(), array(), array(), $ENV);

		$this->assertEquals('', $request->getENV('test'));
	}


	public function testGetENVDefault(){
		$ENV = array();
		$request = new Request(array(), array(), array(), array(), $ENV);

		$this->assertEquals('default', $request->getENV('test', 'default'));
	}


	// session
	public function testGetSESSION(){
		$SESSION = array('test' => 'somevalue');
		$request = new Request(array(), array(), array(), array(), array(), 
								$SESSION);

		$this->assertEquals('somevalue', $request->getSESSION('test'));
	}


	public function testGetSESSIONEmpty(){
		$SESSION = array();
		$request = new Request(array(), array(), array(), array(), array(), 
								$SESSION);

		$this->assertEquals('', $request->getSESSION('test'));
	}


	public function testGetSESSIONDefault(){
		$SESSION = array();
		$request = new Request(array(), array(), array(), array(), array(), 
								$SESSION);

		$this->assertEquals('default', $request->getSESSION('test', 'default'));
	}


	// cookie
	public function testGetCOOKIE(){
		$COOKIE = array('test' => 'somevalue');
		$request = new Request(array(), array(), array(), array(), array(), 
								array(), $COOKIE);

		$this->assertEquals('somevalue', $request->getCOOKIE('test'));
	}


	public function testGetCOOKIEEmpty(){
		$COOKIE = array();
		$request = new Request(array(), array(), array(), array(), array(), 
								array(), $COOKIE);

		$this->assertEquals('', $request->getCOOKIE('test'));
	}


	public function testGetCOOKIEDefault(){
		$COOKIE = array();
		$request = new Request(array(), array(), array(), array(), array(), 
								array(), $COOKIE);

		$this->assertEquals('default', $request->getCOOKIE('test', 'default'));
	}



	// urlParams
	public function testGeturlParams(){
		$urlParams = array('test' => 'somevalue');
		$request = new Request(array(), array(), array(), array(), array(), 
								array(), array(), $urlParams);

		$this->assertEquals('somevalue', $request->getURLParams('test'));
	}


	public function testGeturlParamsEmpty(){
		$urlParams = array();
		$request = new Request(array(), array(), array(), array(), array(), 
								array(), array(), $urlParams);

		$this->assertEquals('', $request->getURLParams('test'));
	}


	public function testGeturlParamsDefault(){
		$urlParams = array();
		$request = new Request(array(), array(), array(), array(), array(), 
								array(), array(), $urlParams);

		$this->assertEquals('default', $request->getURLParams('test', 'default'));
	}


	public function testGetMethod(){
		$server = array('REQUEST_METHOD' => 'hi');
		$request = new Request(array(), array(), array(), $server);

		$this->assertEquals('hi', $request->getMethod());
	}


	public function testSetSession(){
		$request = new Request();
		$request->setSESSION('my', 'value');

		$this->assertEquals('value', $request->getSESSION('my'));
	}

}
