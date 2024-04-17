<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\Date;
use Piwik\Period;
use Piwik\Plugins\CoreVisualizations\Visualizations\EvolutionPeriodSelector;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreVisualizations
 * @group EvolutionPeriodSelector
 * @group Plugins
 */
class EvolutionPeriodSelectorTest extends IntegrationTestCase
{
    /**
     * @var EvolutionPeriodSelector
     */
    private $selector;

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }

        $this->selector = new EvolutionPeriodSelector(new Config());
    }

    public function tearDown(): void
    {
        unset($_GET['period']);
        unset($_GET['idSite']);
        unset($_GET['date']);
        parent::tearDown();
    }

    /**
     * @dataProvider getNumDaysDifferenceProvider
     */
    public function test_getNumDaysDifference($expected, $date1, $date2)
    {
        $this->assertSame($expected, $this->selector->getNumDaysDifference(Date::factory($date1), Date::factory($date2)));
    }

    public function getNumDaysDifferenceProvider()
    {
        return [
            [0, '2022-02-20','2022-02-20'],
            [0, '2022-02-20 01:02:03','2022-02-20 01:02:05'],
            [1, '2022-02-20 01:02:03','2022-02-20 16:02:05'],
            [1, '2022-02-20','2022-02-21'],
            [2, '2022-02-22','2022-02-20'], // first date is later
            [2, '2022-02-20','2022-02-22'],
            [792, '2020-02-20','2022-04-22'],
        ];
    }

    public function test_getComparisonPeriodObjects_whenNoPeriodsGiven()
    {
        $this->assertEquals([], $this->selector->getComparisonPeriodObjects(false, false));
        $this->assertEquals([], $this->selector->getComparisonPeriodObjects([], []));
    }

    public function test_getComparisonPeriodObjects_whenOnlyOnePeriodGiven()
    {
        /** @var Period[] $periods */
        $periods = $this->selector->getComparisonPeriodObjects(['day'], ['2022-01-02']);
        $this->assertSame('day', $periods[0]->getLabel());
        $this->assertSame('2022-01-02,2022-01-02', $periods[0]->getRangeString());
        $this->assertCount(1, $periods);
    }

    public function test_getComparisonPeriodObjects_whenMultipleComparisonPeriodsGiven()
    {
        /** @var Period[] $periods */
        $periods = $this->selector->getComparisonPeriodObjects(['week', 'month'], ['2022-01-02', '2020-05-09']);


        $this->assertSame('week', $periods[0]->getLabel());
        $this->assertSame('2021-12-27,2022-01-02', $periods[0]->getRangeString());

        $this->assertSame('month', $periods[1]->getLabel());
        $this->assertSame('2020-05-01,2020-05-31', $periods[1]->getRangeString());

        $this->assertCount(2, $periods);
    }

    public function test_getHighestPeriodInCommon_noComparision()
    {
        $day = Period\Factory::build('day', 'today');
        $week = Period\Factory::build('week', 'today');
        $month = Period\Factory::build('month', 'today');
        $year = Period\Factory::build('year', 'today');
        $shortRange = Period\Factory::build('range', '2022-01-02,2022-02-03');
        $mediumRange = Period\Factory::build('range', '2022-01-02,2022-10-03');
        $largeRange = Period\Factory::build('range', '2020-10-02,2022-10-03');

        $this->assertHighestPeriodInCommon('day', $day, []);
        $this->assertHighestPeriodInCommon('day', $week, []);
        $this->assertHighestPeriodInCommon('day', $month, []);
        $this->assertHighestPeriodInCommon('week', $year, []);
        $this->assertHighestPeriodInCommon('day', $shortRange, []);
        $this->assertHighestPeriodInCommon('week', $mediumRange, []);
        $this->assertHighestPeriodInCommon('month', $largeRange, []);
    }

    public function test_getHighestPeriodInCommon_withComparision()
    {
        $day = Period\Factory::build('day', 'today');
        $week = Period\Factory::build('week', 'today');
        $month = Period\Factory::build('month', 'today');
        $year = Period\Factory::build('year', 'today');
        $shortRange = Period\Factory::build('range', '2022-01-02,2022-02-03');
        $mediumRange = Period\Factory::build('range', '2022-01-02,2022-10-03');
        $largeRange = Period\Factory::build('range', '2020-10-02,2022-10-03');

        $this->assertHighestPeriodInCommon('day', $day, [$largeRange]);
        $this->assertHighestPeriodInCommon('day', $month, [$largeRange]);
        $this->assertHighestPeriodInCommon('week', $year, [$largeRange]);
        $this->assertHighestPeriodInCommon('week', $year, [$mediumRange, $year]);
        $this->assertHighestPeriodInCommon('week', $year, [$mediumRange]);
        $this->assertHighestPeriodInCommon('month', $largeRange, [$largeRange]);
        $this->assertHighestPeriodInCommon('day', $largeRange, [$largeRange, $shortRange]);
        $this->assertHighestPeriodInCommon('day', $largeRange, [$largeRange, $week]);
    }

    public function test_setSparklineDatePeriods_whenNotComparing()
    {
        $day = Period\Factory::build('day', '2022-05-02');
        $week = Period\Factory::build('week', '2022-05-02');
        $month = Period\Factory::build('month', '2022-05-02');
        $year = Period\Factory::build('year', '2022-05-02');
        $shortRange = Period\Factory::build('range', '2022-01-02,2022-02-03');
        $mediumRange = Period\Factory::build('range', '2022-01-02,2022-10-03');
        $largeRange = Period\Factory::build('range', '2020-10-02,2022-10-03');

        $this->assertSetSparklineDatePeriods(['period' => 'day','date' => '2022-04-03,2022-05-02'], $day, []);
        $this->assertSetSparklineDatePeriods(['period' => 'week','date' => '2021-10-11,2022-05-08'], $week, []);
        $this->assertSetSparklineDatePeriods(['period' => 'month','date' => '2019-12-01,2022-05-31'], $month, []);
        $this->assertSetSparklineDatePeriods(['period' => 'year','date' => '2013-01-01,2022-12-31'], $year, []);
        $this->assertSetSparklineDatePeriods(['period' => 'day','date' => '2022-01-02,2022-02-03'], $shortRange, []);
        $this->assertSetSparklineDatePeriods(['period' => 'week','date' => '2022-01-02,2022-10-03'], $mediumRange, []);
        $this->assertSetSparklineDatePeriods(['period' => 'month','date' => '2020-10-02,2022-10-03'], $largeRange, []);
    }

    public function test_setSparklineDatePeriods_whenComparing()
    {
        $day = Period\Factory::build('day', '2022-05-02');
        $dayPrevious = Period\Factory::build('day', '2022-05-01');
        $week = Period\Factory::build('week', '2022-05-02');
        $weekPrevious = Period\Factory::build('week', '2022-04-25');
        $month = Period\Factory::build('month', '2022-05-02');
        $monthPrevious = Period\Factory::build('month', '2022-04-02');
        $year = Period\Factory::build('year', '2022-05-02');
        $yearPrevious = Period\Factory::build('year', '2021-05-02');

        $shortRange = Period\Factory::build('range', '2022-01-02,2022-02-03');
        $shortRangePrevious = Period\Factory::build('range', '2021-12-20,2022-01-02');

        $mediumRange = Period\Factory::build('range', '2022-01-02,2022-10-03');
        $mediumRangePrevious = Period\Factory::build('range', '2021-04-02,2022-01-01');

        $largeRange = Period\Factory::build('range', '2020-10-02,2022-10-03');
        $largeRangePrevious = Period\Factory::build('range', '2018-09-02,2020-09-29');

        // compare with previous
        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => '2022-05-02,2022-05-02',
            'comparePeriods' => ['day'], 'compareDates' => ['2022-05-01,2022-05-01']
        ], $day, [$dayPrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => $week->getRangeString(),
            'comparePeriods' => ['day'], 'compareDates' => [$weekPrevious->getRangeString()]
        ], $week, [$weekPrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => $month->getRangeString(),
            'comparePeriods' => ['day'], 'compareDates' => [$monthPrevious->getRangeString()]
        ], $month, [$monthPrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'week','date' => $year->getRangeString(),
            'comparePeriods' => ['week'], 'compareDates' => [$yearPrevious->getRangeString()]
        ], $year, [$yearPrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => $shortRange->getRangeString(),
            'comparePeriods' => ['day'], 'compareDates' => [$shortRangePrevious->getRangeString()]
        ], $shortRange, [$shortRangePrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'week','date' => $mediumRange->getRangeString(),
            'comparePeriods' => ['week'], 'compareDates' => [$mediumRangePrevious->getRangeString()]
        ], $mediumRange, [$mediumRangePrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'month','date' => $largeRange->getRangeString(),
            'comparePeriods' => ['month'], 'compareDates' => [$largeRangePrevious->getRangeString()]
        ], $largeRange, [$largeRangePrevious]);

        // MIXED LENGTH PERIODS GIVEN SHOULD USE LOWEST PERIOD IN COMMON

        // largeRange would usually use period months but because there is a year it should use lower period week
        $this->assertSetSparklineDatePeriods([
            'period' => 'week','date' => $year->getRangeString(),
            'comparePeriods' => ['week'], 'compareDates' => [$largeRangePrevious->getRangeString()]
        ], $year, [$largeRangePrevious]);

        // largeRange would usually use period months but because there is a year it should use lower period week
        $this->assertSetSparklineDatePeriods([
            'period' => 'week','date' => $largeRangePrevious->getRangeString(),
            'comparePeriods' => ['week'], 'compareDates' => [$year->getRangeString()]
        ], $largeRangePrevious, [$year]);

        // largeRange would usually use period months but because there is a day it should use lower period day
        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => $day->getRangeString(),
            'comparePeriods' => ['day'], 'compareDates' => [$largeRange->getRangeString()]
        ], $day, [$largeRange]);

        // largeRange would usually use period months but because there is a day it should use lower period day
        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => $largeRange->getRangeString(),
            'comparePeriods' => ['day'], 'compareDates' => [$day->getRangeString()]
        ], $largeRange, [$day]);

        // USING MULTIPLE COMPARISON PERIODS

        // largeRange would usually use period months but because the lowest comparison period is a day it should use lower period day
        $this->assertSetSparklineDatePeriods([
            'period' => 'day','date' => $year->getRangeString(),
            'comparePeriods' => ['day', 'day'], 'compareDates' => [$largeRangePrevious->getRangeString(), $weekPrevious->getRangeString()]
        ], $year, [$largeRangePrevious, $weekPrevious]);

        $this->assertSetSparklineDatePeriods([
            'period' => 'week','date' => $year->getRangeString(),
            'comparePeriods' => ['week', 'week'], 'compareDates' => [$largeRangePrevious->getRangeString(), $mediumRangePrevious->getRangeString()]
        ], $year, [$largeRangePrevious, $mediumRangePrevious]);
    }

    private function assertHighestPeriodInCommon($expected, $originalPeriod, $comparePeriods)
    {
        $period = $this->selector->getHighestPeriodInCommon($originalPeriod, $comparePeriods);
        $this->assertSame($expected, $period);
    }

    private function assertSetSparklineDatePeriods($expected, Period $originalPeriod, $comparisonPeriods)
    {
        $_GET['period'] = $originalPeriod->getLabel();
        if ($originalPeriod->getLabel() === 'range') {
            $_GET['date'] = $originalPeriod->getRangeString();
        } else {
            $_GET['date'] = $originalPeriod->getDateEnd()->toString();
        }
        $_GET['idSite'] = 1;
        $period = $this->selector->setDatePeriods([], $originalPeriod, $comparisonPeriods, !empty($comparisonPeriods));
        $this->assertEquals($expected, $period);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
