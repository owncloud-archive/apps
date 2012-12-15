<?php
/**
 * Copyright (c) 2011-2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('contacts');
$bookid = isset($_GET['bookid']) ? $_GET['bookid'] : null;
$contactid = isset($_GET['contactid']) ? $_GET['contactid'] : null;
$selectedids = isset($_GET['selectedids']) ? $_GET['selectedids'] : null;
$nl = "\n";
if(!is_null($bookid)) {
	try {
		$addressbook = OCA\Contacts\Addressbook::find($bookid);
	} catch(Exception $e) {
		OCP\JSON::error(
			array(
				'data' => array(
					'message' => $e->getMessage(),
				)
			)
		);
		exit();
	}

	header('Content-Type: text/directory');
	header('Content-Disposition: inline; filename='
		. str_replace(' ', '_', $addressbook['displayname']) . '.vcf');

	$start = 0;
	$batchsize = OCP\Config::getUserValue(OCP\User::getUser(),
		'contacts',
		'export_batch_size', 20);
	while($cardobjects = OCA\Contacts\VCard::all($bookid, $start, $batchsize, array('carddata'))) {
		foreach($cardobjects as $card) {
			echo $card['carddata'] . $nl;
		}
		$start += $batchsize;
	}
} elseif(!is_null($contactid)) {
	try {
		$data = OCA\Contacts\VCard::find($contactid);
	} catch(Exception $e) {
		OCP\JSON::error(
			array(
				'data' => array(
					'message' => $e->getMessage(),
				)
			)
		);
		exit();
	}
	header('Content-Type: text/vcard');
	header('Content-Disposition: inline; filename='
		. str_replace(' ', '_', $data['fullname']) . '.vcf');
	echo $data['carddata'];
} elseif(!is_null($selectedids)) {
	$selectedids = explode(',', $selectedids);
	$l10n = \OC_L10N::get('contacts');
	header('Content-Type: text/directory');
	header('Content-Disposition: inline; filename=' 
		. $l10n->t('%d_selected_contacts', array(count($selectedids))) . '.vcf');
		
	foreach($selectedids as $id) {
		try {
			$data = OCA\Contacts\VCard::find($id);
			echo $data['carddata'] . $nl;
		} catch(Exception $e) {
			continue;
		}
	}
}
