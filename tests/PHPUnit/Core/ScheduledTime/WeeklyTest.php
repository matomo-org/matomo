<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ScheduledTime\Weekly;

class ScheduledTime_WeeklyTest extends PHPUnit_Framework_TestCase
{
    public static $_JANUARY_01_1971_09_10_00; // initialized below class declaration
    public static $_JANUARY_04_1971_00_00_00;
    public static $_JANUARY_04_1971_09_00_00;
    public static $_JANUARY_05_1971_09_00_00;
    public static $_JANUARY_11_1971_00_00_00;
    public static $_JANUARY_15_1971_00_00_00;
    public static $_JANUARY_08_1971_00_00_00;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    /**
     * Tests invalid call to setHour on Weekly
     * @group Core
     */
    public function testSetHourScheduledTimeWeeklyNegative()
    {
        try {
            $weeklySchedule = new Weekly();
            $weeklySchedule->setHour(-1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Weekly
     * @group Core
     */
    public function testSetHourScheduledTimeWeeklyOver24()
    {
        try {
            $weeklySchedule = new Weekly();
            $weeklySchedule->setHour(25);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setDay on Weekly
     * @group Core
     */
    public function testSetDayScheduledTimeWeeklyDay0()
    {
        try {
            $weeklySchedule = new Weekly();
            $weeklySchedule->setDay(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setDay on Weekly
     * @group Core
     */
    public function testSetDayScheduledTimeWeeklyOver7()
    {
        try {
            $weeklySchedule = new Weekly();
            $weeklySchedule->setDay(8);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests getRescheduledTime on Weekly with unspecified hour and unspecified day
     * @group Core
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
        $mock = $this->getMock('\Piwik\ScheduledTime\Weekly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_01_1971_09_10_00));
        $this->assertEquals(self::$_JANUARY_04_1971_00_00_00, $mock->getRescheduledTime());
    }

    /**
     * Tests getRescheduledTime on Weekly with specified hour and unspecified day
     * @group Core
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
        $mock = $this->getMock('\Piwik\ScheduledTime\Weekly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_01_1971_09_10_00));
        $mock->setHour(9);
        $this->assertEquals(self::$_JANUARY_04_1971_09_00_00, $mock->getRescheduledTime());
    }

    /**
     * Returns data used in testGetRescheduledTimeWeeklyUnspecifiedHourSpecifiedDay test.
     */
    public function getSetDayParametersToTest()
    {
        return array(
            array(1, self::$_JANUARY_11_1971_00_00_00),
            array(5, self::$_JANUARY_08_1971_00_00_00),
            array('monday', self::$_JANUARY_11_1971_00_00_00),
            array('Monday', self::$_JANUARY_11_1971_00_00_00),
            array('FRIDAY', self::$_JANUARY_08_1971_00_00_00),
            array('FrIdAy', self::$_JANUARY_08_1971_00_00_00)
        );
    }

    /**
     * Tests getRescheduledTime on Weekly with unspecified hour and specified day
     * 
     * Context :
     *  - getRescheduledTime called Monday January 4 1971 09:00:00 UTC
     *  - setHour is not called, defaulting to midnight
     *  - setDay is set to $dayToSet
     * 
     * @group Core
     * @dataProvider getSetDayParametersToTest
     */
    public function testGetRescheduledTimeWeeklyUnspecifiedHourSpecifiedDay($dayToSet, $expectedRescheduledTime)
    {
        $mock = $this->getMock('\Piwik\ScheduledTime\Weekly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_04_1971_09_00_00));
        $mock->setDay($dayToSet);
        $this->assertEquals($expectedRescheduledTime, $mock->getRescheduledTime());
    }
}

ScheduledTime_WeeklyTest::$_JANUARY_01_1971_09_10_00 = mktime(9, 10, 00, 1, 1, 1971);
ScheduledTime_WeeklyTest::$_JANUARY_04_1971_00_00_00 = mktime(0, 00, 00, 1, 4, 1971);
ScheduledTime_WeeklyTest::$_JANUARY_04_1971_09_00_00 = mktime(9, 00, 00, 1, 4, 1971);
ScheduledTime_WeeklyTest::$_JANUARY_05_1971_09_00_00 = mktime(9, 00, 00, 1, 5, 1971);
ScheduledTime_WeeklyTest::$_JANUARY_11_1971_00_00_00 = mktime(0, 00, 00, 1, 11, 1971);
ScheduledTime_WeeklyTest::$_JANUARY_15_1971_00_00_00 = mktime(0, 00, 00, 1, 15, 1971);
ScheduledTime_WeeklyTest::$_JANUARY_08_1971_00_00_00 = mktime(0, 00, 00, 1, 8, 1971);