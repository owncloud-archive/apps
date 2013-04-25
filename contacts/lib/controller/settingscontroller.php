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
use OCA\AppFramework\Controller\Controller as BaseController;
use OCA\AppFramework\Core\API;


/**
 * Controller class for groups/categories
 */
class SettingsController extends BaseController {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function set() {
		$request = $this->request;
		$key = $request->post['key'];
		$value = $request->post['value'];

		$response = new JSONResponse();

		if(is_null($key) || $key === "") {
			$response->bailOut(App::$l10n->t('No key is given.'));
		}

		if(is_null($value) || $value === "") {
			$response->bailOut(App::$l10n->t('No value is given.'));
		}

		if(\OCP\Config::setUserValue($this->api->getUserId(), 'contacts', $key, $value)) {
			$response->setParams(array(
				'key' => $key,
				'value' => $value)
			);
			return $response;
		} else {
			$response->bailOut(App::$l10n->t(
				'Could not set preference: ' . $key . ':' . $value)
			);
		}
	}
}