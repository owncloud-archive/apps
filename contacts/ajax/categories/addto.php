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

$categoryid = isset($_POST['categoryid']) ? $_POST['categoryid'] : null;
$contactid = isset($_POST['contactid']) ? $_POST['contactid'] : null;

if(is_null($categoryid)) {
	bailOut(OCA\Contacts\App::$l10n->t('Group ID missing from request.'));
}

if(is_null($contactid)) {
	bailOut(OCA\Contacts\App::$l10n->t('Contact ID missing from request.'));
}

debug('id: ' . $contactid .', categoryid: ' . $categoryid);

$catmgr = OCA\Contacts\App::getVCategories();

if(!$catmgr->addToCategory($contactid, $categoryid)) {
	bailOut(OCA\Contacts\App::$l10n->t('Error adding contact to group.'));
}

OCP\JSON::success();
