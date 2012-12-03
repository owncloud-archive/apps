<?php

/**
* ownCloud - user_saml
*
* @author Sixto Martin <smartin@yaco.es>
* @copyright 2012 Yaco Sistemas // CONFIA
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


if (OCP\App::isEnabled('user_saml')) {

	require_once 'apps/user_saml/user_saml.php';

	OCP\App::registerAdmin('user_saml', 'settings');

	// register user backend
	OC_User::useBackend( 'SAML' );

	OC::$CLASSPATH['OC_USER_SAML_Hooks'] = 'apps/user_saml/lib/hooks.php';
	OCP\Util::connectHook('OC_User', 'post_login', 'OC_USER_SAML_Hooks', 'post_login');
	OCP\Util::connectHook('OC_User', 'logout', 'OC_USER_SAML_Hooks', 'logout');

	if( isset($_GET['app']) && $_GET['app'] == 'user_saml' ) {

		require_once 'apps/user_saml/auth.php';

		if (!OC_User::login('', '')) {
			$error = true;
			OC_Log::write('saml','Error trying to authenticate the user', OC_Log::DEBUG);
		}
		
		if (isset($_SERVER["QUERY_STRING"]) && !empty($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"] != 'app=user_saml') {
			header( 'Location: ' . OC::$WEBROOT . '/?' . $_SERVER["QUERY_STRING"]);
			exit();
		}

        OC::$REQUESTEDAPP = '';
		OC_Util::redirectToDefaultPage();
	}


	if (!OCP\User::isLoggedIn()) {

		// Load js code in order to render the SAML link and to hide parts of the normal login form
		OCP\Util::addScript('user_saml', 'utils');
	}
}
