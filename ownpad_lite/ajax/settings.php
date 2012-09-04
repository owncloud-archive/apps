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

//TODO: Validation!!!

if ($username){
	App::setUsername($username);
}

if ($serviceUrl){
	App::setServiceUrl($serviceUrl);
}

