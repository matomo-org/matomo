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
}