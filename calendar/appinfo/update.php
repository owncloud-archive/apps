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
if ($installedVersion == '0.6') {
	// the update script in this version was not correct
	// also sharing of calendars did not work
	//$query = OCP\DB::prepare("DELETE FROM `*PREFIX*share` WHERE `item_type` IN ('calendar', 'event')");
	//$query->execute();
}
if (version_compare($installedVersion, '0.6.1', '<=')) {
	$calendar_stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*calendar_share_calendar`');
	$calendar_result = $calendar_stmt->execute();
	while( $cal = $calendar_result->fetchRow()) {
		$shareType = OCP\Share::SHARE_TYPE_USER;
		if ($cal['sharetype'] == 'group') {
			$shareType = OCP\Share::SHARE_TYPE_GROUP;
		}
		else if ($cal['sharetype'] == 'public') {
			$shareType = OCP\Share::SHARE_TYPE_LINK;
		}
		OC_User::setUserId($cal['owner']);
		try {
			OCP\Share::shareItem('calendar', $cal['calendarid'], $shareType, $cal['share'], $cal['permissions']?31:17); // CRUDS:RS
		}
		catch (Exception $e) {
			// nothing to do, the exception is already written to the log
		}
	}
	$event_stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*calendar_share_event`');
	$event_result = $event_stmt->execute();
	while( $event = $event_result->fetchRow()) {
		$shareType = OCP\Share::SHARE_TYPE_USER;
		if ($event['sharetype'] == 'group') {
			$shareType = OCP\Share::SHARE_TYPE_GROUP;
		}
		else if ($event['sharetype'] == 'public') {
			$shareType = OCP\Share::SHARE_TYPE_LINK;
		}
		OC_User::setUserId($event['owner']);
		try {
			OCP\Share::shareItem('event', $event['eventid'], $shareType, $event['share'], $event['permissions']?31:17); // CRUDS:RS
		}
		catch (Exception $e) {
			// nothing to do, the exception is already written to the log
		}
	}
	//logout and login - fix wrong calendar permissions from oc-1914
	$user = OCP\User::getUser();
	session_unset();
	session_destroy();
	OC_User::unsetMagicInCookie();
	session_regenerate_id(true);
	OC_User::setUserId($user);
}
