<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

class Test_Piwik_Period_Range extends UnitTestCase
{
	function test_CustomRange_weekInside_endingToday()
	{
	 	$range = new Piwik_Period_Range( 'range', '2007-12-22,2008-01-03', 'UTC', Piwik_Date::factory('2008-01-03') );
	 	
	 	$correct = array(
 			'2007-12-22',
 			'2007-12-23',
	 		array(
 				'2007-12-24',
 				'2007-12-25',
 				'2007-12-26',
 				'2007-12-27',
 				'2007-12-28',
 				'2007-12-29',
 				'2007-12-30',
	 		),
	 		array(
	 			'2007-12-31',
	 			'2008-01-01',
	 			'2008-01-02',
	 			'2008-01-03',
	 			'2008-01-04',
	 			'2008-01-05',
	 			'2008-01-06',
	 		)
 		);
//	 	var_dump($range->toString());
	 	//var_dump($correct);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }
	
	function test_CustomRange_weekInside_endingYesterday()
	{
		$todays = array(
			Piwik_Date::factory('2008-01-04'),
			Piwik_Date::factory('2008-01-05'),
			Piwik_Date::factory('2008-01-14'),
			Piwik_Date::factory('2008-02-14'),
			Piwik_Date::factory('2009-02-14'),
		);
		
		foreach($todays as $today)
		{
		 	$range = new Piwik_Period_Range( 'range', '2007-12-22,2008-01-03', 'UTC', $today );
		 	
		 	$correct = array(
	 			'2007-12-22',
	 			'2007-12-23',
		 		array(
	 				'2007-12-24',
	 				'2007-12-25',
	 				'2007-12-26',
	 				'2007-12-27',
	 				'2007-12-28',
	 				'2007-12-29',
	 				'2007-12-30',
		 		),
	 			'2007-12-31',
	 			'2008-01-01',
	 			'2008-01-02',
	 			'2008-01-03',
	 		);
	//	 	var_dump($range->toString());
		 	//var_dump($correct);
		 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
		 	$this->assertEqual( $range->toString(), $correct);
		}
	 }
	 
	 function test_CustomRange_onlyDaysLessThanOneWeek()
	{
	 	$range = new Piwik_Period_Range( 'range', '2007-12-30,2008-01-01' );
	 	
	 	$correct = array(
 			'2007-12-30',
 			'2007-12-31',
 			'2008-01-01',
 		);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }
	
	function test_CustomRange_oneWeekOnly()
	{
	 	$range = new Piwik_Period_Range( 'range', '2007-12-31,2008-01-06' );
	 	
	 	$correct = array(
	 		array(
 			'2007-12-31',
 			'2008-01-01',
 			'2008-01-02',
 			'2008-01-03',
 			'2008-01-04',
 			'2008-01-05',
 			'2008-01-06',
	 		)
 		);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }
	 	
	 function test_CustomRange_startsWithWeek()
	{
	 	$range = new Piwik_Period_Range( 'range', '2007-12-31,2008-01-08' );
	 	
	 	$correct = array(
	 		array(
 			'2007-12-31',
 			'2008-01-01',
 			'2008-01-02',
 			'2008-01-03',
 			'2008-01-04',
 			'2008-01-05',
 			'2008-01-06',
	 		),
 			'2008-01-07',
 			'2008-01-08',
 		);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }
	 
	 function test_CustomRange_endsWithWeek()
	{
	 	$range = new Piwik_Period_Range( 'range', '2007-12-21,2008-01-06' );
	 	
	 	$correct = array(
 			'2007-12-21',
 			'2007-12-22',
 			'2007-12-23',
	 		array(
 			'2007-12-24',
 			'2007-12-25',
 			'2007-12-26',
 			'2007-12-27',
 			'2007-12-28',
 			'2007-12-29',
 			'2007-12-30',
	 		),
	 		array(
 			'2007-12-31',
 			'2008-01-01',
 			'2008-01-02',
 			'2008-01-03',
 			'2008-01-04',
 			'2008-01-05',
 			'2008-01-06',
	 		),
 		);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }

	 function test_CustomRange_containsMonthAndWeek()
	 {
	 	$range = new Piwik_Period_Range( 'range', '2011-09-18,2011-11-02', 'UTC', Piwik_Date::factory('2012-01-01') );
	 	
	 	$correct = array(
	 	 
 	 	'2011-09-18',
	 	array(
	 	 	'2011-09-19',
	 	 	'2011-09-20',
	 	 	'2011-09-21',
	 	 	'2011-09-22',
	 	 	'2011-09-23',
	 	 	'2011-09-24',
	 	 	'2011-09-25',
	 	),
	 	 
 		'2011-09-26',
 		'2011-09-27',
 		'2011-09-28',
 		'2011-09-29',
 		'2011-09-30',
 		array(
			"2011-10-01",
			"2011-10-02",
			"2011-10-03",
			"2011-10-04",
			"2011-10-05",
			"2011-10-06",
			"2011-10-07",
			"2011-10-08",
			"2011-10-09",
			"2011-10-10",
			"2011-10-11",
			"2011-10-12",
			"2011-10-13",
			"2011-10-14",
			"2011-10-15",
			"2011-10-16",
			"2011-10-17",
			"2011-10-18",
			"2011-10-19",
			"2011-10-20",
			"2011-10-21",
			"2011-10-22",
			"2011-10-23",
			"2011-10-24",
			"2011-10-25",
			"2011-10-26",
			"2011-10-27",
			"2011-10-28",
			"2011-10-29",
			"2011-10-30",
			"2011-10-31",
 		),
		"2011-11-01",
		"2011-11-02",
 		);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }

	 function test_CustomRange_containsSeveralMonthsAndWeeks_startingWithMonth()
	 {
	 	// Testing when "today" is in the same month, or later in the future
	 	$todays = array(
	 		Piwik_Date::factory('2011-10-18'),
	 		Piwik_Date::factory('2011-10-19'),
	 		Piwik_Date::factory('2011-10-24'),
	 		Piwik_Date::factory('2011-11-01'),
	 		Piwik_Date::factory('2011-11-30'),
	 		Piwik_Date::factory('2011-12-31'),
	 		Piwik_Date::factory('2021-10-18')
	 	);
	 	foreach($todays as $today)
	 	{
		 	$range = new Piwik_Period_Range( 'range', '2011-08-01,2011-10-17', 'UTC', $today );
		 	
		 	$correct = array(
		 	 
	 		array(
				"2011-08-01",
				"2011-08-02",
				"2011-08-03",
				"2011-08-04",
				"2011-08-05",
				"2011-08-06",
				"2011-08-07",
				"2011-08-08",
				"2011-08-09",
				"2011-08-10",
				"2011-08-11",
				"2011-08-12",
				"2011-08-13",
				"2011-08-14",
				"2011-08-15",
				"2011-08-16",
				"2011-08-17",
				"2011-08-18",
				"2011-08-19",
				"2011-08-20",
				"2011-08-21",
				"2011-08-22",
				"2011-08-23",
				"2011-08-24",
				"2011-08-25",
				"2011-08-26",
				"2011-08-27",
				"2011-08-28",
				"2011-08-29",
				"2011-08-30",
				"2011-08-31",
	 		),
	 		array(
				"2011-09-01",
				"2011-09-02",
				"2011-09-03",
				"2011-09-04",
				"2011-09-05",
				"2011-09-06",
				"2011-09-07",
				"2011-09-08",
				"2011-09-09",
				"2011-09-10",
				"2011-09-11",
				"2011-09-12",
				"2011-09-13",
				"2011-09-14",
				"2011-09-15",
				"2011-09-16",
				"2011-09-17",
				"2011-09-18",
				"2011-09-19",
				"2011-09-20",
				"2011-09-21",
				"2011-09-22",
				"2011-09-23",
				"2011-09-24",
				"2011-09-25",
				"2011-09-26",
				"2011-09-27",
				"2011-09-28",
				"2011-09-29",
				"2011-09-30",
	 		),	 	 
			"2011-10-01",
			"2011-10-02",
	 		
	 		array(
				"2011-10-03",
				"2011-10-04",
				"2011-10-05",
				"2011-10-06",
				"2011-10-07",
				"2011-10-08",
				"2011-10-09",
	 		),
	 		array(
				"2011-10-10",
				"2011-10-11",
				"2011-10-12",
				"2011-10-13",
				"2011-10-14",
				"2011-10-15",
				"2011-10-16",
	 		),
	 			"2011-10-17",
	 		);
//		 	var_dump( $range->toString() );
	 		
		 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
		 	$this->assertEqual( $range->toString(), $correct, "Fail for Today = " . $today);
	 	}
	 }
 
	 function test_CustomRange_oneMonthOnly()
	{
	 	$range = new Piwik_Period_Range( 'range', '2011-09-01,2011-09-30' );
	 	
	 	$correct = array(
	 	array(
			"2011-09-01",
			"2011-09-02",
			"2011-09-03",
			"2011-09-04",
			"2011-09-05",
			"2011-09-06",
			"2011-09-07",
			"2011-09-08",
			"2011-09-09",
			"2011-09-10",
			"2011-09-11",
			"2011-09-12",
			"2011-09-13",
			"2011-09-14",
			"2011-09-15",
			"2011-09-16",
			"2011-09-17",
			"2011-09-18",
			"2011-09-19",
			"2011-09-20",
			"2011-09-21",
			"2011-09-22",
			"2011-09-23",
			"2011-09-24",
			"2011-09-25",
			"2011-09-26",
			"2011-09-27",
			"2011-09-28",
			"2011-09-29",
			"2011-09-30",
 		));
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }	 	 
	 
	 function test_CustomRange_startsWithWeek_EndsWithMonth()
	 {
	 	$range = new Piwik_Period_Range( 'range', '2011-07-25,2011-08-31' );
	 	
	 	$correct = array(
	 	 
	 	array(
	 	 	'2011-07-25',
	 	 	'2011-07-26',
	 	 	'2011-07-27',
	 	 	'2011-07-28',
	 	 	'2011-07-29',
	 	 	'2011-07-30',
	 	 	'2011-07-31',
	 	),
 		array(
			"2011-08-01",
			"2011-08-02",
			"2011-08-03",
			"2011-08-04",
			"2011-08-05",
			"2011-08-06",
			"2011-08-07",
			"2011-08-08",
			"2011-08-09",
			"2011-08-10",
			"2011-08-11",
			"2011-08-12",
			"2011-08-13",
			"2011-08-14",
			"2011-08-15",
			"2011-08-16",
			"2011-08-17",
			"2011-08-18",
			"2011-08-19",
			"2011-08-20",
			"2011-08-21",
			"2011-08-22",
			"2011-08-23",
			"2011-08-24",
			"2011-08-25",
			"2011-08-26",
			"2011-08-27",
			"2011-08-28",
			"2011-08-29",
			"2011-08-30",
			"2011-08-31",
 		),
 		);
//var_dump($range->toString());
//var_dump($correct);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	}
	
	function test_CustomRange_beforeIsAfter_yeahRight()
	{
	 	$range = new Piwik_Period_Range( 'range', '2007-02-09,2007-02-01' );
	 	$this->assertEqual( $range->getNumberOfSubperiods(), 0);
	 	$this->assertEqual( $range->toString(), array());
	 	
	 	try {
	 		$range->getPrettyString();
	 		$this->fail();
	 	} catch(Exception $e) {
	 		$this->pass();
	 		
	 	}
	} 	
	
	function test_CustomRange_lastN()
	{
	 	$range = new Piwik_Period_Range( 'range', 'last4' );
	 	$range->setDefaultEndDate(Piwik_Date::factory('2008-01-03'));
	 	$correct = array(
	 			'2007-12-31',
	 			'2008-01-01',
	 			'2008-01-02',
	 			'2008-01-03',
 		);
 		//var_dump($range->toString());
	 	//var_dump($correct);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }

	function test_CustomRange_previousN()
	{
	 	$range = new Piwik_Period_Range( 'range', 'previous3' );
	 	$range->setDefaultEndDate(Piwik_Date::factory('2008-01-03'));
	 	$correct = array(
	 			'2007-12-31',
	 			'2008-01-01',
	 			'2008-01-02',
 		);
	 	//var_dump($range->toString());
	 	//var_dump($correct);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }
	function test_CustomRange_previousN_endToday()
	{
	 	$range = new Piwik_Period_Range( 'range', 'previous3' );
	 	$correct = array(
	 			date('Y-m-d',time()-86400*3),
	 			date('Y-m-d',time()-86400*2),
	 			date('Y-m-d',time()-86400*1),
 		);
//var_dump($range->toString());
	 	//var_dump($correct);
	 	$this->assertEqual( $range->getNumberOfSubperiods(), count($correct));
	 	$this->assertEqual( $range->toString(), $correct);
	 }
	
	 function test_InvalidRange_throws()
	 {
	 	try {
	 		$range = new Piwik_Period_Range( 'range', '0001-01-01,today' );
	 		echo $range->getLocalizedLongString();
	 		$this->fail();
	 	} catch(Exception $e) {
	 		$this->pass();
	 	}
	 }
	 
}
