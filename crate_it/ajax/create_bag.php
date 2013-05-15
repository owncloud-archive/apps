<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

$user = OCP\User::getUser();

$crateRoot = OC::$SERVERROOT.'/data/'.$user.'/crate_it';//TODO this must be a constant

if(!file_exists($crateRoot)){
	mkdir($crateRoot);
}
$inputDir = OC::$SERVERROOT.'/data/'.$user.'/files';
$bagDir = $crateRoot.'/crate';
$dataDir = 'data';

//create bag if not and store file in the bag
$bag = new BagIt($bagDir);
	
if(basename($dir) === 'Shared'){
	//TODO need to fetch the url from relevant location
}
else if(substr($dir, -1) === '/'){
	$inputDir .= '/';
	$dataDir .= '/';
}
else{
	$inputDir .= $dir.'/';
	$dataDir .= $dir.'/';
}

//add the file urls to fetch.txt so when you package the bag,
//you can populate the data dir with those files
$fetchItems = $bag->fetch->getData();
$file_exists = false;
foreach ($fetchItems as $item){
	if($item['url'] === $inputDir.$file){
		$file_exists = true;
		break;
	}
}
if(!$file_exists){
	$bag->fetch->add($inputDir.$file, $dataDir.$file);
}

// update the hashes
$bag->update();

