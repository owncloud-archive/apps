<?php

// Check if we are a user
OCP\User::checkLoggedIn();
//OCP\App::setActiveNavigationEntry('cart');

//OCP\Util::addscript('crate_it/3rdparty', 'jstree');
//OCP\Util::addStyle('crate_it/3rdparty/js/themes/default', 'style');

$user = OCP\User::getUser();

$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('bagged_files', $bagit_manager->getFetchData());
$tmpl->assign('crates', $bagit_manager->getCrateList());
$tmpl->printPage();