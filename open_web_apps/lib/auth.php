<?php
/**
 * Copyright (c) 2012 Michiel de Jong <michiel@unhosted.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class MyAuth {
  public static function giveAccess($token, $uid, $module, $level) {
    try {
      $stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*remotestorage_access` (`access_token`, `uid_owner`, `module`, `level`) VALUES (?, ?, ?, ?)' );
      $result = $stmt->execute(array($token, $uid, $module, $level));
    } catch(Exception $e) {
      error_log(var_export($e, true));
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      return false;
    }
  }

  public static function hasOneOf($token, $uid, $modules, $levels) {
    try {
      $stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*remotestorage_access` WHERE `access_token` = ? AND `uid_owner` = ?' );
      $result = $stmt->execute(array($token, $uid));
    } catch(Exception $e) {
      var_dump($e);
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      return false;
    }
    $rows = $stmt->fetchAll();
    foreach($rows as $row) {
      if(in_array($row['module'], $modules) && in_array($row['level'], $levels)) {
        return true;
      }
    }
    return false;
  }
}
