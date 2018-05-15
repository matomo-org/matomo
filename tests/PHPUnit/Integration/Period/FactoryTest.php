<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Period;

use Piwik\Config;
use Piwik\Period;
use Piwik\Period\Day;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Period\Week;
use Piwik\Period\Year;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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

class FactoryTest extends IntegrationTestCase
{
    /**
     * @dataProvider getBuildTestData
     */
    public function test_build_CreatesCorrectPeriodInstances($strPeriod, $date, $timezone, $expectedPeriodClass,
                                                            $expectedRangeString)
    {
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

            // multiple periods
            ['day', '2015-01-01,2015-01-10', 'UTC', Range::class, '2015-01-01,2015-01-10'],
            ['week', '2015-01-01,2015-01-10', 'UTC', Range::class, '2014-12-29,2015-01-11'],
            ['month', '2015-01-01,2015-02-10', 'UTC', Range::class, '2015-01-01,2015-02-28'],
            ['year', '2015-01-01,2016-01-10', 'UTC', Range::class, '2015-01-01,2016-12-31'],
        ];
    }

    public function test_build_CreatesCustomPeriodInstances()
    {
        Config::getInstance()->General['enabled_periods_API'] .= ',customperiod';

        $period = Period\Factory::build('customperiod', '2015-01-01');
        $this->assertInstanceOf(TestPeriod::class, $period);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage General_ExceptionInvalidPeriod
     */
    public function test_build_ThrowsIfPeriodIsUnrecognized()
    {
        Period\Factory::build('garbageperiod', '2015-01-01');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage General_ExceptionInvalidPeriod
     */
    public function test_build_ThrowsIfPeriodIsNotEnabledForApi()
    {
        Config::getInstance()->General['enabled_periods_API'] = 'day';
        Period\Factory::build('week', '2015-01-01');
    }

    public function provideContainerConfig()
    {
        return [
            \Piwik\Plugin\Manager::class => \DI\object(MockPluginManager::class),
        ];
    }
}
