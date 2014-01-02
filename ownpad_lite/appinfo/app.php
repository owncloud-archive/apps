<?php

/**
 * ownCloud - ownpad_lite plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\ownpad_lite;

class App {
	// Application key
	const APP_ID = 'ownpad_lite';

	// config key to store Url of the hosted Etherpad service
	const CONFIG_ETHERPAD_URL = 'etherpad_url';

	// Default value for Url of the hosted Etherpad service
	const CONFIG_ETHERPAD_URL_DEFAULT = 'http://beta.etherpad.org/p/';

	// Url of the hosted Etherpad solution
	const CONFIG_USERNAME = 'etherpad_username';

	const ERROR_URL_INVALID = 'invalid URL';

	const ERROR_USERNAME_INVALID = 'invalid username';

	// Localization
	static public $l10n;

	static public function getServiceUrl() {
		return self::getValue(self::CONFIG_ETHERPAD_URL, self::CONFIG_ETHERPAD_URL_DEFAULT);
	}

	static public function setServiceUrl($url) {
		return \OCP\Config::setUserValue(\OCP\User::getUser(), self::APP_ID, self::CONFIG_ETHERPAD_URL, $url);
	}

	static public function getUsername() {
		$username =  self::getValue(self::CONFIG_USERNAME, \OCP\User::getDisplayName());
		if (!$username) {
			$username = \OCP\User::getUser();
		}
		return $username;
	}

	static public function setUsername($username) {
		return \OCP\Config::setUserValue(\OCP\User::getUser(), self::APP_ID, self::CONFIG_USERNAME, $username);
	}

	static protected function getValue($key, $defaultValue) {
		return \OCP\Config::getUserValue(\OCP\User::getUser(), self::APP_ID, $key, $defaultValue);
	}
}

class UrlParam{
	const CONFIG_URL = 'url';
	const CONFIG_USERNAME = 'username';
	const SHARE_SEARCH = 'search';
	const SHARE_WITH = 'target';
	const SHARE_WHAT = 'document';

	static public function getParam($key){
		$param = self::post($key);
		if (!$param) {
			$param = self::get($key);
		}
		return $param;
	}

	static public function get($key){
		return self::getKey($_POST, $key);
	}

	static public function post($key){

		return self::getKey($_POST, $key);
	}

	static protected function getKey($array, $key){
		if (isset($array[$key])) {
			return $array[$key];
		}
		return false;
	}
}

App::$l10n = \OCP\Util::getL10N(App::APP_ID);

\OCP\App::addNavigationEntry( array(
	'id' => 'ownpad_lite_index',
	'order' => 90,
	'href' => \OCP\Util::linkTo( App::APP_ID, 'index.php' ),
	'icon' => \OCP\Util::imagePath( 'settings', 'users.svg' ),
	'name' => App::$l10n->t('My pad') )
);

\OC::$CLASSPATH['OCA\ownpad_lite\Contacts'] = App::APP_ID.'/lib/contacts.php';