<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

$id = isset($_GET['id'])?$_GET['id']:null;
if(is_null($id)) {
	OCP\JSON::error(array(
		'data' => array(
			'message' => OCA\Contacts\App::$l10n->t('No ID provided'))));
	exit();
}

$vcard = OCA\Contacts\App::getContactVCard( $id );
foreach($vcard->children as $property) {
	if($property->name == 'CATEGORIES') {
		$checksum = md5($property->serialize());
		OCP\JSON::success(array(
			'data' => array(
				'value' => $property->value,
				'checksum' => $checksum,
				)));
		exit();
	}
}
OCP\JSON::error(array(
	'data' => array(
		'message' => OCA\Contacts\App::$l10n->t('Error setting checksum.'))));
