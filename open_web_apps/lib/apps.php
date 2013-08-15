<?php

require_once('open_web_apps/lib/storage.php');
require_once('open_web_apps/lib/parser.php');
require_once('open_web_apps/lib/auth.php');

class MyApps {
  public static function getScope($token) {
    try {
      $stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*remotestorage_access` WHERE `access_token` = ?' );
      $result = $stmt->execute(array($token));
    } catch(Exception $e) {
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' token: '.$token, OCP\Util::DEBUG);
      return false;
    }
    $scopesFromDb = $result->fetchAll();
    $strs = array();
    foreach($scopesFromDb as $obj) {
      $strs[] = $obj['module'].':'.$obj['level'];
    }
    return implode(' ', $strs);
  }
  public static function getApps($uid) {
    try {
      $stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*open_web_apps` WHERE `uid_owner` = ?' );
      $result = $stmt->execute(array($uid));
    } catch(Exception $e) {
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      return false;
    }
    $appsFromDb = $result->fetchAll();
    $apps = array();
    foreach($appsFromDb as $app) {
      $manifest = self::getManifest($app['app_id']);
      if($manifest) {
        $origin = MyParser::idToOrigin($app['app_id']);
        $launchUrlObj = MyParser::parseUrl($origin.$manifest['launch_path']);
        $iconUrlObj = MyParser::parseUrl($origin.$manifest['icons'][128]);//in JSON this is ['128']
        $apps[$app['app_id']] = array(
          'name' => $manifest['name'],
          'launch_url' => $launchUrlObj['clean'],
          'icon_url' => $iconUrlObj['clean'],
          'scope' => self::getScope($app['access_token']),
          'token' => $app['access_token']
        );
      }
    }
    return $apps;
  }
  public static function getManifest($id) {
    $ret = MyStorage::get(OCP\USER::getUser(), 'apps/'.$id.'/manifest.json');
    $manifest;
    try {
      $manifest = json_decode($ret['content'], true);
    } catch(Exception $e) {
    }
    $manifest['disk'] = $ret;
    return $manifest;
  }
  public static function getToken($uid, $id) {
    try {
      $stmt = OCP\DB::prepare( 'SELECT `access_token` FROM `*PREFIX*open_web_apps` WHERE `uid_owner` = ? AND `app_id` = ?' );
      $result = $stmt->execute(array($uid, $id));
    } catch(Exception $e) {
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
      OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
      return false;
    }
    $rows = $result->fetchAll();
    if(count($rows) == 0) {
      return null;
    } else {
      return $rows[0]['access_token'];
    }
  }
  public static function store($id, $launchPath, $name, $icon, $scopeMap) {
    $uid = OCP\USER::getUser();
    $token = self::getToken($uid, $id);
    if(!$token) {
      $token = base64_encode(OC_Util::generate_random_bytes(40));
      try {
        $stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*open_web_apps` (`uid_owner`, `app_id`, `access_token`) VALUES (?, ?, ?)' );
        $result = $stmt->execute(array($uid, $id, $token));
      } catch(Exception $e) {
        var_dump($e);
        OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' exception: '.$e->getMessage(), OCP\Util::ERROR);
        OCP\Util::writeLog('open_web_apps', __CLASS__.'::'.__METHOD__.' uid: '.$uid, OCP\Util::DEBUG);
        return false;
      }
      $manifestPath = 'apps/'.$id.'/manifest.json';
      MyStorage::store($uid, $manifestPath, 'application/json', false, json_encode(array(
        'launch_path' => $launchPath,
        'name' => $name,
        'icons' => array(
          '128' => $icon
        )
      ), true));
    }
    foreach($scopeMap as $module => $level) {
      MyAuth::giveAccess($token, $uid, $module, $level);
    }
    return $token;
  }
}
