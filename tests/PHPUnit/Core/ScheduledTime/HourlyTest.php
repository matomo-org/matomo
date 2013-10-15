<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ScheduledTime\Hourly;

class ScheduledTime_HourlyTest extends PHPUnit_Framework_TestCase
{
    private static $_JANUARY_01_1971_09_00_00;
    private static $_JANUARY_01_1971_09_10_00;
    private static $_JANUARY_01_1971_10_00_00;

    public static function setUpBeforeClass()
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
        try {
            $hourlySchedule = new Hourly();
            $hourlySchedule->setHour(0);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * Tests forbidden call to setDay on Hourly
     * @group Core
     */
    public function testSetDayScheduledTimeHourly()
    {
        try {
            $hourlySchedule = new Hourly();
            $hourlySchedule->setDay(1);
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
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
        $mock = $this->getMock('\Piwik\ScheduledTime\Hourly', array('getTime'));
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
        $mock = $this->getMock('\Piwik\ScheduledTime\Hourly', array('getTime'));
        $mock->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue(self::$_JANUARY_01_1971_09_10_00));
        $this->assertEquals(self::$_JANUARY_01_1971_10_00_00, $mock->getRescheduledTime());
    }
}
