<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Contacts;

use OCA\AppFramework\App as Main;
use OCA\Contacts\DIContainer;

//define the routes
//for the index
$this->create('contacts_index', '/')
	->actionInclude('contacts/index.php');
// 	->action(
// 		function($params){
// 			//
// 		}
// 	);

$this->create('contacts_jsconfig', 'ajax/config.js')
	->actionInclude('contacts/js/config.php');

/* TODO: Check what it requires to be a RESTful API. I think maybe {user}
	shouldn't be in the URI but be authenticated in headers or elsewhere.
*/
$this->create('contacts_address_books_for_user', 'addressbooks/')
	->get()
	->action(
		function($params) {
			session_write_close();
			Main::main('AddressBookController', 'userAddressBooks', $params, new DIContainer());
		}
	);

$this->create('contacts_address_book_collection', 'addressbook/{backend}/{addressbookid}/contacts')
	->get()
	->action(
		function($params) {
			session_write_close();
			Main::main('AddressBookController', 'getAddressBook', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbookid'));

$this->create('contacts_address_book_add', 'addressbook/{backend}/add')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('AddressBookController', 'addAddressBook', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbookid'));

$this->create('contacts_address_book_delete', 'addressbook/{backend}/{addressbookid}/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('AddressBookController', 'deleteAddressBook', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbookid'));

$this->create('contacts_address_book_add_contact', 'addressbook/{backend}/{addressbookid}/contact/add')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('AddressBookController', 'addChild', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbookid'));

$this->create('contacts_address_book_delete_contact', 'addressbook/{backend}/{addressbookid}/contact/{contactid}/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('AddressBookController', 'deleteChild', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbookid', 'contactid'));

$this->create('contacts_contact_photo', 'addressbook/{backend}/{addressbookid}/contact/{contactid}/photo')
	->get()
	->action(
		function($params) {
			session_write_close();
			Main::main('ContactController', 'getPhoto', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbook', 'contactid'));

$this->create('contacts_contact_delete_property', 'addressbook/{backend}/{addressbookid}/contact/{contactid}/property/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('ContactController', 'deleteProperty', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbook', 'contactid'));

// Save a single property.
$this->create('contacts_contact_save_property', 'addressbook/{backend}/{addressbookid}/contact/{contactid}/property/save')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('ContactController', 'saveProperty', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbook', 'contactid'));

// Save all properties. Used for merging contacts.
$this->create('contacts_contact_save_all', 'addressbook/{backend}/{addressbookid}/contact/{contactid}/save')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('ContactController', 'saveContact', $params, new DIContainer());
		}
	)
	->requirements(array('backend', 'addressbook', 'contactid'));

$this->create('contacts_categories_list', 'groups/')
	->get()
	->action(
		function($params) {
			session_write_close();
			Main::main('GroupController', 'getGroups', $params, new DIContainer());
		}
	);

$this->create('contacts_categories_add', 'groups/add')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('GroupController', 'addGroup', $params, new DIContainer());
		}
	);

$this->create('contacts_categories_delete', 'groups/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('GroupController', 'deleteGroup', $params, new DIContainer());
		}
	);

$this->create('contacts_categories_addto', 'groups/addto/{categoryid}')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('GroupController', 'addToGroup', $params, new DIContainer());
		}
	);

$this->create('contacts_categories_removefrom', 'groups/removefrom/{categoryid}')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('GroupController', 'removeFromGroup', $params, new DIContainer());
		}
	)
	->requirements(array('categoryid'));

$this->create('contacts_setpreference', 'preference/set')
	->post()
	->action(
		function($params) {
			session_write_close();
			Main::main('SettingsController', 'set', $params, new DIContainer());
		}
	);

$this->create('contacts_index_properties', 'indexproperties/{user}/')
	->post()
	->action(
		function($params) {
			session_write_close();
			// TODO: Add BackgroundJob for this.
			\OC_Hook::emit('OCA\Contacts', 'indexProperties', array());

			\OCP\Config::setUserValue($params['user'], 'contacts', 'contacts_properties_indexed', 'yes');
			\OCP\JSON::success(array('isIndexed' => true));
		}
	)
	->requirements(array('user'))
	->defaults(array('user' => \OCP\User::getUser()));
