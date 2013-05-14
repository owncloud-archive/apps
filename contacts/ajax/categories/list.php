<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

$catmgr = OCA\Contacts\App::getVCategories();
$categories = $catmgr->categories(OC_VCategories::FORMAT_MAP);
foreach($categories as &$category) {
	$ids = array();
	$contacts = $catmgr->itemsForCategory(
			$category['name'],
			array(
				'tablename' => '*PREFIX*contacts_cards',
				'fields' => array('id',),
			));
	foreach($contacts as $contact) {
		$ids[] = $contact['id'];
	}
	$category['contacts'] = $ids;
}

$favorites = $catmgr->getFavorites();

// workaround for https://github.com/owncloud/core/issues/2814
$sharedAddressbooks = array();
$maybeSharedAddressBook = OCP\Share::getItemsSharedWith('addressbook', OCA\Contacts\Share_Backend_Addressbook::FORMAT_ADDRESSBOOKS);
foreach($maybeSharedAddressBook as $sharedAddressbook) {
	if(isset($sharedAddressbook['id']) && OCA\Contacts\Addressbook::find($sharedAddressbook['id'])) {
		$sharedAddressbooks[] = $sharedAddressbook;
	}
}

OCP\JSON::success(array(
	'data' => array(
		'categories' => $categories,
		'favorites' => $favorites,
		'shared' => $sharedAddressbooks,
		'lastgroup' => OCP\Config::getUserValue(
						OCP\User::getUser(),
						'contacts',
						'lastgroup', 'all'),
		'sortorder' => OCP\Config::getUserValue(
						OCP\User::getUser(),
						'contacts',
						'groupsort', ''),
		)
	)
);
