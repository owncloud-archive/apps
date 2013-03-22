<?php
/**
 * Copyright (c) 2012, 2013 Thomas Tanghus <thomas@tanghus.net>
 * Copyright (c) 2011 Jakob Sack mail@jakobsack.de
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;

// Check if we are a user
\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('contacts');

// Get active address books. This creates a default one if none exists.
//$ids = OCA\Contacts\Addressbook::activeIds(OCP\USER::getUser());

// Load the files we need
\OCP\App::setActiveNavigationEntry('contacts_index');

$impp_types = Utils\Properties::getTypesForProperty('IMPP');
$adr_types = Utils\Properties::getTypesForProperty('ADR');
$phone_types = Utils\Properties::getTypesForProperty('TEL');
$email_types = Utils\Properties::getTypesForProperty('EMAIL');
$ims = Utils\Properties::getIMOptions();
$im_protocols = array();
foreach($ims as $name => $values) {
	$im_protocols[$name] = $values['displayname'];
}

$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');

\OCP\Util::addscript('', 'multiselect');
\OCP\Util::addscript('', 'jquery.multiselect');
\OCP\Util::addscript('', 'oc-vcategories');
\OCP\Util::addscript('contacts', 'modernizr.custom');
\OCP\Util::addscript('contacts', 'octemplate');
\OCP\Util::addscript('contacts', 'app');
\OCP\Util::addscript('contacts', 'contacts');
\OCP\Util::addscript('contacts', 'storage');
\OCP\Util::addscript('contacts', 'groups');
//\OCP\Util::addscript('contacts', 'expanding');
\OCP\Util::addscript('contacts', 'jquery.combobox');
\OCP\Util::addscript('files', 'jquery.fileupload');
\OCP\Util::addscript('contacts', 'jquery.Jcrop');
\OCP\Util::addStyle('3rdparty/fontawesome', 'font-awesome');
\OCP\Util::addStyle('contacts', 'font-awesome');
\OCP\Util::addStyle('', 'multiselect');
\OCP\Util::addStyle('', 'jquery.multiselect');
\OCP\Util::addStyle('contacts', 'jquery.combobox');
\OCP\Util::addStyle('contacts', 'jquery.Jcrop');
\OCP\Util::addStyle('contacts', 'contacts');

$tmpl = new \OCP\Template( "contacts", "contacts", "user" );
$tmpl->assign('uploadMaxFilesize', $maxUploadFilesize);
$tmpl->assign('uploadMaxHumanFilesize',
	\OCP\Util::humanFileSize($maxUploadFilesize), false);
//$tmpl->assign('addressbooks', OCA\Contacts\Addressbook::all(OCP\USER::getUser()));
$tmpl->assign('phone_types', $phone_types);
$tmpl->assign('email_types', $email_types);
$tmpl->assign('adr_types', $adr_types);
$tmpl->assign('impp_types', $impp_types);
$tmpl->assign('im_protocols', $im_protocols);
$tmpl->printPage();
