<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class Test_Calendar_Calendars extends UnitTestCase {
	function testBasic() {
		$uid=uniqid();

		$this->assertEqual(OC_Calendar_Calendar::allCalendars($uid),array());
		$calId1=OC_Calendar_Calendar::addCalendar($uid,'test');

		$all=OC_Calendar_Calendar::allCalendars($uid);
		$this->assertEqual(count($all),1);

		$this->assertEqual($all[0]['id'],$calId1);
		$this->assertEqual($all[0]['displayname'],'test');
		$this->assertEqual($all[0]['uri'],'test');

		$calId2=OC_Calendar_Calendar::addCalendar($uid,'test');
		$this->assertNotEqual($calId1, $calId2);

		$all=OC_Calendar_Calendar::allCalendars($uid);
		$this->assertEqual(count($all),2);

		$this->assertEqual($all[1]['id'],$calId2);
		$this->assertEqual($all[1]['displayname'],'test');
		$this->assertEqual($all[1]['uri'],'test1');

		$cal1=OC_Calendar_Calendar::find($calId1);
		$this->assertEqual($cal1,$all[0]);

		OC_Calendar_Calendar::deleteCalendar($calId1);
		OC_Calendar_Calendar::deleteCalendar($calId2);
		$this->assertEqual(OC_Calendar_Calendar::allCalendars($uid),array());
	}
}