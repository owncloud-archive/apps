<?php
/**
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OC_App::loadApp('contacts');
class Test_Contacts_Backend_Datebase extends PHPUnit_Framework_TestCase {
	protected static $schema_file = 'static://test_db_scheme';
	protected static $test_prefix;
	protected static $backend;
	protected static $user;
	protected static $addressBooksTableName;
	protected static $cardsTableName;

	public static function setUpBeforeClass() {
		$dbfile = __DIR__.'/../appinfo/database.xml';

		self::$test_prefix = '_'.OC_Util::generate_random_bytes('4').'_';
		$content = file_get_contents($dbfile);
		$content = str_replace( '*dbprefix*', '*dbprefix*'.self::$test_prefix, $content );
		file_put_contents( self::$schema_file, $content );
		OC_DB::createDbFromStructure(self::$schema_file);

		self::$addressBooksTableName = '*PREFIX*'.self::$test_prefix.'contacts_addressbooks';
		self::$cardsTableName = '*PREFIX*'.self::$test_prefix.'contacts_cards';

		OC_User::clearBackends();
		OC_User::useBackend('dummy');
		self::$user = uniqid('user_');
		OC_User::createUser(self::$user, 'pass');
		OC_User::setUserId(self::$user);

		self::$backend = new OCA\Contacts\Backend\Database(
			self::$user,
			self::$addressBooksTableName,
			self::$cardsTableName
		);
	}

	public static function tearDownAfterClass() {
		OC_DB::removeDBStructure(self::$schema_file);
		unlink(self::$schema_file);
	}

	public function testDatabaseBackend() {

		$this->assertEquals(array(), self::$backend->getAddressBooksForUser());

		$aid = self::$backend->createAddressBook(
			array(
				'displayname' => 'Contacts',
				'description' => 'My Contacts',
			)
		);

		// Test address books
		$this->assertEquals(1, count(self::$backend->getAddressBooksForUser()));
		$this->assertTrue(self::$backend->hasAddressBook($aid));
		$addressBook = self::$backend->getAddressBook($aid);
		$this->assertEquals('Contacts', $addressBook['displayname']);
		$this->assertEquals('My Contacts', $addressBook['description']);
		self::$backend->updateAddressBook($aid, array('description' => 'All my contacts'));
		$addressBook = self::$backend->getAddressBook($aid);
		$this->assertEquals('All my contacts', $addressBook['description']);

		// Test contacts
		$this->assertEquals(array(), self::$backend->getContacts($aid));

		$carddata = file_get_contents(__DIR__ . '/data/test.vcf');
		$id = self::$backend->createContact($aid, $carddata);
		$this->assertNotEquals(false, $id); // Isn't there an assertNotFalse() ?
		$this->assertEquals(1, count(self::$backend->getContacts($aid)));
		$this->assertTrue(self::$backend->hasContact($aid, $id));
		$contact = self::$backend->getContact($aid, $id);
		$this->assertEquals('Max Mustermann', $contact['displayname']);
		$carddata = file_get_contents(__DIR__ . '/data/test2.vcf');
		$this->assertTrue(self::$backend->updateContact($aid, $id, $carddata));
		$contact = self::$backend->getContact($aid, $id);
		$this->assertEquals('John Q. Public', $contact['displayname']);
		$this->assertTrue(self::$backend->deleteContact($aid, $id));

		$this->assertTrue(self::$backend->deleteAddressBook($aid));
	}

	public function testAddressBook() {
		$addressBook = new OCA\Contacts\AddressBook(
			self::$backend,
			array(
				'displayname' => 'Contacts',
				'description' => 'My Contacts',
			)
		);
		$this->assertEquals(0, count($addressBook));
		$id = $addressBook->addChild(
			array(
				'displayname' => 'John Q. Public'
				)
		);
		$this->assertNotEquals(false, $id);
		$this->assertEquals(1, count($addressBook));
		$contact = $addressBook->getChild($id);
		$this->assertEquals('John Q. Public', (string)$contact->FN);
		$contact->FN = 'Max Mustermann';
		$contact->save();
		$contact = $addressBook[$id];
		$metadata = $contact->getMetaData();
		$this->assertEquals('Max Mustermann', $metadata['displayname']);
		$this->assertEquals($contact->getPermissions(), $addressBook->getPermissions());

		// Array access
		$this->assertEquals($contact, $addressBook[$id]);
		$this->assertTrue(isset($addressBook[$id]));

		// Magic accessors
		//$this->assertEquals($contact, $addressBook->{$id});

		$this->assertTrue($addressBook->deleteChild($id));
		$this->assertEquals(0, count($addressBook));
	}
}
