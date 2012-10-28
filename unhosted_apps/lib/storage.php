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
    $appid = 'unhosted_apps';
    $view = new OC_FilesystemView('/'.$uid);
		if(!$view->file_exists($appid)) {
	   $view->mkdir($appid);
		}
		return new OC_FilesystemView('/'.$uid.'/'.$appid);
  }
  static function getDir($uid, $path) {
    $view = self::getView($uid);
    $handle=$view->opendir($path);
    $res = array();
    while (false !== ($entry = readdir($handle))) {
      if($entry==null) {
        return $res;
      }
      if($entry != '.' && $entry != '..') {
        if($view->is_dir($path.$entry)) {
          $res[$entry.'/'] = $view->filemtime($path.$entry);
        } else {
          $res[$entry] = $view->filemtime($path.$entry);
        }
      }
    }
    closedir($handle);
    return $res;
  }
  
  static function get($uid, $path) {
    $view = self::getView($uid);
    $contents = $view->file_get_contents($path);
    $firstNewLine = strpos($contents, "\n");
    return array(
      'content' => substr($contents, $firstNewLine+1),
      'mimeType' => substr($contents, 0, $firstNewLine),
      'timestamp' => $view->filemtime($path)
    );
  }
  
  static function store($uid, $path, $mimeType, $contents) {
    $view = self::getView($uid);
    $pathParts = explode('/', $path);
    for($i=1; $i<count($pathParts); $i++) {
      $view->mkdir(implode('/', array_slice($pathParts, 0, $i)));
    }
    $view->file_put_contents($path, $mimeType."\n".$contents);
    return $view->filemtime($path);
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
  static function remove($uid, $path) {
    $view = self::getView($uid);
    $stat = $view->stat($path);
    if(!$stat) {
      return false;
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
    return true;
  }
}
