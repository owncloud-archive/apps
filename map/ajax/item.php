<?php

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

OCP\JSON::checkAppEnabled('map');

if(! isset($_GET['action'])) exit;

if($_GET['action']=='load') {
	$items = OC_Map::findAll();
	OCP\JSON::success($items);
	exit();
}
OC_JSON::error();
exit();
