<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Contacts;

require_once __DIR__.'/../ajax/loghandler.php';

//define the routes
//for the index
$this->create('contacts_index', '/')
	->actionInclude('contacts/index.php');
// 	->action(
// 		function($params){
// 			//
// 		}
// 	);

/* TODO:
	- Check what it requires to be a RESTful API. I think maybe {user}
	shouldn't be in the URI but be authenticated in headers or elsewhere.
	- Do security checks: logged in, csrf
	- Move the actual code to controllers.
*/
$this->create('contacts_address_books_for_user', 'addressbooks/{user}/')
	->get()
	->action(
		function($params) {
			session_write_close();
			$app = new App($params['user']);
			$addressBooks = $app->getAddressBooksForUser();
			$response = array();
			foreach($addressBooks as $addressBook) {
				$response[] = $addressBook->getMetaData();
			}
			\OCP\JSON::success(array(
				'data' => array(
					'addressbooks' => $response,
				)
			));
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_address_book_collection', 'addressbook/{user}/{backend}/{addressbookid}/contacts')
	->get()
	->action(
		function($params) {
			session_write_close();
			$app = new App($params['user']);
			$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
			$lastModified = $addressBook->lastModified();
			if(!is_null($lastModified)) {
				\OCP\Response::enableCaching();
				\OCP\Response::setLastModifiedHeader($lastModified);
				\OCP\Response::setETagHeader(md5($lastModified));
			}
			$contacts = array();
			foreach($addressBook->getChildren() as $contact) {
				//$contact->retrieve();
				//error_log(__METHOD__.' jsondata: '.print_r($contact, true));
				$response = Utils\JSONSerializer::serializeContact($contact);
				if($response !== null) {
					$contacts[] = $response;
				}
			}
			\OCP\JSON::success(array(
				'data' => array(
					'contacts' => $contacts,
				)
			));
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_address_book_add', 'addressbook/{user}/{backend}/add')
	->post()
	->action(
		function($params) {
			session_write_close();
			$app = new App($params['user']);
			$backend = App::getBackend('database', $params['user']);
			$id = $backend->createAddressBook($_POST);
			if($id === false) {
				bailOut(App::$l10n->t('Error creating address book'));
			}
			\OCP\JSON::success(array(
				'data' => $backend->getAddressBook($id)
			));
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_address_book_delete', 'addressbook/{user}/{backend}/{addressbookid}/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			$app = new App($params['user']);
			$backend = App::getBackend('database', $params['user']);
			if(!$backend->deleteAddressBook($params['addressbookid'])) {
				bailOut(App::$l10n->t('Error deleting address book'));
			}
			\OCP\JSON::success();
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_address_book_add_contact', 'addressbook/{user}/{backend}/{addressbookid}/contact/add')
	->post()
	->action(
		function($params) {
			session_write_close();
			$app = new App($params['user']);
			$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
			$id = $addressBook->addChild();
			if($id === false) {
				bailOut(App::$l10n->t('Error creating contact.'));
			}
			$contact = $addressBook->getChild($id);
			\OCP\JSON::success(array(
				'data' => Utils\JSONSerializer::serializeContact($contact),
			));
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_address_book_delete_contact', 'addressbook/{user}/{backend}/{addressbookid}/contact/{contactid}/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			$app = new App($params['user']);
			$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
			$response = $addressBook->deleteChild($params['contactid']);
			if($response === false) {
				bailOut(App::$l10n->t('Error deleting contact.'));
			}
			\OCP\JSON::success();
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_contact_delete_property', 'addressbook/{user}/{backend}/{addressbookid}/contact/{contactid}/property/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			$name = $request->post['name'];
			$checksum = $request->post['checksum'];

			debug('contacts_contact_delete_property, name: ' . print_r($name, true));
			debug('contacts_contact_delete_property, checksum: ' . print_r($checksum, true));

			$app = new App($request->parameters['user']);
			$contact = $app->getContact(
				$request->parameters['backend'],
				$request->parameters['addressbookid'],
				$request->parameters['contactid']
			);

			if(!$contact) {
				bailOut(App::$l10n->t('Couldn\'t find contact.'));
			}
			if(!$name) {
				bailOut(App::$l10n->t('Property name is not set.'));
			}
			if(!$checksum && in_array($name, Utils\Properties::$multi_properties)) {
				bailOut(App::$l10n->t('Property checksum is not set.'));
			}
			if(!is_null($checksum)) {
				try {
					$contact->unsetPropertyByChecksum($checksum);
				} catch(Exception $e) {
					bailOut(App::$l10n->t('Information about vCard is incorrect. Please reload the page.'));
				}
			} else {
				unset($contact->{$name});
			}
			if(!$contact->save()) {
				bailOut(App::$l10n->t('Error saving contact to backend.'));
			}
			\OCP\JSON::success(array(
				'data' => array(
					'backend' => $request->parameters['backend'],
					'addressbookid' => $request->parameters['addressbookid'],
					'contactid' => $request->parameters['contactid'],
					'lastmodified' => $contact->lastModified(),
				)
			));
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_contact_save_property', 'addressbook/{user}/{backend}/{addressbookid}/contact/{contactid}/property/save')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			// TODO: When value is empty unset the property and return a checksum of 'new' if multi_property
			$name = $request->post['name'];
			$value = $request->post['value'];
			$parameters = isset($request->post['parameters']) ? $request->post['parameters'] : null;
			$checksum = isset($request->post['checksum']) ? $request->post['checksum'] : null;

			debug('contacts_contact_save_property, name: ' . print_r($name, true));
			debug('contacts_contact_save_property, value: ' . print_r($value, true));
			debug('contacts_contact_save_property, parameters: ' . print_r($parameters, true));
			debug('contacts_contact_save_property, checksum: ' . print_r($checksum, true));

			$app = new App($params['user']);
			$contact = $app->getContact($params['backend'], $params['addressbookid'], $params['contactid']);

			$response = array('contactid' => $params['contactid']);

			if(!$contact) {
				bailOut(App::$l10n->t('Couldn\'t find contact.'));
			}
			if(!$name) {
				bailOut(App::$l10n->t('Property name is not set.'));
			}
			if(!$checksum && in_array($name, Utils\Properties::$multi_properties)) {
				bailOut(App::$l10n->t('Property checksum is not set.'));
			} elseif($checksum && in_array($name, Utils\Properties::$multi_properties)) {
				try {
					$checksum = $contact->setPropertyByChecksum($checksum, $name, $value, $parameters);
					$response['checksum'] = $checksum;
				} catch(Exception $e)	{
					bailOut(App::$l10n->t('Information about vCard is incorrect. Please reload the page.'));
				}
			} elseif(!in_array($name, Utils\Properties::$multi_properties)) {
				if(!$contact->setPropertyByName($name, $value, $parameters)) {
					bailOut(App::$l10n->t('Error setting property'));
				}
			}
			if(!$contact->save()) {
				bailOut(App::$l10n->t('Error saving property to backend'));
			}
			$response['lastmodified'] = $contact->lastModified();
			$contact->save();
			\OCP\JSON::success(array('data' => $response));
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_categories_list', 'groups/{user}/')
	->get()
	->action(
		function($params) {
			session_write_close();
			$catmgr = new \OC_VCategories('contact', $params['user']);
			$categories = $catmgr->categories(\OC_VCategories::FORMAT_MAP);
			foreach($categories as &$category) {
				$ids = $catmgr->idsForCategory($category['name']);
				$category['contacts'] = $ids;
			}

			$favorites = $catmgr->getFavorites();

			\OCP\JSON::success(array(
				'data' => array(
					'categories' => $categories,
					'favorites' => $favorites,
					'shared' => \OCP\Share::getItemsSharedWith('addressbook', Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS),
					'lastgroup' => \OCP\Config::getUserValue(
									$params['user'],
									'contacts',
									'lastgroup', 'all'),
					'sortorder' => \OCP\Config::getUserValue(
									$params['user'],
									'contacts',
									'groupsort', ''),
					)
				)
			);
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_categories_add', 'groups/{user}/add')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			$name = $request->post['name'];

			if(is_null($name) || $name === "") {
				bailOut(App::$l10n->t('No group name given.'));
			}

			$catman = new \OC_VCategories('contact', $params['user']);
			$id = $catman->add($name);

			if($id !== false) {
				\OCP\JSON::success(array('data' => array('id'=>$id, 'name' => $name)));
			} else {
				bailOut(App::$l10n->t('Error adding group.'));
			}
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_categories_delete', 'groups/{user}/delete')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			$name = $request->post['name'];

			if(is_null($name) || $name === "") {
				bailOut(App::$l10n->t('No group name given.'));
			}

			$catman = new \OC_VCategories('contact', $params['user']);
			$catman->delete($name);

			\OCP\JSON::success();
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_categories_addto', 'groups/{user}/addto/{categoryid}')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			$categoryid = $request['categoryid'];
			$ids = $request['contactids'];
			debug('request: '.print_r($request->post, true));

			if(is_null($categoryid) || $categoryid === '') {
				bailOut(App::$l10n->t('Group ID missing from request.'));
			}

			if(is_null($ids)) {
				bailOut(App::$l10n->t('Contact ID missing from request.'));
			}

			$catman = new \OC_VCategories('contact', $params['user']);
			foreach($ids as $contactid) {
				debug('contactid: ' . $contactid . ', categoryid: ' . $categoryid);
				$catman->addToCategory($contactid, $categoryid);
			}

			\OCP\JSON::success();
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_categories_removefrom', 'groups/{user}/removefrom/{categoryid}')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			$categoryid = $request['categoryid'];
			$ids = $request['contactids'];
			debug('request: '.print_r($request->post, true));

			if(is_null($categoryid) || $categoryid === '') {
				bailOut(App::$l10n->t('Group ID missing from request.'));
			}

			if(is_null($ids)) {
				bailOut(App::$l10n->t('Contact ID missing from request.'));
			}

			$catman = new \OC_VCategories('contact', $params['user']);
			foreach($ids as $contactid) {
				debug('contactid: ' . $contactid . ', categoryid: ' . $categoryid);
				$catman->removeFromCategory($contactid, $categoryid);
			}

			\OCP\JSON::success();
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_setpreference', 'preference/{user}/set')
	->post()
	->action(
		function($params) {
			session_write_close();
			$request = new Request(array('urlParams' => $params));
			$key = $request->post['key'];
			$value = $request->post['value'];

			if(is_null($key) || $key === "") {
				bailOut(App::$l10n->t('No key is given.'));
			}

			if(is_null($value) || $value === "") {
				bailOut(App::$l10n->t('No value is given.'));
			}

			if(\OCP\Config::setUserValue($params['user'], 'contacts', $key, $value)) {
				\OCP\JSON::success(array(
					'data' => array(
						'key' => $key,
						'value' => $value)
					)
				);
			} else {
				bailOut(App::$l10n->t(
					'Could not set preference: ' . $key . ':' . $value)
				);
			}
		}
	)
	->defaults(array('user' => \OCP\User::getUser()));
