<?php
/**
 * Copyright (c) 2012 Michiel de Jong <michiel@unhosted.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

require_once 'unhosted_apps/lib/storage.php';

function handle() {
  try {
    $params = json_decode(file_get_contents('php://input'), true);
  } catch(Exception $e) {
    OCP\JSON::error('post a JSON string please');
    return;
  }
  OCP\JSON::checkLoggedIn();
  OCP\JSON::checkAppEnabled('unhosted_apps');
  OCP\JSON::callCheck();

  $uid = OCP\USER::getUser();
  MyStorage::store($uid, $params['manifest_path'], 'application/json', json_encode(array(
    'launch_url' => $params['launch_url'],
    'name' => $params['name'],
    'icon' => $params['icon']
  )));    
  OCP\JSON::success(array('token'=>$token));
}
handle();
