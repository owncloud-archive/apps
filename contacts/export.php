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
$nl = "\n";
if(!is_null($bookid)) {
	try {
		$addressbook = OCA\Contacts\Addressbook::find($bookid); // is owner access check
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
	//$cardobjects = OCA\Contacts\VCard::all($bookid);
	header('Content-Type: text/directory');
	header('Content-Disposition: inline; filename='
		. str_replace(' ', '_', $addressbook['displayname']) . '.vcf');

	$start = 0;
	$batchsize = OCP\Config::getUserValue(OCP\User::getUser(),
		'contacts',
		'export_batch_size', 20);
	while($cardobjects = OCA\Contacts\VCard::all($bookid, $start, $batchsize)) {
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
}
