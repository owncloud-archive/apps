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
OCP\Util::addStyle('activity', 'settings');

$l=OC_L10N::get('activity');
$types = \OCA\Activity\Data::getNotificationTypes($l);

$user = OCP\User::getUser();
$activities = array();
foreach ($types as $type => $desc) {
	$activities[$type] = array(
		'desc'		=> $desc,
		'email'		=> \OCA\Activity\Data::getUserSetting($user, 'email', $type),
		'stream'	=> \OCA\Activity\Data::getUserSetting($user, 'stream', $type),
	);
}

$template = new OCP\Template('activity', 'personal');
$template->assign('activities', $activities);
return $template->fetchPage();
