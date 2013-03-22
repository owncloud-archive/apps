<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Contacts;

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
*/
/*$this->create('core_lostpassword_send_email', 'contacts/contact/{id}')
	->post()
	->action('Utils\Properties', 'saveProperty');*/
$this->create('contacts_address_books_for_user', 'addressbooks/{user}/')
	->get()
	->action(
		function($params){
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
	)->defaults(array('user' => \OCP\User::getUser()));

$this->create('contacts_address_book_collection', 'addressbook/{user}/{backend}/{id}/contacts')
	->get()
	->action(
		function($params){
			session_write_close();
			$app = new App($params['user']);
			$addressBook = $app->getAddressBook($params['backend'], $params['id']);
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
	)->defaults(array('user' => \OCP\User::getUser()));
