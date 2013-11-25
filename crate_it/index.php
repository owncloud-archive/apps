<?php

// Check if we are a user
OCP\User::checkLoggedIn();

$user = OCP\User::getUser();

$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

$manifestData = $bagit_manager->getManifestData();
$config = $bagit_manager->getConfig();

$description_length = empty($config['description_length']) ? 4000 : $config['description_length'];


// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('previews', $bagit_manager->showPreviews());
$tmpl->assign('bagged_files', $bagit_manager->getBaggedFiles());
$tmpl->assign('description', $manifestData['description']);
$tmpl->assign('description_length', $description_length);
$tmpl->assign('crates', $bagit_manager->getCrateList());
$tmpl->assign('top_for', $bagit_manager->lookUpMint("", 'top'));
$tmpl->assign('selected_crate', $bagit_manager->getSelectedCrate());
$tmpl->assign('creators', array_values($manifestData['creators']));
$tmpl->assign('mint_status', $bagit_manager->getMintStatus());
$tmpl->printPage();