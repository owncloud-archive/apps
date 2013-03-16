<?php
/**
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;

// Check if we are a user
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('contacts');
require_once  __DIR__.'/../loghandler.php';

$app = new App();

$addressBooks = $app->getAllAddressBooksForUser();
$addressBooksMetaData = array();
$contacts = array();

foreach($addressBooks as $addressBook) {
	$addressBooksMetaData[] = $addressBook->getMetaData();
	foreach($addressBook->getChildren() as $contact) {
		$response = Utils\JSONSerializer::serializeContact($contact);
		if($response !== null) {
			$contacts[] = $response;
		}
	}
}

\OCP\JSON::success(array(
	'data' => array(
		'addressbooks' => $addressBooksMetaData,
		'contacts' => $contacts, //Utils\JSONSerializer::serialize($contacts),
		'is_indexed' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'contacts', 'contacts_indexed', 'no') === 'yes'
	)
));
