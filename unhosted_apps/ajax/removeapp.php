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
  $token = base64_encode(openssl_random_pseudo_bytes(40));
  //var_dump($params);
  try {
	  $stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*unhosted_apps` WHERE `uid_owner` = ? AND `access_token` = ?' );
  	$result = $stmt->execute(array($uid, $params['token']));
  } catch(Exception $e) {
    var_dump($e);
   	OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
  	OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
	  return false;
  }

  OCP\JSON::success(array());
}
handle();
