<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Period;

use Piwik\Config;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Day;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Period\Week;
use Piwik\Period\Year;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

class TestPeriod
{
    // empty
}

class TestPeriodFactory extends Period\Factory
{
    /**
     * @var Config
     */
    private $config;

    // use constructor to make sure period factories are injected
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function shouldHandle($strPeriod, $strDate)
    {
        return $strPeriod == 'customperiod';
    }

    public function make($strPeriod, $date, $timezone)
    {
        return new TestPeriod();
    }
}

class MockPluginManager extends \Piwik\Plugin\Manager
{
    public function findComponents($componentName, $expectedSubclass)
    {
        if ($componentName == 'PeriodFactory') {
            return [
                TestPeriodFactory::class,
            ];
        }

        return parent::findComponents($componentName, $expectedSubclass);
    }
}

/**
 * @group PeriodFactoryTest
 */
class FactoryTest extends UnitTestCase
{
    /**
     * @dataProvider getTestDataForMakePeriodFromQueryParams
     */
    public function testMakePeriodFromQueryParamsAppliesTimezoneProperly($now, $timezone, $period, $date, $expectedLabel, $expectedRange)
    {
        Date::$now = strtotime($now);

        $factory = Period\Factory::makePeriodFromQueryParams($timezone, $period, $date);
        $this->assertEquals($expectedLabel, $factory->getLabel());
        $this->assertEquals($expectedRange, $factory->getRangeString());
    }

    public function getTestDataForMakePeriodFromQueryParams()
    {
        return [
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'now', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'today', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 16:37:00', 'America/Chicago', 'day', 'today', 'day', '2020-12-24,2020-12-24'],
            ['2020-12-24 22:37:00', 'UTC+5', 'day', 'today', 'day', '2020-12-25,2020-12-25'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'yesterday', 'day', '2020-12-22,2020-12-22'],
            ['2020-12-24 03:37:00', 'UTC+5', 'day', 'yesterday', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 16:37:00', 'UTC+12', 'day', 'yesterday', 'day', '2020-12-24,2020-12-24'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'yesterdaySameTime', 'day', '2020-12-22,2020-12-22'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'last-week', 'day', '2020-12-16,2020-12-16'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'last-month', 'day', '2020-11-23,2020-11-23'],
            ['2020-12-24 03:37:00', 'UTC', 'week', 'last-month', 'week', '2020-11-23,2020-11-29'],
            ['2020-12-23 03:37:00', 'America/Chicago', 'week', 'last-month', 'week', '2020-11-16,2020-11-22'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'last-year', 'day', '2019-12-23,2019-12-23'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', '2020-12-23', 'day', '2020-12-23,2020-12-23'],

            // A relative range is measured back from a "today" of its own, which is the day
            // the timezone is on, not the one UTC is on.
            ['2020-12-24 16:37:00', 'UTC+12', 'range', 'previous7', 'range', '2020-12-18,2020-12-24'],
            ['2020-12-24 16:37:00', 'UTC+12', 'range', 'last7', 'range', '2020-12-19,2020-12-25'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'range', 'previous7', 'range', '2020-12-16,2020-12-22'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'range', 'last7', 'range', '2020-12-17,2020-12-23'],

            // A relative range end has to land on that same day.
            ['2020-12-24 16:37:00', 'UTC+12', 'range', '2020-12-01,today', 'range', '2020-12-01,2020-12-25'],
            ['2020-12-24 20:00:00', 'America/Chicago', 'range', '2020-12-01,today', 'range', '2020-12-01,2020-12-24'],

            // The patterns that admit these keywords are case-insensitive, so resolving them
            // has to be too, in a range end and on its own.
            ['2020-12-24 20:00:00', 'America/Chicago', 'range', '2020-12-01,Today', 'range', '2020-12-01,2020-12-24'],
            ['2020-12-24 16:37:00', 'UTC+12', 'range', '2020-12-01,YESTERDAY', 'range', '2020-12-01,2020-12-24'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'Today', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'Last-Week', 'day', '2020-12-16,2020-12-16'],

            // A relative endpoint is a keyword, not a shape, and a start resolves like an end.
            // NOW is 2020-12-23 in Chicago and 2020-12-24 in UTC, so UTC lands a day late.
            ['2020-12-24 03:37:00', 'America/Chicago', 'range', '2020-12-01,last-week', 'range', '2020-12-01,2020-12-16'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'range', '2020-12-01,last week', 'range', '2020-12-01,2020-12-16'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'range', 'last-week,today', 'range', '2020-12-16,2020-12-23'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'range', 'last week,today', 'range', '2020-12-16,2020-12-23'],

            // A padded or url-encoded spelling arrives from the request like any other. One
            // that is not recognised falls through to a UTC-resolved date, a day off here.
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'today ', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', ' TODAY', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'yesterday ', 'day', '2020-12-22,2020-12-22'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'last%20week', 'day', '2020-12-16,2020-12-16'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'LAST%20WEEK', 'day', '2020-12-16,2020-12-16'],
            ['2020-12-24 03:37:00', 'America/Chicago', 'day', 'today%20', 'day', '2020-12-23,2020-12-23'],
            ['2020-12-24 16:37:00', 'UTC+12', 'day', ' today ', 'day', '2020-12-25,2020-12-25'],
            ['2020-12-24 16:37:00', 'UTC+12', 'week', 'last%20month', 'week', '2020-11-23,2020-11-29'],
        ];
    }

    /**
     * @dataProvider getBuildTestData
     */
    public function testBuildCreatesCorrectPeriodInstances(
        $strPeriod,
        $date,
        $timezone,
        $expectedPeriodClass,
        $expectedRangeString
    ) {
        $period = Period\Factory::build($strPeriod, $date, $timezone);
        $this->assertInstanceOf($expectedPeriodClass, $period);
        $this->assertEquals($expectedRangeString, $period->getRangeString());
    }

    public function getBuildTestData()
    {
        return [
            ['day', '2015-01-01', 'UTC', Day::class, '2015-01-01,2015-01-01'],
            ['week', '2015-01-01', 'UTC', Week::class, '2014-12-29,2015-01-04'],
            ['month', '2015-01-01', 'UTC', Month::class, '2015-01-01,2015-01-31'],
            ['year', '2015-01-01', 'UTC', Year::class, '2015-01-01,2015-12-31'],

            ['range', '2015-01-01,2015-01-10', 'UTC', Range::class, '2015-01-01,2015-01-10'],
            ['range', '2015-01-01,2015-01-10', 'Antarctica/Casey', Range::class, '2015-01-01,2015-01-10'],
            ['range', '2015-01-01,2015-01-01', 'Antarctica/Casey', Day::class, '2015-01-01,2015-01-01'],

            // multiple periods
            ['day', '2015-01-01,2015-01-10', 'UTC', Range::class, '2015-01-01,2015-01-10'],
            ['week', '2015-01-01,2015-01-10', 'UTC', Range::class, '2014-12-29,2015-01-11'],
            ['month', '2015-01-01,2015-02-10', 'UTC', Range::class, '2015-01-01,2015-02-28'],
            ['year', '2015-01-01,2016-01-10', 'UTC', Range::class, '2015-01-01,2016-12-31'],
        ];
    }

    public function testMakePeriodFromQueryParams()
    {
        $factory = Period\Factory::makePeriodFromQueryParams('UTC', 'range', '2019-01-01,2019-01-01');
        $this->assertTrue($factory instanceof Day);
        $this->assertEquals('2019-01-01', $factory->toString());
    }

    public function testBuildCreatesCustomPeriodInstances()
    {
        Config::getInstance()->General['enabled_periods_API'] .= ',customperiod';

        $period = Period\Factory::build('customperiod', '2015-01-01');
        $this->assertInstanceOf(TestPeriod::class, $period);
    }

    public function testBuildThrowsIfPeriodIsUnrecognized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionInvalidPeriod');

        Period\Factory::build('garbageperiod', '2015-01-01');
    }

    public function testBuildThrowsIfPeriodIsNotEnabledForApi()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionInvalidPeriod');

        Config::getInstance()->General['enabled_periods_API'] = 'day';
        Period\Factory::build('week', '2015-01-01');
    }

    public function provideContainerConfig()
    {
        return [
            \Piwik\Plugin\Manager::class => \Piwik\DI::autowire(MockPluginManager::class),
        ];
    }
}
