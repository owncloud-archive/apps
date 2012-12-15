<?php

/**
 * ownCloud - user_cas
 *
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @copyright Sixto Martin Garcia. 2012
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


if (OCP\App::isEnabled('user_cas')) {

	require_once 'apps/user_cas/user_cas.php';

	OCP\App::registerAdmin('user_cas', 'settings');

	// register user backend
	OC_User::useBackend( 'CAS' );

	OC::$CLASSPATH['OC_USER_CAS_Hooks'] = 'apps/user_cas/lib/hooks.php';
	OCP\Util::connectHook('OC_User', 'post_login', 'OC_USER_CAS_Hooks', 'post_login');
	OCP\Util::connectHook('OC_User', 'logout', 'OC_USER_CAS_Hooks', 'logout');

	if( isset($_GET['app']) && $_GET['app'] == 'user_cas' ) {

		require_once 'apps/user_cas/auth.php';

		if (!OC_User::login('', '')) {
			$error = true;
			OC_Log::write('cas','Error trying to authenticate the user', OC_Log::DEBUG);
		}
		
		if (isset($_SERVER["QUERY_STRING"]) && !empty($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"] != 'app=user_cas') {
			header( 'Location: ' . OC::$WEBROOT . '/?' . $_SERVER["QUERY_STRING"]);
			exit();
		}

		OC::$REQUESTEDAPP = '';
		OC_Util::redirectToDefaultPage();
	}


	if (!OCP\User::isLoggedIn()) {

		// Load js code in order to render the CAS link and to hide parts of the normal login form
		OCP\Util::addScript('user_cas', 'utils');
	}
}
