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
use OCA\Contacts\ImageResponse;
use OCA\Contacts\Utils\JSONSerializer;
use OCA\Contacts\Utils\Properties;
//use OCA\Contacts\Request;
use OCA\AppFramework\Controller\Controller as BaseController;
use OCA\AppFramework\Core\API;


/**
 * Controller class For Contacts
 */
class ContactController extends BaseController {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function saveContact() {
		$app = new App($this->api->getUserId());

		$request = $this->request;
		$response = new JSONResponse();

		$contact = $app->getContact(
			$request->parameters['backend'],
			$request->parameters['addressbookid'],
			$request->parameters['contactid']
		);

		if(!$contact) {
			$response->bailOut(App::$l10n->t('Couldn\'t find contact.'));
		}

		if(!$contact->mergeFromArray($request->params)) {
			$response->bailOut(App::$l10n->t('Error merging into contact.'));
		}
		if(!$contact->save()) {
			$response->bailOut(App::$l10n->t('Error saving contact to backend.'));
		}
		$data = JSONSerializer::serializeContact($contact);

		$response->setParams($data);

		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 */
	public function getPhoto() {
		// TODO: Cache resized photo
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());
		$etag = null;
		$max_size = 170;
		$contact = $app->getContact($params['backend'], $params['addressbookid'], $params['contactid']);
		$image = new \OC_Image();
		if (isset($contact->PHOTO) && $image->loadFromBase64((string)$contact->PHOTO)) {
			// OK
			$etag = md5($contact->PHOTO);
		}
		else
		// Logo :-/
		if(isset($contact->LOGO) && $image->loadFromBase64((string)$contact->LOGO)) {
			// OK
			$etag = md5($contact->LOGO);
		}
		if($image->valid()) {
			$response = new ImageResponse($image);
			$lastModified = $contact->lastModified();
			// Force refresh if modified within the last minute.
			if(!is_null($lastModified)) {
				$response->setLastModified(\DateTime::createFromFormat('U', $lastModified));
			}
			if(!is_null($etag)) {
				$response->setETag($etag);
			}
			if ($image->width() > $max_size || $image->height() > $max_size) {
				$image->resize($max_size);
			}
			return $response;
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function deleteProperty() {
		$app = new App($$this->api->getUserId());

		$request = $this->request;
		$response = new JSONResponse();

		$name = $request->post['name'];
		$checksum = isset($request->post['checksum']) ? $request->post['checksum'] : null;

		$response->debug(__METHOD__ . ', name: ' . print_r($name, true));
		$response->debug(__METHOD__ . ', checksum: ' . print_r($checksum, true));

		$app = new App($request->parameters['user']);
		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
		$contact = $addressBook->getChild($params['contactid']);

		if(!$contact) {
			$response->bailOut(App::$l10n->t('Couldn\'t find contact.'));
		}
		if(!$name) {
			$response->bailOut(App::$l10n->t('Property name is not set.'));
		}
		if(!$checksum && in_array($name, Properties::$multi_properties)) {
			$response->bailOut(App::$l10n->t('Property checksum is not set.'));
		}
		if(!is_null($checksum)) {
			try {
				$contact->unsetPropertyByChecksum($checksum);
			} catch(Exception $e) {
				$response->bailOut(App::$l10n->t('Information about vCard is incorrect. Please reload the page.'));
			}
		} else {
			unset($contact->{$name});
		}
		if(!$contact->save()) {
			$response->bailOut(App::$l10n->t('Error saving contact to backend.'));
		}

		$response->setParams(array(
			'backend' => $request->parameters['backend'],
			'addressbookid' => $request->parameters['addressbookid'],
			'contactid' => $request->parameters['contactid'],
			'lastmodified' => $contact->lastModified(),
		));

		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function saveProperty() {
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());

		$request = $this->request;
		$response = new JSONResponse();

		$name = $request->post['name'];
		$value = $request->post['value'];
		$checksum = isset($request->post['checksum']) ? $request->post['checksum'] : null;
		$parameters = isset($request->post['parameters']) ? $request->post['parameters'] : null;

		$response->debug(__METHOD__ . ', name: ' . print_r($name, true));
		$response->debug(__METHOD__ . ', value: ' . print_r($value, true));
		$response->debug(__METHOD__ . ', checksum: ' . print_r($checksum, true));
		$response->debug(__METHOD__ . ', parameters: ' . print_r($parameters, true));

		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);
		$response->debug(__METHOD__ . ', addressBook: ' . print_r($addressBook, true));
		$contact = $addressBook->getChild($params['contactid']);

		if(!$contact) {
			$response->bailOut(App::$l10n->t('Couldn\'t find contact.'));
		}
		if(!$name) {
			$response->bailOut(App::$l10n->t('Property name is not set.'));
		}
		if(!$checksum && in_array($name, Properties::$multi_properties)) {
			$response->bailOut(App::$l10n->t('Property checksum is not set.'));
		}
		if(is_array($value)) {
			// NOTE: Important, otherwise the compound value will be
			// set in the order the fields appear in the form!
			ksort($value);
		}
		$result = array('contactid' => $params['contactid']);
		if(!$checksum && in_array($name, Properties::$multi_properties)) {
			$response->bailOut(App::$l10n->t('Property checksum is not set.'));
		} elseif($checksum && in_array($name, Properties::$multi_properties)) {
			try {
				$checksum = $contact->setPropertyByChecksum($checksum, $name, $value, $parameters);
				$result['checksum'] = $checksum;
			} catch(Exception $e)	{
				$response->bailOut(App::$l10n->t('Information about vCard is incorrect. Please reload the page.'));
			}
		} elseif(!in_array($name, Properties::$multi_properties)) {
			if(!$contact->setPropertyByName($name, $value, $parameters)) {
				$response->bailOut(App::$l10n->t('Error setting property'));
			}
		}
		if(!$contact->save()) {
			$response->bailOut(App::$l10n->t('Error saving property to backend'));
		}
		$result['lastmodified'] = $contact->lastModified();

		$response->setParams($result);

		return $response;
	}

}

