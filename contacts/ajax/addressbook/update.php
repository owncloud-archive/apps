<?php
/**
 * Copyright (c) 2011-2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
require_once  __DIR__.'/../loghandler.php';

$id = $_POST['id'];
$name = trim(strip_tags($_POST['name']));
$description = trim(strip_tags($_POST['description']));

if(!$id) {
	bailOut(OCA\Contacts\App::$l10n->t('id is not set.'));
}

if(!$name) {
	bailOut(OCA\Contacts\App::$l10n->t('Cannot update addressbook with an empty name.'));
}

try {
	OCA\Contacts\Addressbook::edit($id, $name, $description);
} catch(Exception $e) {
	bailOut($e->getMessage());
}

if(!OCA\Contacts\Addressbook::setActive($id, $_POST['active'])) {
	bailOut(OCA\Contacts\App::$l10n->t('Error (de)activating addressbook.'));
}

$addressbook = OCA\Contacts\Addressbook::find($id);
OCP\JSON::success(array(
	'data' => array('addressbook' => $addressbook),
));
