<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Scheduler\Schedule;

use Exception;
use Piwik\Scheduler\Schedule\Hourly;

/**
 * @group Scheduler
 */
class HourlyTest extends \PHPUnit\Framework\TestCase
{
    private static $_JANUARY_01_1971_09_00_00;
    private static $_JANUARY_01_1971_09_10_00;
    private static $_JANUARY_01_1971_10_00_00;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$_JANUARY_01_1971_09_00_00 = mktime(9, 00, 00, 1, 1, 1971);
        self::$_JANUARY_01_1971_09_10_00 = mktime(9, 10, 00, 1, 1, 1971);
        self::$_JANUARY_01_1971_10_00_00 = mktime(10, 00, 00, 1, 1, 1971);
    }

    /**
     * Tests forbidden call to setHour on Hourly
     * @group Core
     */
    public function testSetHourScheduledTimeHourly()
    {
        $this->expectException(Exception::class);

        $hourlySchedule = new Hourly();
        $hourlySchedule->setHour(0);
    }

    /**
     * Tests forbidden call to setDay on Hourly
     * @group Core
     */
    public function testSetDayScheduledTimeHourly()
    {
        $this->expectException(Exception::class);

        $hourlySchedule = new Hourly();
        $hourlySchedule->setDay(1);
    }

    /**
     * Tests getRescheduledTime on Hourly
     * @group Core
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
        $mock = $this->createPartialMock('Piwik\Scheduler\Schedule\Hourly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_01_1971_09_00_00));
        $this->assertEquals(self::$_JANUARY_01_1971_10_00_00, $mock->getRescheduledTime());

        /*
         * Test 2
         *
         * Context :
         *  - getRescheduledTime called Friday January 1 1971 09:10:00 GMT
         *
         * Expected :
         *  getRescheduledTime returns Friday January 1 1971 10:00:00 GMT
         */
        $mock = $this->createPartialMock('Piwik\Scheduler\Schedule\Hourly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_01_1971_09_10_00));
        $this->assertEquals(self::$_JANUARY_01_1971_10_00_00, $mock->getRescheduledTime());
    }
}
