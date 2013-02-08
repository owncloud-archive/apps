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



class RedirectResponseTest extends \PHPUnit_Framework_TestCase {


	protected $response;

	protected function setUp(){
		$this->response = new RedirectResponse('/url');
	}


	public function testHeaders() {
		$headers = $this->response->getHeaders();
		$this->assertTrue(in_array('Location: /url', $headers));
	}

	public function testGetRedirectUrl(){
		$this->assertEquals('/url', $this->response->getRedirectUrl());
	}


}