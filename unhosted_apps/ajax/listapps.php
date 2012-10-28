<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('unhosted_apps');

$uid = OCP\USER::getUser();
try {
	$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*unhosted_apps` WHERE `uid_owner` = ?' );
	$result = $stmt->execute(array($uid));
} catch(Exception $e) {
	OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
	OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
	return false;
}

$apps = array();
while( $row = $result->fetchRow()) {
	$apps[] = $row;
}
OCP\JSON::success(array('apps'=>$apps));
