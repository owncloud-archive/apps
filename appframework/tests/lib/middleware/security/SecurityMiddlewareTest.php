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


require_once(__DIR__ . "/../../../classloader.php");



class SecurityMiddlewareTest extends \PHPUnit_Framework_TestCase {

    private $middleware;
    private $controller;
    private $secException;
    private $secAjaxException;

    public function setUp() {
        $api = $this->getMock('OCA\AppFramework\API', array(), array('test'));
        $this->controller = $this->getMock('OCA\AppFramework\Controller',
                array(), array($api, new Request()));

        $this->middleware = new SecurityMiddleware($api);
        $this->secException = new SecurityException('hey', false);
        $this->secAjaxException = new SecurityException('hey', true);
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


}