<?php
/**
* ownCloud
*
* @author Michiel de Jong
* @copyright 2012 owncloud.org
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

require_once 'lib/storage.php';
require_once 'lib/auth.php';
require_once 'lib/rest.php';

class Test_RemoteStorage extends UnitTestCase {
	function setUp() {
    // Check if we are a user
    OCP\User::checkLoggedIn();
	}

	function testStorage() {
    $timestamp = MyStorage::store('admin', 'zapps/todomvc/manifest.json', 'application/json', '{"url":"http://todomvc.michiel.5apps.com"}');
    $dirList = MyStorage::getDir('admin', 'zapps/todomvc/');
    $found = false;
    foreach($dirList as $k=>$v) {
      if($k == 'manifest.json') {
        if($v == $timestamp) {
          $found = true;
        } else {
          return 'dir list does not have same timestamp';
        }
      } else {
        return "foreign element in dir list: $k $v";
      }
    }
    $this->assertTrue(!$found);
    $retrieved = MyStorage::get('admin', 'zapps/todomvc/manifest.json');
    $this->assertEquals($retrieved['mimeType'], 'application/json');
    $this->assertEquals($retrieved['content'], '{"url":"http://todomvc.michiel.5apps.com"}');
    $timestamp = MyStorage::remove('admin', 'zapps/todomvc/manifest.json');
    $dirList = MyStorage::getDir('admin', 'zapps/');
    $this->assertEquals($dirList, array());
	}

  function testAuth() {
    $token = MyAuth::addApp('admin', 'apps/todomvc/manifest.json', array('r' => array('tasks'), 'w' => array('tasks')));

    $apps = MyAuth::getApps('admin');
    $this->assertEquals(count($apps), 1);
    $this->assertTrue($apps[$token]);//true ~ non-empty
    $this->assertEquals($apps[$token]['manifestPath'], 'apps/todomvc/manifest.json');
    $this->assertEquals(count($apps[$token]['scopes']), 2);
    $this->assertEquals(count($apps[$token]['scopes']['r']), 1);
    $this->assertEquals(count($apps[$token]['scopes']['w']), 1);
    $this->assertEquals($apps[$token]['scopes']['r'][0], 'tasks');
    $this->assertEquals($apps[$token]['scopes']['w'][0], 'tasks');
    $token = MyAuth::addApp('admin', 'apps/todomvc/manifest.json', array('r' => array('tasks')));
    $apps = MyAuth::getApps('admin');
    $this->assertEquals(count($apps), 1);
    $this->assertTrue($apps[$token]);//true ~ non-empty
    $this->assertEquals($apps[$token]['manifestPath'], 'apps/todomvc/manifest.json');
    $this->assertEquals(count($apps[$token]['scopes']), 1);
    $this->assertEquals(count($apps[$token]['scopes']['r']), 1);
    $this->assertEquals($apps[$token]['scopes']['r'][0], 'tasks');
    MyAuth::removeApp('admin', $token);
    $apps = MyAuth::getApps('admin');
    $this->assertEquals(count($apps), 0);
  }

  function testRest() {
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array(), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('GET', 'admin', 'public/foo/bar', array(), '');
    $this->assertEquals($a[0], 404);
    $token = MyAuth::addApp('admin', 'bla', array('r' => array('foo', 'bar'), 'w' => array('foo')));
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('PUT', 'admin', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('PUT', 'admin', 'bar/foo', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('GET', 'admin', 'bar/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $this->assertEquals($a[1]['Content-Type'], 'application/json');
    $this->assertEquals($a[2], '[]');
    $a = MyRest::handleRequest('PUT', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token, 'Content-Type' => 'muddy/sludge'), 'vwavwavwa');
    $this->assertEquals($a[0], 200);
    $this->assertEquals(count($a[1]), 1);
    $this->assertEquals(!$a[1]['Last-Modified']);
    $timestamp = $a[1]['Last-Modified'];
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $this->assertEquals(count($a[1]), 2);
    $this->assertEquals($a[1]['Last-Modified'], $timestamp);
    $this->assertEquals($a[1]['Content-Type'], 'muddy/sludge');
    $this->assertEquals($a[2], 'vwavwavwa');
    $a = MyRest::handleRequest('DELETE', 'admin', 'foo/baz', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('DELETE', 'admin', 'floo/baz', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('DELETE', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('GET', 'admin', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $this->assertEquals($a[2], '[]');
  }
}
