<?php

require_once __DIR__ . '/bootstrap.php';

OCP\App::addNavigationEntry( array(
  'id' => 'contacts_index',
  'order' => 10,
  'href' => OCP\Util::linkTo( 'contacts', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'contacts', 'contacts.svg' ),
  'name' => OC_L10N::get('contacts')->t('Contacts') ));

OCP\Util::addscript('contacts', 'loader');

OC_Search::registerProvider('OCA\Contacts\SearchProvider');

if(OCP\User::isLoggedIn()) {
	foreach(OCA\Contacts\Addressbook::all(OCP\USER::getUser()) as $addressbook)  {
		OCP\Contacts::registerAddressBook(new OCA\Contacts\AddressbookProvider($addressbook['id']));
	}
}
