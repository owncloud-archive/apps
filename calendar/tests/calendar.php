<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OC_App::loadApp('calendar');
class Test_Calendar_Calendars extends PHPUnit_Framework_TestCase {
	function testBasic() {
		$uid=uniqid();
		$this->assertEquals(OC_Calendar_Calendar::allCalendars($uid),array());
		OC_User::setUserId($uid);
		$calId1=OC_Calendar_Calendar::addCalendar($uid,'test');

		$all=OC_Calendar_Calendar::allCalendars($uid);
		$this->assertEquals(count($all),1);

		$this->assertEquals($all[0]['id'],$calId1);
		$this->assertEquals($all[0]['displayname'],'test');
		$this->assertEquals($all[0]['uri'],'test');
		$this->assertEquals($uid, $all[0]['userid']);

		$calId2=OC_Calendar_Calendar::addCalendar($uid,'test');
		$this->assertNotEquals($calId1, $calId2);

		$all=OC_Calendar_Calendar::allCalendars($uid);
		$this->assertEquals(count($all),2);

		$this->assertEquals($all[1]['id'],$calId2);
		$this->assertEquals($all[1]['displayname'],'test');
		$this->assertEquals($all[1]['uri'],'test1');

		//$cal1=OC_Calendar_Calendar::find($calId1);
		//$this->assertEquals($cal1,$all[0]);

		OC_Calendar_Calendar::deleteCalendar($calId1);
		OC_Calendar_Calendar::deleteCalendar($calId2);
		$this->assertEquals(OC_Calendar_Calendar::allCalendars($uid),array());
	}
}
