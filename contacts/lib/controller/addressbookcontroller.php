<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts\Controller;

use OCA\Contacts\App;
use OCA\Contacts\JSONResponse;
use OCA\Contacts\Utils\JSONSerializer;
//use OCA\Contacts\Request;
use OCA\AppFramework\Controller\Controller as BaseController;
use OCA\AppFramework\Core\API;


/**
 * Controller class For Address Books
 */
class AddressBookController extends BaseController {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function userAddressBooks() {
		$app = new App($this->api->getUserId());
		$addressBooks = $app->getAddressBooksForUser();
		$response = array();
		foreach($addressBooks as $addressBook) {
			$response[] = $addressBook->getMetaData();
		}
		$response = new JSONResponse(
			array(
				'addressbooks' => $response,
			));
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function getAddressBook() {
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());

		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
		$lastModified = $addressBook->lastModified();
		$response = new JSONResponse();

		if(!is_null($lastModified)) {
			$response->addHeader('Cache-Control', 'private, must-revalidate');
			$response->setLastModified(\DateTime::createFromFormat('U', $lastModified));
			$response->setETag(md5($lastModified));
		}

		$contacts = array();
		foreach($addressBook->getChildren() as $i => $contact) {
			$result = JSONSerializer::serializeContact($contact);
			//\OCP\Util::writeLog('contacts', __METHOD__.' contact: '.print_r($result, true), \OCP\Util::DEBUG);
			if($result !== null) {
				$contacts[] = $result;
			}
		}
		$response->setParams(array(
				'contacts' => $contacts,
			));
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function addAddressBook() {
		$app = new App($this->api->getUserId());

		$response = new JSONResponse();

		$backend = App::getBackend('local', $this->api->getUserId());
		$id = $backend->createAddressBook($this->request->post);
		if($id === false) {
			$response->bailOut(App::$l10n->t('Error creating address book'));
			return $response;
		}

		$response->setParams($backend->getAddressBook($id));
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function deleteAddressBook() {
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());

		$response = new JSONResponse();

		$backend = App::getBackend('local', $this->api->getUserId());
		if(!$backend->deleteAddressBook($params['addressbookid'])) {
			$response->bailOut(App::$l10n->t('Error deleting address book'));
		}
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function addChild() {
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());

		$response = new JSONResponse();

		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
		$id = $addressBook->addChild();
		if($id === false) {
			$response->bailOut(App::$l10n->t('Error creating contact.'));
		}
		$contact = $addressBook->getChild($id);
		$response->setParams(Utils\JSONSerializer::serializeContact($contact));
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function deleteChild() {
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());

		$response = new JSONResponse();

		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
		$result = $addressBook->deleteChild($params['contactid']);
		if($result === false) {
			$response->bailOut(App::$l10n->t('Error deleting contact.'));
		}
		return $response;
	}


}

