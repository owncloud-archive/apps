<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/**
 * This class manages the caching of repeating events
 * Events will be cached for the current year ± 5 years
 */
class OC_Calendar_Repeat{
	/**
	 * @brief returns the cache of an event
	 * @param (int) $id - id of the event
	 * @return (array)
	 */
	public static function get($id) {
		$stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*clndr_repeat` WHERE `eventid` = ?');
		$result = $stmt->execute(array($id));
		$return = array();
		while($row = $result->fetchRow()) {
			$return[] = $row;
		}
		return $return;
	}
	/**
	 * @brief returns the cache of an event in a specific peroid
	 * @param (int) $id - id of the event
	 * @param (DateTime) $from - start for period in UTC
	 * @param (DateTime) $until - end for period in UTC
	 * @return (array)
	 */
	public static function get_inperiod($id, $from, $until) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_repeat` WHERE `eventid` = ?'
		.' AND ((`startdate` >= ? AND `startdate` <= ?)'
		.' OR (`enddate` >= ? AND `enddate` <= ?))');
		$result = $stmt->execute(array($id,
					OC_Calendar_Object::getUTCforMDB($from), OC_Calendar_Object::getUTCforMDB($until),
					OC_Calendar_Object::getUTCforMDB($from), OC_Calendar_Object::getUTCforMDB($until)));
		$return = array();
		while($row = $result->fetchRow()) {
			$return[] = $row;
		}
		return $return;
	}
	/**
	 * @brief returns the cache of all repeating events of a calendar
	 * @param (int) $id - id of the calendar
	 * @return (array)
	 */
	public static function getCalendar($id) {
		$stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*clndr_repeat` WHERE `calid` = ?');
		$result = $stmt->execute(array($id));
		$return = array();
		while($row = $result->fetchRow()) {
			$return[] = $row;
		}
		return $return;
	}
	/**
	 * @brief returns the cache of all repeating events of a calendar in a specific period
	 * @param (int) $id - id of the event
	 * @param (string) $from - start for period in UTC
	 * @param (string) $until - end for period in UTC
	 * @return (array)
	 */
	public static function getCalendar_inperiod($id, $from, $until) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_repeat` WHERE `calid` = ?'
		.' AND ((`startdate` >= ? AND `startdate` <= ?)'
		.' OR (`enddate` >= ? AND `enddate` <= ?))');
		$result = $stmt->execute(array($id,
					$from, $until,
					$from, $until));
		$return = array();
		while($row = $result->fetchRow()) {
			$return[] = $row;
		}
		return $return;
	}
	/**
	 * @brief generates the cache the first time
	 * @param (int) id - id of the event
	 * @return (bool)
	 */
	public static function generate($id) {
		$event = OC_Calendar_Object::find($id);
		if($event['repeating'] == 0) {
			return false;
		}
		$object = OC_VObject::parse($event['calendardata']);
		$start = new DateTime('01-01-' . date('Y') . ' 00:00:00', new DateTimeZone('UTC'));
		$start->modify('-5 years');
		$end = new DateTime('31-12-' . date('Y') . ' 23:59:59', new DateTimeZone('UTC'));
		$end->modify('+5 years');
		$object->expand($start, $end);
		foreach($object->getComponents() as $vevent) {
			if(!($vevent instanceof Sabre\VObject\Component\VEvent)) {
				continue;
			}
			$startenddate = OC_Calendar_Object::generateStartEndDate($vevent->DTSTART, OC_Calendar_Object::getDTEndFromVEvent($vevent), ($vevent->DTSTART->getDateType() == Sabre\VObject\Property\DateTime::DATE)?true:false, 'UTC');
			$stmt = OCP\DB::prepare('INSERT INTO `*PREFIX*clndr_repeat` (`eventid`,`calid`,`startdate`,`enddate`) VALUES(?,?,?,?)');
			$stmt->execute(array($id,OC_Calendar_Object::getCalendarid($id),$startenddate['start'],$startenddate['end']));
		}
		return true;
	}
	/**
	 * @brief generates the cache the first time for all repeating event of an calendar
	 * @param (int) id - id of the calendar
	 * @return (bool)
	 */
	public static function generateCalendar($id) {
		$allobjects = OC_Calendar_Object::all($id);
		foreach($allobjects as $event) {
			self::generate($event['id']);
		}
		return true;
	}
	/**
	 * @brief updates an event that is already cached
	 * @param (int) id - id of the event
	 * @return (bool)
	 */
	public static function update($id) {
		self::clean($id);
		self::generate($id);
		return true;
	}
	/**
	 * @brief updates all repating events of a calendar that are already cached
	 * @param (int) id - id of the calendar
	 * @return (bool)
	 */
	public static function updateCalendar($id) {
		self::cleanCalendar($id);
		self::generateCalendar($id);
		return true;
	}
	/**
	 * @brief checks if an event is already cached
	 * @param (int) id - id of the event
	 * @return (bool)
	 */
	public static function is_cached($id) {
		if(count(self::get($id)) != 0) {
			return true;
		}else{
			return false;
		}
	}
	/**
	 * @brief checks if an event is already cached in a specific period
	 * @param (int) id - id of the event
	 * @param (DateTime) $from - start for period in UTC
	 * @param (DateTime) $until - end for period in UTC
	 * @return (bool)
	 */
	public static function is_cached_inperiod($id, $start, $end) {
		if(count(self::get_inperiod($id, $start, $end)) != 0) {
			return true;
		}else{
			return false;
		}

	}
	/**
	 * @brief checks if a whole calendar is already cached
	 * @param (int) id - id of the calendar
	 * @return (bool)
	 */
	public static function is_calendar_cached($id) {
		$cachedevents = count(self::getCalendar($id));
		$repeatingevents = 0;
		$allevents = OC_Calendar_Object::all($id);
		foreach($allevents as $event) {
			if($event['repeating'] === 1) {
				$repeatingevents++;
			}
		}
		if($cachedevents < $repeatingevents) {
			return false;
		}else{
			return true;
		}
	}
	/**
	 * @brief removes the cache of an event
	 * @param (int) id - id of the event
	 * @return (bool)
	 */
	public static function clean($id) {
		$stmt = OCP\DB::prepare('DELETE FROM `*PREFIX*clndr_repeat` WHERE `eventid` = ?');
		$stmt->execute(array($id));
	}
	/**
	 * @brief removes the cache of all events of a calendar
	 * @param (int) id - id of the calendar
	 * @return (bool)
	 */
	public static function cleanCalendar($id) {
		$stmt = OCP\DB::prepare('DELETE FROM `*PREFIX*clndr_repeat` WHERE `calid` = ?');
		$stmt->execute(array($id));
	}
}