<?php

// Check if we are a user
OCP\User::checkLoggedIn();
//OCP\App::setActiveNavigationEntry('cart');

$user = OCP\User::getUser();

//get the files which are in the cart
$bagDir = OC::$SERVERROOT.'/data/'.$user.'/crate_it/crate';
$bag = new BagIt($bagDir);
$fetchItems = $bag->fetch->getData();
$items = array();

foreach ($fetchItems as $fetch){
	array_push($items, $fetch['filename']);
}

// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('bagged_files', $items);
$tmpl->printPage();