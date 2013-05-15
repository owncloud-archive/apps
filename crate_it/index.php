<?php

// Check if we are a user
OCP\User::checkLoggedIn();
//OCP\App::setActiveNavigationEntry('cart');

$user = OCP\User::getUser();

$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('bagged_files', $bagit_manager->getFetchData());
$tmpl->printPage();