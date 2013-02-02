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


	public function testGetGETAndPOST(){
		$get = array('johnny' => 'begood');
		$post = array('also' => 'rockit');
		$request = new Request($get, $post);

		$this->assertEquals(array_merge($get, $post), $request->getGETAndPOST());
	}


}
