<?php
/**
 * Copyright (c) 2011 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

$id = $_GET['id'];
$checksum = isset($_GET['checksum'])?$_GET['checksum']:'';
$vcard = OCA\Contacts\App::getContactVCard($id);
$adr_types = OCA\Contacts\App::getTypesOfProperty('ADR');

$tmpl = new OCP\Template("contacts", "part.edit_address_dialog");
if($checksum) {
	$line = OCA\Contacts\App::getPropertyLineByChecksum($vcard, $checksum);
	$element = $vcard->children[$line];
	$adr = OCA\Contacts\VCard::structureProperty($element);
	$types = array();
	if(isset($adr['parameters']['TYPE'])) {
		if(is_array($adr['parameters']['TYPE'])) {
			$types = array_map('htmlspecialchars', $adr['parameters']['TYPE']);
			$types = array_map('strtoupper', $types);
		} else {
			$types = array(strtoupper(htmlspecialchars($adr['parameters']['TYPE'])));
		}
	}
	$tmpl->assign('types', $types, false);
	$adr = array_map('htmlspecialchars', $adr['value']);
	$tmpl->assign('adr', $adr, false);
}

$tmpl->assign('id', $id);
$tmpl->assign('adr_types', $adr_types);

$page = $tmpl->fetchPage();
OCP\JSON::success(array('data' => array('page'=>$page, 'checksum'=>$checksum)));
