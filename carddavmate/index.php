<?php
// init owncloud
try {
	// OC < 4
	include_once('../../lib/base.php');
} catch(Exception $e) {
	// OC >= 4
	require_once('lib/base.php');
}
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('carddavmate');
// load required style sheets:
OC_Util::addStyle('carddavmate', 'oc_app');
OC_Util::addStyle('carddavmate', 'default');
OC_Util::addStyle('carddavmate', 'lib/jquery.tagsinput');
// load required javascripts:
OC_Util::addScript('carddavmate', 'lib/jquery-1.7.2.min');
OC_Util::addScript('carddavmate', 'lib/jshash-2.2_sha256');
OC_Util::addScript('carddavmate', 'lib/jquery.tagsinput');
OC_Util::addScript('carddavmate', 'lib/jquery.quicksearch');
OC_Util::addScript('carddavmate', 'lib/jquery.placeholder-1.1.9');
OC_Util::addScript('carddavmate', 'config');
OC_Util::addScript('carddavmate', 'localization');
OC_Util::addScript('carddavmate', 'interface');
OC_Util::addScript('carddavmate', 'vcard_rfc_regex');
OC_Util::addScript('carddavmate', 'webdav_protocol');
OC_Util::addScript('carddavmate', 'common');
OC_Util::addScript('carddavmate', 'resource');
OC_Util::addScript('carddavmate', 'addressbook');
OC_Util::addScript('carddavmate', 'data_process');
OC_Util::addScript('carddavmate', 'main');
OC_Util::addScript('carddavmate', 'oc_app');
// unfortunately ownCloud's default jquery-UI makes it behave awkward:
OC_Util::addStyle('carddavmate', 'lib/jquery-ui-1.8.19.custom');
OC_util::addScript('carddavmate', 'lib/jquery-ui-1.8.19.custom.min');

OC_App::setActiveNavigationEntry('carddavmate_index');
$carddavUrl = OC_Helper::linkTo('contacts', 'carddav.php', null, true) . '/addressbooks/';
$tmpl = new OC_TEMPLATE( "carddavmate", "mate", "user" );
$tmpl->assign("carddavUrl", $carddavUrl);
$tmpl->printPage();
