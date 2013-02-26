<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();
$view = $_POST['v'];
switch($view) {
	case 'agendaWeek':
	case 'month';
	case 'list':
		break;
	default:
		OCP\JSON::error(array('message'=>'unexpected parameter: ' . $view));
		exit;
}
OCP\Config::setUserValue(OCP\USER::getUser(), 'calendar', 'currentview', $view);
OCP\JSON::success();