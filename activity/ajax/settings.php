<?php

/**
* ownCloud - Activity App
*
* @author Joas Schilling
* @copyright 2014 Joas Schilling nickvergessen@gmx.de
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('activity');
OCP\JSON::callCheck();

$notify_email = $notify_stream = array();

$l = OCP\Util::getL10N('activity');
$types = \OCA\Activity\Data::getNotificationTypes($l);
foreach ($types as $type => $desc) {
	OCP\Config::setUserValue(OCP\User::getUser(), 'activity', 'notify_email_' . $type, !empty($_POST[$type . '_email']));
	OCP\Config::setUserValue(OCP\User::getUser(), 'activity', 'notify_stream_' . $type, !empty($_POST[$type . '_stream']));
}

$email_batch_time = 3600;
if ($_POST['notify_setting_batchtime'] == 1) {
	$email_batch_time = 3600 * 24;
}
if ($_POST['notify_setting_batchtime'] == 2) {
	$email_batch_time = 3600 * 24 * 7;
}
OCP\Config::setUserValue(OCP\User::getUser(), 'activity', 'notify_setting_batchtime', $email_batch_time);

OC_JSON::success(array("data" => array( "message" => $l->t('Your settings have been updated.'))));
