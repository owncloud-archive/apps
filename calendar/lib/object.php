<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
 /**
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE calendar_objects (
 *     id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *     calendarid INTEGER UNSIGNED NOT NULL,
 *     objecttype VARCHAR(40) NOT NULL,
 *     startdate DATETIME,
 *     enddate DATETIME,
 *     repeating INT(1),
 *     summary VARCHAR(255),
 *     calendardata TEXT,
 *     uri VARCHAR(100),
 *     lastmodified INT(11)
 * );
 *
 */

/**
 * This class manages our calendar objects
 */
class OC_Calendar_Object{
	/**
	 * @brief Returns all objects of a calendar
	 * @param integer $id
	 * @return array
	 *
	 * The objects are associative arrays. You'll find the original vObject in
	 * ['calendardata']
	 */
	public static function all($id) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ?' );
		$result = $stmt->execute(array($id));

		$calendarobjects = array();
		while( $row = $result->fetchRow()) {
			$calendarobjects[] = $row;
		}

		return $calendarobjects;
	}

	/**
	 * @brief Returns all objects of a calendar between $start and $end
	 * @param integer $id
	 * @param DateTime $start
	 * @param DateTime $end
	 * @return array
	 *
	 * The objects are associative arrays. You'll find the original vObject
	 * in ['calendardata']
	 */
	public static function allInPeriod($id, $start, $end) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ?'
		.' AND ((`startdate` >= ? AND `startdate` <= ? AND `repeating` = 0)'
		.' OR (`enddate` >= ? AND `enddate` <= ? AND `repeating` = 0)'
		.' OR (`startdate` <= ? AND `repeating` = 1))' );
		$start = self::getUTCforMDB($start);
		$end = self::getUTCforMDB($end);
		$result = $stmt->execute(array($id,
					$start, $end,
					$start, $end,
					$end));

		$calendarobjects = array();
		while( $row = $result->fetchRow()) {
			$calendarobjects[] = $row;
		}

		return $calendarobjects;
	}

	/**
	 * @brief Returns an object
	 * @param integer $id
	 * @return associative array
	 */
	public static function find($id) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `id` = ?' );
		$result = $stmt->execute(array($id));

		return $result->fetchRow();
	}

	/**
	 * @brief finds an object by its DAV Data
	 * @param integer $cid Calendar id
	 * @param string $uri the uri ('filename')
	 * @return associative array
	 */
	public static function findWhereDAVDataIs($cid,$uri) {
		$stmt = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ? AND `uri` = ?' );
		$result = $stmt->execute(array($cid,$uri));

		return $result->fetchRow();
	}

	/**
	 * @brief Adds an object
	 * @param integer $id Calendar id
	 * @param string $data  object
	 * @return insertid
	 */
	public static function add($id,$data) {
		$calendar = OC_Calendar_Calendar::find($id);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_CREATE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to add events to this calendar.'
					)
				);
			}
		}
		$object = OC_VObject::parse($data);
		list($type,$startdate,$enddate,$summary,$repeating,$uid) = self::extractData($object);

		if(is_null($uid)) {
			$object->setUID();
			$data = $object->serialize();
		}

		$uri = 'owncloud-'.md5($data.rand().time()).'.ics';

		$stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*calendar_objects` (`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`) VALUES(?,?,?,?,?,?,?,?,?)' );
		$stmt->execute(array($id,$type,$startdate,$enddate,$repeating,$summary,$data,$uri,time()));
		$object_id = OCP\DB::insertid('*PREFIX*calendar_objects');

		OC_Calendar_App::loadCategoriesFromVCalendar($object_id, $object);

		OC_Calendar_Calendar::touchCalendar($id);
		OCP\Util::emitHook('OC_Calendar', 'addEvent', $object_id);
		return $object_id;
	}

	/**
	 * @brief Adds an object with the data provided by sabredav
	 * @param integer $id Calendar id
	 * @param string $uri   the uri the card will have
	 * @param string $data  object
	 * @return insertid
	 */
	public static function addFromDAVData($id,$uri,$data) {
		$calendar = OC_Calendar_Calendar::find($id);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_CREATE)) {
				throw new Sabre_DAV_Exception_Forbidden(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to add events to this calendar.'
					)
				);
			}
		}
		$object = OC_VObject::parse($data);
		list($type,$startdate,$enddate,$summary,$repeating,$uid) = self::extractData($object);

		$stmt = OCP\DB::prepare( 'INSERT INTO `*PREFIX*calendar_objects` (`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`) VALUES(?,?,?,?,?,?,?,?,?)' );
		$stmt->execute(array($id,$type,$startdate,$enddate,$repeating,$summary,$data,$uri,time()));
		$object_id = OCP\DB::insertid('*PREFIX*calendar_objects');

		OC_Calendar_Calendar::touchCalendar($id);
		OCP\Util::emitHook('OC_Calendar', 'addEvent', $object_id);
		return $object_id;
	}

	/**
	 * @brief edits an object
	 * @param integer $id id of object
	 * @param string $data  object
	 * @return boolean
	 */
	public static function edit($id, $data) {
		$oldobject = self::find($id);

		$calendar = OC_Calendar_Calendar::find($oldobject['calendarid']);
		$oldvobject = OC_VObject::parse($oldobject['calendardata']);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			$sharedAccessClassPermissions = OC_Calendar_App::getAccessClassPermissions($oldvobject->VEVENT->CLASS->value);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_UPDATE) || !($sharedAccessClassPermissions & OCP\PERMISSION_UPDATE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to edit this event.'
					)
				);
			}
		}
		$object = OC_VObject::parse($data);
		OC_Calendar_App::loadCategoriesFromVCalendar($id, $object);
		list($type,$startdate,$enddate,$summary,$repeating,$uid) = self::extractData($object);

		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*calendar_objects` SET `objecttype`=?,`startdate`=?,`enddate`=?,`repeating`=?,`summary`=?,`calendardata`=?,`lastmodified`= ? WHERE `id` = ?' );
		$stmt->execute(array($type,$startdate,$enddate,$repeating,$summary,$data,time(),$id));

		OC_Calendar_Calendar::touchCalendar($oldobject['calendarid']);
		OCP\Util::emitHook('OC_Calendar', 'editEvent', $id);

		return true;
	}

	/**
	 * @brief edits an object with the data provided by sabredav
	 * @param integer $id calendar id
	 * @param string $uri   the uri of the object
	 * @param string $data  object
	 * @return boolean
	 */
	public static function editFromDAVData($cid,$uri,$data) {
		$oldobject = self::findWhereDAVDataIs($cid,$uri);

		$calendar = OC_Calendar_Calendar::find($cid);
		$oldvobject = OC_VObject::parse($oldobject['calendardata']);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $cid);
			$sharedAccessClassPermissions = OC_Calendar_App::getAccessClassPermissions($oldvobject->VEVENT->CLASS->value);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_UPDATE) || !($sharedAccessClassPermissions & OCP\PERMISSION_UPDATE)) {
				throw new Sabre_DAV_Exception_Forbidden(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to edit this event.'
					)
				);
			}
		}
		$object = OC_VObject::parse($data);
		list($type,$startdate,$enddate,$summary,$repeating,$uid) = self::extractData($object);

		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*calendar_objects` SET `objecttype`=?,`startdate`=?,`enddate`=?,`repeating`=?,`summary`=?,`calendardata`=?,`lastmodified`= ? WHERE `id` = ?' );
		$stmt->execute(array($type,$startdate,$enddate,$repeating,$summary,$data,time(),$oldobject['id']));

		OC_Calendar_Calendar::touchCalendar($oldobject['calendarid']);
		OCP\Util::emitHook('OC_Calendar', 'editEvent', $oldobject['id']);

		return true;
	}

	/**
	 * @brief deletes an object
	 * @param integer $id id of object
	 * @return boolean
	 */
	public static function delete($id) {
		$oldobject = self::find($id);
		$calendar = OC_Calendar_Calendar::find($oldobject['calendarid']);
		$object = OC_VObject::parse($oldobject['calendardata']);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			$sharedAccessClassPermissions = OC_Calendar_App::getAccessClassPermissions($object->VEVENT->CLASS->value);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_DELETE) || !($sharedAccessClassPermissions & OCP\PERMISSION_DELETE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to delete this event.'
					)
				);
			}
		}
		$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*calendar_objects` WHERE `id` = ?' );
		$stmt->execute(array($id));
		OC_Calendar_Calendar::touchCalendar($oldobject['calendarid']);

		OCP\Share::unshareAll('event', $id);

		OCP\Util::emitHook('OC_Calendar', 'deleteEvent', $id);

		OC_Calendar_App::getVCategories()->purgeObject($id);

		return true;
	}

	/**
	 * @brief deletes an  object with the data provided by sabredav
	 * @param integer $cid calendar id
	 * @param string $uri the uri of the object
	 * @return boolean
	 */
	public static function deleteFromDAVData($cid,$uri) {
		$oldobject = self::findWhereDAVDataIs($cid, $uri);
		$calendar = OC_Calendar_Calendar::find($cid);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $cid);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_DELETE)) {
				throw new Sabre_DAV_Exception_Forbidden(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to delete this event.'
					)
				);
			}
		}
		$stmt = OCP\DB::prepare( 'DELETE FROM `*PREFIX*calendar_objects` WHERE `calendarid`= ? AND `uri`=?' );
		$stmt->execute(array($cid,$uri));
		OC_Calendar_Calendar::touchCalendar($cid);
		OCP\Util::emitHook('OC_Calendar', 'deleteEvent', $oldobject['id']);

		return true;
	}

	public static function moveToCalendar($id, $calendarid) {
		$calendar = OC_Calendar_Calendar::find($calendarid);
		if ($calendar['userid'] != OCP\User::getUser()) {
			$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & OCP\PERMISSION_DELETE)) {
				throw new Exception(
					OC_Calendar_App::$l10n->t(
						'You do not have the permissions to add events to this calendar.'
					)
				);
			}
		}
		$stmt = OCP\DB::prepare( 'UPDATE `*PREFIX*calendar_objects` SET `calendarid`=? WHERE `id`=?' );
		$stmt->execute(array($calendarid,$id));

		OC_Calendar_Calendar::touchCalendar($id);
		OCP\Util::emitHook('OC_Calendar', 'moveEvent', $id);

		return true;
	}

	/**
     * @brief Creates a UID
     * @return string
     */
    protected static function createUID() {
        return substr(md5(rand().time()),0,10);
    }

	/**
	 * @brief Extracts data from a vObject-Object
	 * @param Sabre_VObject $object
	 * @return array
	 *
	 * [type, start, end, summary, repeating, uid]
	 */
	protected static function extractData($object) {
		$return = array('',null,null,'',0,null);

		// Child to use
		$children = 0;
		$use = null;
		foreach($object->children as $property) {
			if($property->name == 'VEVENT') {
				$children++;
				$thisone = true;

				foreach($property->children as &$element) {
					if($element->name == 'RECURRENCE-ID') {
						$thisone = false;
					}
				} unset($element);

				if($thisone) {
					$use = $property;
				}
			}
			elseif($property->name == 'VTODO' || $property->name == 'VJOURNAL') {
				$return[0] = $property->name;
				foreach($property->children as &$element) {
					if($element->name == 'SUMMARY') {
						$return[3] = $element->value;
					}
					elseif($element->name == 'UID') {
						$return[5] = $element->value;
					}
				};

				// Only one VTODO or VJOURNAL per object
				// (only one UID per object but a UID is required by a VTODO =>
				//    one VTODO per object)
				break;
			}
		}

		// find the data
		if(!is_null($use)) {
			$return[0] = $use->name;
			foreach($use->children as $property) {
				if($property->name == 'DTSTART') {
					$return[1] = self::getUTCforMDB($property->getDateTime());
				}
				elseif($property->name == 'DTEND') {
					$return[2] = self::getUTCforMDB($property->getDateTime());
				}
				elseif($property->name == 'SUMMARY') {
					$return[3] = $property->value;
				}
				elseif($property->name == 'RRULE') {
					$return[4] = 1;
				}
				elseif($property->name == 'UID') {
					$return[5] = $property->value;
				}
			}
		}

		// More than one child means reoccuring!
		if($children > 1) {
			$return[4] = 1;
		}
		return $return;
	}

	/**
	 * @brief DateTime to UTC string
	 * @param DateTime $datetime The date to convert
	 * @returns date as YYYY-MM-DD hh:mm
	 *
	 * This function creates a date string that can be used by MDB2.
	 * Furthermore it converts the time to UTC.
	 */
	public static function getUTCforMDB($datetime) {
		return date('Y-m-d H:i', $datetime->format('U'));
	}

	/**
	 * @brief returns the DTEND of an $vevent object
	 * @param object $vevent vevent object
	 * @return object
	 */
	public static function getDTEndFromVEvent($vevent) {
		if ($vevent->DTEND) {
			$dtend = $vevent->DTEND;
		}else{
			$dtend = clone $vevent->DTSTART;
			// clone creates a shallow copy, also clone DateTime
			$dtend->setDateTime(clone $dtend->getDateTime(), $dtend->getDateType());
			if ($vevent->DURATION) {
				$duration = strval($vevent->DURATION);
				$invert = 0;
				if ($duration[0] == '-') {
					$duration = substr($duration, 1);
					$invert = 1;
				}
				if ($duration[0] == '+') {
					$duration = substr($duration, 1);
				}
				$interval = new DateInterval($duration);
				$interval->invert = $invert;
				$dtend->getDateTime()->add($interval);
			}
		}
		return $dtend;
	}

	/**
	 * @brief Remove all properties which should not be exported for the AccessClass Confidential
	 * @param string $calendarId Calendar ID
	 * @param Sabre_VObject $vobject Sabre VObject
	 * @return object
	 */
	public static function cleanByAccessClass($calendarId, $vobject) {

		// Do not clean your own calendar
		if(OC_Calendar_Object::getowner($calendarId) === OCP\USER::getUser()) {
			return $vobject;
		}

		$vevent = $vobject->VEVENT;
		if($vevent->CLASS->value == 'CONFIDENTIAL') {
			foreach ($vevent->children as &$property) {
				switch($property->name) {
					case 'CREATED':
					case 'DTSTART':
					case 'RRULE':
					case 'DURATION':
					case 'DTEND':
					case 'CLASS':
					case 'UID':
						break;
					case 'SUMMARY':
						$property->value = OC_Calendar_App::$l10n->t('Busy');
						break;
					default:
						$vevent->__unset($property->name);
						break;
				}
			}
		}
		return $vobject;
	}

	/**
	 * @brief returns the options for the access class of an event
	 * @return array - valid inputs for the access class of an event
	 */
	public static function getAccessClassOptions($l10n) {
		return array(
			'PUBLIC'       => (string)$l10n->t('Public'),
			'PRIVATE'      => (string)$l10n->t('Private'),
			'CONFIDENTIAL' => (string)$l10n->t('Confidential')
		);
	}

	/**
	 * @brief returns the options for the repeat rule of an repeating event
	 * @return array - valid inputs for the repeat rule of an repeating event
	 */
	public static function getRepeatOptions($l10n) {
		return array(
			'doesnotrepeat' => (string)$l10n->t('Does not repeat'),
			'daily'         => (string)$l10n->t('Daily'),
			'weekly'        => (string)$l10n->t('Weekly'),
			'weekday'       => (string)$l10n->t('Every Weekday'),
			'biweekly'      => (string)$l10n->t('Bi-Weekly'),
			'monthly'       => (string)$l10n->t('Monthly'),
			'yearly'        => (string)$l10n->t('Yearly')
		);
	}

	/**
	 * @brief returns the options for the end of an repeating event
	 * @return array - valid inputs for the end of an repeating events
	 */
	public static function getEndOptions($l10n) {
		return array(
			'never' => (string)$l10n->t('never'),
			'count' => (string)$l10n->t('by occurrences'),
			'date'  => (string)$l10n->t('by date')
		);
	}

	/**
	 * @brief returns the options for an monthly repeating event
	 * @return array - valid inputs for monthly repeating events
	 */
	public static function getMonthOptions($l10n) {
		return array(
			'monthday' => (string)$l10n->t('by monthday'),
			'weekday'  => (string)$l10n->t('by weekday')
		);
	}

	/**
	 * @brief returns the options for an weekly repeating event
	 * @return array - valid inputs for weekly repeating events
	 */
	public static function getWeeklyOptions($l10n) {
		return array(
			'MO' => (string)$l10n->t('Monday'),
			'TU' => (string)$l10n->t('Tuesday'),
			'WE' => (string)$l10n->t('Wednesday'),
			'TH' => (string)$l10n->t('Thursday'),
			'FR' => (string)$l10n->t('Friday'),
			'SA' => (string)$l10n->t('Saturday'),
			'SU' => (string)$l10n->t('Sunday')
		);
	}

	/**
	 * @brief returns the options for an monthly repeating event which occurs on specific weeks of the month
	 * @return array - valid inputs for monthly repeating events
	 */
	public static function getWeekofMonth($l10n) {
		return array(
			'auto' => (string)$l10n->t('events week of month'),
			'1' => (string)$l10n->t('first'),
			'2' => (string)$l10n->t('second'),
			'3' => (string)$l10n->t('third'),
			'4' => (string)$l10n->t('fourth'),
			'5' => (string)$l10n->t('fifth'),
			'-1' => (string)$l10n->t('last')
		);
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific days of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByYearDayOptions() {
		$return = array();
		foreach(range(1,366) as $num) {
			$return[(string) $num] = (string) $num;
		}
		return $return;
	}

	/**
	 * @brief returns the options for an yearly or monthly repeating event which occurs on specific days of the month
	 * @return array - valid inputs for yearly or monthly repeating events
	 */
	public static function getByMonthDayOptions() {
		$return = array();
		foreach(range(1,31) as $num) {
			$return[(string) $num] = (string) $num;
		}
		return $return;
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific month of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByMonthOptions($l10n) {
		return array(
			'1'  => (string)$l10n->t('January'),
			'2'  => (string)$l10n->t('February'),
			'3'  => (string)$l10n->t('March'),
			'4'  => (string)$l10n->t('April'),
			'5'  => (string)$l10n->t('May'),
			'6'  => (string)$l10n->t('June'),
			'7'  => (string)$l10n->t('July'),
			'8'  => (string)$l10n->t('August'),
			'9'  => (string)$l10n->t('September'),
			'10' => (string)$l10n->t('October'),
			'11' => (string)$l10n->t('November'),
			'12' => (string)$l10n->t('December')
		);
	}

	/**
	 * @brief returns the options for an yearly repeating event
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getYearOptions($l10n) {
		return array(
			'bydate' => (string)$l10n->t('by events date'),
			'byyearday' => (string)$l10n->t('by yearday(s)'),
			'byweekno'  => (string)$l10n->t('by weeknumber(s)'),
			'bydaymonth'  => (string)$l10n->t('by day and month')
		);
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific week numbers of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByWeekNoOptions() {
		return range(1, 52);
	}

	/**
	 * @brief validates a request
	 * @param array $request
	 * @return mixed (array / boolean)
	 */
	public static function validateRequest($request) {
		$errnum = 0;
		$errarr = array('title'=>'false', 'cal'=>'false', 'from'=>'false', 'fromtime'=>'false', 'to'=>'false', 'totime'=>'false', 'endbeforestart'=>'false');
		if($request['title'] == '') {
			$errarr['title'] = 'true';
			$errnum++;
		}

		$fromday = substr($request['from'], 0, 2);
		$frommonth = substr($request['from'], 3, 2);
		$fromyear = substr($request['from'], 6, 4);
		if(!checkdate($frommonth, $fromday, $fromyear)) {
			$errarr['from'] = 'true';
			$errnum++;
		}
		$allday = isset($request['allday']);
		if(!$allday && self::checkTime(urldecode($request['fromtime']))) {
			$errarr['fromtime'] = 'true';
			$errnum++;
		}

		$today = substr($request['to'], 0, 2);
		$tomonth = substr($request['to'], 3, 2);
		$toyear = substr($request['to'], 6, 4);
		if(!checkdate($tomonth, $today, $toyear)) {
			$errarr['to'] = 'true';
			$errnum++;
		}
		if($request['repeat'] != 'doesnotrepeat') {
			if(is_nan($request['interval']) && $request['interval'] != '') {
				$errarr['interval'] = 'true';
				$errnum++;
			}
			if(array_key_exists('repeat', $request) && !array_key_exists($request['repeat'], self::getRepeatOptions(OC_Calendar_App::$l10n))) {
				$errarr['repeat'] = 'true';
				$errnum++;
			}
			if(array_key_exists('advanced_month_select', $request) && !array_key_exists($request['advanced_month_select'], self::getMonthOptions(OC_Calendar_App::$l10n))) {
				$errarr['advanced_month_select'] = 'true';
				$errnum++;
			}
			if(array_key_exists('advanced_year_select', $request) && !array_key_exists($request['advanced_year_select'], self::getYearOptions(OC_Calendar_App::$l10n))) {
				$errarr['advanced_year_select'] = 'true';
				$errnum++;
			}
			if(array_key_exists('weekofmonthoptions', $request) && !array_key_exists($request['weekofmonthoptions'], self::getWeekofMonth(OC_Calendar_App::$l10n))) {
				$errarr['weekofmonthoptions'] = 'true';
				$errnum++;
			}
			if($request['end'] != 'never') {
				if(!array_key_exists($request['end'], self::getEndOptions(OC_Calendar_App::$l10n))) {
					$errarr['end'] = 'true';
					$errnum++;
				}
				if($request['end'] == 'count' && is_nan($request['byoccurrences'])) {
					$errarr['byoccurrences'] = 'true';
					$errnum++;
				}
				if($request['end'] == 'date') {
					list($bydate_day, $bydate_month, $bydate_year) = explode('-', $request['bydate']);
					if(!checkdate($bydate_month, $bydate_day, $bydate_year)) {
						$errarr['bydate'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('weeklyoptions', $request)) {
				foreach($request['weeklyoptions'] as $option) {
					if(!in_array($option, self::getWeeklyOptions(OC_Calendar_App::$l10n))) {
						$errarr['weeklyoptions'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('byyearday', $request)) {
				foreach($request['byyearday'] as $option) {
					if(!array_key_exists($option, self::getByYearDayOptions())) {
						$errarr['byyearday'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('weekofmonthoptions', $request)) {
				if(is_nan((double)$request['weekofmonthoptions'])) {
					$errarr['weekofmonthoptions'] = 'true';
					$errnum++;
				}
			}
			if(array_key_exists('bymonth', $request)) {
				foreach($request['bymonth'] as $option) {
					if(!in_array($option, self::getByMonthOptions(OC_Calendar_App::$l10n))) {
						$errarr['bymonth'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('byweekno', $request)) {
				foreach($request['byweekno'] as $option) {
					if(!array_key_exists($option, self::getByWeekNoOptions())) {
						$errarr['byweekno'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('bymonthday', $request)) {
				foreach($request['bymonthday'] as $option) {
					if(!array_key_exists($option, self::getByMonthDayOptions())) {
						$errarr['bymonthday'] = 'true';
						$errnum++;
					}
				}
			}
		}
		if(!$allday && self::checkTime(urldecode($request['totime']))) {
			$errarr['totime'] = 'true';
			$errnum++;
		}
		if($today < $fromday && $frommonth == $tomonth && $fromyear == $toyear) {
			$errarr['endbeforestart'] = 'true';
			$errnum++;
		}
		if($today == $fromday && $frommonth > $tomonth && $fromyear == $toyear) {
			$errarr['endbeforestart'] = 'true';
			$errnum++;
		}
		if($today == $fromday && $frommonth == $tomonth && $fromyear > $toyear) {
			$errarr['endbeforestart'] = 'true';
			$errnum++;
		}
		if(!$allday && $fromday == $today && $frommonth == $tomonth && $fromyear == $toyear) {
			list($tohours, $tominutes) = explode(':', $request['totime']);
			list($fromhours, $fromminutes) = explode(':', $request['fromtime']);
			if($tohours < $fromhours) {
				$errarr['endbeforestart'] = 'true';
				$errnum++;
			}
			if($tohours == $fromhours && $tominutes < $fromminutes) {
				$errarr['endbeforestart'] = 'true';
				$errnum++;
			}
		}
		if ($errnum)
		{
			return $errarr;
		}
		return false;
	}

	/**
	 * @brief validates time
	 * @param string $time
	 * @return boolean
	 */
	protected static function checkTime($time) {
		if(strpos($time, ':') === false ) {
			return true;
		}
		list($hours, $minutes) = explode(':', $time);
		return empty($time)
			|| $hours < 0 || $hours > 24
			|| $minutes < 0 || $minutes > 60;
	}

	/**
	 * @brief creates an VCalendar Object from the request data
	 * @param array $request
	 * @return object created $vcalendar
	 */	public static function createVCalendarFromRequest($request) {
		$vcalendar = new OC_VObject('VCALENDAR');
		$vcalendar->add('PRODID', 'ownCloud Calendar');
		$vcalendar->add('VERSION', '2.0');

		$vevent = new OC_VObject('VEVENT');
		$vcalendar->add($vevent);

		$vevent->setDateTime('CREATED', 'now', Sabre\VObject\Property\DateTime::UTC);

		$vevent->setUID();
		return self::updateVCalendarFromRequest($request, $vcalendar);
	}

	/**
	 * @brief updates an VCalendar Object from the request data
	 * @param array $request
	 * @param object $vcalendar
	 * @return object updated $vcalendar
	 */
	public static function updateVCalendarFromRequest($request, $vcalendar) {
		$accessclass = $request["accessclass"];
		$title = $request["title"];
		$location = $request["location"];
		$categories = $request["categories"];
		$allday = isset($request["allday"]);
		$from = $request["from"];
		$to  = $request["to"];
		if (!$allday) {
			$fromtime = $request['fromtime'];
			$totime = $request['totime'];
		}
		$vevent = $vcalendar->VEVENT;
		$description = $request["description"];
		$repeat = $request["repeat"];
		if($repeat != 'doesnotrepeat') {
			$rrule = '';
			$interval = $request['interval'];
			$end = $request['end'];
			$byoccurrences = $request['byoccurrences'];
			switch($repeat) {
				case 'daily':
					$rrule .= 'FREQ=DAILY';
					break;
				case 'weekly':
					$rrule .= 'FREQ=WEEKLY';
					if(array_key_exists('weeklyoptions', $request)) {
						$byday = '';
						$daystrings = array_flip(self::getWeeklyOptions(OC_Calendar_App::$l10n));
						foreach($request['weeklyoptions'] as $days) {
							if($byday == '') {
								$byday .= $daystrings[$days];
							}else{
								$byday .= ',' .$daystrings[$days];
							}
						}
						$rrule .= ';BYDAY=' . $byday;
					}
					break;
				case 'weekday':
					$rrule .= 'FREQ=WEEKLY';
					$rrule .= ';BYDAY=MO,TU,WE,TH,FR';
					break;
				case 'biweekly':
					$rrule .= 'FREQ=WEEKLY';
					$interval = $interval * 2;
					break;
				case 'monthly':
					$rrule .= 'FREQ=MONTHLY';
					if($request['advanced_month_select'] == 'monthday') {
						break;
					}elseif($request['advanced_month_select'] == 'weekday') {
						if($request['weekofmonthoptions'] == 'auto') {
							list($_day, $_month, $_year) = explode('-', $from);
							$weekofmonth = floor($_day/7);
						}else{
							$weekofmonth = $request['weekofmonthoptions'];
						}
						$days = array_flip(self::getWeeklyOptions(OC_Calendar_App::$l10n));
						$byday = '';
						foreach($request['weeklyoptions'] as $day) {
							if($byday == '') {
								$byday .= $weekofmonth . $days[$day];
							}else{
								$byday .= ',' . $weekofmonth . $days[$day];
							}
						}
						if($byday == '') {
							$byday = 'MO,TU,WE,TH,FR,SA,SU';
						}
						$rrule .= ';BYDAY=' . $byday;
					}
					break;
				case 'yearly':
					$rrule .= 'FREQ=YEARLY';
					if($request['advanced_year_select'] == 'bydate') {

					}elseif($request['advanced_year_select'] == 'byyearday') {
						list($_day, $_month, $_year) = explode('-', $from);
						$byyearday = date('z', mktime(0,0,0, $_month, $_day, $_year)) + 1;
						if(array_key_exists('byyearday', $request)) {
							foreach($request['byyearday'] as $yearday) {
								$byyearday .= ',' . $yearday;
							}
						}
						$rrule .= ';BYYEARDAY=' . $byyearday;
					}elseif($request['advanced_year_select'] == 'byweekno') {
						list($_day, $_month, $_year) = explode('-', $from);
						$rrule .= ';BYDAY=' . strtoupper(substr(date('l', mktime(0,0,0, $_month, $_day, $_year)), 0, 2));
						$byweekno = '';
						foreach($request['byweekno'] as $weekno) {
							if($byweekno == '') {
								$byweekno = $weekno;
							}else{
								$byweekno .= ',' . $weekno;
							}
						}
						$rrule .= ';BYWEEKNO=' . $byweekno;
					}elseif($request['advanced_year_select'] == 'bydaymonth') {
						if(array_key_exists('weeklyoptions', $request)) {
							$days = array_flip(self::getWeeklyOptions(OC_Calendar_App::$l10n));
							$byday = '';
							foreach($request['weeklyoptions'] as $day) {
								if($byday == '') {
								      $byday .= $days[$day];
								}else{
								      $byday .= ',' . $days[$day];
								}
							}
							$rrule .= ';BYDAY=' . $byday;
						}
						if(array_key_exists('bymonth', $request)) {
							$monthes = array_flip(self::getByMonthOptions(OC_Calendar_App::$l10n));
							$bymonth = '';
							foreach($request['bymonth'] as $month) {
								if($bymonth == '') {
								      $bymonth .= $monthes[$month];
								}else{
								      $bymonth .= ',' . $monthes[$month];
								}
							}
							$rrule .= ';BYMONTH=' . $bymonth;

						}
						if(array_key_exists('bymonthday', $request)) {
							$bymonthday = '';
							foreach($request['bymonthday'] as $monthday) {
								if($bymonthday == '') {
								      $bymonthday .= $monthday;
								}else{
								      $bymonthday .= ',' . $monthday;
								}
							}
							$rrule .= ';BYMONTHDAY=' . $bymonthday;

						}
					}
					break;
				default:
					break;
			}
			if($interval != '') {
				$rrule .= ';INTERVAL=' . $interval;
			}
			if($end == 'count') {
				$rrule .= ';COUNT=' . $byoccurrences;
			}
			if($end == 'date') {
				list($bydate_day, $bydate_month, $bydate_year) = explode('-', $request['bydate']);
				$rrule .= ';UNTIL=' . $bydate_year . $bydate_month . $bydate_day;
			}
			$vevent->setString('RRULE', $rrule);
			$repeat = "true";
		}else{
			$repeat = "false";
		}


		$vevent->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
		$vevent->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);
		$vevent->setString('SUMMARY', $title);

		if($allday) {
			$start = new DateTime($from);
			$end = new DateTime($to.' +1 day');
			$vevent->setDateTime('DTSTART', $start, Sabre\VObject\Property\DateTime::DATE);
			$vevent->setDateTime('DTEND', $end, Sabre\VObject\Property\DateTime::DATE);
		}else{
			$timezone = OC_Calendar_App::getTimezone();
			$timezone = new DateTimeZone($timezone);
			$start = new DateTime($from.' '.$fromtime, $timezone);
			$start->setTimezone(new DateTimeZone('UTC'));
			$end = new DateTime($to.' '.$totime, $timezone);
			$end->setTimezone(new DateTimeZone('UTC'));
			$vevent->setDateTime('DTSTART', $start, Sabre\VObject\Property\DateTime::UTC);
			$vevent->setDateTime('DTEND', $end, Sabre\VObject\Property\DateTime::UTC);
		}
		unset($vevent->DURATION);

		$vevent->setString('CLASS', $accessclass);
		$vevent->setString('LOCATION', $location);
		$vevent->setString('DESCRIPTION', $description);
		$vevent->setString('CATEGORIES', $categories);

		/*if($repeat == "true") {
			$vevent->RRULE = $repeat;
		}*/

		return $vcalendar;
	}

	/**
	 * @brief returns the owner of an object
	 * @param integer $id
	 * @return string
	 */
	public static function getowner($id) {
		$event = self::find($id);
		$cal = OC_Calendar_Calendar::find($event['calendarid']);
		return $cal['userid'];
	}

	/**
	 * @brief returns the calendarid of an object
	 * @param integer $id
	 * @return integer
	 */
	public static function getCalendarid($id) {
		$event = self::find($id);
		return $event['calendarid'];
	}

	/**
	 * @brief checks if an object is repeating
	 * @param integer $id
	 * @return boolean
	 */
	public static function isrepeating($id) {
		$event = self::find($id);
		return ($event['repeating'] == 1)?true:false;
	}

	/**
	 * @brief converts the start_dt and end_dt to a new timezone
	 * @param object $dtstart
	 * @param object $dtend
	 * @param boolean $allday
	 * @param string $tz
	 * @return array
	 */
	public static function generateStartEndDate($dtstart, $dtend, $allday, $tz) {
		$start_dt = $dtstart->getDateTime();
		$end_dt = $dtend->getDateTime();
		$return = array();
		if($allday) {
			$return['start'] = $start_dt->format('Y-m-d');
			$end_dt->modify('-1 minute');
			while($start_dt >= $end_dt) {
				$end_dt->modify('+1 day');
			}
			$return['end'] = $end_dt->format('Y-m-d');
		}else{
			$start_dt->setTimezone(new DateTimeZone($tz));
			$end_dt->setTimezone(new DateTimeZone($tz));
			$return['start'] = $start_dt->format('Y-m-d H:i:s');
			$return['end'] = $end_dt->format('Y-m-d H:i:s');
		}
		return $return;
	}
}
