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
class MyStorage {
  private static function getView($uid) { 
    $appid = 'open_web_apps';
    $view = new OC_FilesystemView('/'.$uid);
		if(!$view->file_exists($appid)) {
	   $view->mkdir($appid);
		}
		return new OC_FilesystemView('/'.$uid.'/'.$appid);
  }
  static function getDir($uid, $path) {
    $view = self::getView($uid);
    $res = array();
    $timestamp = '0';
		if($view->file_exists($path)) {
      $timestamp = strval($view->filemtime($path));
      $handle=$view->opendir($path);
      while (false !== ($entry = readdir($handle))) {
        if($entry==null) {
          break;
        }
        if($entry != '.' && $entry != '..') {
          if($view->is_dir($path.$entry)) {
            $res[$entry.'/'] = strval($view->filemtime($path.$entry));
          } else {
            $res[$entry] = strval($view->filemtime($path.$entry));
          }
        }
      }
      closedir($handle);
    }
    return array(
      'timestamp' => $timestamp,
      'mimeType' => (count($res)?'application/json':null),//null mimetype will trigger 404 response
      'content' => json_encode($res)
    );
  }
  
  static function get($uid, $path) {
    $view = self::getView($uid);
		if($view->file_exists($path)) {
      $contents = $view->file_get_contents($path);
      try {
        $mimeType = @xattr_get(OCP\Config::getSystemValue( "datadirectory", OC::$SERVERROOT.'/data' ).$view->getAbsolutePath($path), 'Content-Type');
      } catch(Exception $e) {
      }
      if(!$mimeType) {
        $mimeType = 'application/octet-stream';
      }
      return array(
        'content' => $contents,
        'mimeType' => $mimeType,
        'timestamp' => $view->filemtime($path)
      );
    } else {
      return array();
    }
  }
  static function store($uid, $path, $mimeType, $matchThis, $contents) {
    $view = self::getView($uid);
    $pathParts = explode('/', $path);
    for($i=1; $i<count($pathParts); $i++) {
      $view->mkdir(implode('/', array_slice($pathParts, 0, $i)));
    }
    if($matchThis===null) {//If-None-Match: *
      if($view->file_exists($path)) {
        return array('match'=>false);
      }
    } else if($matchThis) {
      if(!$view->file_exists($path)
          || $matchThis !== strval($view->filemtime($path))) {
        return array('match'=>false);
      }
    } // else no If-Match or If-None-Match header was sent

    $view->file_put_contents($path, $contents);
    @xattr_set(OCP\Config::getSystemValue( "datadirectory", OC::$SERVERROOT.'/data' ).$view->getAbsolutePath($path), 'Content-Type', $mimeType);
    return array('match' => true, 'timestamp' => $view->filemtime($path));
  }

  private static function getContainingDir($path) {
    $path = '/'.$path;
    if($path=='/' || substr($path, 0, 1) != '/') {
      return null;
    }
    $pathParts = explode('/', $path);
    if(strlen($pathParts[count($pathParts)-1])==0) {// /foo/ -> /
      $chop=2;
    } else { // /foo -> /
      $chop=1;
    }
    return substr(implode('/', array_slice($pathParts, 0, count($pathParts)-$chop)).'/', 1);
  }
  static function remove($uid, $path, $matchThis) {
    $view = self::getView($uid);
    if(!$view->file_exists($path)) {
      return false;
    }
    $timestamp = strval($view->filemtime($path));
    if($matchThis && $matchThis != $timestamp) {
      return array('match'=>false, 'timestamp'=>$timestamp);
    }
    $view->unlink($path);
    $path = self::getContainingDir($path);
    while($path != null) {
      $containingDirList = self::getDir($path);
      if($containingDirList==array()) {
        $view->rmdir($path);
      } else {
         break;
      }
      $path = self::getContainingDir($path);
    }
    return array('match' => true, 'timestamp' => $timestamp);
  }
}
