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
OC::$CLASSPATH['OCA\Contacts\VObject\StringProperty'] = 'contacts/lib/vobject/stringproperty.php';
OC::$CLASSPATH['OCA\\Contacts\\SearchProvider'] = 'contacts/lib/search.php';

Sabre\VObject\Property::$classMap['FN'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['TITLE'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['ROLE'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['NOTE'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['NICKNAME'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['EMAIL'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['TEL'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['IMPP'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['URL'] = 'OCA\Contacts\VObject\StringProperty';
Sabre\VObject\Property::$classMap['GEO'] = 'Sabre\VObject\Property\Compound';

OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\Contacts\Hooks', 'createUser');
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\Contacts\Hooks', 'deleteUser');
OCP\Util::connectHook('OC_Calendar', 'getEvents', 'OCA\Contacts\Hooks', 'getBirthdayEvents');
OCP\Util::connectHook('OC_Calendar', 'getSources', 'OCA\Contacts\Hooks', 'getCalenderSources');

OCP\Share::registerBackend('contact', 'OCA\Contacts\Share_Backend_Contact');
OCP\Share::registerBackend('addressbook', 'OCA\Contacts\Share_Backend_Addressbook', 'contact');
