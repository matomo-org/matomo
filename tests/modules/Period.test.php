<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

Zend_Loader::loadClass('Piwik_Period');
Zend_Loader::loadClass('Piwik_Date');

class Test_Piwik_Period extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
		$this->timer = new Piwik_Timer;
	}
	
	public function tearDown()
	{
//		echo $this->timer . "<br> ";
	}
	
	
	/**
	 * Testing Period_Day
	 */
	
	// today is NOT finished
	function test_isFinished_today()
	{
		$period = new Piwik_Period_Day( Piwik_Date::today());
		$this->assertEqual( $period->isFinished(), false);
		$this->assertEqual( $period->toString(), date("Y-m-d"));
		$this->assertEqual( $period->getSubperiods(), array());
		$this->assertEqual( $period->getNumberOfSubperiods(), 0);
	}
	// yesterday 23:59:59 is finished
	function test_isFinished_yesterday()
	{
		
		$period = new Piwik_Period_Day( Piwik_Date::yesterday());
		$this->assertEqual( $period->isFinished(), true);
		$this->assertEqual( $period->toString(), date("Y-m-d", time()-86400));
		$this->assertEqual( $period->getSubperiods(), array());
		$this->assertEqual( $period->getNumberOfSubperiods(), 0);
	}
	
	// tomorrow is not finished
	function test_isFinished_tomorrow()
	{	
		$period = new Piwik_Period_Day( new Piwik_Date(date("Y-m-d",time()+86400)));
		$this->assertEqual( $period->isFinished(), false);
		$this->assertEqual( $period->toString(), date("Y-m-d", time()+86400));
		$this->assertEqual( $period->getSubperiods(), array());
		$this->assertEqual( $period->getNumberOfSubperiods(), 0);
	}
	
	// TODO test day doesnt exist 31st feb
	function test_isFinished_31stfeb()
	{	
		$period = new Piwik_Period_Day( new Piwik_Date("2007-02-31"));
		$this->assertEqual( $period->isFinished(), true);
		$this->assertEqual( $period->toString(), "2007-03-03");
		$this->assertEqual( $period->getSubperiods(), array());
		$this->assertEqual( $period->getNumberOfSubperiods(), 0);
	}
		
	/**
	 * Testing Period_Month
	 *
	 */
	 // testing december
	 function test_monthDec()
	 {
	 	$month = new Piwik_Period_Month( new Piwik_Date("2006-12-31"));
	 	$correct=array(
			"2006-12-01",
			"2006-12-02",
			"2006-12-03",
			"2006-12-04",
			"2006-12-05",
			"2006-12-06",
			"2006-12-07",
			"2006-12-08",
			"2006-12-09",
			"2006-12-10",
			"2006-12-11",
			"2006-12-12",
			"2006-12-13",
			"2006-12-14",
			"2006-12-15",
			"2006-12-16",
			"2006-12-17",
			"2006-12-18",
			"2006-12-19",
			"2006-12-20",
			"2006-12-21",
			"2006-12-22",
			"2006-12-23",
			"2006-12-24",
			"2006-12-25",
			"2006-12-26",
			"2006-12-27",
			"2006-12-28",
			"2006-12-29",
			"2006-12-30",
			"2006-12-31",);
		$this->assertEqual( $month->toString(), $correct);
	 	$this->assertEqual( $month->getNumberOfSubperiods(), 31);
	 	$this->assertEqual( $month->isFinished(), true);
	 }
	 // testing month feb leap year
	 function test_monthFebLeap()
	 {
	 	$month = new Piwik_Period_Month( new Piwik_Date("2024-02-11"));
	 	$correct=array(
			"2024-02-01",
			"2024-02-02",
			"2024-02-03",
			"2024-02-04",
			"2024-02-05",
			"2024-02-06",
			"2024-02-07",
			"2024-02-08",
			"2024-02-09",
			"2024-02-10",
			"2024-02-11",
			"2024-02-12",
			"2024-02-13",
			"2024-02-14",
			"2024-02-15",
			"2024-02-16",
			"2024-02-17",
			"2024-02-18",
			"2024-02-19",
			"2024-02-20",
			"2024-02-21",
			"2024-02-22",
			"2024-02-23",
			"2024-02-24",
			"2024-02-25",
			"2024-02-26",
			"2024-02-27",
			"2024-02-28",
			"2024-02-29",);
		$this->assertEqual( $month->toString(), $correct);
	 	$this->assertEqual( $month->getNumberOfSubperiods(), 29);
	 	$this->assertEqual( $month->isFinished(), false);
	 }
	 // testing month feb non-leap year
	 function test_monthFebNonLeap()
	 {
	 	$month = new Piwik_Period_Month( new Piwik_Date("2023-02-11"));
	 	$correct=array(
			"2023-02-01",
			"2023-02-02",
			"2023-02-03",
			"2023-02-04",
			"2023-02-05",
			"2023-02-06",
			"2023-02-07",
			"2023-02-08",
			"2023-02-09",
			"2023-02-10",
			"2023-02-11",
			"2023-02-12",
			"2023-02-13",
			"2023-02-14",
			"2023-02-15",
			"2023-02-16",
			"2023-02-17",
			"2023-02-18",
			"2023-02-19",
			"2023-02-20",
			"2023-02-21",
			"2023-02-22",
			"2023-02-23",
			"2023-02-24",
			"2023-02-25",
			"2023-02-26",
			"2023-02-27",
			"2023-02-28",);
		$this->assertEqual( $month->toString(), $correct);
	 	$this->assertEqual( $month->getNumberOfSubperiods(), 28);
	 	$this->assertEqual( $month->isFinished(), false);
	 }
	 // testing jan
	  function test_monthJan()
	 {
	 	$month = new Piwik_Period_Month( new Piwik_Date("2007-01-01"));
	 	$correct=array(
			"2007-01-01",
			"2007-01-02",
			"2007-01-03",
			"2007-01-04",
			"2007-01-05",
			"2007-01-06",
			"2007-01-07",
			"2007-01-08",
			"2007-01-09",
			"2007-01-10",
			"2007-01-11",
			"2007-01-12",
			"2007-01-13",
			"2007-01-14",
			"2007-01-15",
			"2007-01-16",
			"2007-01-17",
			"2007-01-18",
			"2007-01-19",
			"2007-01-20",
			"2007-01-21",
			"2007-01-22",
			"2007-01-23",
			"2007-01-24",
			"2007-01-25",
			"2007-01-26",
			"2007-01-27",
			"2007-01-28",
			"2007-01-29",
			"2007-01-30",
			"2007-01-31",);
		$this->assertEqual( $month->toString(), $correct);
	 	$this->assertEqual( $month->getNumberOfSubperiods(), 31);
	 	$this->assertEqual( $month->isFinished(), true);
	 }
	 // testing month containing a time change (DST)
	 
	  function test_monthDSTChangeMarch()
	 {
	 	$month = new Piwik_Period_Month( new Piwik_Date("2007-02-31"));
	 	$correct=array(
			"2007-03-01",
			"2007-03-02",
			"2007-03-03",
			"2007-03-04",
			"2007-03-05",
			"2007-03-06",
			"2007-03-07",
			"2007-03-08",
			"2007-03-09",
			"2007-03-10",
			"2007-03-11",
			"2007-03-12",
			"2007-03-13",
			"2007-03-14",
			"2007-03-15",
			"2007-03-16",
			"2007-03-17",
			"2007-03-18",
			"2007-03-19",
			"2007-03-20",
			"2007-03-21",
			"2007-03-22",
			"2007-03-23",
			"2007-03-24",
			"2007-03-25",
			"2007-03-26",
			"2007-03-27",
			"2007-03-28",
			"2007-03-29",
			"2007-03-30",
			"2007-03-31",);
		$this->assertEqual( $month->toString(), $correct);
	 	$this->assertEqual( $month->getNumberOfSubperiods(), 31);
	 	$this->assertEqual( $month->isFinished(), true);
	 }
	  function test_monthDSTChangeOct()
	 {
	 	$month = new Piwik_Period_Month( new Piwik_Date("2017-10-31"));
	 	$correct=array(
			"2017-10-01",
			"2017-10-02",
			"2017-10-03",
			"2017-10-04",
			"2017-10-05",
			"2017-10-06",
			"2017-10-07",
			"2017-10-08",
			"2017-10-09",
			"2017-10-10",
			"2017-10-11",
			"2017-10-12",
			"2017-10-13",
			"2017-10-14",
			"2017-10-15",
			"2017-10-16",
			"2017-10-17",
			"2017-10-18",
			"2017-10-19",
			"2017-10-20",
			"2017-10-21",
			"2017-10-22",
			"2017-10-23",
			"2017-10-24",
			"2017-10-25",
			"2017-10-26",
			"2017-10-27",
			"2017-10-28",
			"2017-10-29",
			"2017-10-30",
			"2017-10-31",);
		$this->assertEqual( $month->toString(), $correct);
	 	$this->assertEqual( $month->getNumberOfSubperiods(), 31);
	 	$this->assertEqual( $month->isFinished(), false);
	 }
	/**
	 * Testing Period_Week
	 *
	 */
	/* //http://framework.zend.com/issues/browse/ZF-1832
	 function test_week_zendsetweekday()
	 {
	 	$date = new Zend_Date('2006-01-01','YYYY-MM-dd', 'en');
	 	$date->setWeekday(1);	 	
	 	$this->assertEqual('2005-12-26', $date->toString("Y-m-d"));
	 }*/
	// test week between 2 years
	function test_week_Between2years()
	 {
	 	$week = new Piwik_Period_Week( new Piwik_Date("2006-01-01"));
	 	$correct=array(
			"2005-12-26",
			"2005-12-27",
			"2005-12-28",
			"2005-12-29",
			"2005-12-30",
			"2005-12-31",
			"2006-01-01",);
		$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), true);
	 }
	// test week between 2 months Week Mai 29 To Mai 31 2006
	function test_week_Between2month()
	 {
	 	$week = new Piwik_Period_Week( new Piwik_Date("2006-05-29"));
	 	$correct=array(
			"2006-05-29",
			"2006-05-30",
			"2006-05-31",
			"2006-06-01",
			"2006-06-02",
			"2006-06-03",
			"2006-06-04",);
		$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), true);
	 }
	// test week between feb and march for leap year
	function test_week_febLeapyear()
	 {
	 	$correct=array(
			'2023-02-27',
			'2023-02-28',
			'2023-03-01',
			'2023-03-02',
			'2023-03-03',
			'2023-03-04',
			'2023-03-05',);
			
	 	$week = new Piwik_Period_Week( new Piwik_Date('2023-02-27'));
	 	$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), false);
	 	$week = new Piwik_Period_Week( new Piwik_Date('2023-03-01'));
	 	$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), false);
	 }
	// test week between feb and march for no leap year
	function test_week_febnonLeapyear()
	 {
	 	$correct=array(
			'2024-02-26',
			'2024-02-27',
			'2024-02-28',
			'2024-02-29',
			'2024-03-01',
			'2024-03-02',
			'2024-03-03',);
			
	 	$week = new Piwik_Period_Week( new Piwik_Date('2024-02-27'));
	 	$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), false);
	 	$week = new Piwik_Period_Week( new Piwik_Date('2024-03-01'));
	 	$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), false);
	 }
	// test week normal middle of the month
	function test_week_middleofmonth()
	 {
	 	$correct=array(
			'2024-10-07',
			'2024-10-08',
			'2024-10-09',
			'2024-10-10',
			'2024-10-11',
			'2024-10-12',
			'2024-10-13',);
			
	 	$week = new Piwik_Period_Week( new Piwik_Date('2024-10-09'));
	 	$this->assertEqual( $week->toString(), $correct);
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 7);
	 	$this->assertEqual( $week->isFinished(), false);
	 }
	 
	 /**
	  * Testing Period_Year
	  */
	 
	 
	// test normal case
	function test_year_normalcase()
	 {
	 	$correct=array(
			'2024-01-01',
			'2024-02-01',
			'2024-03-01',
			'2024-04-01',
			'2024-05-01',
			'2024-06-01',
			'2024-07-01',
			'2024-08-01',
			'2024-09-01',
			'2024-10-01',
			'2024-11-01',
			'2024-12-01',);
		
	 	$week = new Piwik_Period_Year( new Piwik_Date('2024-10-09'));
	 	$this->assertEqual( $week->getNumberOfSubperiods(), 12);
	 	$this->assertEqual( $week->isFinished(), false);
	 	$this->assertEqual( $week->toString(), $correct);

	 }
	 
	// test past
	function test_year_pastAndWrongdate()
	 {
	 	$correct=array(
			'2000-01-01',
			'2000-02-01',
			'2000-03-01',
			'2000-04-01',
			'2000-05-01',
			'2000-06-01',
			'2000-07-01',
			'2000-08-01',
			'2000-09-01',
			'2000-10-01',
			'2000-11-01',
			'2000-12-01',
		);
			
//	 	$week = new Piwik_Period_Year( new Piwik_Date('2000-02-15'));
//	 	$this->assertEqual( $week->getNumberOfSubperiods(), 12);
//	 	$this->assertEqual( $week->isFinished(), true);
//	 	$this->assertEqual( $week->toString(), $correct);
	 }
}

