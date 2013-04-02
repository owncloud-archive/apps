<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('carddavmate');
// load required style sheets:
OCP\Util::addStyle('carddavmate', 'oc_app');
OCP\Util::addStyle('carddavmate', 'default');
OCP\Util::addStyle('carddavmate', 'lib/jquery.tagsinput');
// load required javascripts:
OCP\Util::addScript('carddavmate', 'lib/jquery-1.7.2.min');
OCP\Util::addScript('carddavmate', 'lib/jshash-2.2_sha256');
OCP\Util::addScript('carddavmate', 'lib/jquery.tagsinput');
OCP\Util::addScript('carddavmate', 'lib/jquery.quicksearch');
OCP\Util::addScript('carddavmate', 'lib/jquery.placeholder-1.1.9');
OCP\Util::addScript('carddavmate', 'config');
OCP\Util::addScript('carddavmate', 'localization');
OCP\Util::addScript('carddavmate', 'interface');
OCP\Util::addScript('carddavmate', 'vcard_rfc_regex');
OCP\Util::addScript('carddavmate', 'webdav_protocol');
OCP\Util::addScript('carddavmate', 'common');
OCP\Util::addScript('carddavmate', 'resource');
OCP\Util::addScript('carddavmate', 'addressbook');
OCP\Util::addScript('carddavmate', 'data_process');
OCP\Util::addScript('carddavmate', 'main');
OCP\Util::addScript('carddavmate', 'oc_app');
// unfortunately ownCloud's default jquery-UI makes it behave awkward:
OCP\Util::addStyle('carddavmate', 'lib/jquery-ui-1.8.20.custom');
OCP\Util::addScript('carddavmate', 'lib/jquery-ui-1.8.20.custom.min');

OCP\App::setActiveNavigationEntry('carddavmate_index');
$carddavUrl = OCP\Util::linkToRemote('carddav') . 'addressbooks/';
$tmpl = new OCP\Template( "carddavmate", "mate", "user" );
$tmpl->assign("carddavUrl", $carddavUrl);
$tmpl->printPage();
