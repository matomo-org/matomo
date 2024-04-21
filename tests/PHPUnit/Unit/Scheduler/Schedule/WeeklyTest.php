<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Scheduler\Schedule;

use Exception;
use Piwik\Scheduler\Schedule\Weekly;

/**
 * @group Scheduler
 */
class WeeklyTest extends \PHPUnit\Framework\TestCase
{
    public static $JANUARY_01_1971_09_10_00; // initialized below class declaration
    public static $JANUARY_04_1971_00_00_00;
    public static $JANUARY_04_1971_09_00_00;
    public static $JANUARY_05_1971_09_00_00;
    public static $JANUARY_11_1971_00_00_00;
    public static $JANUARY_15_1971_00_00_00;
    public static $JANUARY_08_1971_00_00_00;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /**
     * Tests invalid call to setHour on Weekly
     */
    public function testSetHourScheduledTimeWeeklyNegative()
    {
        $this->expectException(Exception::class);

        $weeklySchedule = new Weekly();
        $weeklySchedule->setHour(-1);
    }

    /**
     * Tests invalid call to setHour on Weekly
     */
    public function testSetHourScheduledTimeWeeklyOver24()
    {
        $this->expectException(Exception::class);

        $weeklySchedule = new Weekly();
        $weeklySchedule->setHour(25);
    }

    /**
     * Tests invalid call to setDay on Weekly
     */
    public function testSetDayScheduledTimeWeeklyDay0()
    {
        $this->expectException(Exception::class);

        $weeklySchedule = new Weekly();
        $weeklySchedule->setDay(0);
    }

    /**
     * Tests invalid call to setDay on Weekly
     */
    public function testSetDayScheduledTimeWeeklyOver7()
    {
        $this->expectException(Exception::class);

        $weeklySchedule = new Weekly();
        $weeklySchedule->setDay(8);
    }

    /**
     * Tests getRescheduledTime on Weekly with unspecified hour and unspecified day
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
        $mock = $this->getWeeklyMock(self::$JANUARY_01_1971_09_10_00);
        $this->assertEquals(self::$JANUARY_04_1971_00_00_00, $mock->getRescheduledTime());
    }

    /**
     * Tests getRescheduledTime on Weekly with specified hour and unspecified day
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
        $mock = $this->getWeeklyMock(self::$JANUARY_01_1971_09_10_00);
        $mock->setHour(9);
        $this->assertEquals(self::$JANUARY_04_1971_09_00_00, $mock->getRescheduledTime());
    }

    /**
     * Returns data used in testGetRescheduledTimeWeeklyUnspecifiedHourSpecifiedDay test.
     */
    public function getSetDayParametersToTest()
    {
        return array(
            array(1, self::$JANUARY_11_1971_00_00_00),
            array(5, self::$JANUARY_08_1971_00_00_00),
            array('monday', self::$JANUARY_11_1971_00_00_00),
            array('Monday', self::$JANUARY_11_1971_00_00_00),
            array('FRIDAY', self::$JANUARY_08_1971_00_00_00),
            array('FrIdAy', self::$JANUARY_08_1971_00_00_00)
        );
    }

    public function test_setTimezone_ShouldConvertRescheduledTime()
    {
        $oneHourInSeconds = 3600;

        $mock    = $this->getWeeklyMock(self::$JANUARY_01_1971_09_10_00);
        $timeUTC = $mock->getRescheduledTime();
        $this->assertEquals(self::$JANUARY_04_1971_00_00_00, $timeUTC);

        $mock->setTimezone('Pacific/Auckland');
        $timeAuckland = $mock->getRescheduledTime();
        $this->assertEquals(-13 * $oneHourInSeconds, $timeAuckland - $timeUTC);

        $mock->setTimezone('America/Los_Angeles');
        $timeLosAngeles = $mock->getRescheduledTime();
        $this->assertEquals(8 * $oneHourInSeconds, $timeLosAngeles - $timeUTC);
    }

    /**
     * Tests getRescheduledTime on Weekly with unspecified hour and specified day
     *
     * Context :
     *  - getRescheduledTime called Monday January 4 1971 09:00:00 UTC
     *  - setHour is not called, defaulting to midnight
     *  - setDay is set to $dayToSet
     *
     * @dataProvider getSetDayParametersToTest
     */
    public function testGetRescheduledTimeWeeklyUnspecifiedHourSpecifiedDay($dayToSet, $expectedRescheduledTime)
    {
        $mock = $this->getWeeklyMock(self::$JANUARY_04_1971_09_00_00);
        $mock->setDay($dayToSet);

        $this->assertEquals($expectedRescheduledTime, $mock->getRescheduledTime());
    }

    /**
     * @param $currentTime
     * @return Weekly
     */
    private function getWeeklyMock($currentTime)
    {
        $mock = $this->createPartialMock('Piwik\Scheduler\Schedule\Weekly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($currentTime));
        return $mock;
    }
}

WeeklyTest::$JANUARY_01_1971_09_10_00 = mktime(9, 10, 00, 1, 1, 1971);
WeeklyTest::$JANUARY_04_1971_00_00_00 = mktime(0, 00, 00, 1, 4, 1971);
WeeklyTest::$JANUARY_04_1971_09_00_00 = mktime(9, 00, 00, 1, 4, 1971);
WeeklyTest::$JANUARY_05_1971_09_00_00 = mktime(9, 00, 00, 1, 5, 1971);
WeeklyTest::$JANUARY_11_1971_00_00_00 = mktime(0, 00, 00, 1, 11, 1971);
WeeklyTest::$JANUARY_15_1971_00_00_00 = mktime(0, 00, 00, 1, 15, 1971);
WeeklyTest::$JANUARY_08_1971_00_00_00 = mktime(0, 00, 00, 1, 8, 1971);
