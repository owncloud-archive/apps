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

require_once 'open_web_apps/lib/apps.php';
require_once 'open_web_apps/lib/parser.php';

function calcScopeDiff($existingScope, $addingScope) {
  $clean = MyParser::parseScope($existingScope);//this shouldn't be necessary if the db is clean
  $existingScope = $clean['normalized'];
  $newScopeObj = MyParser::parseScope($existingScope.' '.$addingScope);
  if($newScopeObj['normalized'] == $existingScope) {
    return false;
  } else {
    return $newScopeObj;
  }
}

function checkForAdd($apps) {
  $params = array();
  $paramStrs = explode('&', $_SERVER['QUERY_STRING']);
  foreach($paramStrs as $str) {
    $parts = explode('=', $str);
    if(count($parts) == 2) {
      $params[urldecode($parts[0])] = urldecode($parts[1]);
    }
  }
  if(isset($params['redirect_uri']) && isset($params['scope'])) {
    $urlObj = MyParser::parseUrl($params['redirect_uri']);
    $appId = $urlObj['id'];
    if(isset($apps[$appId])) {
      $scopeDiff = calcScopeDiff($apps[$appId]['scope'], $params['scope']);
      if($scopeDiff) {
        return array(
          'scope_diff_id' => $appId,
          'scope_diff_add' => $scopeDiff
        );
      } else {
        return array( 'launch_app' => $appId );
      }
    } else {
      return array(
        'adding_id' => $appId,
        'adding_launch_url' => $urlObj['clean'],
        'adding_name' => MyParser::cleanName($params['client_id']),
        'adding_scope' => MyParser::parseScope($params['scope'])//scope.normalized and scope.human will only contain [a-zA-Z0-9%\-_\.] and spaces
      );
    }
  } else {//no app to add
    return array();
  }
}

//...
OCP\User::checkLoggedIn();
$uid = OCP\USER::getUser();
$apps = MyApps::getApps($uid);
$storage_origin = OCP\Config::getAppValue('open_web_apps',  "storage_origin", '' );
OCP\App::setActiveNavigationEntry( 'open_web_apps' );
$tmpl = new OCP\Template( 'open_web_apps', 'main', 'user' );
$adds = checkForAdd($apps);
foreach($adds as $k => $v) {
  $tmpl->assign($k, $v);
}
$tmpl->assign( 'user_address', $uid.'@'.$_SERVER['SERVER_NAME'] );
$tmpl->assign( 'uid', $uid );
$tmpl->assign( 'storage_origin', $storage_origin );
$tmpl->assign( 'apps', $apps );
$tmpl->printPage();
