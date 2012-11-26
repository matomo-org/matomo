<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class ScheduledTimeTest extends PHPUnit_Framework_TestCase
{
    private $_JANUARY_01_1971_09_00_00;
    private $_JANUARY_01_1971_09_10_00;
    private $_JANUARY_01_1971_10_00_00;
    private $_JANUARY_01_1971_12_10_00;
    private $_JANUARY_02_1971_00_00_00;
    private $_JANUARY_02_1971_09_00_00;
    private $_JANUARY_04_1971_00_00_00;
    private $_JANUARY_04_1971_09_00_00;
    private $_JANUARY_05_1971_09_00_00;
    private $_JANUARY_11_1971_00_00_00;
    private $_JANUARY_15_1971_00_00_00;
    private $_JANUARY_15_1971_09_00_00;
    private $_FEBRUARY_01_1971_00_00_00;
    private $_FEBRUARY_02_1971_00_00_00;
    private $_FEBRUARY_03_1971_09_00_00;
    private $_FEBRUARY_21_1971_09_00_00;
    private $_FEBRUARY_28_1971_00_00_00;

    public function setUp()
    {
        parent::setUp();
        $this->_JANUARY_01_1971_09_00_00 = mktime(9,00,00,1,1,1971);
        $this->_JANUARY_01_1971_09_10_00 = mktime(9,10,00,1,1,1971);
        $this->_JANUARY_01_1971_10_00_00 = mktime(10,00,00,1,1,1971);
        $this->_JANUARY_01_1971_12_10_00 = mktime(12,10,00,1,1,1971);
        $this->_JANUARY_02_1971_00_00_00 = mktime(0,00,00,1,2,1971);
        $this->_JANUARY_02_1971_09_00_00 = mktime(9,00,00,1,2,1971);
        $this->_JANUARY_04_1971_00_00_00 = mktime(0,00,00,1,4,1971);
        $this->_JANUARY_04_1971_09_00_00 = mktime(9,00,00,1,4,1971);
        $this->_JANUARY_05_1971_09_00_00 = mktime(9,00,00,1,5,1971);
        $this->_JANUARY_11_1971_00_00_00 = mktime(0,00,00,1,11,1971);
        $this->_JANUARY_15_1971_00_00_00 = mktime(0,00,00,1,15,1971);
        $this->_JANUARY_15_1971_09_00_00 = mktime(9,00,00,1,15,1971);
        $this->_FEBRUARY_01_1971_00_00_00 = mktime(0,00,00,2,1,1971);
        $this->_FEBRUARY_02_1971_00_00_00 = mktime(0,00,00,2,2,1971);
        $this->_FEBRUARY_03_1971_09_00_00 = mktime(0,00,00,2,3,1971);
        $this->_FEBRUARY_21_1971_09_00_00 = mktime(0,00,00,2,21,1971);
        $this->_FEBRUARY_28_1971_00_00_00 = mktime(0,00,00,2,28,1971);
    }
    
    /**
     * Tests forbidden call to setHour on Piwik_ScheduledTime_Hourly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimeHourly()
    {
        try {
            $hourlySchedule = new Piwik_ScheduledTime_Hourly();
            $hourlySchedule->setHour(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Piwik_ScheduledTime_Daily
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimeDailyNegative()
    {
        try
        {
            $dailySchedule = new Piwik_ScheduledTime_Daily();
            $dailySchedule->setHour(-1);

        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Piwik_ScheduledTime_Daily
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimeDailyOver24()
    {
        try {
            $dailySchedule = new Piwik_ScheduledTime_Daily();
            $dailySchedule->setHour(25);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }
    
    /**
     * Tests invalid call to setHour on Piwik_ScheduledTime_Weekly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimeWeeklyNegative()
    {
        try {
            $weeklySchedule = new Piwik_ScheduledTime_Weekly();
            $weeklySchedule->setHour(-1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Piwik_ScheduledTime_Weekly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimeWeeklyOver24()
    {
        try {
            $weeklySchedule = new Piwik_ScheduledTime_Weekly();
            $weeklySchedule->setHour(25);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }
    
    /**
     * Tests invalid call to setHour on Piwik_ScheduledTime_Monthly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimeMonthlyNegative()
    {
        try {
            $monthlySchedule = new Piwik_ScheduledTime_Monthly();
            $monthlySchedule->setHour(-1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Piwik_ScheduledTime_Monthly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetHourScheduledTimMonthlyOver24()
    {
        try {
            $monthlySchedule = new Piwik_ScheduledTime_Monthly();
            $monthlySchedule->setHour(25);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests forbidden call to setDay on Piwik_ScheduledTime_Hourly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetDayScheduledTimeHourly()
    {
        try {
            $hourlySchedule = new Piwik_ScheduledTime_Hourly();
            $hourlySchedule->setDay(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }
    
    /**
     * Tests forbidden call to setDay on Piwik_ScheduledTime_Daily
     * @group Core
     * @group ScheduledTime
     */
    public function testSetDayScheduledTimeDaily()
    {
        try {
            $dailySchedule = new Piwik_ScheduledTime_Daily();
            $dailySchedule->setDay(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }
    
    /**
     * Tests invalid call to setDay on Piwik_ScheduledTime_Weekly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetDayScheduledTimeWeeklyDay0()
    {
        try {
            $weeklySchedule = new Piwik_ScheduledTime_Weekly();
            $weeklySchedule->setDay(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setDay on Piwik_ScheduledTime_Weekly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetDayScheduledTimeWeeklyOver7()
    {
        try {
            $weeklySchedule = new Piwik_ScheduledTime_Weekly();
            $weeklySchedule->setDay(8);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }
    
    /**
     * Tests invalid call to setDay on Piwik_ScheduledTime_Monthly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetDayScheduledTimeMonthlyDay0()
    {
        try {
            $monthlySchedule = new Piwik_ScheduledTime_Monthly();
            $monthlySchedule->setDay(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setDay on Piwik_ScheduledTime_Monthly
     * @group Core
     * @group ScheduledTime
     */
    public function testSetDayScheduledTimeMonthlyOver31()
    {
        try {
            $monthlySchedule = new Piwik_ScheduledTime_Monthly();
            $monthlySchedule->setDay(32);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }
    

    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Hourly
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeHourly()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:00:00 GMT
         *
         * Expected :
         *  getRescheduledTime returns Friday January 1 1971 10:00:00 GMT
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Hourly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_00_00));
        $this->assertEquals($this->_JANUARY_01_1971_10_00_00, $mock->getRescheduledTime());

        /*
         * Test 2
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:10:00 GMT
         *
         * Expected :
         *  getRescheduledTime returns Friday January 1 1971 10:00:00 GMT
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Hourly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_10_00));
        $this->assertEquals($this->_JANUARY_01_1971_10_00_00, $mock->getRescheduledTime());
    }

    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Daily with unspecified hour
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeDailyUnspecifiedHour()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:10:00 UTC
         *  - setHour is not called, defaulting to midnight
         *
         * Expected :
         *  getRescheduledTime returns Saturday January 2 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Daily', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_10_00));
        $this->assertEquals($this->_JANUARY_02_1971_00_00_00, $mock->getRescheduledTime());
    }

    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Daily with specified hour
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeDailySpecifiedHour()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:00:00 UTC
         *  - setHour is set to 9
         *
         * Expected :
         *  getRescheduledTime returns Saturday January 2 1971 09:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Daily', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_00_00));
        $mock->setHour(9);
        $this->assertEquals($this->_JANUARY_02_1971_09_00_00, $mock->getRescheduledTime());

        /*
         * Test 2
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 12:10:00 UTC
         *  - setHour is set to 9
         *
         * Expected :
         *  getRescheduledTime returns Saturday January 2 1971 09:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Daily', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_12_10_00));
        $mock->setHour(9);
        $this->assertEquals($this->_JANUARY_02_1971_09_00_00, $mock->getRescheduledTime());

        /*
         * Test 3
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 12:10:00 UTC
         *  - setHour is set to 0
         *
         * Expected :
         *  getRescheduledTime returns Saturday January 2 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Daily', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_12_10_00));
        $mock->setHour(0);
        $this->assertEquals($this->_JANUARY_02_1971_00_00_00, $mock->getRescheduledTime());
    }
    
    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Weekly with unspecified hour and unspecified day
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeWeeklyUnspecifiedHourUnspecifiedDay()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:10:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is not called, defaulting to monday
         *
         * Expected :
         *  getRescheduledTime returns Monday January 4 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Weekly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_10_00));
        $this->assertEquals($this->_JANUARY_04_1971_00_00_00, $mock->getRescheduledTime());
    }
    
    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Weekly with specified hour and unspecified day
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeWeeklySpecifiedHourUnspecifiedDay()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:10:00 UTC
         *  - setHour is set to 9
         *  - setDay is not called, defaulting to monday
         *
         * Expected :
         *  getRescheduledTime returns Monday January 4 1971 09:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Weekly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_10_00));
        $mock->setHour(9);
        $this->assertEquals($this->_JANUARY_04_1971_09_00_00, $mock->getRescheduledTime());
    }

    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Weekly with unspecified hour and specified day
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeWeeklyUnspecifiedHourSpecifiedDay()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Monday January 4 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 1, Monday
         *
         * Expected :
         *  getRescheduledTime returns Monday January 11 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Weekly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_04_1971_09_00_00));
        $mock->setDay(1);
        $this->assertEquals($this->_JANUARY_11_1971_00_00_00, $mock->getRescheduledTime());
        
        /*
         * Test 2
         *
         * Context :
         *  - getRescheduledTime called Tuesday 5 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 1, Monday
         *
         * Expected :
         *  getRescheduledTime returns Monday January 11 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Weekly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_05_1971_09_00_00));
        $mock->setDay(1);
        $this->assertEquals($this->_JANUARY_11_1971_00_00_00, $mock->getRescheduledTime());

        /*
         * Test 3
         *
         * Context :
         *  - getRescheduledTime called Monday January 4 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 1, Friday
         *
         * Expected :
         *  getRescheduledTime returns Friday January 15 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Weekly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_04_1971_09_00_00));
        $mock->setDay(5);
        $this->assertEquals($this->_JANUARY_15_1971_00_00_00, $mock->getRescheduledTime());
    }

    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Monthly with unspecified hour and unspecified day
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeMonthlyUnspecifiedHourUnspecifiedDay()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is not called, defaulting to first day of the month
         *
         * Expected :
         *  getRescheduledTime returns Monday February 1 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_00_00));
        $this->assertEquals($this->_FEBRUARY_01_1971_00_00_00, $mock->getRescheduledTime());
        
        /*
         * Test 2
         *
         * Context :
         *  - getRescheduledTime called Tuesday January 5 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is not called, defaulting to first day of the month
         *
         * Expected :
         *  getRescheduledTime returns Monday February 1 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_05_1971_09_00_00));
        $this->assertEquals($this->_FEBRUARY_01_1971_00_00_00, $mock->getRescheduledTime());
    }

    
    /**
     * Tests getRescheduledTime on Piwik_ScheduledTime_Monthly with unspecified hour and specified day
     * @group Core
     * @group ScheduledTime
     */
    public function testGetRescheduledTimeMonthlyUnspecifiedHourSpecifiedDay()
    {
        /*
         * Test 1
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 1
         *
         * Expected :
         *  getRescheduledTime returns Monday February 1 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_01_1971_09_00_00));
        $mock->setDay(1);
        $this->assertEquals($this->_FEBRUARY_01_1971_00_00_00, $mock->getRescheduledTime());
        
        /*
         * Test 2
         *
         * Context :
         *  - getRescheduledTime called Saturday January 2 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 2
         *
         * Expected :
         *  getRescheduledTime returns Tuesday February 2 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_02_1971_09_00_00));
        $mock->setDay(2);
        $this->assertEquals($this->_FEBRUARY_02_1971_00_00_00, $mock->getRescheduledTime());
        
        /*
         * Test 3
         *
         * Context :
         *  - getRescheduledTime called Friday January 15 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 2
         *
         * Expected :
         *  getRescheduledTime returns Tuesday February 1 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_15_1971_09_00_00));
        $mock->setDay(2);
        $this->assertEquals($this->_FEBRUARY_02_1971_00_00_00, $mock->getRescheduledTime());

        /*
         * Test 4
         *
         * Context :
         *  - getRescheduledTime called Friday January 15 1971 09:00:00 UTC
         *  - setHour is not called, defaulting to midnight
         *  - setDay is set to 31
         *
         * Expected :
         *  getRescheduledTime returns Sunday February 28 1971 00:00:00 UTC
         */
        $mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
        $mock->expects($this->any())
             ->method('getTime')
             ->will($this->returnValue($this->_JANUARY_15_1971_09_00_00));
        $mock->setDay(31);
        $this->assertEquals($this->_FEBRUARY_28_1971_00_00_00, $mock->getRescheduledTime());
    }
	
	/**
	 * @group Core
	 * @group ScheduledTime
	 */
	public function testMonthlyDayOfWeek()
	{
		$mock = $this->getMock('Piwik_ScheduledTime_Monthly', array('getTime'));
		$mock->expects($this->any())
			 ->method('getTime')
			 ->will($this->returnValue($this->_JANUARY_15_1971_09_00_00));
		$mock->setDayOfWeek(3, 0); // first wednesday
		$this->assertEquals($this->_FEBRUARY_03_1971_09_00_00, $mock->getRescheduledTime());
		
		$mock->setDayOfWeek(0, 2); // third sunday
		$this->assertEquals($this->_FEBRUARY_21_1971_09_00_00, $mock->getRescheduledTime());
	}
}
