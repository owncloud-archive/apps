<?php
/**
 * Copyright (c) 2011 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

function cmp($a, $b)
{
    if ($a['fullname'] == $b['fullname']) {
        return 0;
    }
    return ($a['fullname'] < $b['fullname']) ? -1 : 1;
}

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$aid = isset($_GET['aid'])?$_GET['aid']:null;

$active_addressbooks = array();
if(is_null($aid)) {
	// Called initially to get the active addressbooks.
	$active_addressbooks = OCA\Contacts\Addressbook::active(OCP\USER::getUser());
} else {
	// called each time more contacts has to be shown.
	$active_addressbooks = array(OCA\Contacts\Addressbook::find($aid));
}

$lastModified = OCA\Contacts\App::lastModified();
if(!is_null($lastModified)) {
	OCP\Response::enableCaching();
	OCP\Response::setLastModifiedHeader($lastModified);
	OCP\Response::setETagHeader(md5($lastModified->format('U')));
}
session_write_close();

// create the addressbook associate array
$contacts_addressbook = array();
$ids = array();
foreach($active_addressbooks as $addressbook) {
	$ids[] = $addressbook['id'];
	/*if(!isset($contacts_addressbook[$addressbook['id']])) {
		$contacts_addressbook[$addressbook['id']]
				= array('contacts' => array('type' => 'book',));
		$contacts_addressbook[$addressbook['id']]['displayname']
				= $addressbook['displayname'];
		$contacts_addressbook[$addressbook['id']]['description']
				= $addressbook['description'];
		$contacts_addressbook[$addressbook['id']]['permissions']
				= $addressbook['permissions'];
		$contacts_addressbook[$addressbook['id']]['owner']
				= $addressbook['userid'];
	}*/
}

$contacts_alphabet = array();

// get next 50 for each addressbook.
$contacts_alphabet = array_merge(
	$contacts_alphabet,
	OCA\Contacts\VCard::all($ids)
);
/*foreach($ids as $id) {
	if($id) {
		$contacts_alphabet = array_merge(
				$contacts_alphabet,
				OCA\Contacts\VCard::all($id, $offset, 50)
		);
	}
}*/

uasort($contacts_alphabet, 'cmp');

$contacts = array();


// Our new array for the contacts sorted by addressbook
if($contacts_alphabet) {
	foreach($contacts_alphabet as $contact) {
		try {
			$vcard = Sabre\VObject\Reader::read($contact['carddata']);
			$details = OCA\Contacts\VCard::structureContact($vcard);
			$contacts[] = array(
					'id' => $contact['id'],
					'aid' => $contact['addressbookid'],
					'data' => $details,
				);
		} catch (Exception $e) {
			continue;
		}
		// This should never execute.
		/*if(!isset($contacts_addressbook[$contact['addressbookid']])) {
			$contacts_addressbook[$contact['addressbookid']] = array(
				'contacts' => array('type' => 'book',)
			);
		}
		$display = trim($contact['fullname']);
		if(!$display) {
			$vcard = OCA\Contacts\App::getContactVCard($contact['id']);
			if(!is_null($vcard)) {
				$struct = OCA\Contacts\VCard::structureContact($vcard);
				$display = isset($struct['EMAIL'][0])
					? $struct['EMAIL'][0]['value']
					: '[UNKNOWN]';
			}
		}
		$contacts_addressbook[$contact['addressbookid']]['contacts'][] = array(
			'type' => 'contact',
			'id' => $contact['id'],
			'addressbookid' => $contact['addressbookid'],
			'displayname' => htmlspecialchars($display),
			'permissions' =>
				isset($contacts_addressbook[$contact['addressbookid']]['permissions'])
					? $contacts_addressbook[$contact['addressbookid']]['permissions']
					: '0',
		);*/
	}
}
//unset($contacts_alphabet);
//uasort($contacts, 'cmp');

OCP\JSON::success(array(
	'data' => array(
		'contacts' => $contacts,
		'addressbooks' => $active_addressbooks,
		'is_indexed' => OCP\Config::getUserValue(OCP\User::getUser(), 'contacts', 'contacts_indexed', 'no') === 'yes'
	)
));
