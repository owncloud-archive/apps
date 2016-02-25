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
	$ocVersion = implode('.',OCP\Util::getVersion());
	if (version_compare($ocVersion,'5.0','<')) {
		if ( ! function_exists('p')) {
			function p($string) {
				print(OC_Util::sanitizeHTML($string));
			}
		}
	}

	require_once 'user_saml/user_saml.php';

	OCP\App::registerAdmin('user_saml', 'settings');

	// register user backend
	OC_User::useBackend( 'SAML' );

	OC::$CLASSPATH['OC_USER_SAML_Hooks'] = 'user_saml/lib/hooks.php';
	OCP\Util::connectHook('OC_User', 'post_createUser', 'OC_USER_SAML_Hooks', 'post_createUser');
	OCP\Util::connectHook('OC_User', 'post_login', 'OC_USER_SAML_Hooks', 'post_login');
	OCP\Util::connectHook('OC_User', 'logout', 'OC_USER_SAML_Hooks', 'logout');

	$forceLogin = OCP\Config::getAppValue('user_saml', 'saml_force_saml_login', false)
		&& shouldEnforceAuthentication();


	if( (isset($_GET['app']) && $_GET['app'] == 'user_saml') || (!OCP\User::isLoggedIn() && $forceLogin && !isset($_GET['admin_login']) )) {

		require_once 'user_saml/auth.php';

		if (!OC_User::login('', '')) {
			$error = true;
			OCP\Util::writeLog('saml','Error trying to authenticate the user', OCP\Util::DEBUG);
		}
		
		if (isset($_GET["linktoapp"])) {
			$path = OC::$WEBROOT . '/?app='.$_GET["linktoapp"];
            if (isset($_GET["linktoargs"])) {
				$path .= '&'.urldecode($_GET["linktoargs"]);
			}
			header( 'Location: ' . $path);
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


/*
 * Checks if requiring SAML authentication on current URL makes sense when
 * forceLogin is set.
 *
 * Disables it when using the command line too
 */
function shouldEnforceAuthentication()
{
	if (OC::$CLI) {
		return false;
	}

	$script = basename($_SERVER['SCRIPT_FILENAME']);
	return !in_array($script,
		array(
			'cron.php',
			'public.php',
			'remote.php',
			'status.php',
		)
	);
}
