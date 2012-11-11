<?php
/**
 * Copyright (c) 2012 Michiel de Jong <michiel@unhosted.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

require_once 'storage.php';
require_once 'auth.php';

class MyRest {
  private static function may($action, $uid, $path, $headers) {
    $actionsToPerms = array(
      'l' => 'r',
      'r' => 'r',
      'w' => 'w',
      'd' => 'w'
    );
    $require_oncedPerms = $actionsToPerms[$action];
    $pathParts = explode('/', $path);
    $module = $pathParts[0];
    if($module == 'public') {
      //special case:
      if($action == 'r') {
        return true;
      }
      $module = $pathParts[1];
    }
    if(strlen($module)==0) {// access to '/' and 'public/'
      $module = 'root';
    }
    $apps = MyAuth::getApps($uid);
    $token = substr($headers['Authorization'], strlen('Bearer '));
    if((!$apps[$token]) || (!$apps[$token]['scopes']) || (!$apps[$token]['scopes'][$require_oncedPerms])) {
      return false;
    }
    return ((in_array($module, $apps[$token]['scopes'][$require_oncedPerms])) || (in_array('root', $apps[$token]['scopes'][$require_oncedPerms])));
  }
  private static function getMimeType($headers) {
    return $headers['Content-Type'];
  }
  private static function isDir($path) {
    return (substr($path, -1) == '/');
  }
  static function HandleRequest($verb, $uid, $path, $headers, $body) {
    if($verb == 'GET') {
      if(self::isDir($path)) {
        $action = 'l';
        $obj = array(
          'mimeType' => 'application/json',
          'content' => json_encode(MyStorage::getDir($uid, $path))
        );
      } else {
        $action = 'r';
        $obj = MyStorage::get($uid, $path);
      }
      if(self::may($action, $uid, $path, $headers)) {
        if(!$obj['mimeType']) {
          return array(404, array(), 'Not found');
        } else {
          return array(200, array('Content-Type' => $obj['mimeType'], 'Last-Modified' => $obj['timestamp']), $obj['content']);
        }
      } else {
        return array(401, array(), 'Computer says no');
      }
    } else if($verb == 'PUT') {
      if(self::isDir($path)) {
        return array(401, array(), 'Computer says no');
      }
      if(self::may('w', $uid, $path, $headers)) {
        $timestamp = MyStorage::store($uid, $path, self::getMimeType($headers), $body);
        return array(200, array('Last-Modified' => $timestamp), '');
      } else {
        return array(401, array(), 'Computer says no');
      }
    } else if($verb == 'DELETE') {
      if(self::isDir($path)) {
        return array(401, array(), 'Computer says no');
      }
      if(self::may('d', $uid, $path, $headers)) {
        $found = MyStorage::remove($uid, $path, self::getMimeType($headers));
        if($found) {
          return array(200, array('Last-Modified' => $timestamp), '');
        } else {
          return array(404, array(), 'Not found');
        }
      } else {
        return array(401, array(), 'Computer says no');
      }
    } else if($verb == 'OPTIONS') {
      return array(200, array(), '');
    } else {
      return array(405, array(), 'Verb not recognized');
    }
  }
}
