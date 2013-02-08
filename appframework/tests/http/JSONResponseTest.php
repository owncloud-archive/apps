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



class JSONResponseTest extends \PHPUnit_Framework_TestCase {

	private $json;

	protected function setUp() {
		$this->json = new JSONResponse();
	}


	public function testHeader(){
		$headers = $this->json->getHeaders();
		$this->assertTrue(in_array('Content-type: application/json', $headers));
	}


	public function testSetParams(){
		$params = array('hi', 'yo');
		$this->json->setParams($params);

		$this->assertEquals(array('hi', 'yo'), $this->json->getParams());
	}


	public function testRender(){
		$params = array('test' => 'hi');
		$this->json->setParams($params);

		$expected = '{"status":"success","data":{"test":"hi"}}';

		$this->assertEquals($expected, $this->json->render());
	}


	public function testRenderError(){
		$params = array('test' => 'hi');
		$this->json->setParams($params);
		$this->json->setErrorMessage('kaputt');

		$expected = '{"status":"error","data":{"test":"hi"},"msg":"kaputt"}';

		$this->assertEquals($expected, $this->json->render());
	}


}