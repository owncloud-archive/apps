<?php

OC_App::loadApp('mozilla_sync');
class Test_User extends UnitTestCase {
  private $user;

	function setUp() {
	  $this->user = uniqid('test_');
	}

	function test() {
    // TODO
	}

}
