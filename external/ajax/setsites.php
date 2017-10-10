<?php

/**
 * 2012 Frank Karlitschek frank@owncloud.org
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */


OCP\JSON::checkAppEnabled('external');
OCP\User::checkAdminUser();
OCP\JSON::callCheck();

$valid_targets = array('_blank', '_top', '_self');  // TODO: import this from external/settings.php

$sites = array();
for ($i = 0; $i < sizeof($_POST['site_name']); $i++) {
	if (!empty($_POST['site_name'][$i]) && !empty($_POST['site_url'][$i])) {
		// make sure site_target is a safe link target type
		if (!in_array($_POST['site_target'][$i], $valid_targets)) {
		    throw new Exception('Invalid site target, must be one of: ' . implode(", ", $valid_targets));
		}
		array_push($sites, array(
			strip_tags($_POST['site_name'][$i]),
			strip_tags($_POST['site_url'][$i]),
			strip_tags($_POST['site_icon'][$i]),
			strip_tags($_POST['site_target'][$i]),
		));
	}
}

$l = \OC::$server->getL10N('external');

foreach($sites as $site) {
	if (strpos($site[1], 'https://') === 0) {
		continue;
	}
	if (strpos($site[1], 'http://') === 0) {
		continue;
	}
	if (strncmp($site[1], '/', 1) === 0) {
		continue;
	}
	OC_JSON::error(array("data" => array( "message" => $l->t('Please enter valid urls - they have to start with either http://, https:// or /') )));
	return;
}

if (sizeof($sites) == 0) {
	$appConfig = \OC::$server->getAppConfig();
	$appConfig->deleteKey('external', 'sites');
} else {
	OCP\Config::setAppValue('external', 'sites', json_encode($sites));
}
OC_JSON::success(array("data" => array( "message" => $l->t("External sites saved.") )));
