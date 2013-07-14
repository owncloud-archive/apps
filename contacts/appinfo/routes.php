<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace OCA\Contacts;

use OCA\Contacts\Controller\ImportController;

$this->create('contacts_import_upload', 'addressbook/{addressbookid}/import/upload')
	->post()
	->action(
		function($params) {
			session_write_close();
			$controller = new ImportController($params);
			$response = $controller->upload();
			print $response->render();
		}
	)
	->requirements(array('addressbookid'));

$this->create('contacts_import_start', 'addressbook/{addressbookid}/import/start')
	->post()
	->action(
		function($params) {
			session_write_close();
			$controller = new ImportController($params);
			$response = $controller->start();
			print $response->render();
		}
	)
	->requirements(array('addressbookid'));

$this->create('contacts_import_status', 'addressbook/{addressbookid}/import/status')
	->get()
	->action(
		function($params) {
			session_write_close();
			$controller = new ImportController($params);
			$response = $controller->status();
			print $response->render();
		}
	)
	->requirements(array('addressbookid'));
