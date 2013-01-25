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

namespace OCA\AppFramework\Middleware\Twig;

use OCA\AppFramework\Controller\Controller as Controller;
use OCA\AppFramework\Http\Response as Response;
use OCA\AppFramework\Http\JSONResponse as JSONResponse;
use OCA\AppFramework\Http\TemplateResponse as TemplateResponse;
use OCA\AppFramework\Http\TwigResponse as TwigResponse;
use OCA\AppFramework\Core\API as API;


require_once(__DIR__ . "/../../classloader.php");


class TwigMiddlewareTest extends \PHPUnit_Framework_TestCase {


        public function setUp(){
                $this->api = $this->getMock('OCA\AppFramework\Core\API', array('getTemplate'), array('hi'));
        $this->twig = $this->getMock('Twig', array('render'));
        $this->middleware = new TwigMiddleware($this->api, $this->twig);
        $this->octpl = $this->getMock('OC\Template', array('assign', 'fetchPage'));
        }


        public function testAfterControllerNoTemplateResponse(){
                $response = $this->middleware->afterController('a', 'b', new JSONResponse());
                $this->assertTrue($response instanceof JSONResponse);
        }


        public function testAfterControllerExchangesTemplateResponse(){
                $response = $this->middleware->afterController('a', 'b', new TemplateResponse($this->api, 'a'));
                $this->assertTrue($response instanceof TwigResponse);
        }


        public function testAfterControllerHeadersAreTransferred(){
                $tpl = new TemplateResponse($this->api, 'a');
                $tpl->addHeader('john');
                $tpl->addHeader('tom');
                $response = $this->middleware->afterController('a', 'b', $tpl);
                $this->assertContains('john', $response->getHeaders());
                $this->assertContains('tom', $response->getHeaders());
        }


        public function testAfterControllerTplNameIsTransferred(){
                $tpl = new TemplateResponse($this->api, 'hohohoho');
                $response = $this->middleware->afterController('a', 'b', $tpl);
                $this->assertEquals('hohohoho.php', $response->getTemplateName());
        }

        public function testAfterControllerParamsAreTransferred(){
                $params = array('john' => 'doe', 'frank' => 'john');
                $tpl = new TemplateResponse($this->api, 'a');
                $tpl->setParams($params);
                $response = $this->middleware->afterController('a', 'b', $tpl);
                $this->assertEquals($params, $response->getParams());
        }


        public function testBeforeOutputRenderBlankReturnsOutput(){
                $tpl = new TemplateResponse($this->api, 'a');
                $tpl->renderAs('blank');
                $this->middleware->afterController('a', 'b', $tpl);
                $out = $this->middleware->beforeOutput('a', 'b', 'out');
                $this->assertEquals('out', $out);
        }


        public function testBeforeOutputOutputIsAltered(){
                $this->api->expects($this->once())
                                ->method('getTemplate')
                                ->with($this->equalTo('twig'), $this->equalTo('mager'), $this->equalTo('appframework'))
                                ->will($this->returnValue($this->octpl));

                $this->octpl->expects($this->once())
                                ->method('fetchPage')
                                ->will($this->returnValue('testsss'));

                $tpl = new TemplateResponse($this->api, 'a');
                $tpl->renderAs('mager');
                $this->middleware->afterController('a', 'b', $tpl);
                $out = $this->middleware->beforeOutput('a', 'b', 'out');
                $this->assertEquals('testsss', $out);
        }


        public function testBeforeOutputTemplateIsAssignedCorrectly(){
                $this->api->expects($this->once())
                                ->method('getTemplate')
                                ->with($this->equalTo('twig'), $this->equalTo('mager'), $this->equalTo('appframework'))
                                ->will($this->returnValue($this->octpl));

                $this->octpl->expects($this->once())
                                ->method('assign')
                                ->with($this->equalTo('twig'), $this->equalTo('out'), $this->equalTo(false))
                                ->will($this->returnValue('testsss'));

                $tpl = new TemplateResponse($this->api, 'a');
                $tpl->renderAs('mager');
                $this->middleware->afterController('a', 'b', $tpl);
                $this->middleware->beforeOutput('a', 'b', 'out');
        }


        public function testBeforeOutputTemplateIsInstantiatedCorrectly(){
                $this->api->expects($this->once())
                                ->method('getTemplate')
                                ->with($this->equalTo('twig'), $this->equalTo('mager'), $this->equalTo('appframework'))
                                ->will($this->returnValue($this->octpl));

                $tpl = new TemplateResponse($this->api, 'a');
                $tpl->renderAs('mager');
                $this->middleware->afterController('a', 'b', $tpl);
                $this->middleware->beforeOutput('a', 'b', 'out');
        }


}