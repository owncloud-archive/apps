<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

$user = OCP\User::getUser();

$inputDir = OC::$SERVERROOT.'/data/'.$user.'/files/';
$outputDir = OC::$SERVERROOT.'/data/'.$user.'/crate_it';

//create bag if not and store file in the bag
$bag = new BagIt($outputDir);

// add a file; these are relative to the data directory
$bag->addFile($inputDir.$file, $file);

// update the hashes
$bag->update();

