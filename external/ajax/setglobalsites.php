<?php
/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
OCP\JSON::checkAdminUser();
OCP\JSON::callCheck();

$sites = array();
for ($i = 0; $i < sizeof($_POST['site_name']); $i++) {
	if (!empty($_POST['site_name'][$i]) && !empty($_POST['site_url'][$i])) {
		array_push($sites, array(strip_tags($_POST['site_name'][$i]), strip_tags($_POST['site_url'][$i])));
	}
}

if (sizeof($sites) == 0){
	OCP\Config::setAppValue('external', 'globalSites', '');
}
else {
	OCP\Config::setAppValue('external', 'globalSites', json_encode($sites));
}

OCP\JSON::success();
