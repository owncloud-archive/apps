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

require_once 'open_web_apps/lib/storage.php';
require_once 'open_web_apps/lib/auth.php';
require_once 'open_web_apps/lib/rest.php';

class Test_RemoteStorage extends UnitTestCase {
	function setUp() {
    $backend=new OC_User_Dummy();
    $backend->createUser('dummy','dummy');
    OC_User::clearBackends();
    OC_User::useBackend($backend);
	}

	function testStorage() {
    $timestamp = MyStorage::store('dummy', 'zapps/todomvc/manifest.json', 'application/json', false, '{"url":"http://todomvc.michiel.5apps.com"}');
    $dirList = MyStorage::getDir('dummy', 'zapps/todomvc/');
    $found = false;
    foreach($dirList as $k=>$v) {
      $this->assertEquals($k, 'manifest.json');
      $this->assertEquals($v, $timestamp);
      $found = true;
    }
    $this->assertTrue($found);
    $retrieved = MyStorage::get('dummy', 'zapps/todomvc/manifest.json');
    $this->assertEquals($retrieved['mimeType'], 'application/json');
    $this->assertEquals($retrieved['content'], '{"url":"http://todomvc.michiel.5apps.com"}');
    $timestamp = MyStorage::remove('dummy', 'zapps/todomvc/manifest.json');
    $dirList = MyStorage::getDir('dummy', 'zapps/');
    $this->assertEquals($dirList, array());
	}

  function testAuth() {
    $token = MyAuth::addApp('dummy', 'apps/todomvc/manifest.json', array('r' => array('tasks'), 'w' => array('tasks')));

    $apps = MyAuth::getApps('dummy');
    $this->assertEquals(count($apps), 1);
    $this->assertTrue($apps[$token]);//true ~ non-empty
    $this->assertEquals($apps[$token]['manifestPath'], 'apps/todomvc/manifest.json');
    $this->assertEquals(count($apps[$token]['scopes']), 2);
    $this->assertEquals(count($apps[$token]['scopes']['r']), 1);
    $this->assertEquals(count($apps[$token]['scopes']['w']), 1);
    $this->assertEquals($apps[$token]['scopes']['r'][0], 'tasks');
    $this->assertEquals($apps[$token]['scopes']['w'][0], 'tasks');

    //update perms of existing app to read-only: 
    $newToken = MyAuth::addApp('dummy', 'apps/todomvc/manifest.json', array('r' => array('tasks')));
    $this->assertEquals($token, $newToken);
    $apps = MyAuth::getApps('dummy');
    $this->assertEquals(count($apps), 1);
    $this->assertTrue($apps[$token]);//true ~ non-empty
    $this->assertEquals($apps[$token]['manifestPath'], 'apps/todomvc/manifest.json');
    $this->assertEquals(count($apps[$token]['scopes']), 1);
    $this->assertEquals(count($apps[$token]['scopes']['r']), 1);
    $this->assertEquals($apps[$token]['scopes']['r'][0], 'tasks');
    MyAuth::removeApp('dummy', $token);
    $apps = MyAuth::getApps('dummy');
    $this->assertEquals(count($apps), 0);
  }

  function testRoot() {
    $token = MyAuth::addApp('dummy', 'apps/rooter/manifest.json', array('r' => array('root'), 'w' => array('root')));
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('GET', 'dummy', 'public/foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('GET', 'dummy', 'public/foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('GET', 'dummy', 'public/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('GET', 'dummy', '/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('PUT', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('PUT', 'dummy', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('PUT', 'dummy', 'public/foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('PUT', 'dummy', 'public/foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('PUT', 'dummy', 'public/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('PUT', 'dummy', '/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'public/foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'public/foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'public/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('DELETE', 'dummy', '/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    MyAuth::removeApp('dummy', $token);
  }

  function testRest() {
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/bar', array(), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('GET', 'dummy', 'public/foo/bar', array(), '');
    $this->assertEquals($a[0], 404);
    $token = MyAuth::addApp('dummy', 'bla', array('r' => array('foo', 'bar'), 'w' => array('foo')));
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('PUT', 'dummy', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('PUT', 'dummy', 'bar/foo', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('GET', 'dummy', 'bar/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $this->assertEquals($a[1]['Content-Type'], 'application/json');
    $this->assertEquals($a[2], '[]');
    $a = MyRest::handleRequest('PUT', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token, 'Content-Type' => 'muddy/sludge'), 'vwavwavwa');
    $this->assertEquals($a[0], 200);
    $this->assertEquals(count($a[1]), 1);
    $this->assertEquals(!$a[1]['Last-Modified']);
    $timestamp = $a[1]['Last-Modified'];
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $this->assertEquals(count($a[1]), 2);
    $this->assertEquals($a[1]['Last-Modified'], $timestamp);
    $this->assertEquals($a[1]['Content-Type'], 'muddy/sludge');
    $this->assertEquals($a[2], 'vwavwavwa');
    $a = MyRest::handleRequest('DELETE', 'dummy', 'foo/baz', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'floo/baz', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 401);
    $a = MyRest::handleRequest('DELETE', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 404);
    $a = MyRest::handleRequest('GET', 'dummy', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    $this->assertEquals($a[0], 200);
    $this->assertEquals($a[2], '[]');
    MyAuth::removeApp('dummy', $token);
  }
}
