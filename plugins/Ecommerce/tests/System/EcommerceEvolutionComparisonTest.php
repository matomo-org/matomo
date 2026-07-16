<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\System;

use Piwik\API\Request;
use Piwik\Plugin\ReportsProvider;
use Piwik\Tests\Fixtures\TwoSitesEcommerceOrderWithItems;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Reproducer for DEV-20289: Ecommerce > Overview "Evolution over the period"
 * chart shows $0 Total Revenue for both the main and comparison series when
 * "Compare to previous period" is enabled.
 *
 * The chart issues a Goals.get request shaped by JqplotGraph\Evolution and
 * EvolutionPeriodSelector::setDatePeriods. When isComparing() is true, the
 * chart reads every series (including the originally selected period) from
 * each row's comparison subtable, so populating that subtable correctly is
 * what makes the chart display real revenue values.
 *
 * @group EcommerceEvolutionComparisonTest
 * @group Plugins
 */
class EcommerceEvolutionComparisonTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function testEvolutionComparisonReturnsRevenueForMainSeries()
    {
        $idSite      = self::$fixture->idSite;
        $mainDate    = '2011-04-05,2011-04-05';
        $compareDate = '2011-04-04,2011-04-04';

        $result = Request::processRequest('Goals.get', [
            'idSite'                     => $idSite,
            'period'                     => 'day',
            'date'                       => $mainDate,
            'idGoal'                     => 'ecommerceOrder',
            'columns'                    => 'revenue',
            'showAllGoalSpecificMetrics' => 1,
            'format_metrics'             => 0,
            'compareDates'               => [$compareDate],
            'comparePeriods'             => ['day'],
            'compareSegments'            => [''],
            'compare'                    => 1,
        ]);

        $tables = $result->getDataTables();
        $rows = reset($tables)->getFirstRow()->getComparisons()->getRows();

        $mainRevenue = $rows[0]->getColumn('revenue');
        $this->assertIsNumeric($mainRevenue, 'Main-series revenue must be numeric, not a formatted currency string');
        $this->assertGreaterThan(0, $mainRevenue, 'Main-series revenue must reflect the day\'s tracked orders');

        // The comparison row may legitimately be 0 (the fixture has no orders on 2011-04-04),
        // but it must still be numeric — the original bug formatted it into a currency string too.
        $this->assertIsNumeric($rows[1]->getColumn('revenue'), 'Comparison-series revenue must be numeric, not a formatted currency string');
    }

    public function testEvolutionComparisonReturnsConversionRateAsRawQuotient()
    {
        $idSite      = self::$fixture->idSite;
        $mainDate    = '2011-04-05,2011-04-05';
        $compareDate = '2011-04-04,2011-04-04';

        $result = Request::processRequest('Goals.get', [
            'idSite'                     => $idSite,
            'period'                     => 'day',
            'date'                       => $mainDate,
            'idGoal'                     => 'ecommerceOrder',
            'columns'                    => 'conversion_rate',
            'showAllGoalSpecificMetrics' => 1,
            'format_metrics'             => 0,
            'compareDates'               => [$compareDate],
            'comparePeriods'             => ['day'],
            'compareSegments'            => [''],
            'compare'                    => 1,
        ]);

        $tables = $result->getDataTables();
        $rows = reset($tables)->getFirstRow()->getComparisons()->getRows();

        $mainConversionRate = $rows[0]->getColumn('conversion_rate');
        $this->assertIsNumeric(
            $mainConversionRate,
            'Main-series conversion_rate must be a raw numeric quotient (e.g. 0.0984), not a formatted "x%" string — '
            . 'the evolution chart\'s Chart.php multiplies the raw quotient by 100 itself, so a pre-formatted string '
            . 'would render as 984%. Actual value: ' . var_export($mainConversionRate, true)
        );
        $this->assertGreaterThan(0, $mainConversionRate, 'Fixture tracked conversions on 2011-04-05');
        $this->assertLessThan(1, $mainConversionRate, 'A conversion-rate quotient is always in [0, 1]');

        $comparisonConversionRate = $rows[1]->getColumn('conversion_rate');
        $this->assertIsNumeric(
            $comparisonConversionRate,
            'Comparison-series conversion_rate must also be numeric, not a "0%" / "x%" string. Actual: '
            . var_export($comparisonConversionRate, true)
        );
    }

    public function testGoalsGetReportRegistersConversionRateAsProcessedMetric()
    {
        // The Ecommerce Overview sparklines call formatSparklineMetricValue, which only formats
        // conversion_rate if it's registered as a ProcessedMetric instance on the Goals.get report.
        // Pre-DEV-20289, the queued Goals formatter coincidentally pre-formatted the value so the
        // sparkline displayed correctly. With format_metrics=0 honoured, the sparkline now depends
        // on this registration being present — otherwise it renders the raw quotient (e.g. "0.1"
        // instead of "9.84%").
        $report = ReportsProvider::factory('Goals', 'get');
        $processedMetrics = $report->getProcessedMetricsById();

        $this->assertArrayHasKey(
            'conversion_rate',
            $processedMetrics,
            'Goals.get must register conversion_rate as a ProcessedMetric instance so sparkline + compare formatting works'
        );
    }
}

EcommerceEvolutionComparisonTest::$fixture = new TwoSitesEcommerceOrderWithItems();
