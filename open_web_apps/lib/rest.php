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
  private static function parsePath($path) {
    $underPublic = false;
    if(substr($path, 0, strlen('public/')) == 'public/') {
      $path = substr($path, strlen('public/'));
      $underPublic = true;
    }
    if($path == '') {
      return array(
        'modules' => array('root'),
        'under_public' => $underPublic
      );
    } else {
      $parts = explode('/', $path);
      if($parts[0] == '') {
        return false;//empty module name not permitted
      } else {
        return array(
          'modules' => array('root', $parts[0]),
          'under_public' => $underPublic
        );
      }
    }
  }
  private static function may($action, $uid, $path, $headers) {
    $obj = self::parsePath($path);
    if($obj['under_public'] && $action == 'r') {
      return true;//'r'eading anything under /public/ requires no token
    }
    $token = substr($headers['Authorization'], strlen('Bearer '));
    if($action == 'l' || $action == 'r') {
      return MyAuth::hasOneOf($token, $uid, $obj['modules'], array('r', 'rw'));
    } else {
      return MyAuth::hasOneOf($token, $uid, $obj['modules'], array('rw'));
    }
  }
  private static function getMimeType($headers) {
    return $headers['Content-Type'];
  }
  private static function getIfMatch($headers) {
    if($headers['If-None-Match'] == '*') {
      return null;
    } else if($headers['If-Match']) {
      return $headers['If-Match'];
    } else {
      return array();//something that is not null and not a string
    }
  }
  private static function isDir($path) {
    return ($path=='' || substr($path, -1) == '/');
  }
  static function HandleRequest($verb, $uid, $path, $headers, $body) {
    if($verb == 'GET') {
      if(self::may((self::isDir($path)?'l':'r'), $uid, $path, $headers)) {
        if(self::isDir($path)) {
          $obj = MyStorage::getDir($uid, $path);
        } else {
          $obj = MyStorage::get($uid, $path);
        }
        if($obj['mimeType']) {
          $matchers = explode(',', $headers['If-None-Match']);
          foreach($matchers as $m) {
            if($m == strval($obj['timestamp'])) {
//              return array(304, array('ETag' => strval($obj['timestamp'])));
            }
          }
          return array(200, array('Content-Type' => $obj['mimeType'], 'ETag' => strval($obj['timestamp'])), $obj['content']);
        } else {
          return array(404, array(), 'Not found');
        }
      } else {
        return array(401, array(), 'Computer says no');
      }
    } else if($verb == 'PUT') {
      if(self::isDir($path)) {
        return array(401, array(), 'Computer says no');
      } else {
        if(self::may('w', $uid, $path, $headers)) {
          //todo: check for If-Match header
          $res = MyStorage::store($uid, $path, self::getMimeType($headers), self::getIfMatch($headers), $body);
         return array($res['match']?200:412, array('ETag' => strval($res['timestamp'])), '');
         } else {
          return array(401, array(), 'Computer says no');
        }
      }
    } else if($verb == 'DELETE') {
      if(self::isDir($path)) {
        return array(401, array(), 'Computer says no');
      } else {
        if(self::may('d', $uid, $path, $headers)) {
          $res = MyStorage::remove($uid, $path, self::getIfMatch($headers));
          if($res['timestamp']) {
            return array($res['match']?200:412, array('ETag' => $res['timestamp']), '');
          } else {
            return array(404, array(), 'Not found');
          }
        } else {
          return array(401, array(), 'Computer says no');
        }
      }
    } else if($verb == 'OPTIONS') {
      return array(200, array(), '');
    } else {
      return array(405, array(), 'Verb not recognized');
    }
  }
}
