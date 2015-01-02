<?php

/**
 * ownCloud - push notifications app
 *
 * @author Frank Karlitschek
 * @copyright 2014 Frank Karlitschek frank@owncloud.org
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */


// Check permissions
OCP\JSON::callCheck();
OC_JSON::checkLoggedIn();

// load L10N
$l = \OC::$server->getL10N('pushnotifications');

$username =  OC_User::getUser();
$pushnotificationid = $_POST["pushnotificationid"];

// store the new ID
if( \OCP\Config::setUserValue($username, 'pushnotifications', 'pushid', $pushnotificationid) ) {
	OC_JSON::success(array("data" => array( "message" => $l->t('Your id has been changed.'), "username" => $username, 'pushnotificationid' => $pushnotificationid )));
} else {
	OC_JSON::error(array("data" => array( "message" => $l->t("Unable to change the id"), 'pushnotificationid' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'pushnotifications', 'pushid', '') )));
}
