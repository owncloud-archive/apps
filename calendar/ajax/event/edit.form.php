<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



if(!OCP\User::isLoggedIn()) {
	OCP\User::checkLoggedIn();
}

OCP\JSON::checkAppEnabled('calendar');

$id = $_POST['id'];
$data = OC_Calendar_App::getEventObject($id, false, false);

if(!$data) {
	OCP\JSON::error(array('data' => array('message' => OC_Calendar_App::$l10n->t('Wrong calendar'))));
	exit;
}
$object = OC_VObject::parse($data['calendardata']);
$vevent = $object->VEVENT;
$object = OC_Calendar_Object::cleanByAccessClass($id, $object);
$permissions = OC_Calendar_App::getPermissions($id, OC_Calendar_App::EVENT, $vevent->CLASS->value);

$dtstart = $vevent->DTSTART;
$dtend = OC_Calendar_Object::getDTEndFromVEvent($vevent);
switch($dtstart->getDateType()) {
	case Sabre\VObject\Property\DateTime::UTC:
		$timezone = new DateTimeZone(OC_Calendar_App::getTimezone());
		$newDT    = $dtstart->getDateTime();
		$newDT->setTimezone($timezone);
		$dtstart->setDateTime($newDT);
		$newDT    = $dtend->getDateTime();
		$newDT->setTimezone($timezone);
		$dtend->setDateTime($newDT);
	case Sabre\VObject\Property\DateTime::LOCALTZ:
	case Sabre\VObject\Property\DateTime::LOCAL:
		$startdate = $dtstart->getDateTime()->format('d-m-Y');
		$starttime = $dtstart->getDateTime()->format('H:i');
		$enddate = $dtend->getDateTime()->format('d-m-Y');
		$endtime = $dtend->getDateTime()->format('H:i');
		$allday = false;
		break;
	case Sabre\VObject\Property\DateTime::DATE:
		$startdate = $dtstart->getDateTime()->format('d-m-Y');
		$starttime = '';
		$dtend->getDateTime()->modify('-1 day');
		$enddate = $dtend->getDateTime()->format('d-m-Y');
		$endtime = '';
		$allday = true;
		break;
}

$accessclass = $vevent->getAsString('CLASS');
$summary = $vevent->getAsString('SUMMARY');
$location = $vevent->getAsString('LOCATION');
$categories = $vevent->getAsString('CATEGORIES');
$description = $vevent->getAsString('DESCRIPTION');
$last_modified = $vevent->__get('LAST-MODIFIED');
if ($last_modified) {
	$lastmodified = $last_modified->getDateTime()->format('U');
}else{
	$lastmodified = 0;
}
if($data['repeating'] == 1) {
	$rrule = explode(';', $vevent->getAsString('RRULE'));
	$rrulearr = array();
	foreach($rrule as $rule) {
		list($attr, $val) = explode('=', $rule);
		$rrulearr[$attr] = $val;
	}
	if(!isset($rrulearr['INTERVAL']) || $rrulearr['INTERVAL'] == '') {
		$rrulearr['INTERVAL'] = 1;
	}
	if(array_key_exists('BYDAY', $rrulearr)) {
		if(substr_count($rrulearr['BYDAY'], ',') == 0) {
			if(strlen($rrulearr['BYDAY']) == 2) {
				$repeat['weekdays'] = array($rrulearr['BYDAY']);
			}elseif(strlen($rrulearr['BYDAY']) == 3) {
				$repeat['weekofmonth'] = substr($rrulearr['BYDAY'], 0, 1);
				$repeat['weekdays'] = array(substr($rrulearr['BYDAY'], 1, 2));
			}elseif(strlen($rrulearr['BYDAY']) == 4) {
				$repeat['weekofmonth'] = substr($rrulearr['BYDAY'], 0, 2);
				$repeat['weekdays'] = array(substr($rrulearr['BYDAY'], 2, 2));
			}
		}else{
			$byday_days = explode(',', $rrulearr['BYDAY']);
			foreach($byday_days as $byday_day) {
				if(strlen($byday_day) == 2) {
					$repeat['weekdays'][] = $byday_day;
				}elseif(strlen($byday_day) == 3) {
					$repeat['weekofmonth'] = substr($byday_day , 0, 1);
					$repeat['weekdays'][] = substr($byday_day , 1, 2);
				}elseif(strlen($byday_day) == 4) {
					$repeat['weekofmonth'] = substr($byday_day , 0, 2);
					$repeat['weekdays'][] = substr($byday_day , 2, 2);
				}
			}
		}
	}
	if(array_key_exists('BYMONTHDAY', $rrulearr)) {
		if(substr_count($rrulearr['BYMONTHDAY'], ',') == 0) {
			$repeat['bymonthday'][] = $rrulearr['BYMONTHDAY'];
		}else{
			$bymonthdays = explode(',', $rrulearr['BYMONTHDAY']);
			foreach($bymonthdays as $bymonthday) {
				$repeat['bymonthday'][] = $bymonthday;
			}
		}
	}
	if(array_key_exists('BYYEARDAY', $rrulearr)) {
		if(substr_count($rrulearr['BYYEARDAY'], ',') == 0) {
			$repeat['byyearday'][] = $rrulearr['BYYEARDAY'];
		}else{
			$byyeardays = explode(',', $rrulearr['BYYEARDAY']);
			foreach($byyeardays  as $yearday) {
				$repeat['byyearday'][] = $yearday;
			}
		}
	}
	if(array_key_exists('BYWEEKNO', $rrulearr)) {
		if(substr_count($rrulearr['BYWEEKNO'], ',') == 0) {
			$repeat['byweekno'][] = (string) $rrulearr['BYWEEKNO'];
		}else{
			$byweekno = explode(',', $rrulearr['BYWEEKNO']);
			foreach($byweekno as $weekno) {
				$repeat['byweekno'][] = (string) $weekno;
			}
		}
	}
	if(array_key_exists('BYMONTH', $rrulearr)) {
		$months = OC_Calendar_App::getByMonthOptions();
		if(substr_count($rrulearr['BYMONTH'], ',') == 0) {
			$repeat['bymonth'][] = $months[$month];
		}else{
			$bymonth = explode(',', $rrulearr['BYMONTH']);
			foreach($bymonth as $month) {
				$repeat['bymonth'][] = $months[$month];
			}
		}
	}
	switch($rrulearr['FREQ']) {
		case 'DAILY':
			$repeat['repeat'] = 'daily';
			break;
		case 'WEEKLY':
			if($rrulearr['INTERVAL'] % 2 == 0) {
				$repeat['repeat'] = 'biweekly';
				$rrulearr['INTERVAL'] = $rrulearr['INTERVAL'] / 2;
			}elseif($rrulearr['BYDAY'] == 'MO,TU,WE,TH,FR') {
				$repeat['repeat'] = 'weekday';
			}else{
				$repeat['repeat'] = 'weekly';
			}
			break;
		case 'MONTHLY':
			$repeat['repeat'] = 'monthly';
			if(array_key_exists('BYDAY', $rrulearr)) {
				$repeat['month'] = 'weekday';
			}else{
				$repeat['month'] = 'monthday';
			}
			break;
		case 'YEARLY':
			$repeat['repeat'] = 'yearly';
			if(array_key_exists('BYMONTH', $rrulearr)) {
				$repeat['year'] = 'bydaymonth';
			}elseif(array_key_exists('BYWEEKNO', $rrulearr)) {
				$repeat['year'] = 'byweekno';
			}else{
				$repeat['year'] = 'byyearday';
			}
	}
	$repeat['interval'] = $rrulearr['INTERVAL'];
	if(array_key_exists('COUNT', $rrulearr)) {
		$repeat['end'] = 'count';
		$repeat['count'] = $rrulearr['COUNT'];
	}elseif(array_key_exists('UNTIL', $rrulearr)) {
		$repeat['end'] = 'date';
		$endbydate_day = substr($rrulearr['UNTIL'], 6, 2);
		$endbydate_month = substr($rrulearr['UNTIL'], 4, 2);
		$endbydate_year = substr($rrulearr['UNTIL'], 0, 4);
		$repeat['date'] = $endbydate_day . '-' .  $endbydate_month . '-' . $endbydate_year;
	}else{
		$repeat['end'] = 'never';
	}
	if(array_key_exists('weekdays', $repeat)) {
		$repeat_weekdays_ = array();
		$days = OC_Calendar_App::getWeeklyOptions();
		foreach($repeat['weekdays'] as $weekday) {
			$repeat_weekdays_[] = $days[$weekday];
		}
		$repeat['weekdays'] = $repeat_weekdays_;
	}
}else{
	$repeat['repeat'] = 'doesnotrepeat';
}
$calendar_options = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser());
$category_options = OC_Calendar_App::getCategoryOptions();
$access_class_options = OC_Calendar_App::getAccessClassOptions();
$repeat_options = OC_Calendar_App::getRepeatOptions();
$repeat_end_options = OC_Calendar_App::getEndOptions();
$repeat_month_options = OC_Calendar_App::getMonthOptions();
$repeat_year_options = OC_Calendar_App::getYearOptions();
$repeat_weekly_options = OC_Calendar_App::getWeeklyOptions();
$repeat_weekofmonth_options = OC_Calendar_App::getWeekofMonth();
$repeat_byyearday_options = OC_Calendar_App::getByYearDayOptions();
$repeat_bymonth_options = OC_Calendar_App::getByMonthOptions();
$repeat_byweekno_options = OC_Calendar_App::getByWeekNoOptions();
$repeat_bymonthday_options = OC_Calendar_App::getByMonthDayOptions();

if($permissions & OCP\PERMISSION_UPDATE) {
	$tmpl = new OCP\Template('calendar', 'part.editevent');
} elseif($permissions & OCP\PERMISSION_READ) {
	$tmpl = new OCP\Template('calendar', 'part.showevent');
} elseif($permissions === 0) {
	OCP\JSON::error(array('data' => array('message' => OC_Calendar_App::$l10n->t('You do not have the permissions to edit this event.'))));
	exit;
}

$tmpl->assign('eventid', $id);
$tmpl->assign('permissions', $permissions);
$tmpl->assign('lastmodified', $lastmodified);
$tmpl->assign('calendar_options', $calendar_options);
$tmpl->assign('access_class_options', $access_class_options);
$tmpl->assign('repeat_options', $repeat_options);
$tmpl->assign('repeat_month_options', $repeat_month_options);
$tmpl->assign('repeat_weekly_options', $repeat_weekly_options);
$tmpl->assign('repeat_end_options', $repeat_end_options);
$tmpl->assign('repeat_year_options', $repeat_year_options);
$tmpl->assign('repeat_byyearday_options', $repeat_byyearday_options);
$tmpl->assign('repeat_bymonth_options', $repeat_bymonth_options);
$tmpl->assign('repeat_byweekno_options', $repeat_byweekno_options);
$tmpl->assign('repeat_bymonthday_options', $repeat_bymonthday_options);
$tmpl->assign('repeat_weekofmonth_options', $repeat_weekofmonth_options);

$tmpl->assign('title', $summary);
$tmpl->assign('accessclass', $accessclass);
$tmpl->assign('location', $location);
$tmpl->assign('categories', $categories);
$tmpl->assign('calendar', $data['calendarid']);
$tmpl->assign('allday', $allday);
$tmpl->assign('startdate', $startdate);
$tmpl->assign('starttime', $starttime);
$tmpl->assign('enddate', $enddate);
$tmpl->assign('endtime', $endtime);
$tmpl->assign('description', $description);

$tmpl->assign('repeat', $repeat['repeat']);
if($repeat['repeat'] != 'doesnotrepeat') {
	$tmpl->assign('repeat_month', isset($repeat['month']) ? $repeat['month'] : 'monthday');
	$tmpl->assign('repeat_weekdays', isset($repeat['weekdays']) ? $repeat['weekdays'] : array());
	$tmpl->assign('repeat_interval', isset($repeat['interval']) ? $repeat['interval'] : '1');
	$tmpl->assign('repeat_end', isset($repeat['end']) ? $repeat['end'] : 'never');
	$tmpl->assign('repeat_count', isset($repeat['count']) ? $repeat['count'] : '10');
	$tmpl->assign('repeat_weekofmonth', $repeat['weekofmonth']);
	$tmpl->assign('repeat_date', isset($repeat['date']) ? $repeat['date'] : '');
	$tmpl->assign('repeat_year', isset($repeat['year']) ? $repeat['year'] : array());
	$tmpl->assign('repeat_byyearday', isset($repeat['byyearday']) ? $repeat['byyearday'] : array());
	$tmpl->assign('repeat_bymonthday', isset($repeat['bymonthday']) ? $repeat['bymonthday'] : array());
	$tmpl->assign('repeat_bymonth', isset($repeat['bymonth']) ? $repeat['bymonth'] : array());
	$tmpl->assign('repeat_byweekno', isset($repeat['byweekno']) ? $repeat['byweekno'] : array());
} else {
	$tmpl->assign('repeat_month', 'monthday');
	$tmpl->assign('repeat_weekdays', array());
	$tmpl->assign('repeat_interval', 1);
	$tmpl->assign('repeat_end', 'never');
	$tmpl->assign('repeat_count', '10');
	$tmpl->assign('repeat_weekofmonth', 'auto');
	$tmpl->assign('repeat_date', '');
	$tmpl->assign('repeat_year', 'bydate');
}
$tmpl->printpage();
