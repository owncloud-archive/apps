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
	bailOut(OC_Contacts_App::$l10n->t('Group ID missing from request.'));
}

if(is_null($contactid)) {
	bailOut(OC_Contacts_App::$l10n->t('Contact ID missing from request.'));
}

debug('id: ' . $contactid .', categoryid: ' . $categoryid);

$catmgr = OC_Contacts_App::getVCategories();
if(!$catmgr->createRelation($contactid, $categoryid)) {
	bailOut(OC_Contacts_App::$l10n->t('Error removing contact from group.'));
}

OCP\JSON::success();
