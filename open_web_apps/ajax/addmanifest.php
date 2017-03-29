<?php
/**
 * Copyright (c) 2012 Michiel de Jong <michiel@unhosted.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

//params:
// origin        string
// manifest_path string
// scope_map     array( module => level )

require_once('open_web_apps/lib/storage.php');
require_once('open_web_apps/lib/apps.php');
require_once('open_web_apps/lib/parser.php');

function fetchManifest($url) {
  $curl = curl_init();

  curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_USERAGENT, "ownCloud Server Crawler");
  if(OCP\Config::getSystemValue('proxy','')<>'') {
    curl_setopt($curl, CURLOPT_PROXY, OCP\Config::getSystemValue('proxy'));
  }
  if(OCP\Config::getSystemValue('proxyuserpwd','')<>'') {
    curl_setopt($curl, CURLOPT_PROXYUSERPWD, OCP\Config::getSystemValue('proxyuserpwd'));
  }
  $str = curl_exec($curl);
  curl_close($curl);
  
  try {
    $obj = json_decode($str, true);
  } catch(Exception $e) {
    OCP\JSON::error('manifest should be a JSON string please');
    exit();
  }
  return array(
    'str' => $str,
    'name' => MyParser::cleanName($obj['name']),
    'icon' => MyParser::cleanUrlPath($obj['icons']['128']),
    'launch_path' => MyParser::cleanUrlPath($obj['launch_path'])
  );
}
function handle() {
  try {
    $params = json_decode(file_get_contents('php://input'), true);
  } catch(Exception $e) {
    OCP\JSON::error('post a JSON string please');
    return;
  }
  OCP\JSON::checkLoggedIn();
  OCP\JSON::checkAppEnabled('open_web_apps');
  OCP\JSON::callCheck();

  $urlObj = MyParser::parseUrl($params['manifest_url_dirty']);
  $manifestClean = fetchManifest($urlObj['clean']);
  if($manifestClean) {
    $token = MyApps::store($urlObj['id'], $manifestClean['launch_path'],
        $manifestClean['name'], $manifestClean['icon'], $params['scope_map']);
    if($token) {
      OCP\JSON::success(array($urlObj['id']));
    } else {
      OCP\JSON::error('adding failed');
    }
  } else {
    OCP\JSON::error('fetching failed');
  }
}
handle();
