<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::callCheck();

require_once __DIR__.'/../loghandler.php';

$categories = isset($_POST['categories']) ? $_POST['categories'] : null;
$fromobjects = (isset($_POST['fromobjects']) 
	&& ($_POST['fromobjects'] === 'true' || $_POST['fromobjects'] === '1')) ? true : false;

if(is_null($categories)) {
	bailOut(OCA\Contacts\App::$l10n->t('No categories selected for deletion.'));
}

debug(print_r($categories, true));
if($fromobjects) {
	$addressbooks = OCA\Contacts\Addressbook::all(OCP\USER::getUser());
	if(count($addressbooks) == 0) {
		bailOut(OCA\Contacts\App::$l10n->t('No address books found.'));
	}
	$addressbookids = array();
	foreach($addressbooks as $addressbook) {
		$addressbookids[] = $addressbook['id'];
	}
	$contacts = OCA\Contacts\VCard::all($addressbookids);
	if(count($contacts) == 0) {
		bailOut(OCA\Contacts\App::$l10n->t('No contacts found.'));
	}

	$cards = array();
	foreach($contacts as $contact) {
		$cards[] = array($contact['id'], $contact['carddata']);
	}
}

$catman = new OC_VCategories('contact');
$catman->delete($categories, $cards);

if($fromobjects) {
	OCA\Contacts\VCard::updateDataByID($cards);
}
OCP\JSON::success();
