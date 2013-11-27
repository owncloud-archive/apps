<?php

// Check if we are a user
OCP\User::checkLoggedIn();

$user = OCP\User::getUser();

$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

$manifestData = $bagit_manager->getManifestData();
$config = $bagit_manager->getConfig();

$description_length = empty($config['description_length']) ? 4000 : $config['description_length'];
$max_sword_mb = empty($config['max_sword_mb']) ? 0 : $config['max_sword_mb'];
$max_zip_mb = empty($config['max_zip_mb']) ? 0 : $config['max_zip_mb'];

// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('previews', $bagit_manager->showPreviews());
$tmpl->assign('bagged_files', $bagit_manager->getBaggedFiles());
$tmpl->assign('description', $manifestData['description']);
$tmpl->assign('description_length', $description_length);
$tmpl->assign('crates', $bagit_manager->getCrateList());
$tmpl->assign('top_for', $bagit_manager->lookUpMint("", 'top'));
$tmpl->assign('selected_crate', $bagit_manager->getSelectedCrate());

if ($manifestData['creators']) {
   $tmpl->assign('creators', array_values($manifestData['creators']));
}
else {
   $tmpl->assign('creators', array());
}
$tmpl->assign('mint_status', $bagit_manager->getMintStatus());
$tmpl->assign('sword_status', $bagit_manager->getSwordStatus());
$tmpl->assign('sword_collections', $bagit_manager->getCollectionsList());
$tmpl->assign('max_sword_mb', $max_sword_mb);
$tmpl->assign('max_zip_mb', $max_zip_mb);
$tmpl->printPage();