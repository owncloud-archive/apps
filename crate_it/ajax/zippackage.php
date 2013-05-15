<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');

$user = OCP\User::getUser();

$crateRoot = OC::$SERVERROOT.'/data/'.$user.'/crate_it'; //TODO make this a constant
$bagDir = $crateRoot.'/crate';

$tmp = OC_Helper::tmpFolder();
OC_Helper::copyr($bagDir, $tmp);

//create a bag at the outputDir
$bag = new BagIt($tmp);

if(count($bag->getBagErrors(true)) == 0){
	//use the fetch file to add data to bag, but don't use $bag->fetch->download(), yea I know it's weird
	//but have to do at this time
	$fetchItems = $bag->fetch->getData();
	foreach ($fetchItems as $item){
		$bag->addFile($item['url'], $item['filename']);
	}
	$bag->update();
	
	//see if there's one already
	//check if it's latest, if so only create the package
	if(!file_exists($crateRoot.'/packages')){
		mkdir($crateRoot.'/packages');
	}
	$bag->package($crateRoot.'/packages/crate', 'zip');
}

echo "Zip created at ".$crateRoot.'/packages/';

//call zip class
/*$filename = OC_Helper::tmpFile('.zip');

$zip=new ZipArchive();
$ab = $zip->open($filename, ZipArchive::OVERWRITE);
$res = $zip->addFile($inputDir.$items[0], $items[0]);
//$zipFile = OC_Archive_ZIP::open($filename);
//$res = $zipFile->addFile($items[0], $inputDir.$items[0]);
echo "res : ".$res;*/



