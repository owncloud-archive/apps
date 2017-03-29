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
  OCP\JSON::checkAppEnabled('open_web_apps');
  OCP\JSON::callCheck();

  $uid = OCP\USER::getUser();
  try {
	$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*open_web_apps` WHERE `uid_owner` = ? AND `app_id` = ?' );
  	$result = $stmt->execute(array($uid, $params['id']));
  } catch(Exception $e) {
    var_dump($e);
   	OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
  	OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
	  return false;
  }

  OCP\JSON::success(array());
}
handle();
