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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*/

OCP\Util::addScript('files_external', 'settings');
OCP\Util::addStyle('files_external', 'settings');
OCP\Util::addScript('activity', 'settings');

$l=OC_L10N::get('activity');
$notify_email = unserialize(OCP\Config::getUserValue(OCP\User::getUser(), 'activity', 'notify_email', serialize(\OCA\Activity\Data::getUserDefaultSetting('email'))));
$notify_stream = unserialize(OCP\Config::getUserValue(OCP\User::getUser(), 'activity', 'notify_stream', serialize(\OCA\Activity\Data::getUserDefaultSetting('stream'))));
$types = \OCA\Activity\Data::getNotificationTypes($l);

$activities = array();
foreach ($types as $type => $data)
{
	$checked_email = array_intersect($data['types'], $notify_email);
	$checked_stream = array_intersect($data['types'], $notify_stream);
	$activities[$type] = array(
		'desc'		=> $data['desc'],
		'email'		=> !empty($checked_email),
		'stream'	=> !empty($checked_stream),
	);
}

$template = new OCP\Template('activity', 'personal');
$template->assign('activities', $activities);
return $template->fetchPage();
