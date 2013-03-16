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

		// Test address books
		$this->assertEquals(1, count($this->backend->getAddressBooksForUser()));
		$this->assertTrue($this->backend->hasAddressBook($aid));
		$addressBook = $this->backend->getAddressBook($aid);
		$this->assertEquals('Contacts', $addressBook['displayname']);
		$this->assertEquals('My Contacts', $addressBook['description']);
		$this->backend->updateAddressBook($aid, array('description' => 'All my contacts'));
		$addressBook = $this->backend->getAddressBook($aid);
		$this->assertEquals('All my contacts', $addressBook['description']);

		// Test contacts
		$this->assertEquals(array(), $this->backend->getContacts($aid));

		$carddata = file_get_contents(__DIR__ . '/data/test.vcf');
		$id = $this->backend->createContact($aid, $carddata);
		$this->assertNotEquals(false, $id); // Isn't there an assertNotFalse() ?
		$this->assertEquals(1, count($this->backend->getContacts($aid)));
		$this->assertTrue($this->backend->hasContact($aid, $id));
		$contact = $this->backend->getContact($aid, $id);
		$this->assertEquals('Max Mustermann', $contact['displayname']);
		$carddata = file_get_contents(__DIR__ . '/data/test2.vcf');
		$this->assertTrue($this->backend->updateContact($aid, $id, $carddata));
		$contact = $this->backend->getContact($aid, $id);
		$this->assertEquals('John Q. Public', $contact['displayname']);
		$this->assertTrue($this->backend->deleteContact($aid, $id));

		$this->assertTrue($this->backend->deleteAddressBook($aid));
	}
}
