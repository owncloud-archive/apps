<?php
OC::$CLASSPATH['OCA\Contacts\App'] = 'contacts/lib/app.php';
OC::$CLASSPATH['OCA\Contacts\Addressbook'] = 'contacts/lib/addressbook.php';
OC::$CLASSPATH['OCA\Contacts\VCard'] = 'contacts/lib/vcard.php';
OC::$CLASSPATH['OCA\Contacts\Hooks'] = 'contacts/lib/hooks.php';
OC::$CLASSPATH['OCA\Contacts\Share_Backend_Contact'] = 'contacts/lib/share/contact.php';
OC::$CLASSPATH['OCA\Contacts\Share_Backend_Addressbook'] = 'contacts/lib/share/addressbook.php';
OC::$CLASSPATH['OCA\Contacts\AddressbookProvider'] = 'contacts/lib/addressbookprovider.php';
OC::$CLASSPATH['OC_Connector_Sabre_CardDAV'] = 'contacts/lib/sabre/backend.php';
OC::$CLASSPATH['OC_Connector_Sabre_CardDAV_AddressBookRoot'] = 'contacts/lib/sabre/addressbookroot.php';
OC::$CLASSPATH['OC_Connector_Sabre_CardDAV_UserAddressBooks'] = 'contacts/lib/sabre/useraddressbooks.php';
OC::$CLASSPATH['OC_Connector_Sabre_CardDAV_AddressBook'] = 'contacts/lib/sabre/addressbook.php';
OC::$CLASSPATH['OC_Connector_Sabre_CardDAV_Card'] = 'contacts/lib/sabre/card.php';
OC::$CLASSPATH['OCA\\Contacts\\SearchProvider'] = 'contacts/lib/search.php';
OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\Contacts\Hooks', 'createUser');
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\Contacts\Hooks', 'deleteUser');
OCP\Util::connectHook('OC_Calendar', 'getEvents', 'OCA\Contacts\Hooks', 'getBirthdayEvents');
OCP\Util::connectHook('OC_Calendar', 'getSources', 'OCA\Contacts\Hooks', 'getCalenderSources');

OCP\App::addNavigationEntry( array(
  'id' => 'contacts_index',
  'order' => 10,
  'href' => OCP\Util::linkTo( 'contacts', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'contacts', 'contacts.svg' ),
  'name' => OC_L10N::get('contacts')->t('Contacts') ));

OCP\Util::addscript('contacts', 'loader');

OC_Search::registerProvider('OCA\Contacts\SearchProvider');
OCP\Share::registerBackend('contact', 'OCA\Contacts\Share_Backend_Contact');
OCP\Share::registerBackend('addressbook', 'OCA\Contacts\Share_Backend_Addressbook', 'contact');

if(OCP\User::isLoggedIn()) {
	foreach(OCA\Contacts\Addressbook::all(OCP\USER::getUser()) as $addressbook)  {
		OCP\Contacts::registerAddressBook(new OCA\Contacts\AddressbookProvider($addressbook['id']));
	}
}
