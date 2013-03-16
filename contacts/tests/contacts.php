<?php
/**
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OC_App::loadApp('contacts');
class Test_Contacts extends PHPUnit_Framework_TestCase {
	protected static $schema_file = 'static://test_db_scheme';
	protected $test_prefix;

	public function setUp() {
		$dbfile = __DIR__.'/../appinfo/database.xml';

		$this->test_prefix = '_'.OC_Util::generate_random_bytes('4').'_';
		$content = file_get_contents($dbfile);
		$content = str_replace( '*dbprefix*', '*dbprefix*'.$this->test_prefix, $content );
		file_put_contents( self::$schema_file, $content );
		OC_DB::createDbFromStructure(self::$schema_file);

		$this->addressBooksTableName = '*PREFIX*'.$this->test_prefix.'contacts_addressbooks';
		$this->cardsTableName = '*PREFIX*'.$this->test_prefix.'contacts_cards';

		OC_User::clearBackends();
		OC_User::useBackend('dummy');
		$this->user = uniqid('user_');
		OC_User::createUser($this->user, 'pass');
		OC_User::setUserId($this->user);
	}

	public function tearDown() {
		OC_DB::removeDBStructure(self::$schema_file);
		unlink(self::$schema_file);
	}

	public function testDatabaseBackend() {
		$this->backend = new OCA\Contacts\Backend\Database(
			$this->user,
			$this->addressBooksTableName,
			$this->cardsTableName
		);

		$this->assertEquals(array(), $this->backend->getAddressBooksForUser());

		$aid = $this->backend->createAddressBook(
			array(
				'displayname' => 'Contacts',
				'description' => 'My Contacts',
			)
		);

		$this->assertEquals(1, count($this->backend->getAddressBooksForUser()));

	}
/*
	function testAddressbook() {
		$uid=uniqid();
		OC_User::setUserId($uid);
		$this->assertEqual(array(), OCA\Contacts\Addressbook::all($uid));
		$aid1 = OCA\Contacts\Addressbook::add($uid, 'test');
		$this->assertTrue(OCA\Contacts\Addressbook::isActive($aid1))

		$all = OCA\Contacts\Addressbook::all($uid);
		$this->assertEqual(1, count($all));

		$this->assertEqual($aid1, $all[0]['id']);
		$this->assertEqual('test', $all[0]['displayname']);
		$this->assertEqual('test', $all[0]['uri']);
		$this->assertEqual($uid, $all[0]['userid']);

		$aid2=OCA\Contacts\Addressbook::add($uid, 'test');
		$this->assertNotEqual($aid1, $aid2);

		$all=OCA\Contacts\Addressbook::all($uid);
		$this->assertEqual(2, count($all));

		$this->assertEqual($aid2, $all[1]['id']);
		$this->assertEqual('test', $all[1]['displayname']);
		$this->assertEqual('test1', $all[1]['uri']);

		//$cal1=OCA\Contacts\Addressbook::find($calId1);
		//$this->assertEqual($cal1,$all[0]);

		OCA\Contacts\Addressbook::delete($aid1);
		OCA\Contacts\Addressbook::delete($aid);
		$this->assertEqual(array(), OCA\Contacts\Addressbook::all($uid));
	}
*/
}
