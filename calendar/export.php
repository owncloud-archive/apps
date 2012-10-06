<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');
$cal = isset($_GET['calid']) ? $_GET['calid'] : NULL;
$event = isset($_GET['eventid']) ? $_GET['eventid'] : NULL;
if(isset($cal)) {
	$calendar = OC_Calendar_App::getCalendar($cal, true);
	if(!$calendar) {
		header('HTTP/1.0 404 Not Found');
		exit;
	}
	header('Content-Type: text/calendar');
	header('Content-Disposition: inline; filename=' . str_replace(' ', '-', $calendar['displayname']) . '.ics');
	echo OC_Calendar_Export::export($cal, OC_Calendar_Export::CALENDAR);
}elseif(isset($event)) {
	$data = OC_Calendar_App::getEventObject($_GET['eventid'], true);
	if(!$data) {
		header('HTTP/1.0 404 Not Found');
		exit;
	}
	header('Content-Type: text/calendar');
	header('Content-Disposition: inline; filename=' . str_replace(' ', '-', $data['summary']) . '.ics');
	echo OC_Calendar_Export::export($event, OC_Calendar_Export::EVENT);
}