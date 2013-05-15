<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');
$user = OCP\User::getUser();

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

//Get an instance of BagItManager
$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

switch ($action){
	case 'add':
		$bagit_manager->addToBag($dir, $file);
		break;
	case 'clear':
		$bagit_manager->clearBag();
		break;
	case 'zip':
		$bagit_manager->createZip();
		break;
}