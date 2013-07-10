<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');
$user = OCP\User::getUser();

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$crate_id = isset($_GET['crate_id']) ? $_GET['crate_id'] : '';
$crate_name = isset($_GET['crate_name']) ? $_GET['crate_name'] : '';
$neworder = isset($_GET['neworder']) ? $_GET['neworder'] : array();
$element_id = isset($_POST['elementid']) ? $_POST['elementid'] : '';
$newvalue = isset($_POST['new_title']) ? $_POST['new_title'] : '';

//Get an instance of BagItManager
$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

switch ($action){
	case 'create':
		$msg = $bagit_manager->createCrate($crate_name);
		if(!$msg){
			header('HTTP/1.1 400 No name given');
		}
		else {
			print $msg;
		}
		break;
	case 'switch':
		$ok = $bagit_manager->switchCrate($crate_id);
		if(!$ok){
			header('HTTP/1.1 400 No name',400);
		}
		break;
	case 'get_crate':
		$msg = $bagit_manager->getSelectedCrate();
		print $msg;
		break;
	case 'get_items':
		$msg = $bagit_manager->getFetchData();
		echo json_encode($msg);
		break;
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
		$ok = $bagit_manager->editTitle($element_id, $new_title);
		if($ok){
			echo $new_title;
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