<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Date;
use Piwik\Period\Day;
use Piwik\Period;
use Piwik\Period\Month;
use Piwik\Period\Week;
use Piwik\Period\Year;

/**
 * @group Core
 */
class PeriodTest extends \PHPUnit\Framework\TestCase
{
    public function testGetId()
    {
        $period = new Day(Date::today());
        $this->assertNotEquals(0, $period->getId());
        $period = new Week(Date::today());
        $this->assertNotEquals(0, $period->getId());
        $period = new Month(Date::today());
        $this->assertNotEquals(0, $period->getId());
        $period = new Year(Date::today());
        $this->assertNotEquals(0, $period->getId());
    }

    public function testGetLabel()
    {
        $period = new Day(Date::today());
        $label = $period->getLabel();
        self::assertIsString($label);
        $this->assertNotEmpty($label);
        $period = new Week(Date::today());
        $label = $period->getLabel();
        self::assertIsString($label);
        $this->assertNotEmpty($label);
        $period = new Month(Date::today());
        $label = $period->getLabel();
        self::assertIsString($label);
        $this->assertNotEmpty($label);
        $period = new Year(Date::today());
        $label = $period->getLabel();
        self::assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function testValidate_ValidDates()
    {
        self::expectNotToPerformAssertions();

        Period::checkDateFormat('today');
        Period::checkDateFormat('yesterday');
        Period::checkDateFormat('yesterdaySameTime');
        Period::checkDateFormat('now');
        Period::checkDateFormat('2013-01-01,2013-02-02');
        Period::checkDateFormat('2013-01-01,today');
        Period::checkDateFormat('last7');
        Period::checkDateFormat('previous30');
        Period::checkDateFormat('+1 day');
        Period::checkDateFormat('next Thursday');
    }

    /**
     * @dataProvider getInvalidDateFormats
     */
    public function testValidate_InvalidDates($invalidDateFormat)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Date format must be: YYYY-MM-DD, or \'today\' or \'yesterday\' or any keyword supported by the strtotime function (see http://php.net/strtotime for more information):');

        Period::checkDateFormat($invalidDateFormat);
    }

    public function getInvalidDateFormats()
    {
        return array(
            array('last7testfoobar'),
            array('today,last7'),
            array('2013-01-01,last7'),
            array('today,2013-01-01'),
            array('1990-01-0111'),
            array('foobar'),
        );
    }

    /**
     * @dataProvider getTestDataForToString
     */
    public function test_toString_CreatesCommaSeparatedStringList($periodType, $date, $format, $expected)
    {
        $period = Period\Factory::build($periodType, $date);
        $actual = $period->toString($format);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForToString()
    {
        return [
            ['day', '2012-02-03', 'Y-m-d', '2012-02-03'],
            ['day', '2012-02-03', 'Y_m', '2012_02'],

            ['week', '2012-02-04', 'Y-m-d', [
                '2012-01-30',
                '2012-01-31',
                '2012-02-01',
                '2012-02-02',
                '2012-02-03',
                '2012-02-04',
                '2012-02-05',
            ]],
            ['month', '2012-02-04', 'Y-m-d', [
                '2012-02-01',
                '2012-02-02',
                '2012-02-03',
                '2012-02-04',
                '2012-02-05',
                '2012-02-06,2012-02-07,2012-02-08,2012-02-09,2012-02-10,2012-02-11,2012-02-12',
                '2012-02-13,2012-02-14,2012-02-15,2012-02-16,2012-02-17,2012-02-18,2012-02-19',
                '2012-02-20,2012-02-21,2012-02-22,2012-02-23,2012-02-24,2012-02-25,2012-02-26',
                '2012-02-27',
                '2012-02-28',
                '2012-02-29',
            ]],
            ['month', '2012-02-04', 'Y-m', [
                '2012-02',
                '2012-02',
                '2012-02',
                '2012-02',
                '2012-02',
                '2012-02,2012-02,2012-02,2012-02,2012-02,2012-02,2012-02',
                '2012-02,2012-02,2012-02,2012-02,2012-02,2012-02,2012-02',
                '2012-02,2012-02,2012-02,2012-02,2012-02,2012-02,2012-02',
                '2012-02',
                '2012-02',
                '2012-02',
            ]],
            ['range', '2012-03-05,2012-03-12', 'Y-m-d', [
                '2012-03-05,2012-03-06,2012-03-07,2012-03-08,2012-03-09,2012-03-10,2012-03-11',
                '2012-03-12',
            ]],
        ];
    }

    /**
     * @dataProvider getInvalidDatesBeforeFirstWebsite
     */
    public function testValidate_InvalidDatesBeforeFirstWebsite($invalidDatesBeforeFirstWebsite)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is a date before first website was online. Try date that\'s after');

        Period::checkDateFormat($invalidDatesBeforeFirstWebsite);
    }

    public function getInvalidDatesBeforeFirstWebsite()
    {
        return array(
            array('1990-01-01'),
            array(3434),
        );
    }

    /**
     * @dataProvider getTestDataForGetAllOverlappingChildPeriods
     */
    public function test_getAllOverlappingChildPeriods_ReturnsTheCorrectChildPeriods($periodType, $dateRange, $expectedChildPeriodRanges)
    {
        $period = Period\Factory::build($periodType, $dateRange);

        $overlappingPeriods = $period->getAllOverlappingChildPeriods();
        $overlappingPeriods = $this->getPeriodInfoForAssert($overlappingPeriods);

        $this->assertEquals($expectedChildPeriodRanges, $overlappingPeriods);
    }

    public function getTestDataForGetAllOverlappingChildPeriods()
    {
        return array(
            array(
                'month',
                '2015-09-10',
                array(
                    array('week', '2015-08-31,2015-09-06'),
                    array('week', '2015-09-07,2015-09-13'),
                    array('week', '2015-09-14,2015-09-20'),
                    array('week', '2015-09-21,2015-09-27'),
                    array('week', '2015-09-28,2015-10-04'),
                    array('day', '2015-09-01,2015-09-01'),
                    array('day', '2015-09-02,2015-09-02'),
                    array('day', '2015-09-03,2015-09-03'),
                    array('day', '2015-09-04,2015-09-04'),
                    array('day', '2015-09-05,2015-09-05'),
                    array('day', '2015-09-06,2015-09-06'),
                    array('day', '2015-09-07,2015-09-07'),
                    array('day', '2015-09-08,2015-09-08'),
                    array('day', '2015-09-09,2015-09-09'),
                    array('day', '2015-09-10,2015-09-10'),
                    array('day', '2015-09-11,2015-09-11'),
                    array('day', '2015-09-12,2015-09-12'),
                    array('day', '2015-09-13,2015-09-13'),
                    array('day', '2015-09-14,2015-09-14'),
                    array('day', '2015-09-15,2015-09-15'),
                    array('day', '2015-09-16,2015-09-16'),
                    array('day', '2015-09-17,2015-09-17'),
                    array('day', '2015-09-18,2015-09-18'),
                    array('day', '2015-09-19,2015-09-19'),
                    array('day', '2015-09-20,2015-09-20'),
                    array('day', '2015-09-21,2015-09-21'),
                    array('day', '2015-09-22,2015-09-22'),
                    array('day', '2015-09-23,2015-09-23'),
                    array('day', '2015-09-24,2015-09-24'),
                    array('day', '2015-09-25,2015-09-25'),
                    array('day', '2015-09-26,2015-09-26'),
                    array('day', '2015-09-27,2015-09-27'),
                    array('day', '2015-09-28,2015-09-28'),
                    array('day', '2015-09-29,2015-09-29'),
                    array('day', '2015-09-30,2015-09-30'),
                ),
            ),

            array(
                'week',
                '2015-09-03',
                array(
                    array('day', '2015-08-31,2015-08-31'),
                    array('day', '2015-09-01,2015-09-01'),
                    array('day', '2015-09-02,2015-09-02'),
                    array('day', '2015-09-03,2015-09-03'),
                    array('day', '2015-09-04,2015-09-04'),
                    array('day', '2015-09-05,2015-09-05'),
                    array('day', '2015-09-06,2015-09-06'),
                ),
            ),

            array(
                'day',
                '2015-09-05',
                array(),
            ),
        );
    }

    private function getPeriodInfoForAssert($periods)
    {
        return array_map(function (Period $period) {
            return array($period->getLabel(), $period->getRangeString());
        }, $periods);
    }

    /**
     * @dataProvider getTestDataForIsDateInPeriod
     */
    public function test_isDateInPeriod($date, $period, $periodDate, $expected)
    {
        $date = Date::factory($date);
        $period = Period\Factory::build($period, $periodDate);

        $actual = $period->isDateInPeriod($date);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForIsDateInPeriod()
    {
        return [
            ['2014-02-03 00:00:00', 'day', '2014-02-03 03:04:05', true],
            ['2014-02-03 00:00:00', 'week', '2014-02-03 03:04:05', true],
            ['2014-02-03 00:00:00', 'month', '2014-02-03 03:04:05', true],
            ['2014-02-02 23:59:59', 'day', '2014-02-03 03:04:05', false],
            ['2014-01-31 23:59:59', 'month', '2014-02-03 03:04:05', false],
            ['2014-03-01 00:00:00', 'month', '2014-02-03 03:04:05', false],
            ['2014-03-31 23:59:59', 'month', '2014-03-03 03:04:05', true],
        ];
    }

    /**
     * @dataProvider getTestDataForIsPeriodIntersectingWith
     */
    public function test_isPeriodIntersectingWith($date1, $period1, $date2, $period2, $expected)
    {
        $period1Obj = Period\Factory::build($period1, $date1);
        $period2Obj = Period\Factory::build($period2, $date2);
        $actual = $period1Obj->isPeriodIntersectingWith($period2Obj);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForIsPeriodIntersectingWith()
    {
        return [
            ['2015-03-04', 'day', '2015-03-04', 'day', true],
            ['2015-03-04', 'day', '2015-03-04', 'week', true],
            ['2015-03-04', 'day', '2015-03-12', 'week', false],
            ['2015-03-04', 'week', '2015-03-08', 'day', true],
            ['2015-03-04', 'week', '2015-03-09', 'day', false],
            ['2015-03-04', 'month', '2015-03-09', 'day', true],
            ['2015-03-04', 'month', '2015-03-01', 'week', true],
            ['2015-03-04', 'month', '2015-02-28', 'week', true],
            ['2015-03-01', 'month', '2015-02-28', 'month', false],
            ['2015-03-01', 'week', '2015-02-28', 'week', true],
            ['2015-03-04', 'year', '2015-03-01', 'day', true],
            ['2015-03-04', 'year', '2016-03-01', 'week', false],
        ];
    }

    /**
     * @dataProvider getTestDataForGetBoundsInTimezone
     */
    public function test_getBoundsInTimezone($period, $date, $timezone, $expectedDate1, $expectedDate2)
    {
        $periodObj = Period\Factory::build($period, $date);

        list($date1, $date2) = $periodObj->getBoundsInTimezone($timezone);

        $this->assertEquals($expectedDate1, $date1->getDatetime());
        $this->assertEquals($expectedDate2, $date2->getDatetime());
    }

    public function getTestDataForGetBoundsInTimezone()
    {
        return [
            ['day', '2018-03-04', 'America/Los_Angeles', '2018-03-04 08:00:00', '2018-03-05 07:59:59'],
            ['day', '2018-03-04', 'UTC+8', '2018-03-03 16:00:00', '2018-03-04 15:59:59'],
            ['day', '2018-03-04', 'UTC-8', '2018-03-04 08:00:00', '2018-03-05 07:59:59'],
            ['week', '2018-03-04', 'America/Los_Angeles', '2018-02-26 08:00:00', '2018-03-05 07:59:59'],
            ['week', '2018-03-04', 'UTC-4', '2018-02-26 04:00:00', '2018-03-05 03:59:59'],
            ['week', '2018-03-04', 'UTC', '2018-02-26 00:00:00', '2018-03-04 23:59:59'],
        ];
    }
}
