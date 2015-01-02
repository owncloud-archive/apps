<?php

/**
 * ownCloud - push notifications app
 *
 * @author Frank Karlitschek
 * @copyright 2014 Frank Karlitschek frank@owncloud.org
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */


namespace OCA\pushnotifications;

// load external lib
require('Pushover.php');


/**
 * Class to send push notifications
 * @package OCA\pushnotifications
 */
class Push {

	/**
	 * registerthe hooks. Only one for now.
	 *
	 */
	public static function registerHooks() {

		// listen to the activity events
		\OCP\Util::connectHook('OC_Activity', 'post_event', 'OCA\pushnotifications\Push', 'event');
	}


	/**
	 * handle incoming events
	 *
	 */
	public static function event($params) {
		
		// send a push notification
		Push::send($params['subject'].' '.$params['message'].' '.$params['file'],$params['link']);
	}


	/**
	 * send push notifications. Currently only pushover.net is supported
	 *
	 */
	public static function send($subject,$url) {

		$app_key = \OCP\Config::getSystemValue('pushnotifications_pushover_app','');
		$pushid = trim(\OCP\Config::getUserValue(\OCP\User::getUser(), 'pushnotifications', 'pushid', ''));

		if(!empty($pushid)) {
			$push = new \Pushover();
			$push->setToken($app_key);
			$push->setUser($pushid);
			$push->setMessage($subject);
			$push->setUrl($url);
			$push->setUrlTitle('ownCloud');
			$push->setCallback($url);
			$push->setTimestamp(time());
			$push->setDebug(true);
			$go = $push->send();
			unset($push);
		}
	}

}

