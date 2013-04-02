<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//check for addressbooks rights or create new one
ob_start();

OCP\JSON::checkLoggedIn();
OCP\App::checkAppEnabled('contacts');
OCP\JSON::callCheck();
session_write_close();

$nl = "\n";

global $progresskey;
$progresskey = 'contacts.import-' . (isset($_GET['progresskey'])?$_GET['progresskey']:'');

if (isset($_GET['progress']) && $_GET['progress']) {
	echo OC_Cache::get($progresskey);
	die;
}

function writeProgress($pct) {
	global $progresskey;
	OC_Cache::set($progresskey, $pct, 300);
}
writeProgress('10');
$view = null;
$inputfile = strtr($_POST['file'], array('/' => '', "\\" => ''));
if(OC\Files\Filesystem::isFileBlacklisted($inputfile)) {
	OCP\JSON::error(array('data' => array('message' => 'Upload of blacklisted file: ' . $inputfile)));
	exit();
}
if(isset($_POST['fstype']) && $_POST['fstype'] == 'OC_FilesystemView') {
	$view = OCP\Files::getStorage('contacts');
	$file = $view->file_get_contents('/imports/' . $inputfile);
} else {
	$file = \OC\Files\Filesystem::file_get_contents($_POST['path'] . '/' . $inputfile);
}
if(!$file) {
	OCP\JSON::error(array('data' => array('message' => 'Import file was empty.')));
	exit();
}
if(isset($_POST['method']) && $_POST['method'] == 'new') {
	$id = OCA\Contacts\Addressbook::add(OCP\USER::getUser(),
		$_POST['addressbookname']);
	if(!$id) {
		OCP\JSON::error(
			array(
				'data' => array('message' => 'Error creating address book.')
			)
		);
		exit();
	}
	OCA\Contacts\Addressbook::setActive($id, 1);
}else{
	$id = $_POST['id'];
	if(!$id) {
		OCP\JSON::error(
			array(
				'data' => array(
					'message' => 'Error getting the ID of the address book.',
					'file'=>OCP\Util::sanitizeHTML($inputfile)
				)
			)
		);
		exit();
	}
	try {
		OCA\Contacts\Addressbook::find($id); // is owner access check
	} catch(Exception $e) {
		OCP\JSON::error(
			array(
				'data' => array(
					'message' => $e->getMessage(),
					'file'=>OCP\Util::sanitizeHTML($inputfile)
				)
			)
		);
		exit();
	}
}
//analyse the contacts file
writeProgress('40');
$file = str_replace(array("\r","\n\n"), array("\n","\n"), $file);
$lines = explode($nl, $file);

$inelement = false;
$parts = array();
$card = array();
foreach($lines as $line) {
	if(strtoupper(trim($line)) == 'BEGIN:VCARD') {
		$inelement = true;
	} elseif (strtoupper(trim($line)) == 'END:VCARD') {
		$card[] = $line;
		$parts[] = implode($nl, $card);
		$card = array();
		$inelement = false;
	}
	if ($inelement === true && trim($line) != '') {
		$card[] = $line;
	}
}
//import the contacts
writeProgress('70');
$imported = 0;
$failed = 0;
$partial = 0;
if(!count($parts) > 0) {
	OCP\JSON::error(
		array(
			'data' => array(
				'message' => 'No contacts to import in '
					. OCP\Util::sanitizeHTML($inputfile).'. Please check if the file is corrupted.',
				'file'=>OCP\Util::sanitizeHTML($inputfile)
			)
		)
	);
	if(isset($_POST['fstype']) && $_POST['fstype'] == 'OC_FilesystemView') {
		if(!$view->unlink('/imports/' . $inputfile)) {
			OCP\Util::writeLog('contacts',
				'Import: Error unlinking OC_FilesystemView ' . '/' . OCP\Util::sanitizeHTML($inputfile),
				OCP\Util::ERROR);
		}
	}
	exit();
}
foreach($parts as $part) {
	try {
		$vcard = Sabre\VObject\Reader::read($part);
	} catch (Sabre\VObject\ParseException $e) {
		try {
			$vcard = Sabre\VObject\Reader::read($part, Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES);
			$partial += 1;
			OCP\Util::writeLog('contacts',
				'Import: Retrying reading card. Error parsing VCard: ' . $e->getMessage(),
					OCP\Util::ERROR);
		} catch (Exception $e) {
			$failed += 1;
			OCP\Util::writeLog('contacts',
				'Import: skipping card. Error parsing VCard: ' . $e->getMessage(),
					OCP\Util::ERROR);
			continue; // Ditch cards that can't be parsed by Sabre.
		}
	}
	try {
		OCA\Contacts\VCard::add($id, $vcard);
		$imported += 1;
	} catch (Exception $e) {
		OCP\Util::writeLog('contacts',
			'Error importing vcard: ' . $e->getMessage() . $nl . $vcard,
			OCP\Util::ERROR);
		$failed += 1;
	}
}
//done the import
writeProgress('100');
sleep(3);
OC_Cache::remove($progresskey);
if(isset($_POST['fstype']) && $_POST['fstype'] == 'OC_FilesystemView') {
	if(!$view->unlink('/imports/' . $inputfile)) {
		OCP\Util::writeLog('contacts',
			'Import: Error unlinking OC_FilesystemView ' . '/' . $inputfile,
			OCP\Util::ERROR);
	}
}
OCP\JSON::success(
	array(
		'data' => array(
			'imported'=>$imported,
			'failed'=>$failed,
			'file'=>OCP\Util::sanitizeHTML($inputfile),
		)
	)
);
