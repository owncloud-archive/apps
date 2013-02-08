<?php
class MyParser {
  public static function toHuman($map) {
    $items = array();
    foreach($map as $module => $level) {
      if($module == 'root') {
        $thing = 'everything';
      } else {
        $thing = 'your '.$module;
      }
      if($level == 'r') {
        $items[] = 'read-only access to '.$thing;
      } else {
        $items[] = 'full access to '.$thing;
      }
    }
    if(count($items) == 0) {
      return 'no access to anything';
    } else if(count($items) == 1) {
      return $items[0];
    } else if(count($items) == 2) {
      return $items[0].' and '.$items[1];
    } else {
      $str = '';
      for($i = 0; $i<count($items)-1; $i++) {
        $str .= $items[$i].', ';
      }
      return $str.' and '.$items[count($items)-1];
    }
  }
  public static function parseUrl($dirty) {
    $parts = explode('/', $dirty);
    if(count($parts)<4) {
      return array(null, null);
    }
    if($parts[0] == 'http:') {
      $protocol = 'http';
    } else if($parts[0] == 'https:') {
      $protocol = 'https';
    } else {
      return array(null, null);
    }
    if($parts[1] != '') {
      return array(null, null);
    }
    $hostParts = explode(':', $parts[2]);
    $hostName = ereg_replace('[^a-zA-Z0-9\-\.]', '', $hostParts[0]);
    if(count($hostParts) == 2) {
      $hostPort = ereg_replace('[^0-9]', '', $hostParts[1]);
    } else if(count($hostParts) == 1) {
      $hostPort = ($protocol == 'https:' ? '443' : '80');
    } else {
      return array();
    }
    $ret = array(
      'protocol' => $protocol,
      'host' => $hostName,
      'port' => $hostPort,
      'path' => '/'.ereg_replace('[<\']', '', implode('/', array_slice($parts, 3))),
    );
    $ret['id'] = $ret['protocol'].'_'.$ret['host'].'_'.$ret['port'];
    $ret['clean'] = $ret['protocol'].'://'.$ret['host'].':'.$ret['port'].$ret['path'];
    return $ret;
  }
  public static function parseScope($scope) {
    $map = array();
    $parts = explode(' ', $scope);
    foreach($parts as $str) {
      $moduleAndLevel = explode(':', $str);
      if(count($moduleAndLevel)==2 && in_array($moduleAndLevel[1], array('r', 'rw'))) {
        //https://tools.ietf.org/id/draft-dejong-remotestorage-00.txt, section 4:
        //Item names MAY contain a-z, A-Z, 0-9, %,  -, _.
        //Note: we should allow '.' too in remotestorage-01.
        //Allowing it here as an intentional violation:
        $moduleName = ereg_replace('[^a-zA-Z0-9%\-_\.]', '', $moduleAndLevel[0]); 
        if(strlen($moduleName)>0 && $map[$moduleName] != 'rw') {//take the strongest one
          $map[$moduleName] = $moduleAndLevel[1];
        }
      }
    }
    //root:rw is almighty and cannot coexist with other scopes:
    if($map['root'] == 'rw') {
      $map = array('root' => 'rw');
    }
    //root:r cannot coexist with other 'r' scopes:
    if($map['root'] == 'r') {
      foreach($map as $module => $level) {
        if($module != 'root' && $level == 'r') {
          unset($map[$module]);
        }
      }
    }
    $reassembleParts = array();
    foreach($map as $module => $level) {
      $reassembleParts[] = $module.':'.$level;
    }
    sort($reassembleParts);
    return array(
      'map' => $map,
      'normalized' => implode(' ', $reassembleParts),
      'human' => self::toHuman($map)
    );
  }
  public static function idToOrigin($id) {
    $parts = explode('_', $id);
    return $parts[0].'://'.$parts[1].':'.$parts[2];
  }
  public static function cleanName($dirty) {
    return ereg_replace('[^a-zA-Z0-9%\-_\.]', '', $dirty); 
  }
  public static function cleanUrlPath($dirty) {
    return ereg_replace('[^a-zA-Z0-9%\-_\.\/]', '', $dirty); 
  }
}
