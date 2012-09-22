<?php

/**
 * ownCloud - ownpad_lite plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\ownpad_lite;

$serviceUrl = isset($_POST[App::CONFIG_ETHERPAD_URL]) ? $_POST[App::CONFIG_ETHERPAD_URL] : false;
$username = isset($_POST[App::CONFIG_USERNAME]) ? $_POST[App::CONFIG_USERNAME] : false;

$errors = array();

$username = preg_replace('/[^0-9a-zA-Z\.\-_]*/i', '', $username);
if ($username) {
	App::setUsername($username);
} else {
	$errors[] = App::ERROR_USERNAME_INVALID;
}

if ($serviceUrl) {
	if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $serviceUrl)) {
		App::setServiceUrl($serviceUrl);
	} else {
		$errors[] = App::ERROR_URL_INVALID;
	}
}

\OCP\JSON::success(array('data'=>$errors));
