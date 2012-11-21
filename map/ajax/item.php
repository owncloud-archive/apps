<?php

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

OCP\JSON::checkAppEnabled('map');

if(! isset($_REQUEST['action'])) exit;

if($_REQUEST['action'] == 'load') {
	$items = array();
	if($_REQUEST['category'] != '')
		$items = OC_Generic_Map_Loader::findByType($_REQUEST['category']);
	OCP\JSON::success(array('data' => $items));
	exit();
}

elseif($_REQUEST['action'] == 'add') {
	$item = new OC_MapItem();
	$item->fromArray(array(
		'lat' => $_REQUEST['lat'],
		'lon' => $_REQUEST['lon'],
		'name' => $_REQUEST['name'],
		'type' => $_REQUEST['type'],
	));
	$item = OC_MapItem::add($item);
	OCP\JSON::success(array('data' => $item->toArray()));
	exit();
}

OC_JSON::error();
exit();
