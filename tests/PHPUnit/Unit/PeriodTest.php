<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
class PeriodTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
        $period = new Week(Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
        $period = new Month(Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
        $period = new Year(Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
    }

    public function testFactoryDay()
    {
        $period = Period\Factory::build('day', Date::today());
        $this->assertInstanceOf('\Piwik\Period\Day', $period);
    }

    public function testFactoryMonth()
    {
        $period = Period\Factory::build('month', Date::today());
        $this->assertInstanceOf('\Piwik\Period\Month', $period);
    }

    public function testFactoryWeek()
    {
        $period = Period\Factory::build('week', Date::today());
        $this->assertInstanceOf('\Piwik\Period\Week', $period);
    }

    public function testFactoryYear()
    {
        $period = Period\Factory::build('year', Date::today());
        $this->assertInstanceOf('\Piwik\Period\Year', $period);
    }

    public function testFactoryInvalid()
    {
        try {
            Period\Factory::build('inValid', Date::today());
        } catch (\Exception $e) {
            return;
        }
        $this->fail('Expected Exception not raised');
    }

    public function testValidate_ValidDates()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage General_ExceptionInvalidDateFormat
     * @dataProvider getInvalidDateFormats
     */
    public function testValidate_InvalidDates($invalidDateFormat)
    {
        Period::checkDateFormat($invalidDateFormat);
    }

    public function getInvalidDateFormats()
    {
        return array(
            array('last7testfoobar'),
            array('today,last7'),
            array('2013-01-01,last7'),
            array('today,2013-01-01'),
            array('1990-01-01'),
            array('1990-01-0111'),
            array('foobar'),
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
}