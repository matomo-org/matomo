<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ScheduledTime\Monthly;

class ScheduledTime_MonthlyTest extends PHPUnit_Framework_TestCase
{
    private static $_JANUARY_01_1971_09_00_00;
    private static $_JANUARY_02_1971_09_00_00;
    private static $_JANUARY_05_1971_09_00_00;
    private static $_JANUARY_15_1971_09_00_00;
    private static $_FEBRUARY_01_1971_00_00_00;
    private static $_FEBRUARY_02_1971_00_00_00;
    private static $_FEBRUARY_03_1971_09_00_00;
    private static $_FEBRUARY_21_1971_09_00_00;
    private static $_FEBRUARY_28_1971_00_00_00;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_JANUARY_01_1971_09_00_00 = mktime(9, 00, 00, 1, 1, 1971);
        self::$_JANUARY_02_1971_09_00_00 = mktime(9, 00, 00, 1, 2, 1971);
        self::$_JANUARY_05_1971_09_00_00 = mktime(9, 00, 00, 1, 5, 1971);
        self::$_JANUARY_15_1971_09_00_00 = mktime(9, 00, 00, 1, 15, 1971);
        self::$_FEBRUARY_01_1971_00_00_00 = mktime(0, 00, 00, 2, 1, 1971);
        self::$_FEBRUARY_02_1971_00_00_00 = mktime(0, 00, 00, 2, 2, 1971);
        self::$_FEBRUARY_03_1971_09_00_00 = mktime(0, 00, 00, 2, 3, 1971);
        self::$_FEBRUARY_21_1971_09_00_00 = mktime(0, 00, 00, 2, 21, 1971);
        self::$_FEBRUARY_28_1971_00_00_00 = mktime(0, 00, 00, 2, 28, 1971);
    }

    /**
     * Tests invalid call to setHour on Monthly
     * @group Core
     */
    public function testSetHourScheduledTimeMonthlyNegative()
    {
        try {
            $monthlySchedule = new Monthly();
            $monthlySchedule->setHour(-1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Monthly
     * @group Core
     */
    public function testSetHourScheduledTimMonthlyOver24()
    {
        try {
            $monthlySchedule = new Monthly();
            $monthlySchedule->setHour(25);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setDay on Monthly
     * @group Core
     */
    public function testSetDayScheduledTimeMonthlyDay0()
    {
        try {
            $monthlySchedule = new Monthly();
            $monthlySchedule->setDay(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setDay on Monthly
     * @group Core
     */
    public function testSetDayScheduledTimeMonthlyOver31()
    {
        try {
            $monthlySchedule = new Monthly();
            $monthlySchedule->setDay(32);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests getRescheduledTime on Monthly with unspecified hour and unspecified day
     * @group Core
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
        $mock = $this->getMock('\Piwik\ScheduledTime\Monthly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_01_1971_09_00_00));
        $this->assertEquals(self::$_FEBRUARY_01_1971_00_00_00, $mock->getRescheduledTime());

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
        $mock = $this->getMock('\Piwik\ScheduledTime\Monthly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_05_1971_09_00_00));
        $this->assertEquals(self::$_FEBRUARY_01_1971_00_00_00, $mock->getRescheduledTime());
    }


    /**
     * Tests getRescheduledTime on Monthly with unspecified hour and specified day
     * @group Core
     *
     * _Monthly
     *
     * @dataProvider getSpecifiedDayData
     */
    public function testGetRescheduledTimeMonthlyUnspecifiedHourSpecifiedDay($currentTime, $day, $expected)
    {
        $mock = $this->getMock('\Piwik\ScheduledTime\Monthly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$$currentTime));
        $mock->setDay($day);
        $this->assertEquals(self::$$expected, $mock->getRescheduledTime());
    }

    /**
     * DataProvider for testGetRescheduledTimeMonthlyUnspecifiedHourSpecifiedDay
     * @return array
     */
    public function getSpecifiedDayData()
    {
        return array(
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
            array('_JANUARY_01_1971_09_00_00', 1, '_FEBRUARY_01_1971_00_00_00'),
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
            array('_JANUARY_02_1971_09_00_00', 2, '_FEBRUARY_02_1971_00_00_00'),
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
            array('_JANUARY_15_1971_09_00_00', 2, '_FEBRUARY_02_1971_00_00_00'),
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
            array('_JANUARY_15_1971_09_00_00', 31, '_FEBRUARY_28_1971_00_00_00')
        );
    }

    /**
     * @group Core
     */
    public function testMonthlyDayOfWeek()
    {
        $mock = $this->getMock('\Piwik\ScheduledTime\Monthly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_15_1971_09_00_00));
        $mock->setDayOfWeek(3, 0); // first wednesday
        $this->assertEquals(self::$_FEBRUARY_03_1971_09_00_00, $mock->getRescheduledTime());

        $mock->setDayOfWeek(0, 2); // third sunday
        $this->assertEquals(self::$_FEBRUARY_21_1971_09_00_00, $mock->getRescheduledTime());
    }

    /**
     * @group Core
     *
     * _Monthly
     *
     * @dataProvider getInvalidDayOfWeekData
     */
    public function testMonthlyDayOfWeekInvalid($day, $week)
    {
        $mock = $this->getMock('\Piwik\ScheduledTime\Monthly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_15_1971_09_00_00));
        try {
            $mock->setDayOfWeek($day, $week);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected Exception not raised');
    }

    /**
     * DataProvider for testMonthlyDayOfWeekInvalid
     * @return array
     */
    public function getInvalidDayOfWeekData()
    {
        return array(
            array(-4, 0),
            array(8, 0),
            array(0x8, 0),
            array('9dd', 0),
            array(1, -5),
            array(1, 5),
            array(1, 0x8),
            array(1, '9ff'),
        );
    }
}
