<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

$catmgr = OC_Contacts_App::getVCategories();
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

OCP\JSON::success(array(
	'data' => array(
		'categories' => $categories,
		'favorites' => $favorites,
		)
	)
);
