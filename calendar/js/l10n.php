<?php
/**
 * Copyright (c) 2013 Lukas Reschke <lukas@statuscode.ch>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Set the content type to Javascript
header("Content-type: text/javascript");

// Disallow caching
header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 

// Enable l10n support
$l = OC_L10N::get('calendar');


// Get the event sources
$eventSources = array();
$calendars = OC_Calendar_Calendar::allCalendars(OCP\User::getUser());
foreach($calendars as $calendar) {
	if(!array_key_exists('active', $calendar)){
		$calendar['active'] = 1;
	}
	if($calendar['active'] == 1) {
		$eventSources[] = OC_Calendar_Calendar::getEventSourceInfo($calendar);
	}
}

$events_baseURL = OCP\Util::linkTo('calendar', 'ajax/events.php');
$eventSources[] = array('url' => $events_baseURL.'?calendar_id=shared_events',
		'backgroundColor' => '#1D2D44',
		'borderColor' => '#888',
		'textColor' => 'white',
		'editable' => 'false');

OCP\Util::emitHook('OC_Calendar', 'getSources', array('sources' => &$eventSources));

$array = array(
	"defaultView" => "\"".OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month')."\"",
	"eventSources" => json_encode($eventSources),
	"categories" => json_encode(OC_Calendar_App::getCategoryOptions()),
	"dayNames" =>  json_encode(array((string)$l->t('Sunday'), (string)$l->t('Monday'), (string)$l->t('Tuesday'), (string)$l->t('Wednesday'), (string)$l->t('Thursday'), (string)$l->t('Friday'), (string)$l->t('Saturday'))),
	"dayNamesShort" =>  json_encode(array((string)$l->t('Sun.'), (string)$l->t('Mon.'), (string)$l->t('Tue.'), (string)$l->t('Wed.'), (string)$l->t('Thu.'), (string)$l->t('Fri.'), (string)$l->t('Sat.'))),
	"monthNames" =>  json_encode(array((string)$l->t('January'), (string)$l->t('February'), (string)$l->t('March'), (string)$l->t('April'), (string)$l->t('May'), (string)$l->t('June'), (string)$l->t('July'), (string)$l->t('August'), (string)$l->t('September'), (string)$l->t('October'), (string)$l->t('November'), (string)$l->t('December'))),
	"monthNamesShort" =>  json_encode(array((string)$l->t('Jan.'), (string)$l->t('Feb.'), (string)$l->t('Mar.'), (string)$l->t('Apr.'), (string)$l->t('May.'), (string)$l->t('Jun.'), (string)$l->t('Jul.'), (string)$l->t('Aug.'), (string)$l->t('Sep.'), (string)$l->t('Oct.'), (string)$l->t('Nov.'), (string)$l->t('Dec.'))),
	"agendatime" =>  "\"".((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt')."{ -".((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt')."}"."\"",
	"defaulttime" => "\"".((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt')."\"",
	"allDayText" => "\"".addslashes($l->t('All day'))."\"",
	"newcalendar" => "\"".addslashes($l->t('New Calendar'))."\"",
	"missing_field" => "\"".addslashes($l->t('Missing or invalid fields'))."\"",
	"missing_field_title" => "\"".addslashes($l->t('Title'))."\"",
	"missing_field_calendar" => "\"".addslashes($l->t('Calendar'))."\"",
	"missing_field_fromdate" => "\"".addslashes($l->t('From Date'))."\"",
	"missing_field_fromtime" => "\"".addslashes($l->t('From Time'))."\"",
	"missing_field_todate" => "\"".addslashes($l->t('To Date'))."\"",
	"missing_field_totime" => "\"".addslashes($l->t('To Time'))."\"",
	"missing_field_startsbeforeends" => "\"".addslashes($l->t('The event ends before it starts'))."\"",
	"missing_field_dberror" => "\"".addslashes($l->t('There was a database fail'))."\"",
	"totalurl" => "\"".OCP\Util::linkToRemote('caldav')."calendars"."\"",
	"firstDay" => (OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'firstday', 'mo') == 'mo' ? '1' : '0'),
	);

// Echo it
foreach ($array as  $setting => $value) {
	echo("var ". $setting ."=".$value.";\n");
}
