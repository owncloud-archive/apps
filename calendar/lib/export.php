<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/**
 * This class does export and converts all times to UTC
 */
class OC_Calendar_Export{
	/**
	 * @brief Use one of these constants as second parameter if you call OC_Calendar_Export::export()
	 */
	const CALENDAR = 'calendar';
	const EVENT = 'event';

	/**
	 * @brief export a calendar or an event
	 * @param integer $id id of calendar / event
	 * @param string $type use OC_Calendar_Export constants
	 * @return string
	 */
	public static function export($id, $type) {
		if($type == self::EVENT) {
			$return = self::event($id);
		}else{
			$return = self::calendar($id);
		}
		return self::fixLineBreaks($return);
	}

	/**
	 * @brief exports a calendar and convert all times to UTC
	 * @param integer $id id of the calendar
	 * @return string
	 */
	private static function calendar($id) {
		$events = OC_Calendar_Object::all($id);
		$calendar = OC_Calendar_Calendar::find($id);
		$return = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:ownCloud Calendar " . OCP\App::getAppVersion('calendar') . "\nX-WR-CALNAME:" . $calendar['displayname'] . "\n";
		foreach($events as $event) {
			$return .= self::generateEvent($event);
		}
		$return .= "END:VCALENDAR";
		return $return;
	}

	/**
	 * @brief exports an event and convert all times to UTC
	 * @param integer $id id of the event
	 * @return string
	 */
	private static function event($id) {
		$event = OC_Calendar_Object::find($id);
		$return = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:ownCloud Calendar " . OCP\App::getAppVersion('calendar') . "\nX-WR-CALNAME:" . $event['summary'] . "\n";
		$return .= self::generateEvent($event);
		$return .= "END:VCALENDAR";
		return $return;
	 }

	 /**
	  * @brief generates the VEVENT with UTC dates
	  * @param array $event
	  * @return string
	  */
	 private static function generateEvent($event) {
	 	$object = OC_VObject::parse($event['calendardata']);
		if(!$object){
			return false;
		}

		$sharedAccessClassPermissions = OC_Calendar_App::getAccessClassPermissions($object->VEVENT->CLASS->value);
		if(OC_Calendar_Object::getowner($event['id']) !== OCP\User::getUser()){
			if (!($sharedAccessClassPermissions & OCP\PERMISSION_READ)) {
				return '';
			}
		}
		$object = OC_Calendar_Object::cleanByAccessClass($event['calendarid'], $object);

		if($object->VEVENT){
			$dtstart = $object->VEVENT->DTSTART;
			$start_dt = $dtstart->getDateTime();
			$dtend = OC_Calendar_Object::getDTEndFromVEvent($object->VEVENT);
			$end_dt = $dtend->getDateTime();
			if($dtstart->getDateType() !== Sabre\VObject\Property\DateTime::DATE) {
				$start_dt->setTimezone(new DateTimeZone('UTC'));
				$end_dt->setTimezone(new DateTimeZone('UTC'));
				$object->VEVENT->setDateTime('DTSTART', $start_dt, Sabre\VObject\Property\DateTime::UTC);
				$object->VEVENT->setDateTime('DTEND', $end_dt, Sabre\VObject\Property\DateTime::UTC);
			}
			return $object->VEVENT->serialize();
		}
		if($object->VTODO){
			return $object->VTODO->serialize();
		}
		if($object->VJOURNAL){
			return $object->VJOURNAL->serialize();
		}
		return '';
	}

	/**
	 * @brief fixes new line breaks
	 * (fixes problems with Apple iCal)
	 * @param string $string to fix
	 * @return string
	 */
	private static function fixLineBreaks($string) {
		$string = str_replace("\r\n", "\n", $string);
		$string = str_replace("\r", "\n", $string);
		$string = str_replace("\n", "\r\n", $string);
		return $string;
	}
}
