<?php

/**
* ownCloud - Unhosted apps Example
*
* @author Frank Karlitschek
* @author Florian Hülsmann
* @copyright 2011 Frank Karlitschek karlitschek@kde.org
* @copyright 2012 Florian Hülsmann fh@cbix.de
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
// Check if we are a user
OCP\User::checkLoggedIn();

require_once 'lib/storage.php';
require_once 'lib/auth.php';
require_once 'lib/rest.php';

$tests = array(
  'storage' => function() {//simple 'store, getDir, get, remove' cycle.
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
    if(!$found) {
      return 'not found in dir list';
    }
    $retrieved = MyStorage::get('admin', 'zapps/todomvc/manifest.json');
    if($retrieved['mimeType'] != 'application/json') {
      return 'mimeType wrong';
    }
    if($retrieved['content'] != '{"url":"http://todomvc.michiel.5apps.com"}') {
      return 'wrong content';
    }
    $timestamp = MyStorage::remove('admin', 'zapps/todomvc/manifest.json');
    $dirList = MyStorage::getDir('admin', 'zapps/');
    foreach($dirList as $k=>$v) {
      return "residue $k $v";
    }
    return true;
  },
  'auth' => function() {//simple 'add, list, update, remove' cycle.
    $token = MyAuth::addApp('admin', 'apps/todomvc/manifest.json', array('r' => array('tasks'), 'w' => array('tasks')));

    $apps = MyAuth::getApps('admin');
    if(count($apps)!=1) {
      return 'not one app';
    }
    if(!$apps[$token]) {
      return 'wrong token';
    }
    if($apps[$token]['manifestPath']!='apps/todomvc/manifest.json') {
      return 'wrong app';
    }
    if(count($apps[$token]['scopes'])!=2) {
      return 'not two scopes';
    }
    if(count($apps[$token]['scopes']['r'])!=1) {
      return 'wrong number of read scopes';
    }
    if(count($apps[$token]['scopes']['w'])!=1) {
      return 'wrong number of write scopes';
    }
    if($apps[$token]['scopes']['r'][0]!='tasks') {
      return 'wrong read scope';
    }
    if($apps[$token]['scopes']['w'][0]!='tasks') {
      return 'wrong write scope';
    }
    $token = MyAuth::addApp('admin', 'apps/todomvc/manifest.json', array('r' => array('tasks')));
    $apps = MyAuth::getApps('admin');
    if(count($apps)!=1) {
      return 'not one app *';
    }
    if(!$apps[$token]) {
      return 'wrong token *';
    }
    if($apps[$token]['manifestPath']!='apps/todomvc/manifest.json') {
      return 'wrong app *';
    }
    if(count($apps[$token]['scopes'])!=1) {
      return 'not one scope *';
    }
    if(count($apps[$token]['scopes']['r'])!=1) {
      return 'wrong number of read scopes *';
    }
    if($apps[$token]['scopes']['r'][0]!='tasks') {
      return 'wrong read scope *';
    }
    MyAuth::removeApp('admin', $token);
    $apps = MyAuth::getApps('admin');
    if(count($apps) != 0) {
      return 'residue';
    }
    return true;
  },
  'rest' => function() {
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array(), '');
    if($a[0] != 401) {
      return 'should not have access to foo/bar without token';
    }
    $a = MyRest::handleRequest('GET', 'admin', 'public/foo/bar', array(), '');
    if($a[0] != 404) {
      return 'should have access to public/foo/bar without token';
    }
    $token = MyAuth::addApp('admin', 'bla', array('r' => array('foo', 'bar'), 'w' => array('foo')));
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 404) {
      return 'should have access to foo/bar with token';
    }
    $a = MyRest::handleRequest('PUT', 'admin', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 401) {
      return 'should not have write access to foo/';
    }
    $a = MyRest::handleRequest('PUT', 'admin', 'bar/foo', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 401) {
      return 'should not have write access to bar/foo';
    }
    $a = MyRest::handleRequest('GET', 'admin', 'bar/', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 200) {
      return 'should have access to bar/ with token';
    }
    if($a[1]['Content-Type'] != 'application/json') {
      return 'dir list should be application/json';
    }
    if($a[2] != '[]') {
      return 'bar/ should be empty';
    }
    $a = MyRest::handleRequest('PUT', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token, 'Content-Type' => 'muddy/sludge'), 'vwavwavwa');
    if($a[0] != 200) {
      return 'should have write access to foo/bar';
    }
    if(count($a[1]) != 1) {
      return 'was expecting 1 response header';
    }
    if(!$a[1]['Last-Modified']) {
      return 'was expecting a Last-Modified header back';
    }
    $timestamp = $a[1]['Last-Modified'];
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 200) {
      return 'should have access to foo/bar with token';
    }
    if(count($a[1]) != 2) {
      return 'was expecting 2 response headers';
    }
    if($a[1]['Last-Modified'] != $timestamp) {
      return 'timestamp not matched';
    }
    if($a[1]['Content-Type'] != 'muddy/sludge') {
      return 'Content-Type not matched';
    }
    if($a[2] != 'vwavwavwa') {
      return 'content not matched';
    }
    $a = MyRest::handleRequest('DELETE', 'admin', 'foo/baz', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 404) {
      return 'should 404 on foo/baz deletion';
    }
    $a = MyRest::handleRequest('DELETE', 'admin', 'floo/baz', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 401) {
      return 'should 401 on floo/baz deletion';
    }
    $a = MyRest::handleRequest('DELETE', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 200) {
      return 'should have delete access to foo/bar';
    }
    $a = MyRest::handleRequest('GET', 'admin', 'foo/bar', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 404) {
      return 'should remove foo/bar';
    }
    $a = MyRest::handleRequest('GET', 'admin', 'foo/', array('Authorization' => 'Bearer '.$token), '');
    if($a[0] != 200) {
      return 'should let us list foo/';
    }
    if($a[2] != '[]') {
      return 'should remove bar from foo/';
    }
    return true;
  }
);
foreach($tests as $k => $v) {
  $res = $v();
  if($res === true) {
    echo "$k - PASS<br>";
  } else {
    echo "$k - FAIL: $res<br>";
  }
}

//tear down:
$apps = MyAuth::getApps('admin');
foreach($apps as $k => $v) {
  echo "tear down app $k<br>";
  MyAuth::removeApp('admin', $k);
}
function recRemove($path) {
  if(substr($path,-1)=='/') {
    $docs = MyStorage::getDir('admin', $path);
    foreach($docs as $k => $v) {
      echo "in dir $path there is $k<br>";
      recRemove($path.$k);
    }
  } else {
    echo "tear down doc $path<br>";
    MyStorage::remove('admin', $path);
  }
}
recRemove('/');
