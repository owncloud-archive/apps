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
	$active_addressbooks = OC_Contacts_Addressbook::active(OCP\USER::getUser());
} else {
	// called each time more contacts has to be shown.
	$active_addressbooks = array(OC_Contacts_Addressbook::find($aid));
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
	OC_Contacts_VCard::all($ids)
);
/*foreach($ids as $id) {
	if($id) {
		$contacts_alphabet = array_merge(
				$contacts_alphabet,
				OC_Contacts_VCard::all($id, $offset, 50)
		);
	}
}*/

uasort($contacts_alphabet, 'cmp');

$contacts = array();


// Our new array for the contacts sorted by addressbook
if($contacts_alphabet) {
	foreach($contacts_alphabet as $contact) {
		$vcard = OC_VObject::parse($contact['carddata']);
		if(is_null($vcard)) {
			continue;
		}
		$details = OC_Contacts_VCard::structureContact($vcard);
		$contacts[] = array(
				'id' => $contact['id'],
				'aid' => $contact['addressbookid'],
				'data' => $details,
			);
		// This should never execute.
		/*if(!isset($contacts_addressbook[$contact['addressbookid']])) {
			$contacts_addressbook[$contact['addressbookid']] = array(
				'contacts' => array('type' => 'book',)
			);
		}
		$display = trim($contact['fullname']);
		if(!$display) {
			$vcard = OC_Contacts_App::getContactVCard($contact['id']);
			if(!is_null($vcard)) {
				$struct = OC_Contacts_VCard::structureContact($vcard);
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
uasort($contacts_alphabet, 'cmp');

OCP\JSON::success(array('data' => array('contacts' => $contacts, 'addressbooks' => $active_addressbooks)));
