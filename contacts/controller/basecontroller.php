<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts\Controller;

//use OCA\Contacts\Request;
use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Core\API;


/**
 * Baseclass to inherit your controllers from
 */
abstract class BaseController {

	/**
	 * @var API instance of the api layer
	 */
	protected $api;

	protected $request;

	/**
	 * @param API $api an api wrapper instance
	 * @param Request $request an instance of the request
	 */
	public function __construct(API $api, Request $request) {
		$this->api = $api;
		$this->request = $request;
	}

}
