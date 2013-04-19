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
//use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Core\API;


/**
 * Baseclass to inherit your controllers from
 */
class AddressBookController extends BaseController {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function userAddressBooks() {
		$app = new App($this->request->urlParams['user']);
		$addressBooks = $app->getAddressBooksForUser();
		$response = array();
		foreach($addressBooks as $addressBook) {
			$response[] = $addressBook->getMetaData();
		}
		$response = new JSONResponse(array(
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
		$app = new App($params['user']);

		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
		$lastModified = $addressBook->lastModified();
		$response = new JSONResponse();

		if(!is_null($lastModified)) {
			$response->enableCaching();
			$response->setLastModifiedHeader($lastModified);
			$response->setETagHeader(md5($lastModified));
		}

		$contacts = array();
		foreach($addressBook->getChildren() as $i => $contact) {
			$result = JSONSerializer::serializeContact($contact);
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
		$params = $this->request->urlParams;
		$app = new App($params['user']);

		$response = new JSONResponse();

		$backend = App::getBackend('local', $params['user']);
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
		$app = new App($params['user']);

		$response = new JSONResponse();

		$backend = App::getBackend('local', $params['user']);
		if(!$backend->deleteAddressBook($params['addressbookid'])) {
			$response->bailOut(App::$l10n->t('Error deleting address book'));
		}
		return $response;
	}
}

