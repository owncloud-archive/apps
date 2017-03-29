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

require_once '../../../lib/base.php';
require_once '../lib/rest.php';


//deal with e.g. one-hour DJ sets, which can easily be 300Mb in practice:
ini_set('memory_limit', "1024M");

$requiredOrigin = OCP\Config::getAppValue('open_web_apps',  "storage_origin", '' );//set the storage origin to something else than the owncloud admin interface origin to avoid xss vulnz.
if(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])?'https://':'http://').$_SERVER['HTTP_HOST'] != $requiredOrigin) {
  die('To make your remoteStorage work on this origin, please log in to your ownCloud installation as admin, click "admin" (top right) -> Admin -> "Unhosted apps", and change "Storage origin" from "'.$requiredOrigin.'" to "'.($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].'"');
}

$verb = $_SERVER['REQUEST_METHOD'];
$uid = $_GET['user'];
$path = substr($_GET['path'], 1);
$headers = getallheaders();
$body = file_get_contents('php://input');


$response = MyRest::handleRequest($verb, $uid, $path, $headers, $body);
header('HTTP/1.1 '. $response[0]);
if($headers['Origin']) {
  $originToAllow = $headers['Origin'];
} else {
  $originToAllow = '*';
}
header('Access-Control-Allow-Origin: '.$originToAllow);
header('Access-Control-Allow-Methods: GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, ETag, If-Match, If-None-Match, Content-Type, Content-Length, Authorization');
header('Access-Control-Expose-Headers: ETag, Content-Type, Content-Length');
foreach($response[1] as $k => $v) {
  header("$k: $v");
}
echo $response[2];
