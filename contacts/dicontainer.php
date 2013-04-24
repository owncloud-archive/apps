<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;
use OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
use OCA\Contacts\Controller\AddressBookController;
use OCA\Contacts\Controller\GroupController;
use OCA\Contacts\Controller\ContactController;

class DIContainer extends BaseContainer {


	/**
	 * Define your dependencies in here
	 */
	public function __construct(){
		// tell parent container about the app name
		parent::__construct('contacts');

		/**
		 * CONTROLLERS
		 */
		$this['AddressBookController'] = $this->share(function($c){
			return new AddressBookController($c['API'], $c['Request']);
		});

		$this['GroupController'] = $this->share(function($c){
			return new GroupController($c['API'], $c['Request']);
		});

		$this['ContactController'] = $this->share(function($c){
			return new ContactController($c['API'], $c['Request']);
		});

	}
}