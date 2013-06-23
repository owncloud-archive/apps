<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class does import and converts all times to the users current timezone
 */
class OC_Calendar_Import{
	/*
	 * @brief counts the absolute number of parsed elements
	 */
	private $abscount;

	/*
	 * @brief var saves if the percentage should be saved with OC_Cache
	 */
	private $cacheprogress;

	/*
	 * @brief Sabre\VObject\Component\VCalendar object - for documentation see http://code.google.com/p/sabredav/wiki/Sabre_VObject_Component_VCalendar
	 */
	private $calobject;

	/*
	 * @brief var counts the number of imported elements
	 */
	private $count;

	/*
	 * @brief var to check if errors happend while initialization
	 */
	private $error;

	/*
	 * @brief var saves the ical string that was submitted with the __construct function
	 */
	private $ical;

	/*
	 * @brief calendar id for import
	 */
	private $id;

	/*
	 * @brief overwrite flag
	 */
	private $overwrite;

	/*
	 * @brief var saves the percentage of the import's progress
	 */
	private $progress;

	/*
	 * @brief var saves the key for the percentage of the import's progress
	 */
	private $progresskey;

	/*
	 * @brief var saves the timezone the events shell converted to
	 */
	private $tz;

	/*
	 * @brief var saves the userid
	 */
	private $userid;

	/*
	 * public methods
	 */

	/*
	 * @brief does general initialization for import object
	 * @param string $calendar content of ical file
	 * @param string $tz timezone of the user
	 * @return boolean
	 */
	public function __construct($ical) {
		$this->error = null;
		$this->ical = $ical;
		$this->abscount = 0;
		$this->count = 0;
		//fix for multiple subcalendars
		if(substr_count($ical, 'BEGIN:VCALENDAR') > 1){
			$ical = substr_replace($ical, '**##++FancyReplacementForFirstOccurrenceOfTheSearchPattern++##**', 0, 15);
			$ical = str_replace('BEGIN:VCALENDAR', '', $ical);
			$ical = str_replace('END:VCALENDAR', '', $ical);
			$ical = substr_replace($ical, 'BEGIN:VCALENDAR', 0, 64);
			$ical .= "\n" . 'END:VCALENDAR';
			$this->ical = $ical;
		}
		try{
			$this->calobject = OC_VObject::parse($this->ical);
		}catch(Exception $e) {
			//MISSING: write some log
			$this->error = true;
			return false;
		}
		return true;
	}

	/*
	 * @brief imports a calendar
	 * @return boolean
	 */
	public function import() {
		if(!$this->isValid()) {
			return false;
		}
		$numofcomponents = count($this->calobject->getComponents());
		if($this->overwrite) {
			foreach(OC_Calendar_Object::all($this->id) as $obj) {
				OC_Calendar_Object::delete($obj['id']);
			}
		}
		foreach($this->calobject->getComponents() as $object) {
			if(!($object instanceof Sabre\VObject\Component\VEvent) && !($object instanceof Sabre\VObject\Component\VJournal) && !($object instanceof Sabre\VObject\Component\VTodo)) {
				continue;
			}
			if(!is_null($object->DTSTART)){
				$dtend = OC_Calendar_Object::getDTEndFromVEvent($object);
				if($object->DTEND) {
					$object->DTEND->setDateTime($dtend->getDateTime(), $object->DTSTART->getDateType());
				}
			}
			$vcalendar = $this->createVCalendar($object->serialize());
			$insertid = OC_Calendar_Object::add($this->id, $vcalendar);
			$this->abscount++;
			if($this->isDuplicate($insertid)) {
				OC_Calendar_Object::delete($insertid);
			}else{
				$this->count++;
			}
			$this->updateProgress(intval(($this->abscount / $numofcomponents)*100));
		}
		OC_Cache::remove($this->progresskey);
		return true;
	}

	/*
	 * @brief sets the timezone
	 * @return boolean
	 */
	public function setTimeZone($tz) {
		$this->tz = $tz;
		return true;
	}

	/*
	 * @brief sets the overwrite flag
	 * @return boolean
	 */
	public function setOverwrite($overwrite) {
		$this->overwrite = (bool) $overwrite;
		return true;
	}

	/*
	 * @brief sets the progresskey
	 * @return boolean
	 */
	public function setProgresskey($progresskey) {
		$this->progresskey = $progresskey;
		return true;
	}

	/*
	 * @brief checks if something went wrong while initialization
	 * @return boolean
	 */
	public function isValid() {
		if(is_null($this->error)) {
			return true;
		}
		return false;
	}

	/*
	 * @brief returns the percentage of progress
	 * @return integer
	 */
	public function getProgress() {
		return $this->progress;
	}

	/*
	 * @brief enables the cache for the percentage of progress
	 * @return boolean
	 */
	public function enableProgressCache() {
		$this->cacheprogress = true;
		return true;
	}

	/*
	 * @brief disables the cache for the percentage of progress
	 * @return boolean
	 */
	public function disableProgressCache() {
		$this->cacheprogress = false;
		return false;
	}

	/*
	 * @brief generates a new calendar name
	 * @return string
	 */
	public function createCalendarName() {
		$calendars = OC_Calendar_Calendar::allCalendars($this->userid);
		$calendarname = $guessedcalendarname = !is_null($this->guessCalendarName())?($this->guessCalendarName()):(OC_Calendar_App::$l10n->t('New Calendar'));
		$i = 1;
		while(!OC_Calendar_Calendar::isCalendarNameavailable($calendarname, $this->userid)) {
			$calendarname = $guessedcalendarname . ' (' . $i . ')';
			$i++;
		}
		return $calendarname;
	}

	/*
	 * @brief generates a new calendar color
	 * @return string
	 */
	public function createCalendarColor() {
		if(is_null($this->guessCalendarColor())) {
			return '#9fc6e7';
		}
		return $this->guessCalendarColor();
	}

	/*
	 * @brief sets the id for the calendar
	 * @param integer $id of the calendar
	 * @return boolean
	 */
	public function setCalendarID($id) {
		$this->id = $id;
		return true;
	}

	/*
	 * @brief sets the userid to import the calendar
	 * @param string $id of the user
	 * @return boolean
	 */
	public function setUserID($userid) {
		$this->userid = $userid;
		return true;
	}

	/*
	 * @brief returns the private
	 * @param string $id of the user
	 * @return boolean
	 */
	public function getCount() {
		return $this->count;
	}

	/*
	 * private methods
	 */

	/*
	 * @brief generates an unique ID
	 * @return string
	 */
	//private function createUID() {
	//	return substr(md5(rand().time()),0,10);
	//}

	/*
	 * @brief checks is the UID is already in use for another event
	 * @param string $uid uid to check
	 * @return boolean
	 */
	//private function isUIDAvailable($uid) {
	//
	//}

	/*
	 * @brief generates a proper VCalendar string
	 * @param string $vobject
	 * @return string
	 */
	private function createVCalendar($vobject) {
		if(is_object($vobject)) {
			$vobject = @$vobject->serialize();
		}
		$vcalendar = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:ownCloud Calendar " . OCP\App::getAppVersion('calendar') . "\n";
		$vcalendar .= $vobject;
		$vcalendar .= "END:VCALENDAR";
		return $vcalendar;
	}

	/*
	 * @brief checks if an event already exists in the user's calendars
	 * @param integer $insertid id of the new object
	 * @return boolean
	 */
	private function isDuplicate($insertid) {
		$newobject = OC_Calendar_Object::find($insertid);
		$stmt = OCP\DB::prepare('SELECT COUNT(*) AS `count` FROM `*PREFIX*clndr_objects`
								 INNER JOIN `*PREFIX*clndr_calendars` ON `calendarid`=`*PREFIX*clndr_calendars`.`id`
								 WHERE `objecttype`=? AND `startdate`=? AND `enddate`=? AND `repeating`=? AND `summary`=? AND `calendardata`=? AND `userid` = ?');
		$result = $stmt->execute(array($newobject['objecttype'],$newobject['startdate'],$newobject['enddate'],$newobject['repeating'],$newobject['summary'],$newobject['calendardata'], $this->userid));
		$result = $result->fetchRow();
		if($result['count'] >= 2) {
			return true;
		}
		return false;
	}

	/*
	 * @brief updates the progress var
	 * @param integer $percentage
	 * @return boolean
	 */
	private function updateProgress($percentage) {
		$this->progress = $percentage;
		if($this->cacheprogress) {
			OC_Cache::set($this->progresskey, $this->progress, 300);
		}
		return true;
	}

	/*
	 * public methods for (pre)rendering of X-... Attributes
	 */

	/*
	 * @brief guesses the calendar color
	 * @return mixed - string or boolean
	 */
	public function guessCalendarColor() {
		if(!is_null($this->calobject->__get('X-APPLE-CALENDAR-COLOR'))) {
			return $this->calobject->__get('X-APPLE-CALENDAR-COLOR');
		}
		return null;
	}

	/*
	 * @brief guesses the calendar description
	 * @return mixed - string or boolean
	 */
	public function guessCalendarDescription() {
		if(!is_null($this->calobject->__get('X-WR-CALDESC'))) {
			return $this->calobject->__get('X-WR-CALDESC');
		}
		return null;
	}

	/*
	 * @brief guesses the calendar name
	 * @return mixed - string or boolean
	 */
	public function guessCalendarName() {
		if(!is_null($this->calobject->__get('X-WR-CALNAME'))) {
			return $this->calobject->__get('X-WR-CALNAME');
		}
		return null;
	}
}
