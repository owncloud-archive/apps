<?php

/**
* ownCloud - user_pwauth
*
* @author Clément Véret
* @copyright 2012 Clément Véret veretcle+owncloud@mateu.be
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

require_once 'user_pwauth/user_pwauth.php';

OC_APP::registerAdmin('user_pwauth','settings');

// define UID_LIST (first - last user;user;user)
define('OC_USER_BACKEND_PWAUTH_UID_LIST', '1000-1010');
define('OC_USER_BACKEND_PWAUTH_PATH', '/usr/sbin/pwauth');


OC_User::registerBackend('PWAUTH');
OC_User::useBackend('PWAUTH');

// add settings page to navigation
$entry = array(
	'id' => "user_pwauth_settings",
	'order'=>1,
	'href' => OC_Helper::linkTo( "user_pwauth", "settings.php" ),
	'name' => 'PWAUTH'
);
