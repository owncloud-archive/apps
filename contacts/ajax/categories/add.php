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

$category = isset($_POST['category']) ? $_POST['category'] : null;

if(is_null($category)) {
	bailOut(OC_Contacts_App::$l10n->t('No category name given.'));
}

$catman = new OC_VCategories('contact');
$id = $catman->add($category);

if($id !== false) {
	OCP\JSON::success(array('data' => array('id'=>$id)));
} else {
	bailOut(OC_Contacts_App::$l10n->t('Error adding group.'));
}
