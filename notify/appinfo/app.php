<?php
/**
* ownCloud - user notifications
*
* @author Florian Hülsmann
* @copyright 2012 Florian Hülsmann <fh@cbix.de>
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
*
*/

$l = OC_L10N::get('notify');
OC::$CLASSPATH['OC_Notify'] = 'apps/notify/lib/notify.php';
if(OCP\User::isLoggedIn()) {
	// this makes no sense for guests, so only for users
	OCP\Util::addScript('notify', 'notifications');
	OCP\Util::addStyle('notify', 'notifications');
	OCP\Util::addStyle('notify', 'customNotifications');
	OCP\Util::addHeader('link', array(
		'rel' => 'alternate',
		'type' => 'application/atom+xml',
		'title' => $l->t('ownCloud notifications (%s)', 'Atom 1.0'),
		'href' => OCP\Util::linkToRemote('notify_feed') . 'feed.atom'
	));
	OCP\Util::addHeader( 'link', array(
		'rel' => 'alternate',
		'type' => 'application/rss+xml',
		'title' => $l->t('ownCloud notifications (%s)', 'RSS 2.0'),
		'href' => OCP\Util::linkToRemote('notify_feed') . 'feed.atom'
	));
	OCP\App::registerPersonal('notify', 'personal');
	OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_Notify', 'post_deleteUser');
	OCP\Util::connectHook('OCP\Share', 'post_shared', 'OC_Notify', 'post_shared');
}
