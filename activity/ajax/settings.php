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

$notify_email = $notify_stream = array();

$l = OCP\Util::getL10N('activity');
$types = \OCA\Activity\Data::getNotificationTypes($l);
foreach ($types as $type => $data) {
	if (!empty($_POST[$type . '_email'])) {
		$notify_email = array_merge($notify_email, $data['types']);
	}
	if (!empty($_POST[$type . '_stream'])) {
		$notify_stream = array_merge($notify_stream, $data['types']);
	}
}

OCP\Config::setUserValue(OCP\User::getUser(), 'activity', 'notify_email', serialize($notify_email));
OCP\Config::setUserValue(OCP\User::getUser(), 'activity', 'notify_stream', serialize($notify_stream));

OC_JSON::success(array("data" => array( "message" => $l->t('Your settings have been updated.'))));
