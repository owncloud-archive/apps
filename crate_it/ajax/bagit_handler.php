<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');
$user = OCP\User::getUser();

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$crate_id = isset($_GET['crate_id']) ? $_GET['crate_id'] : '';
$crate_name = isset($_GET['crate_name']) ? $_GET['crate_name'] : '';
$neworder = isset($_GET['neworder']) ? $_GET['neworder'] : array();
$element_id = isset($_POST['elementid']) ? $_POST['elementid'] : '';
$new_title = isset($_POST['new_title']) ? $_POST['new_title'] : '';
$new_name = isset($_POST['new_name']) ? $_POST['new_name'] : '';
$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';

$action = '';
if (isset($_GET['action'])) {
	$action = $_GET['action'];
} elseif (isset($_POST['action'])){
	$action = $_POST['action'];
}


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
	case 'describe':
		$ok = $bagit_manager->setDescription($description);
		if($ok){
			echo $description;
		} else {
			header('HTTP/1.1 500 Internal Server Error');
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
		$msg = $bagit_manager->getManifestData();
		echo json_encode($msg);
		break;
	case 'add':
		$msg = $bagit_manager->addToBag($file);
		print $msg;
		break;
	case 'clear':
		$bagit_manager->clearBag();
		break;
	case 'delete':
		$ok = $bagit_manager->removeItem($file_id);
		if(!$ok){
			header('HTTP/1.1 500 Internal Server Error');
		}
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
	case 'rename_crate':
		$ok = $bagit_manager->renameCrate($new_name);
		if($ok){
			echo $new_name;
		}
		else {
			header('HTTP/1.1 500 Internal Server Error');
		}
		break;
	case 'preview':
		$preview = $bagit_manager->getPathFromFileId($file_id);
		if($preview){
			//echo $preview;
			$l = OCP\Util::linkTo( "file_previewer", "docViewer.php" );
			$l .= "?fn=".$preview;
			header("Location: ".$l);
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
		
		$epub_name = $bagit_manager->getSelectedCrate();
		header("Content-type:application/epub+zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=".$epub_name.".epub");
		readfile($epub);
		break;
	case 'zip':
		$zip_file = $bagit_manager->createZip();
		if(!isset($zip_file))
		{
			echo "No files in the bag to download";
			break;
		}
		$path_parts = pathinfo($zip_file);
		$filename = $path_parts['basename'];
		//Download file
		if (headers_sent()) throw new Exception('Headers sent.');
		while (ob_get_level() && ob_end_clean());
		if (ob_get_level()) throw new Exception('Buffering is still active.');
		header("Content-type:application/zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=".$filename);
		readfile($zip_file);
		break;
	case 'get_for_codes':
		//need to access the tmpl var
		$results = $bagit_manager->lookUpMint("", 'top');
		foreach ($results as $item) {
			$vars = get_object_vars($item);
			if($vars["rdf:about"] === $level){
				//send skos:narrower array
				echo json_encode(array_values($vars['skos:narrower']));
			}
		}
}