<?php

/**
 * ownCloud - Persona plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */
namespace OCA\User_persona;

class App{
	const APP_ID = 'user_persona';
	const APP_PATH = 'user_persona/';
	
	public static function init(){
		//check if curl extension installed
		if (!in_array('curl', get_loaded_extensions())) {
			\OCP\Util::writeLog(self::APP_ID, 'This app needs cUrl PHP extension', \OCP\Util::DEBUG);
			return false;
		}

		\OC::$CLASSPATH['OCA\User_persona\Policy'] = self::APP_PATH . 'lib/policy.php';
		\OCP\App::registerAdmin(self::APP_ID, 'settings');

		if (!\OCP\User::isLoggedIn()) {
			\OC::$CLASSPATH['OCA\User_persona\Validator'] = self::APP_PATH . 'lib/validator.php';
			\OC::$CLASSPATH['OC_USER_PERSONA'] = self::APP_PATH . 'user_persona.php';

			\OC_User::useBackend('persona');
			\OCP\Util::connectHook('OC_User', 'post_login', "OCA\User_persona\Validator", "postlogin_hook");
			\OCP\Util::addScript(self::APP_ID, 'utils');
		}
	}
}

if (\OCP\App::isEnabled(App::APP_ID)) {
	App::init();
}
