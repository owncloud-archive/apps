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

namespace OCA\AppFramework;

// get abspath of file directory
$path = realpath( dirname( __FILE__ ) ) . '/';


require_once($path . "../../lib/request.php");


class RequestTest extends \PHPUnit_Framework_TestCase {


	public function testGetPOST(){
		$post = array('test' => 'somevalue');
		$request = new Request(null, $post);

		$this->assertEquals('somevalue', $request->getPOST('test'));
	}


	public function testGetPOSTEmpty(){
		$post = array();
		$request = new Request(null, $post);

		$this->assertEquals('', $request->getPOST('test'));
	}


	public function testGetPOSTDefault(){
		$post = array();
		$request = new Request(null, $post);

		$this->assertEquals('default', $request->getPOST('test', 'default'));
	}


	public function testGetGET(){
		$get = array('test' => 'somevalue');
		$request = new Request($get, null);

		$this->assertEquals('somevalue', $request->getGET('test'));
	}


	public function testGetGETEmpty(){
		$get = array();
		$request = new Request($get, null);

		$this->assertEquals('', $request->getGET('test'));
	}


	public function testGetGETDefault(){
		$get = array();
		$request = new Request($get, null);

		$this->assertEquals('default', $request->getGET('test', 'default'));
	}


}
