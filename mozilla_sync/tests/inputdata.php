<?php

OC_App::loadApp('mozilla_sync');
class Test_InputData extends PHPUnit_Framework_TestCase {

	function test_CommonUsage() {

	  $json_input = '{"password":"tajnehaslo","email":"test1234@test1234.pl","captcha-challenge":null,"captcha-response":null}';

		$InputData = new OCA_mozilla_sync\InputData($json_input);
		$this->assertTrue($InputData->isValid());

		$this->assertTrue($InputData->hasValues(array('password', 'email')));
		$this->assertFalse($InputData->hasValues(array('password', 'email', 'captcha-challenge')));

		$this->assertTrue($InputData->hasValue('password'));
		$this->assertTrue(isset($InputData['password']));
		$this->assertTrue($InputData->getValue('password') === 'tajnehaslo');
		$this->assertTrue($InputData['password'] === 'tajnehaslo');

		$this->assertTrue($InputData->hasValue('email'));
		$this->assertTrue(isset($InputData['email']));
		$this->assertTrue($InputData->getValue('email') === 'test1234@test1234.pl');
		$this->assertTrue($InputData['email'] === 'test1234@test1234.pl');

		$this->assertFalse($InputData->hasValue('captcha-challenge'));
		$this->assertFalse(isset($InputData['captcha-challenge']));
		$this->assertTrue($InputData->getValue('captcha-challenge') === null);
		$this->assertTrue($InputData['captcha-challenge'] === null);

	}

	function test_WrongInput() {

		$json_input = '{"password":"tajnehasl';

		$InputData = new OCA_mozilla_sync\InputData($json_input);
		$this->assertFalse($InputData->isValid());

	}

}