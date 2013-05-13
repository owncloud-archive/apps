<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');

$user = OCP\User::getUser();

$outputDir = OC::$SERVERROOT.'/data/'.$user.'/crate_it';

//create a bag at the outputDir
$bag = new BagIt($outputDir);

$bag->package($outputDir, 'zip');

echo "Zip created at ".OC::$SERVERROOT.'/data/'.$user;
//call zip class
/*$filename = OC_Helper::tmpFile('.zip');

$zip=new ZipArchive();
$ab = $zip->open($filename, ZipArchive::OVERWRITE);
$res = $zip->addFile($inputDir.$items[0], $items[0]);
//$zipFile = OC_Archive_ZIP::open($filename);
//$res = $zipFile->addFile($items[0], $inputDir.$items[0]);
echo "res : ".$res;*/



