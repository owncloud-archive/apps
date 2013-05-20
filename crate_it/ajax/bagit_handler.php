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
		$msg = $bagit_manager->addToBag($dir, $file);
		print $msg;
		break;
	case 'clear':
		$bagit_manager->clearBag();
		break;
	case 'zip':
		$zip_file = $bagit_manager->createZip();
		if(!isset($zip_file))
		{
			echo "No files in the bag to download";
			break;
		}
		//Download file
		header("Content-type:application/zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=crate.zip");
		readfile($zip_file);
		break;
}