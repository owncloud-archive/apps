<?php
//require_once __DIR__ . '/../lib/contact.php';
Sabre\VObject\Component::$classMap['VCARD'] = 'OCA\Contacts\Contact';
Sabre\VObject\Property::$classMap['FN'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['TITLE'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['ROLE'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['NOTE'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['NICKNAME'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['EMAIL'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['TEL'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['IMPP'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['URL'] = 'OC\VObject\StringProperty';
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
