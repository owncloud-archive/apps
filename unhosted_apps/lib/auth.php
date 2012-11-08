<?php
/**
 * Copyright (c) 2012 Michiel de Jong <michiel@unhosted.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class MyAuth {
  static function addApp($uid, $manifestPath, $scopesObj) {
    $scopes = json_encode($scopesObj);
    try {
      $stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*unhosted_apps` WHERE `manifest_path` = ? AND `uid_owner` = ?' );
      $result = $stmt->execute(array($manifestPath, $uid));
    } catch(Exception $e) {
      OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      var_dump($e);
      return false;
    }
    $apps = array();
    while( $row = $result->fetchRow()) {
      $token = $row['access_token'];
      $existingScopes = $row['scopes'];
    }
    if($token) {
      if($existingScopes != $scopes) {
        try {
          $stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*unhosted_apps` SET `scopes` = ? WHERE `manifest_path` = ? AND `uid_owner` = ?' );
          $result = $stmt->execute(array($scopes, $manifestPath, $uid));
        } catch(Exception $e) {
          OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
          OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
          var_dump($e);
          return false;
        }
      }
    } else {
      $token = OC_Util::generate_random_bytes(40);
      try {
        $stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*unhosted_apps` (`manifest_path`, `access_token`, `scopes`, `uid_owner`) VALUES (?,?,?,?)' );
        $result = $stmt->execute(array($manifestPath, $token, $scopes, $uid));
      } catch(Exception $e) {
        OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
        OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
        var_dump($e);
        return false;
      }
    }
    return $token;
  }
  static function getApps($uid) {
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
      $apps[$row['access_token']] = array(
        'manifestPath' => $row['manifest_path'],
        'scopes' => json_decode($row['scopes'], true)
      );
    }
    return $apps;
  }
  static function removeApp($uid, $token) {
    try {
      $stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*unhosted_apps` WHERE `uid_owner` = ? AND `access_token` = ?' );
      $result = $stmt->execute(array($uid, $token));
    } catch(Exception $e) {
      OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('unhosted_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      return false;
    }
  }

}
