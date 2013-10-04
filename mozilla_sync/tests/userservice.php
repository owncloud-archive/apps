<?php

OC_App::loadApp('mozilla_sync');
class Test_UserService extends PHPUnit_Framework_TestCase {

  private $userName = 'testUser';
  private $userHash = 'qwegffggh';
  private $password = 'testPassword';
  private $email    = 'testUser@owncloud.org';
  private $urlParser;

  public function setUp() {

    OCA_mozilla_sync\Utils::setTestState();

    // Create ownCloud Test User
    OC_User::createUser($this->userName, $this->password);
    OC_User::setUserId($this->userName);
    OCP\Config::setUserValue($this->userName,'settings', 'email', $this->email);

    OCA_mozilla_sync\OutputData::$outputFlag = OCA_mozilla_sync\OutputData::ConstOutputBuffer;
    OCA_mozilla_sync\Utils::setTestState();

    $this->urlParser = new OCA_mozilla_sync\UrlParser('/1.0/'. $this->userHash);
  }

  public function tearDown() {
    $userId = OCA_mozilla_sync\User::userHashToId($this->userHash);
    if($userId != false) {
      OCA_mozilla_sync\User::deleteUser($userId);
    }

    OC_Preferences::deleteUser($this->userName);
    OC_User::deleteUser($this->userName);
  }

  /**
   * @brief Standard create user scenario
   */
  public function test_CreateUserScenario() {

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

    // Create user
    $this->createUser();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strtolower($this->userHash));

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '1');

    // Delete user
    $this->deleteUser();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

  }

  /**
   * @brief Create user that already exists
   */
  public function test_CreateExistUser() {

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

    // Create user
    $this->createUser();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strtolower($this->userHash));

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '1');

    // Create user
    $this->createUser();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_INVALID_DATA);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '4');

    // Delete user
    $this->deleteUser();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');
  }

  /**
   * @brief Delete user, wrong password request
   */
  public function test_deleteUserNotAuth() {

    // Create user
    $this->createUser();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === strtolower($this->userHash));

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '1');

    // Delete user authentication failed
    $oldPassword = $this->password;
    $this->password .= '12356';
    $this->deleteUser();
    $this->password = $oldPassword;
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_INVALID_USER);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '1');

  }

  /**
   * @brief Create user with invalid data
   */
  public function test_CreateUserInvalidData() {

  //
  // Wrong password
  //
    $inputArray = array();
    $inputArray['password']           = $this->password . '1234';
    $inputArray['email']              = $this->email;
    $this->createUser($inputArray);
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_INVALID_DATA);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '12');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

  //
  // Wrong email
  //
    $inputArray = array();
    $inputArray['password']           = $this->password;
    $inputArray['email']              = $this->email . '1234';
    $this->createUser($inputArray);
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_INVALID_DATA);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '12');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

  //
  // No email
  //
    $inputArray = array();
    $inputArray['password']           = $this->password;
    $this->createUser($inputArray);
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_INVALID_DATA);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '12');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');

  //
  // No password
  //
    $inputArray = array();
    $inputArray['email']              = $this->email;
    $this->createUser($inputArray);
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_INVALID_DATA);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '7');

    // User exist
    $this->userExist();
    $this->assertTrue(OCA_mozilla_sync\Utils::$lastStatus == OCA_mozilla_sync\Utils::STATUS_OK);
    $this->assertTrue(OCA_mozilla_sync\OutputData::$outputBuffer === '0');
  }


  public function clearRequest() {
    OCA_mozilla_sync\OutputData::$outputBuffer = '';
    OCA_mozilla_sync\Utils::$requestMethod = 'GET';
    OCA_mozilla_sync\Utils::$lastStatus = OCA_mozilla_sync\Utils::STATUS_OK;
  }

  private function userExist() {

    $this->clearRequest();

    $userService = new OCA_mozilla_sync\UserService($this->urlParser);
    $userService->run();

  }

  private function createUser($inputArray = null) {

    $this->clearRequest();

    if($inputArray == null) {
      $inputArray['password']           = $this->password;
      $inputArray['email']              = $this->email;
      $inputArray['captcha-challenge']  = null;
      $inputArray['captcha-response']   = null;
    }
    OCA_mozilla_sync\Utils::$requestMethod = 'PUT';
    $inputData = new OCA_mozilla_sync\InputData(json_encode($inputArray));
    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/'. $this->userHash);
    $userService = new OCA_mozilla_sync\UserService($urlParser, $inputData);
    $userService->run();

  }

  private function deleteUser() {

    $this->clearRequest();

    OCA_mozilla_sync\Utils::$requestMethod = 'DELETE';
    $_SERVER['PHP_AUTH_USER'] = $this->userHash;
    $_SERVER['PHP_AUTH_PW'] = $this->password;

    $urlParser = new OCA_mozilla_sync\UrlParser('/1.0/'. $this->userHash);
    $userService = new OCA_mozilla_sync\UserService($urlParser);
    $userService->run();

  }

}
