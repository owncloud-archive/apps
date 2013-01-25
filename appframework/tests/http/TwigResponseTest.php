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

use OCA\AppFramework\Core\API as API;


require_once(__DIR__ . "/../classloader.php");


class TwigResponseTest extends \PHPUnit_Framework_TestCase {


        protected function setUp(){
                $this->api = $this->getMock('OCA\AppFramework\Core\API', null, array('hi'));
                $this->twig = $this->getMock('Twig', array('render'));
        }


        public function testRender(){
                $templateName = 'test';
                $params = array('john' => 'doe');
                $this->twig->expects($this->once())
                                ->method('render')
                                ->with($this->equalTo($templateName . '.php'), $this->equalTo($params));

                $response = new TwigResponse($this->api, $templateName, $this->twig);
                $response->setParams($params);

                $response->render();
        }

}