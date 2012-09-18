<?php

$installedVersion=OCP\Config::getAppValue('calendar', 'installed_version');
if (version_compare($installedVersion, '0.2.1', '<')) {
	$stmt = OCP\DB::prepare( 'SELECT `id`, `calendarcolor` FROM `*PREFIX*calendar_calendars` WHERE `calendarcolor` IS NOT NULL' );
	$result = $stmt->execute();
	while( $row = $result->fetchRow()) {
		$id = $row['id'];
		$color = $row['calendarcolor'];
		if ($color[0] == '#' || strlen($color) < 6) {
			continue;
		}
		$color = '#' .$color;
		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*calendar_calendars` SET `calendarcolor`=? WHERE `id`=?' );
		$r = $stmt->execute(array($color,$id));
	}
}
if (version_compare($installedVersion, '0.5', '<')) {
	$calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser());
	foreach($calendars as $calendar) {
		OC_Calendar_Repeat::cleanCalendar($calendar['id']);
		OC_Calendar_Repeat::generateCalendar($calendar['id']);
	}
}
if (version_compare($installedVersion, '0.6', '<=')) {
	$calendar_stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*calendar_share_calendar`');
	$calendar_result = $calendar_stmt->execute();
	$calendar = array();
	while( $row = $calendar_result->fetchRow()) {
		$calendar[] = $row;
	}
	foreach($calendar as $cal) {
		$stmt = OCP\DB::prepare('INSERT INTO `*PREFIX*share` (`share_with`,`uid_owner`,`item_type`,`item_target`,`permissions`) VALUES(?,?,\'calendar\',?,?)' );
		$result = $stmt->execute(array($cal['share'],$cal['owner'],$cal['calendarid'], ($cal['permissions'])?31:17));
	}
	$event_stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*calendar_share_event`');
	$event_result = $event_stmt->execute();
	$event = array();
	while( $row = $event_result->fetchRow()) {
		$event[] = $row;
	}
	foreach($event as $evnt) {
		$stmt = OCP\DB::prepare('INSERT INTO `*PREFIX*share` (`share_with`,`uid_owner`,`item_type`,`item_target`,`permissions`) VALUES(?,?,\'event\',?,?)' );
		$result = $stmt->execute(array($evnt['share'],$evnt['owner'],$evnt['eventid'], ($evnt['permissions'])?31:17));
	}
}
