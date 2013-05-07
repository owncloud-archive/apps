<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');
session_start();

$doc_root = $_SERVER["DOCUMENT_ROOT"];
$user = OCP\User::getUser();

//get file and story it in session
$cart = $_SESSION['cart'];
$action = $_GET['action'];
print phpinfo();

$inputDir = $doc_root.'/owncloud/data/'.$user.'/files';
$outputDir = $doc_root.'/owncloud/data/'.$user.'/crate_it/'.date("Y-m-d H:i:s").'/';

if ($cart) {
	$items = explode(',',$cart);
}

//if true, good; if false, zip creation failed
$result = \OCA\crate_it\lib\PackageManager::create_zip($items, $inputDir, $outputDir.$user.'-package.zip');

print $result;