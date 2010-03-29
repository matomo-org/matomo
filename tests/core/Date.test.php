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
		$dateExpected = $dateExpected->addHour(1)->addHour(1)->addHour(1)->subHour(1);
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
	/*
	function _test_setTimezone_standard()
	{
		$today = Piwik_Date::today();
		$date = Piwik_Date::today();

		// UTC == GMT
		$this->assertEqual( $date->getTimestamp(), $today->getTimestamp() );
		$date->setTimezone('GMT');
		$this->assertEqual( $date->getTimestamp(), $today->getTimestamp() );

		// Setting the timezone twice
		for($i = 0 ; $i < 2; $i++)
		{
    		$date->setTimezone('Etc/GMT+1');
    		var_dump(date('Y-m-d H:i:s', $today->getTimestamp()));
    		var_dump(date('Y-m-d H:i:s', $date->getTimestamp()));
    		$this->assertEqual( $date->getTimestamp(), $today->getTimestamp() + 3600);
		}
		
		// Setting GMT minus
		$date->setTimezone('Etc/GMT-1');
		$this->assertEqual( $date->getTimestamp(), $today->addHour(1)->getTimestamp());
	}
	
	
	function _test_setTimezone_DuringDst()
	{
		// create a date before DST change
		$date = Piwik_Date::factory('2010-03-28');
		// set timezone to Paris, that we know apply DST change on 
		$date->setTimezone('Europe/Paris');
		$this->assertEqual($date->getTimestamp(), Piwik_Date::factory('2010-03-28 01:00:00')->getTimestamp());
		
		// add 3 hours
		$date = $date->addHour(3);

		// test that we added 4 hours as expected, after jumping the DST hour
		// see http://www.timeanddate.com/worldclock/converted.html?day=28&month=3&year=2010&hour=4&min=0&sec=0&p1=0&p2=195
		$expectedDate = Piwik_Date::factory('2010-03-28 04:00:00')->setTimezone('Europe/Paris');

		
		$this->assertEqual($date->getTimestamp(), $expectedDate->getTimestamp());
		
		// the date is 6AM UTC (Paris is now in UTC+2)
		$this->assertEqual($date->getTimestamp(), Piwik_Date::factory('2010-03-28 06:00:00')->getTimestamp());
		
		// now testing a non DST day (the day before the change)
		$date = Piwik_Date::factory('2010-03-27 00:00:00')->addHour(3)->setTimezone('Europe/Paris');
		$expectedDate = Piwik_Date::factory('2010-03-27 03:00:00')->setTimezone('Europe/Paris');
		$this->assertEqual($date->getTimestamp(), $expectedDate->getTimestamp());

		// testing the same with the addHour after the timezone conversion
		$date = Piwik_Date::factory('2010-03-27 00:00:00')
					->setTimezone('Europe/Paris')
					->addHour(1)
					->setTimezone('Europe/Paris')
					;
		$expectedDate = Piwik_Date::factory('2010-03-27 03:00:00')->setTimezone('Europe/Paris');
		echo $date; echo "<br>";
		echo $expectedDate;echo "<br>";
		$this->assertEqual($date->getTimestamp(), $expectedDate->getTimestamp());
		
		$date = Piwik_Date::factory('2010-03-27 00:00:00')
					->setTimezone('Europe/Paris')
					->addHour(1)->addHour(1)->addHour(1);
		$expectedDate = Piwik_Date::factory('2010-03-27 00:00:00')
					->setTimezone('Europe/Paris')
					->addHour(3);
		$this->assertEqual($date->getTimestamp(), $expectedDate->getTimestamp());
		
	}

	// convert the same date object to multiple time zones
	function _test_setTimezone_multiple()
	{
		$dateString = '2010-03-01 00:00:00';
		$date = Piwik_Date::factory($dateString);
		$date->setTimezone('Europe/Paris');
		$date->setTimezone('Etc/GMT+7');
		
		$expectedDate = Piwik_Date::factory($dateString)->setTimezone('Etc/GMT+7');
		$this->assertEqual($date->getTimestamp(), $expectedDate->getTimestamp());
		
		$date = $date->setTimezone('UTC')->addDay(1);
		$expectedDate = Piwik_Date::factory('2010-03-02 00:00:00');
		$this->assertEqual($date->setTimezone('UTC')->getTimestamp(), $expectedDate->getTimestamp());
	}*/
	
}

