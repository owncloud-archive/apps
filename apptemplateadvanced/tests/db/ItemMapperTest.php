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

namespace OCA\AppTemplateAdvanced\Db;

require_once(__DIR__ . "/../classloader.php");


class ItemMapperTest extends \PHPUnit_Framework_TestCase {

    private $api;
    private $mapper;
    private $row;

    protected function setUp(){
        $this->api = $this->getMock('OCA\AppFramework\Core\Api', array('prepareQuery'), array('apptemplateadvanced'));
        $this->mapper = new ItemMapper($this->api);
        $this->row = array(
            'id' => 1,
            'user' => 'john',
            'path' => '/test',
            'name' => 'right'
        );

    }


    public function testFindByUserId(){
        $userId = 1;
        $expected = 'SELECT * FROM `*PREFIX*apptemplateadvanced_items` WHERE `user` = ?';

        $cursor = $this->getMock('cursor', array('fetchRow'));
        $cursor->expects($this->once())
                ->method('fetchRow')
                ->will($this->returnValue($this->row));

        $query = $this->getMock('query', array('execute'));
        $query->expects($this->once())
                ->method('execute')
                ->with($this->equalTo(array($userId)))
                ->will($this->returnValue($cursor));

        $this->api->expects($this->once())
                ->method('prepareQuery')
                ->with($this->equalTo($expected))
                ->will($this->returnValue($query));

        $item = $this->mapper->findByUserId($userId);

        $this->assertEquals($this->row['id'], $item->getId());
        $this->assertEquals($this->row['path'], $item->getPath());
        $this->assertEquals($this->row['name'], $item->getName());
        $this->assertEquals($this->row['user'], $item->getUser());
    }


}