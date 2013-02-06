<?php

/**
* ownCloud - First Run Wizard
*
* @author Frank Karlitschek
* @copyright 2012 Frank Karlitschek frank@owncloud.org
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


// Check if we are a user
OCP\User::checkLoggedIn();

//links to clients
$clients = array(
	'desktop' => OC_Config::getValue('customclient_desktop', 'http://owncloud.org/sync-clients/'),
	'android' => OC_Config::getValue('customclient_android', 'https://play.google.com/store/apps/details?id=com.owncloud.android'),
	'ios'     => OC_Config::getValue('customclient_ios', 'https://itunes.apple.com/us/app/owncloud/id543672169?mt=8')
);

$tmpl = new OCP\Template( 'firstrunwizard', 'wizard', '' );
$tmpl->assign('logo', OCP\Util::linkTo('core','img/logo-inverted.svg'));
$tmpl->assign('clients', $clients);
$tmpl->printPage();

