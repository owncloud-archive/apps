<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts\Controller;

use OCA\Contacts\Request;
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
}
