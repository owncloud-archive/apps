<?php
/**
 * Copyright (c) 2012 Michiel de Jong <michiel@unhosted.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
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
  try {
    $stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*unhosted_apps` WHERE `uid_owner` = ? AND `manifest_path` = ? AND `scopes` = ?' );
    $result = $stmt->execute(array($uid, $params['manifest_path'], $params['scopes']));
  } catch(Exception $e) {
    OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
    OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
    return false;
  }

  $apps = $result->fetchAll();
  if(count($apps)) {
    $token = $apps[0]['access_token'];
  } else {
    $token = base64_encode(OC_Util::generate_random_bytes(40));
    //var_dump($params);
    try {
      $stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*unhosted_apps` (`uid_owner`, `manifest_path`, `access_token`, `scopes`) VALUES (?, ?, ?, ?)' );
      $result = $stmt->execute(array($uid, $params['manifest_path'], $token, $params['scopes']));
    } catch(Exception $e) {
      var_dump($e);
      OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      return false;
    }
  }
  OCP\JSON::success(array('token'=>$token));
}
handle();
