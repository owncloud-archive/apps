<?php

OC_App::loadApp('mozilla_sync');
class Test_StorageService extends PHPUnit_Framework_TestCase {

  private $userName = 'testUser';
  private $userHash = 'qwegffggh';
  private $password = 'testPassword';
  private $email    = 'testUser@owncloud.org';


  public function setUp() {

    OCA_mozilla_sync\Utils::setTestState();

    // Create ownCloud Test User
    OC_User::createUser($this->userName, $this->password);
    OC_User::setUserId($this->userName);
    OC_Preferences::setValue($this->userName,'settings', 'email', $this->email);

    OCA_mozilla_sync\OutputData::$outputFlag = OCA_mozilla_sync\OutputData::ConstOutputBuffer;
    OCA_mozilla_sync\Utils::setTestState();

    OCA_mozilla_sync\User::createUser($this->userHash, $this->password, $this->email);
  }

  public function tearDown() {

    $userId = OCA_mozilla_sync\User::userHashToId($this->userHash);
    if($userId != false) {
      OCA_mozilla_sync\Storage::deleteStorage($userId);
      OCA_mozilla_sync\User::deleteUser($userId);
    }

    OC_Preferences::deleteUser($this->userName);
    OC_User::deleteUser($this->userName);
  }

  public function test_BasicScenario() {

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === "[]\n");

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_NOT_FOUND, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

    //
    // post collection
    //
    $this->clearRequest();
    OCA_mozilla_sync\Utils::$requestMethod = 'POST';
    $inputArray = array();
    $inputArray[] = array('payload' => 'payload1',
                          'id' => 'history1',
                          'sortindex' => 1,
                          'ttl' => 5,
                          'parentid' => 3,
                          'predecessorid' => '123');
    $inputArray[] = array('payload' => 'payload2',
                          'id' => 'history2',
                          'sortindex' => 2,
                          'ttl' => 5,
                          'parentid' => 3,
                          'predecessorid' => '123');
    $inputArray[] = array('payload' => 'payload3',
                          'id' => 'history3',
                          'sortindex' => 3,
                          'ttl' => 5,
                          'parentid' => 3,
                          'predecessorid' => '123');
    $inputData = new OCA_mozilla_sync\InputData(json_encode($inputArray));
    $this->request('/1.1/' . $this->userHash . '/storage/history/', $inputData);
    $result = '{"modified":' . OCA_mozilla_sync\Utils::getMozillaTimestamp() . ',"success":["history1","history2","history3"],"failed":[]}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?full=1');
    $result = array();
    $result[] = '{"payload":"payload1","id":"history1","modified":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp() .'","parentid":"3","predecessorid":"123","sortindex":"1","ttl":"5"}';
    $result[] = '{"payload":"payload2","id":"history2","modified":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp() .'","parentid":"3","predecessorid":"123","sortindex":"2","ttl":"5"}';
    $result[] = '{"payload":"payload3","id":"history3","modified":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp() .'","parentid":"3","predecessorid":"123","sortindex":"3","ttl":"5"}';
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $result = '{"history":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp().'"}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);

    //
    // delete wbo
    //
    $this->clearRequest();
    OCA_mozilla_sync\Utils::$requestMethod = 'DELETE';
    $this->request('/1.1/' . $this->userHash . '/storage/history/history1');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strval(OCA_mozilla_sync\Utils::getMozillaTimestamp()));

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?full=1');
    $result = array();
    $result[] = '{"payload":"payload2","id":"history2","modified":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp() .'","parentid":"3","predecessorid":"123","sortindex":"2","ttl":"5"}';
    $result[] = '{"payload":"payload3","id":"history3","modified":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp() .'","parentid":"3","predecessorid":"123","sortindex":"3","ttl":"5"}';
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

    //
    // get wbo
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/bookmarks/bookmark1');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_NOT_FOUND, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $result = '{"history":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp().'"}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);

    //
    // put wbo
    //
    $this->clearRequest();
    OCA_mozilla_sync\Utils::$requestMethod = 'PUT';
    $inputArray = array('payload' => 'bookmarkpayload',
                        'id' => 'bookmark1',
                        'sortindex' => 1,
                        'ttl' => 5,
                        'parentid' => 3,
                        'predecessorid' => '123');
    $inputData = new OCA_mozilla_sync\InputData(json_encode($inputArray));
    $this->request('/1.1/' . $this->userHash . '/storage/bookmarks/bookmark1', $inputData);
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strval(OCA_mozilla_sync\Utils::getMozillaTimestamp()));

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $result = array();
    $result[] = '"history":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp().'"';
    $result[] = '"bookmarks":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp().'"';
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

    //
    // get wbo
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/bookmarks/bookmark1');
    $result = '{"sortindex":"1","payload":"bookmarkpayload","id":"bookmark1","modified":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp().'"}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);

    //
    // delete collection
    //
    $this->clearRequest();
    OCA_mozilla_sync\Utils::$requestMethod = 'DELETE';
    $this->request('/1.1/' . $this->userHash . '/storage/bookmarks');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strval(OCA_mozilla_sync\Utils::getMozillaTimestamp()));

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $result = '{"history":"'.OCA_mozilla_sync\Utils::getMozillaTimestamp().'"}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);

    //
    // delete storage
    //
    $this->clearRequest();
    $_SERVER['HTTP_X_CONFIRM_DELETE'] = '1';
    OCA_mozilla_sync\Utils::$requestMethod = 'DELETE';
    $this->request('/1.1/' . $this->userHash . '/storage/');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strval(OCA_mozilla_sync\Utils::getMozillaTimestamp()));

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '[]'."\n");

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?full=1');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_NOT_FOUND, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/bookmarks');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_NOT_FOUND, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

  }

  public function test_Modifiers() {

    $currentTime = OCA_mozilla_sync\Utils::getMozillaTimestamp();
    $testTime[] = $currentTime + 1000;
    $testTime[] = $currentTime + 20;
    $testTime[] = $currentTime + 346;

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === "[]\n");

    //
    // post collection
    //
    $id = 0;
    foreach($testTime as $time) {
      $this->clearRequest();
      OCA_mozilla_sync\Utils::$testTime = $time;
      OCA_mozilla_sync\Utils::$requestMethod = 'POST';
      $inputArray = array();
      $successString = '';
      for($i = 0; $i < 10; $i++) {
        $inputArray[] = array('payload' => 'payload' . $id,
            'id' => 'history' . $id,
            'sortindex' => $id + 1,
            'ttl' => 100 * $id);
        if($i > 0) {
          $successString .= ',';
        }
        $successString .= '"history' . $id . '"';

        $id++;
      }
      $inputData = new OCA_mozilla_sync\InputData(json_encode($inputArray));
      $this->request('/1.1/' . $this->userHash . '/storage/history/', $inputData);
      $result = '{"modified":' . OCA_mozilla_sync\Utils::getMozillaTimestamp() . ',"success":[' . $successString . '],"failed":[]}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
      $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);
    }

    OCA_mozilla_sync\Utils::$testTime = $currentTime;

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $result = array();
    $result[] = '"history":"'.$testTime[0].'"';
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?ids=history0,history2,history19,history110');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $result = array();
    $result[] = '"history0"';
    $result[] = '"history2"';
    $result[] = '"history19"';
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));
    $result = array('"history110"');
    $this->assertTrue($this->outputNotContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?index_above=3&sort=index&limit=4&offset=2');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '["history27","history26","history25","history24"]'. "\n");

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?index_above=3&sort=index&limit=4&offset=50');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_NOT_FOUND, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?index_below=1');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '["history0"]' . "\n");

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?older='. $testTime[1] . '&sort=oldest');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $result = array();
    for($i = 10; $i < 20; $i++) {
      $result[] = '"history' . $i . '"';
    }
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));
    $result = array();
    for($i = 0; $i < 10; $i++) {
      $result[] = '"history' . $i . '"';
    }
    for($i = 20; $i < 30; $i++) {
      $result[] = '"history' . $i . '"';
    }
    $this->assertTrue($this->outputNotContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));


    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?newer='. $testTime[2] . '&sort=newest');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);

    $result = array();
    for($i = 0; $i < 10; $i++) {
      $result[] = '"history' . $i . '"';
    }
    for($i = 20; $i < 30; $i++) {
      $result[] = '"history' . $i . '"';
    }
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));
    $result = array();
    for($i = 10; $i < 20; $i++) {
      $result[] = '"history' . $i . '"';
    }
    $this->assertTrue($this->outputNotContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

    //
    // delete collection
    //
    $this->clearRequest();
    OCA_mozilla_sync\Utils::$requestMethod = 'DELETE';
    $this->request('/1.1/' . $this->userHash . '/storage/history?older='. $testTime[1]);
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strval(OCA_mozilla_sync\Utils::getMozillaTimestamp()));

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?older='. $testTime[1]. '&sort=oldest');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_NOT_FOUND, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

    //
    // get collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/storage/history?newer='. $testTime[2]. '&sort=newest');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);

    $result = array();
    for($i = 0; $i < 10; $i++) {
      $result[] = '"history' . $i . '"';
    }
    for($i = 20; $i < 30; $i++) {
      $result[] = '"history' . $i . '"';
    }
    $this->assertTrue($this->outputContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));
    $result = array();
    for($i = 10; $i < 20; $i++) {
      $result[] = '"history' . $i . '"';
    }
    $this->assertTrue($this->outputNotContainInput($result, OCA_mozilla_sync\OutputData::$outputBuffer));

  }

  public function test_DeleteOldWbo() {

    $currentTime = OCA_mozilla_sync\Utils::getMozillaTimestamp();
    $testTime = $currentTime - 1000;

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === "[]\n");

    //
    // post collection
    //
    $this->clearRequest();
    OCA_mozilla_sync\Utils::$testTime = $testTime;
    OCA_mozilla_sync\Utils::$requestMethod = 'POST';
    $successString = '';
    $inputArray = array();
    for($i = 0; $i < 10; $i++) {
      $inputArray[] = array('payload' => 'payload' . $i,
          'id' => 'history' . $i,
          'sortindex' => $i + 1,
          'ttl' => 100);
      if($i > 0) {
        $successString .= ',';
      }
      $successString .= '"history' . $i . '"';
    }
    $inputData = new OCA_mozilla_sync\InputData(json_encode($inputArray));
    $this->request('/1.1/' . $this->userHash . '/storage/history/', $inputData);
    $result = '{"modified":' . OCA_mozilla_sync\Utils::getMozillaTimestamp() . ',"success":[' . $successString . '],"failed":[]}'."\n";
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === $result);

    OCA_mozilla_sync\Utils::$testTime = $currentTime;

    //
    // info collection
    //
    $this->clearRequest();
    $this->request('/1.1/' . $this->userHash . '/info/collections/');
    $this->assertEquals(OCA_mozilla_sync\Utils::STATUS_OK, OCA_mozilla_sync\Utils::$lastStatus);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '[]' . "\n");
  }

  private function clearRequest() {
    OCA_mozilla_sync\OutputData::$outputBuffer = '';
    OCA_mozilla_sync\Utils::$requestMethod = 'GET';
    OCA_mozilla_sync\Utils::$lastStatus = OCA_mozilla_sync\Utils::STATUS_OK;
  }

  private function request($url, $inputData = null) {

    $_SERVER['PHP_AUTH_USER'] = $this->userHash;
    $_SERVER['PHP_AUTH_PW'] = $this->password;
    $urlParser = new OCA_mozilla_sync\UrlParser($url);
    $userService = new OCA_mozilla_sync\StorageService($urlParser, $inputData);
    $userService->run();
  }

  private function outputContainInput(&$input, &$output) {

    foreach ($input as $value) {
      if(strpos($output, $value) === false) {
        return false;
      }
    }

    return true;
  }

  private function outputNotContainInput(&$input, &$output) {

    foreach ($input as $value) {
      if(strpos($output, $value) === false) {
        continue;
      }
      else{
        return false;
      }
    }

    return true;
  }

}
