<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');
$user = OCP\User::getUser();

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$neworder = isset($_GET['neworder']) ? $_GET['neworder'] : array();
$element_id = isset($_POST['elementid']) ? $_POST['elementid'] : '';
$newvalue = isset($_POST['newvalue']) ? $_POST['newvalue'] : '';

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
	case 'update':
		$bagit_manager->updateOrder($neworder);
		break;
	case 'edit_title':
		$ok = $bagit_manager->editTitle($element_id, $newvalue);
		if($ok){
			echo $newvalue;
		}
		else {
			header('HTTP/1.1 500 Internal Server Error');
		}
		break;
	case 'epub':
		$epub = $bagit_manager->createEpub();
		if(!isset($epub))
		{
			echo "No epub";
			break;
		}
		
		if (headers_sent()) throw new Exception('Headers sent.');
		while (ob_get_level() && ob_end_clean());
		if (ob_get_level()) throw new Exception('Buffering is still active.');
		header("Content-type:application/epub+zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=crate.epub");
		readfile($epub);
		break;
	case 'zip':
		$zip_file = $bagit_manager->createZip();
		if(!isset($zip_file))
		{
			echo "No files in the bag to download";
			break;
		}
		//Download file
		if (headers_sent()) throw new Exception('Headers sent.');
		while (ob_get_level() && ob_end_clean());
		if (ob_get_level()) throw new Exception('Buffering is still active.');
		header("Content-type:application/zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=crate.zip");
		readfile($zip_file);
		break;
}