<?php
/**
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OC_App::loadApp('contacts');
class Test_Contacts_Request extends PHPUnit_Framework_TestCase {
	protected static $user;

	public static function setUpBeforeClass() {
		OC_User::clearBackends();
		OC_User::useBackend('dummy');
		self::$user = uniqid('user_');
		OC_User::createUser(self::$user, 'pass');
		OC_User::setUserId(self::$user);
	}

	public function testRequestAccessors() {
		$vars = array(
			'get' => array('name' => 'John Q. Public', 'nickname' => 'Joey'),
			'urlParams' => array('user' => self::$user, 'trut' => 'trat'),
		);

		$request = new OCA\Contacts\Request($vars);

		$this->assertEquals(4, count($request));
		$this->assertEquals('Joey', $request['nickname']);
		$this->assertEquals('Joey', $request->{'nickname'});
		$this->assertEquals('Joey', $request->get['nickname']);
		$this->assertEquals('Joey', $request->getVar('get', 'nickname'));
		$this->assertTrue(isset($request['nickname']));
		$this->assertTrue(isset($request->{'nickname'}));
		$this->assertEquals(false, isset($request->{'flickname'}));
		$this->assertEquals(null, $request->{'flickname'});
	}

	// urlParams has precedence over POST which has precedence over GET
	public function testPrecedence() {
		$vars = array(
			'get' => array('name' => 'John Q. Public', 'nickname' => 'Joey'),
			'post' => array('name' => 'Jane Doe', 'nickname' => 'Janey'),
			'urlParams' => array('user' => self::$user, 'name' => 'Johnny Weissmüller'),
		);

		$request = new OCA\Contacts\Request($vars);

		$this->assertEquals(3, count($request));
		$this->assertEquals('Janey', $request->{'nickname'});
		$this->assertEquals('Johnny Weissmüller', $request->{'name'});
	}

	// Test default value
	public function testDefaultValue() {
		$vars = array(
			'get' => array('name' => 'John Q. Public', 'nickname' => 'Joey'),
		);

		$request = new OCA\Contacts\Request($vars);

		$this->assertEquals(2, count($request));
		$this->assertEquals('Joey', $request->getVar('get', 'nickname', 'Janey'));
		$this->assertEquals('Apocalypse Now', $request->getVar('get', 'flickname', 'Apocalypse Now'));
	}

	/**
	* @expectedException RuntimeException
	*/
	public function testImmutableArrayAccess() {
		$vars = array(
			'get' => array('name' => 'John Q. Public', 'nickname' => 'Joey'),
		);

		$request = new OCA\Contacts\Request($vars);
		$request['nickname'] = 'Janey';
	}

	/**
	* @expectedException RuntimeException
	*/
	public function testImmutableMagicAccess() {
		$vars = array(
			'get' => array('name' => 'John Q. Public', 'nickname' => 'Joey'),
		);

		$request = new OCA\Contacts\Request($vars);
		$request->{'nickname'} = 'Janey';
	}

}
