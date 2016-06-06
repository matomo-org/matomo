<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Scheduler\Schedule;

use Exception;
use Piwik\Scheduler\Schedule\Daily;
use Piwik\Scheduler\Schedule\Schedule;

/**
 * @group Scheduler
 */
class DailyTest extends \PHPUnit_Framework_TestCase
{
    private static $_JANUARY_01_1971_09_00_00;
    private static $_JANUARY_01_1971_09_10_00;
    private static $_JANUARY_01_1971_12_10_00;
    private static $_JANUARY_02_1971_00_00_00;
    private static $_JANUARY_02_1971_09_00_00;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_JANUARY_01_1971_09_00_00 = mktime(9, 00, 00, 1, 1, 1971);
        self::$_JANUARY_01_1971_09_10_00 = mktime(9, 10, 00, 1, 1, 1971);
        self::$_JANUARY_01_1971_12_10_00 = mktime(12, 10, 00, 1, 1, 1971);
        self::$_JANUARY_02_1971_00_00_00 = mktime(0, 00, 00, 1, 2, 1971);
        self::$_JANUARY_02_1971_09_00_00 = mktime(9, 00, 00, 1, 2, 1971);
    }

    /**
     * Tests invalid call to setHour on Daily
     */
    public function testSetHourScheduledTimeDailyNegative()
    {
        try {
            $dailySchedule = Schedule::factory('daily');
            $dailySchedule->setHour(-1);

        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests invalid call to setHour on Daily
     */
    public function testSetHourScheduledTimeDailyOver24()
    {
        try {
            $dailySchedule = Schedule::factory('daily');
            $dailySchedule->setHour(25);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests forbidden call to setDay on Daily
     */
    public function testSetDayScheduledTimeDaily()
    {
        try {
            $dailySchedule = Schedule::factory('daily');
            $dailySchedule->setDay(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests getRescheduledTime on Daily with unspecified hour
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
        $mock = $this->getDailyMock(self::$_JANUARY_01_1971_09_10_00);
        $this->assertEquals(self::$_JANUARY_02_1971_00_00_00, $mock->getRescheduledTime());
    }

    public function test_setTimezone_ShouldConvertRescheduledTime()
    {
        $oneHourInSeconds = 3600;

        $mock    = $this->getDailyMock(self::$_JANUARY_01_1971_09_10_00);
        $timeUTC = $mock->getRescheduledTime();
        $this->assertEquals(self::$_JANUARY_02_1971_00_00_00, $timeUTC);

        $mock->setTimezone('Pacific/Auckland');
        $timeAuckland = $mock->getRescheduledTime();
        $this->assertEquals(-13 * $oneHourInSeconds, $timeAuckland - $timeUTC);

        $mock->setTimezone('America/Los_Angeles');
        $timeLosAngeles = $mock->getRescheduledTime();
        $this->assertEquals(8 * $oneHourInSeconds, $timeLosAngeles - $timeUTC);
    }

    /**
     * Tests getRescheduledTime on Daily with specified hour
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
        $mock = $this->getDailyMock(self::$_JANUARY_01_1971_09_00_00);
        $mock->setHour(9);
        $this->assertEquals(self::$_JANUARY_02_1971_09_00_00, $mock->getRescheduledTime());

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

        $mock = $this->getDailyMock(self::$_JANUARY_01_1971_12_10_00);
        $mock->setHour(9);
        $this->assertEquals(self::$_JANUARY_02_1971_09_00_00, $mock->getRescheduledTime());

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
        $mock = $this->getDailyMock(self::$_JANUARY_01_1971_12_10_00);
        $mock->setHour(0);
        $this->assertEquals(self::$_JANUARY_02_1971_00_00_00, $mock->getRescheduledTime());
    }

    /**
     * @param  $currentTime
     * @return Daily
     */
    private function getDailyMock($currentTime)
    {
        $mock = $this->getMock('Piwik\Scheduler\Schedule\Daily', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($currentTime));
        return $mock;
    }
}
