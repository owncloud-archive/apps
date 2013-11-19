<?php

// Check if we are a user
OCP\User::checkLoggedIn();
//OCP\App::setActiveNavigationEntry('cart');

//OCP\Util::addscript('crate_it/3rdparty', 'jstree');
//OCP\Util::addStyle('crate_it/3rdparty/js/themes/default', 'style');

$user = OCP\User::getUser();

$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

$manifestData = $bagit_manager->getManifestData();

// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('previews', $bagit_manager->showPreviews());
$tmpl->assign('bagged_files', array_values($manifestData['titles']));
$tmpl->assign('description', $manifestData['description']);
$tmpl->assign('crates', $bagit_manager->getCrateList());
$tmpl->assign('top_for', $bagit_manager->lookUpMint("", 'top'));
$tmpl->assign('selected_crate', $bagit_manager->getSelectedCrate());
$tmpl->assign('creators', array_values($manifestData['creators']));
$tmpl->printPage();