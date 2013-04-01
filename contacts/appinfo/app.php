<?php
OC::$CLASSPATH['OCA\Contacts\App'] = 'contacts/lib/app.php';
OC::$CLASSPATH['OCA\Contacts\AddressBook'] = 'contacts/lib/addressbook.php';
OC::$CLASSPATH['OCA\Contacts\Contact'] = 'contacts/lib/contact.php';
OC::$CLASSPATH['OCA\Contacts\AddressbookLegacy'] = 'contacts/lib/addressbooklegacy.php';
OC::$CLASSPATH['OCA\Contacts\IPIMObject'] = 'contacts/lib/ipimobject.php';
OC::$CLASSPATH['OCA\Contacts\PIMCollectionAbstract'] = 'contacts/lib/abstractpimcollection.php';
OC::$CLASSPATH['OCA\Contacts\PIMObjectAbstract'] = 'contacts/lib/abstractpimobject.php';
OC::$CLASSPATH['OCA\Contacts\VCard'] = 'contacts/lib/vcard.php';
OC::$CLASSPATH['OCA\Contacts\Hooks'] = 'contacts/lib/hooks.php';
OC::$CLASSPATH['OCA\Contacts\Request'] = 'contacts/lib/request.php';
OC::$CLASSPATH['OCA\Contacts\Utils\JSONSerializer'] = 'contacts/lib/utils/jsonserializer.php';
OC::$CLASSPATH['OCA\Contacts\Utils\Properties'] = 'contacts/lib/utils/properties.php';
OC::$CLASSPATH['OCA\Contacts\Backend\AbstractBackend'] = 'contacts/lib/backend/abstractbackend.php';
OC::$CLASSPATH['OCA\Contacts\Backend\Database'] = 'contacts/lib/backend/database.php';
OC::$CLASSPATH['OCA\Contacts\Backend\Shared'] = 'contacts/lib/backend/shared.php';
OC::$CLASSPATH['OCA\Contacts\Share_Backend_Contact'] = 'contacts/lib/share/contact.php';
OC::$CLASSPATH['OCA\Contacts\Share_Backend_Addressbook'] = 'contacts/lib/share/addressbook.php';
OC::$CLASSPATH['OCA\Contacts\AddressbookProvider'] = 'contacts/lib/addressbookprovider.php';
OC::$CLASSPATH['OCA\Contacts\VObject\VCard'] = 'contacts/lib/vobject/vcard.php';
OC::$CLASSPATH['OCA\Contacts\VObject\StringProperty'] = 'contacts/lib/vobject/stringproperty.php';
OC::$CLASSPATH['OCA\Contacts\CardDAV\Backend'] = 'contacts/lib/carddav/backend.php';
OC::$CLASSPATH['OCA\Contacts\CardDAV\Plugin'] = 'contacts/lib/carddav/plugin.php';
OC::$CLASSPATH['OCA\Contacts\CardDAV\AddressBookRoot'] = 'contacts/lib/carddav/addressbookroot.php';
OC::$CLASSPATH['OCA\Contacts\CardDAV\UserAddressBooks'] = 'contacts/lib/carddav/useraddressbooks.php';
OC::$CLASSPATH['OCA\Contacts\CardDAV\AddressBook'] = 'contacts/lib/carddav/addressbook.php';
OC::$CLASSPATH['OCA\Contacts\CardDAV\Card'] = 'contacts/lib/carddav/card.php';
OC::$CLASSPATH['OCA\Contacts\SearchProvider'] = 'contacts/lib/search.php';

//require_once __DIR__ . '/../lib/contact.php';
//require_once __DIR__ . '/../lib/backend/database.php';
Sabre\VObject\Component::$classMap['VCARD'] = 'OCA\Contacts\Contact';
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

OCP\Util::connectHook('OC_User', 'post_createUser', 'OCA\Contacts\Hooks', 'userCreated');
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\Contacts\Hooks', 'userDeleted');
OCP\Util::connectHook('OCA\Contacts', 'pre_deleteAddressBook', 'OCA\Contacts\Hooks', 'addressBookDeletion');
OCP\Util::connectHook('OCA\Contacts', 'pre_deleteContact', 'OCA\Contacts\Hooks', 'contactDeletion');
OCP\Util::connectHook('OCA\Contacts', 'post_createContact', 'OCA\Contacts\Hooks', 'contactUpdated');
OCP\Util::connectHook('OCA\Contacts', 'post_updateContact', 'OCA\Contacts\Hooks', 'contactUpdated');
OCP\Util::connectHook('OCA\Contacts', 'scanCategories', 'OCA\Contacts\Hooks', 'scanCategories');
OCP\Util::connectHook('OCA\Contacts', 'indexProperties', 'OCA\Contacts\Hooks', 'indexProperties');
OCP\Util::connectHook('OC_Calendar', 'getEvents', 'OCA\Contacts\Hooks', 'getBirthdayEvents');
OCP\Util::connectHook('OC_Calendar', 'getSources', 'OCA\Contacts\Hooks', 'getCalenderSources');

OCP\App::addNavigationEntry( array(
  'id' => 'contacts_index',
  'order' => 10,
  'href' => \OC_Helper::linkToRoute('contacts_index'),
  'icon' => OCP\Util::imagePath( 'contacts', 'contacts.svg' ),
  'name' => OC_L10N::get('contacts')->t('Contacts') ));

OCP\Util::addscript('contacts', 'loader');
OC_Search::registerProvider('OCA\Contacts\SearchProvider');
OCP\Share::registerBackend('contact', 'OCA\Contacts\Share_Backend_Contact');
OCP\Share::registerBackend('addressbook', 'OCA\Contacts\Share_Backend_Addressbook', 'contact');

/*foreach(OCA\Contacts\Addressbook::all(OCP\USER::getUser()) as $addressbook)  {
	OCP\Contacts::registerAddressBook(new OCA\Contacts\AddressbookProvider($addressbook['id']));
}*/
