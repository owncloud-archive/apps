<?php
OC::$CLASSPATH['OCA\Contacts\App'] = 'contacts/lib/app.php';
OC::$CLASSPATH['OCA\Contacts\Addressbook'] = 'contacts/lib/addressbook.php';
OC::$CLASSPATH['OCA\Contacts\VCard'] = 'contacts/lib/vcard.php';
OC::$CLASSPATH['OCA\Contacts\Hooks'] = 'contacts/lib/hooks.php';
OC::$CLASSPATH['OCA\Contacts\Request'] = 'contacts/lib/request.php';
OC::$CLASSPATH['OCA\Contacts\JSONResponse'] = 'contacts/lib/jsonresponse.php';
OC::$CLASSPATH['OCA\Contacts\Controller\BaseController'] = 'contacts/lib/controller/basecontroller.php';
OC::$CLASSPATH['OCA\Contacts\Controller\ImportController'] = 'contacts/lib/controller/importcontroller.php';
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

Sabre\VObject\Property::$classMap['FN'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['TITLE'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['ROLE'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['NOTE'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['NICKNAME'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['EMAIL'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['TEL'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['IMPP'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['URL'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['FN'] = 'OC\VObject\CompoundProperty';
Sabre\VObject\Property::$classMap['ADR'] = 'OC\VObject\CompoundProperty';
Sabre\VObject\Property::$classMap['CATEGORIES'] = 'OC\VObject\CompoundProperty';
Sabre\VObject\Property::$classMap['GEO'] = 'OC\VObject\CompoundProperty';

OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\Contacts\Hooks', 'createUser');
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\Contacts\Hooks', 'deleteUser');
OCP\Util::connectHook('OC_Calendar', 'getEvents', 'OCA\Contacts\Hooks', 'getBirthdayEvents');
OCP\Util::connectHook('OC_Calendar', 'getSources', 'OCA\Contacts\Hooks', 'getCalenderSources');

OCP\Share::registerBackend('contact', 'OCA\Contacts\Share_Backend_Contact');
OCP\Share::registerBackend('addressbook', 'OCA\Contacts\Share_Backend_Addressbook', 'contact');
