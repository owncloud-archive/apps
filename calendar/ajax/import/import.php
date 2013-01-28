<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');
OCP\JSON::callCheck();
session_write_close();
if (isset($_POST['progresskey']) && isset($_POST['getprogress'])) {
	echo OCP\JSON::success(array('percent'=>OC_Cache::get($_POST['progresskey'])));
	exit;
}
$file = \OC\Files\Filesystem::file_get_contents($_POST['path'] . '/' . $_POST['file']);
if(!$file) {
	OCP\JSON::error(array('error'=>'404'));
}
$import = new OC_Calendar_Import($file);
$import->setUserID(OCP\User::getUser());
$import->setTimeZone(OC_Calendar_App::$tz);
$import->enableProgressCache();
$import->setProgresskey($_POST['progresskey']);
if(!$import->isValid()) {
	OCP\JSON::error(array('error'=>'notvalid'));
	exit;
}
$newcal = false;
if($_POST['method'] == 'new') {
	$calendars = OC_Calendar_Calendar::allCalendars(OCP\User::getUser());
	foreach($calendars as $calendar) {
		if($calendar['displayname'] == $_POST['calname']) {
			$id = $calendar['id'];
			$newcal = false;
			break;
		}
		$newcal = true;
	}
	if($newcal) {
		$id = OC_Calendar_Calendar::addCalendar(OCP\USER::getUser(), strip_tags($_POST['calname']),'VEVENT,VTODO,VJOURNAL',null,0,strip_tags($_POST['calcolor']));
		OC_Calendar_Calendar::setCalendarActive($id, 1);
	}
}else{
	$calendar = OC_Calendar_App::getCalendar($_POST['id']);
	if($calendar['userid'] != OCP\USER::getUser()) {
		OCP\JSON::error(array('error'=>'missingcalendarrights'));
		exit();
	}
	$id = $_POST['id'];
	$import->setOverwrite($_POST['overwrite']);
}
$import->setCalendarID($id);
try{
	$import->import();
}catch (Exception $e) {
	OCP\JSON::error(array('message'=>OC_Calendar_App::$l10n->t('Import failed'), 'debug'=>$e->getMessage()));
	//write some log
}
$count = $import->getCount();
if($count == 0) {
	if($newcal) {
		OC_Calendar_Calendar::deleteCalendar($id);
	}
	OCP\JSON::error(array('message'=>OC_Calendar_App::$l10n->t('The file contained either no events or all events are already saved in your calendar.')));
}else{
	if($newcal) {
		OCP\JSON::success(array('message'=>$count . ' ' . OC_Calendar_App::$l10n->t('events has been saved in the new calendar') . ' ' .  strip_tags($_POST['calname'])));
	}else{
		OCP\JSON::success(array('message'=>$count . ' ' . OC_Calendar_App::$l10n->t('events has been saved in your calendar')));
	}
}
