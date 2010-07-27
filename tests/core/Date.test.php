<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'Date.php';

class Test_Piwik_Date extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
	}
	
	public function tearDown()
	{
	}
	
	//create today object check that timestamp is correct (midnight)
	function test_today()
	{
		$date = Piwik_Date::today();
		$this->assertEqual( strtotime(date("Y-m-d "). " 00:00:00"), $date->getTimestamp());
		
		// test getDatetime()
		$this->assertEqual( $date->getDatetime(), $date->getDateStartUTC());
		$date = $date->setTime('12:00:00');
		$this->assertEqual( $date->getDatetime(), date('Y-m-d') . ' 12:00:00');
	}
	
	//create today object check that timestamp is correct (midnight)
	function test_yesterday()
	{
		$date = Piwik_Date::yesterday();
		$this->assertEqual( strtotime(date("Y-m-d",strtotime('-1day')). " 00:00:00"), $date->getTimestamp());
	}

	function test_factoryTimezone()
	{
		// now in UTC converted to UTC+10 means adding 10 hours 
		$date = Piwik_Date::factory('now', 'UTC+10');
		$dateExpected = Piwik_Date::now()->addHour(10);
		$this->assertEqual($date->getDatetime(), $dateExpected->getDatetime());

		// Congo is in UTC+1 all year long (no DST)
		$date = Piwik_Date::factory('now', 'Africa/Brazzaville');
		$dateExpected = Piwik_Date::factory('now')->addHour(1);
		$this->assertEqual($date->getDatetime(), $dateExpected->getDatetime());
		
		// yesterday same time in Congo is the same as today in Congo - 24 hours
		$date = Piwik_Date::factory('yesterdaySameTime', 'Africa/Brazzaville');
		$dateExpected = Piwik_Date::factory('now', 'Africa/Brazzaville')->subHour(24);
		$this->assertEqual($date->getDatetime(), $dateExpected->getDatetime());
	}
	
	function test_setTimezone_dayInUTC()
	{
		$date = Piwik_Date::factory('2010-01-01');
		
		$dayStart = '2010-01-01 00:00:00';
		$dayEnd = '2010-01-01 23:59:59';
		$this->assertEqual($date->getDateStartUTC(), $dayStart);
		$this->assertEqual($date->getDateEndUTC(), $dayEnd);
		
		$date = $date->setTimezone('UTC');
		$this->assertEqual($date->getDateStartUTC(), $dayStart);
		$this->assertEqual($date->getDateEndUTC(), $dayEnd);
		
		$date = $date->setTimezone('Europe/Paris');
		$utcDayStart = '2009-12-31 23:00:00';
		$utcDayEnd = '2010-01-01 22:59:59';
		$this->assertEqual($date->getDateStartUTC(), $utcDayStart);
		$this->assertEqual($date->getDateEndUTC(), $utcDayEnd);
		
		$date = $date->setTimezone('UTC+1');
		$utcDayStart = '2009-12-31 23:00:00';
		$utcDayEnd = '2010-01-01 22:59:59';
		$this->assertEqual($date->getDateStartUTC(), $utcDayStart);
		$this->assertEqual($date->getDateEndUTC(), $utcDayEnd);

		$date = $date->setTimezone('UTC-1');
		$utcDayStart = '2010-01-01 01:00:00';
		$utcDayEnd = '2010-01-02 00:59:59';
		$this->assertEqual($date->getDateStartUTC(), $utcDayStart);
		$this->assertEqual($date->getDateEndUTC(), $utcDayEnd);
	}
	
	function test_modifyDate_withTimezone()
	{
		$date = Piwik_Date::factory('2010-01-01');
		$date = $date->setTimezone('UTC-1');
		
		$timestamp = $date->getTimestamp();
		$date = $date->addHour(0)->addHour(0)->addHour(0);
		$this->assertEqual($timestamp, $date->getTimestamp());
		
		
		$date = Piwik_Date::factory('2010-01-01')->setTimezone('Europe/Paris');
		$dateExpected = clone $date;
		$date = $date->addHour(2);
		$dateExpected = $dateExpected->addHour(1.1)->addHour(0.9)->addHour(1)->subHour(1);
		$this->assertEqual($date->getTimestamp(), $dateExpected->getTimestamp());
	}
	
	function test_getDateStartUTCEnd_DuringDstTimezone()
	{
		$date = Piwik_Date::factory('2010-03-28');
		$parisDayStart = '2010-03-28 00:00:00';
		$parisDayEnd = '2010-03-28 23:59:59';
		
		$date = $date->setTimezone('Europe/Paris');
		$utcDayStart = '2010-03-27 23:00:00'; 
		$utcDayEnd = '2010-03-28 21:59:59';

		$this->assertEqual($date->getDateStartUTC(), $utcDayStart);
		$this->assertEqual($date->getDateEndUTC(), $utcDayEnd);
	}
	
	function test_addHour()
	{
		// add partial hours less than 1
		$dayStart = '2010-03-28 00:00:00';
		$dayExpected = '2010-03-28 00:18:00';
		$date = Piwik_Date::factory($dayStart)->addHour(0.3);
		$this->assertEqual($date->getDatetime(), $dayExpected);
		$date = $date->subHour(0.3);
		$this->assertEqual($date->getDatetime(), $dayStart);
		
		// add partial hours
		$dayExpected = '2010-03-28 05:45:00';
		$date = Piwik_Date::factory($dayStart)->addHour(5.75);
		$this->assertEqual($date->getDatetime(), $dayExpected);
		
		// remove partial hours
		$dayExpected = '2010-03-27 18:15:00';
		$date = Piwik_Date::factory($dayStart)->subHour(5.75);
		$this->assertEqual($date->getDatetime(), $dayExpected);
	}

	function test_addHour_longHours()
	{
    	$dateTime = '2010-01-03 11:22:33';
    	$expectedTime = '2010-01-05 11:28:33';
		$this->assertEqual(Piwik_Date::factory($dateTime)->addHour(48.1)->getDatetime(), $expectedTime);
		$this->assertEqual(Piwik_Date::factory($dateTime)->addHour(48.1)->subHour(48.1)->getDatetime(), $dateTime);
	}

	function test_addPeriod()
	{
		$date = Piwik_Date::factory('2010-01-01');
		$dateExpected = Piwik_Date::factory('2010-01-06');
		$date = $date->addPeriod(5, 'day');
		$this->assertEqual($date->getTimestamp(), $dateExpected->getTimestamp());

		$date = Piwik_Date::factory('2010-03-01');
		$dateExpected = Piwik_Date::factory('2010-04-05');
		$date = $date->addPeriod(5, 'week');
		$this->assertEqual($date->getTimestamp(), $dateExpected->getTimestamp());
}

	function test_subPeriod()
	{
		$date = Piwik_Date::factory('2010-03-01');
		$dateExpected = Piwik_Date::factory('2010-02-15');
		$date = $date->subPeriod(2, 'week');
		$this->assertEqual($date->getTimestamp(), $dateExpected->getTimestamp());

		$date = Piwik_Date::factory('2010-12-15');
		$dateExpected = Piwik_Date::factory('2005-12-15');
		$date = $date->subPeriod(5, 'year');
		$this->assertEqual($date->getTimestamp(), $dateExpected->getTimestamp());
	}
}

