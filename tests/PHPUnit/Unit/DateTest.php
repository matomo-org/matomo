<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\SettingsServer;
use Piwik\Translate;

/**
 */
class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * create today object check that timestamp is correct (midnight)
     *
     * @group Core
     */
    public function testToday()
    {
        $date = Date::today();
        $this->assertEquals(strtotime(date("Y-m-d ") . " 00:00:00"), $date->getTimestamp());

        // test getDatetime()
        $this->assertEquals($date->getDatetime(), $date->getDateStartUTC());
        $date = $date->setTime('12:00:00');
        $this->assertEquals(date('Y-m-d') . ' 12:00:00', $date->getDatetime());
    }

    /**
     * create today object check that timestamp is correct (midnight)
     *
     * @group Core
     */
    public function testYesterday()
    {
        $date = Date::yesterday();
        $this->assertEquals(strtotime(date("Y-m-d", strtotime('-1day')) . " 00:00:00"), $date->getTimestamp());
    }

    /**
     * @group Core
     */
    public function testInvalidDateThrowsException()
    {
        try {
            Date::factory('0001-01-01');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    public function getTimezoneOffsets()
    {
        return array(
            array('UTC-2', -7200),
            array('UTC+1.5', 5400),
            array('UTC', 0),
            array('America/Belize', -21600),
            array('EST', -18000),
            array('Antarctica/Syowa', 10800),
        );
    }

    /**
     * @group Core
     * @group DateTest
     * @dataProvider getTimezoneOffsets
     */
    public function testGetUtcOffset($timezone, $expectedOffset)
    {
        $offset = Date::getUtcOffset($timezone);
        $this->assertEquals($expectedOffset, $offset);
    }

    /**
     * @group Core
     */
    public function testFactoryTimezone()
    {
        // now in UTC converted to UTC+10 means adding 10 hours
        $date = Date::factory('now', 'UTC+10');
        $dateExpected = Date::now()->addHour(10);
        $this->assertEquals($dateExpected->getDatetime(), $date->getDatetime());

        // Congo is in UTC+1 all year long (no DST)
        $dateExpected = Date::factory('now')->addHour(1);
        $date = Date::factory('now', 'Africa/Brazzaville');
        $this->assertEquals($dateExpected->getDatetime(), $date->getDatetime());

        // yesterday same time in Congo is the same as today in Congo - 24 hours
        $date = Date::factory('yesterdaySameTime', 'Africa/Brazzaville');
        $dateExpected = Date::factory('now', 'Africa/Brazzaville')->subHour(24);
        $this->assertEquals($dateExpected->getDatetime(), $date->getDatetime());

        if (SettingsServer::isTimezoneSupportEnabled()) {
            // convert to/from local time
            $now = time();
            $date = Date::factory($now, 'America/New_York');
            $time = $date->getTimestamp();
            $this->assertTrue($time < $now);

            $date = Date::factory($time)->setTimezone('America/New_York');
            $time = $date->getTimestamp();
            $this->assertEquals($now, $time);
        }
    }

    public function test_getHourInUTC()
    {
        $date = Date::factory('today', 'UTC');
        $hour = $date->getHourUTC();
        $this->assertSame('0', $hour); // hour is already in UTC

        $date = Date::factory('today', 'UTC+10');
        $hour = $date->getHourUTC();
        $this->assertSame('10', $hour);

        $date = Date::factory('today');
        $date = $date->setTime('14:00:00')->setTimezone('UTC+10'); // 14-10 = 4
        $hour = $date->getHourUTC();
        $this->assertSame('4', $hour);

        $date = Date::factory('today');
        $date = $date->setTime('14:00:00')->setTimezone('UTC-5'); // 14+5 = 19
        $hour = $date->getHourUTC();
        $this->assertSame('19', $hour);
    }

    /**
     * @group Core
     */
    public function testSetTimezoneDayInUTC()
    {
        $date = Date::factory('2010-01-01');

        $dayStart = '2010-01-01 00:00:00';
        $dayEnd = '2010-01-01 23:59:59';
        $this->assertEquals($dayStart, $date->getDateStartUTC());
        $this->assertEquals($dayEnd, $date->getDateEndUTC());

        // try with empty timezone
        $date = $date->setTimezone('');
        $this->assertEquals($dayStart, $date->getDateStartUTC());
        $this->assertEquals($dayEnd, $date->getDateEndUTC());

        $date = $date->setTimezone('UTC');
        $this->assertEquals($dayStart, $date->getDateStartUTC());
        $this->assertEquals($dayEnd, $date->getDateEndUTC());

        if (SettingsServer::isTimezoneSupportEnabled()) {
            $date = $date->setTimezone('Europe/Paris');
            $utcDayStart = '2009-12-31 23:00:00';
            $utcDayEnd = '2010-01-01 22:59:59';
            $this->assertEquals($utcDayStart, $date->getDateStartUTC());
            $this->assertEquals($utcDayEnd, $date->getDateEndUTC());
        }

        $date = $date->setTimezone('UTC+1');
        $utcDayStart = '2009-12-31 23:00:00';
        $utcDayEnd = '2010-01-01 22:59:59';
        $this->assertEquals($utcDayStart, $date->getDateStartUTC());
        $this->assertEquals($utcDayEnd, $date->getDateEndUTC());

        $date = $date->setTimezone('UTC-1');
        $utcDayStart = '2010-01-01 01:00:00';
        $utcDayEnd = '2010-01-02 00:59:59';
        $this->assertEquals($utcDayStart, $date->getDateStartUTC());
        $this->assertEquals($utcDayEnd, $date->getDateEndUTC());

        if (SettingsServer::isTimezoneSupportEnabled()) {
            $date = $date->setTimezone('America/Vancouver');
            $utcDayStart = '2010-01-01 08:00:00';
            $utcDayEnd = '2010-01-02 07:59:59';
            $this->assertEquals($utcDayStart, $date->getDateStartUTC());
            $this->assertEquals($utcDayEnd, $date->getDateEndUTC());
        }
    }

    /**
     * @group Core
     */
    public function testModifyDateWithTimezone()
    {
        $date = Date::factory('2010-01-01');
        $date = $date->setTimezone('UTC-1');

        $timestamp = $date->getTimestamp();
        $date = $date->addHour(0)->addHour(0)->addHour(0);
        $this->assertEquals($timestamp, $date->getTimestamp());

        if (SettingsServer::isTimezoneSupportEnabled()) {
            $date = Date::factory('2010-01-01')->setTimezone('Europe/Paris');
            $dateExpected = clone $date;
            $date = $date->addHour(2);
            $dateExpected = $dateExpected->addHour(1.1)->addHour(0.9)->addHour(1)->subHour(1);
            $this->assertEquals($dateExpected->getTimestamp(), $date->getTimestamp());
        }
    }

    /**
     * @group Core
     */
    public function testGetDateStartUTCEndDuringDstTimezone()
    {
        if (SettingsServer::isTimezoneSupportEnabled()) {
            $date = Date::factory('2010-03-28');

            $date = $date->setTimezone('Europe/Paris');
            $utcDayStart = '2010-03-27 23:00:00';
            $utcDayEnd = '2010-03-28 21:59:59';

            $this->assertEquals($utcDayStart, $date->getDateStartUTC());
            $this->assertEquals($utcDayEnd, $date->getDateEndUTC());
        }
    }

    /**
     * @group Core
     */
    public function testAddHour()
    {
        // add partial hours less than 1
        $dayStart = '2010-03-28 00:00:00';
        $dayExpected = '2010-03-28 00:18:00';
        $date = Date::factory($dayStart)->addHour(0.3);
        $this->assertEquals($dayExpected, $date->getDatetime());
        $date = $date->subHour(0.3);
        $this->assertEquals($dayStart, $date->getDatetime());

        // add partial hours
        $dayExpected = '2010-03-28 05:45:00';
        $date = Date::factory($dayStart)->addHour(5.75);
        $this->assertEquals($dayExpected, $date->getDatetime());

        // remove partial hours
        $dayExpected = '2010-03-27 18:15:00';
        $date = Date::factory($dayStart)->subHour(5.75);
        $this->assertEquals($dayExpected, $date->getDatetime());
    }

    /**
     * @group Core
     */
    public function testAddHourLongHours()
    {
        $dateTime = '2010-01-03 11:22:33';
        $expectedTime = '2010-01-05 11:28:33';
        $this->assertEquals($expectedTime, Date::factory($dateTime)->addHour(48.1)->getDatetime());
        $this->assertEquals($dateTime, Date::factory($dateTime)->addHour(48.1)->subHour(48.1)->getDatetime());
    }

    /**
     * @group Core
     */
    public function testAddPeriod()
    {
        $date = Date::factory('2010-01-01');
        $dateExpected = Date::factory('2010-01-06');
        $date = $date->addPeriod(5, 'day');
        $this->assertEquals($dateExpected->getTimestamp(), $date->getTimestamp());

        $date = Date::factory('2010-03-01');
        $dateExpected = Date::factory('2010-04-05');
        $date = $date->addPeriod(5, 'week');
        $this->assertEquals($dateExpected->getTimestamp(), $date->getTimestamp());
    }

    /**
     * @group Core
     */
    public function testSubPeriod()
    {
        $date = Date::factory('2010-03-01');
        $dateExpected = Date::factory('2010-02-15');
        $date = $date->subPeriod(2, 'week');
        $this->assertEquals($dateExpected->getTimestamp(), $date->getTimestamp());

        $date = Date::factory('2010-12-15');
        $dateExpected = Date::factory('2005-12-15');
        $date = $date->subPeriod(5, 'year');
        $this->assertEquals($dateExpected->getTimestamp(), $date->getTimestamp());
    }

    /**
     * @group Core
     */
    public function testSubSeconds()
    {
        $date = Date::factory('2010-03-01 00:01:25');
        $dateExpected = Date::factory('2010-03-01 00:00:54');

        $date = $date->subSeconds(31);
        $this->assertSame($dateExpected->getTimestamp(), $date->getTimestamp());

        $date = Date::factory('2010-03-01 00:01:25');
        $dateExpected = Date::factory('2010-03-01 00:01:36');
        $date = $date->subSeconds(-11);
        $this->assertSame($dateExpected->getTimestamp(), $date->getTimestamp());
    }

    /**
     * @group Core
     */
    public function testAddPeriodMonthRespectsMaxDaysInMonth()
    {
        $date = Date::factory('2014-07-31');
        $dateExpected = Date::factory('2014-06-30');
        $dateActual = $date->subPeriod(1, 'month');
        $this->assertEquals($dateExpected->toString(), $dateActual->toString());

        // test leap year
        $date = Date::factory('2000-03-31');
        $dateExpected = Date::factory('2000-02-29');
        $dateActual = $date->subPeriod(1, 'month');
        $this->assertEquals($dateExpected->toString(), $dateActual->toString());

        $date = Date::factory('2000-01-31');
        $dateExpected = Date::factory('2000-02-29');
        $dateActual = $date->addPeriod(1, 'month');
        $this->assertEquals($dateExpected->toString(), $dateActual->toString());
    }

    /**
     * @group Core
     */
    public function testIsLeapYear()
    {
        $date = Date::factory('2011-03-01');
        $this->assertFalse($date->isLeapYear());
        $date = Date::factory('2011-01-01');
        $this->assertFalse($date->isLeapYear());
        $date = Date::factory('2011-01-31');
        $this->assertFalse($date->isLeapYear());

        $date = Date::factory('2012-01-01');
        $this->assertTrue($date->isLeapYear());
        $date = Date::factory('2012-12-31');
        $this->assertTrue($date->isLeapYear());

        $date = Date::factory('2013-01-01');
        $this->assertFalse($date->isLeapYear());
        $date = Date::factory('2013-12-31');
        $this->assertFalse($date->isLeapYear());

        if (PHP_INT_SIZE > 4) { // dates after 19/01/2038 03:14:07 fail on 32-bit arch
            $date = Date::factory('2052-01-01');
            $this->assertTrue($date->isLeapYear());
        }
    }


    public function getLocalizedLongStrings()
    {
        return array(
            array('en', false, '2000-01-01 16:05:52', '16:05:52'),
            array('de', false, '2000-01-01 16:05:52', '16:05:52'),
            array('en', true, '2000-01-01 16:05:52', '4:05:52 PM'),
            array('de', true, '2000-01-01 04:05:52', '4:05:52 vorm.'),
            array('zh-tw', true, '2000-01-01 04:05:52', '上午4:05:52'),
            array('lt', true, '2000-01-01 16:05:52', '04:05:52 popiet'),
            array('ar', true, '2000-01-01 04:05:52', '4:05:52 ص'),
        );
    }

    /**
     * @group Core
     * @dataProvider getLocalizedLongStrings
     */
    public function testGetLocalizedTimeFormats($language, $use12HourClock, $time, $shouldBe)
    {
        Translate::loadAllTranslations();
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);
        StaticContainer::get('Piwik\Intl\Data\Provider\DateTimeFormatProvider')->forceTimeFormat($use12HourClock);

        $date = Date::factory($time);

        $this->assertEquals($shouldBe, $date->getLocalized(Date::TIME_FORMAT));
        Translate::reset();
    }

    /**
     * @dataProvider getTestDataForGetTimezoneOffset
     */
    public function test_getTimezoneOffset_ReturnsCorrectOffsetInHours($timezone, $expectedOffset)
    {
        $offset = Date::getTimezoneOffset($timezone);
        $this->assertEquals($expectedOffset, $offset);
    }

    public function getTestDataForGetTimezoneOffset()
    {
        return [
            ['UTC', 0],
            ['America/Los_Angeles', -8],
            ['America/New_York', -5],
            ['UTC+11', 11],
            ['Europe/Rome', 1],
        ];
    }
}
