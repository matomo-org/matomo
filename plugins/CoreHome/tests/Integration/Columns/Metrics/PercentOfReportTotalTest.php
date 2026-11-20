<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Columns\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\Report;
use Piwik\Plugins\CoreHome\Columns\Metrics\PercentOfReportTotal;
use Piwik\Plugins\CoreHome\Columns\Metrics\VisitsPercent;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group PercentOfReportTotal
 */
class PercentOfReportTotalTest extends IntegrationTestCase
{
    public function testGetNameUsesMetricNameAndSuffix()
    {
        $metric = new PercentOfReportTotal('nb_visits', 'Visits', 100);

        $this->assertEquals('nb_visits_percent_of_total', $metric->getName());
    }

    public function testComputeUsesTheMetricColumnByName()
    {
        $metric = new PercentOfReportTotal('nb_visits', 'Visits', 200);

        $row = new Row([Row::COLUMNS => ['label' => 'row1', 'nb_visits' => 25]]);

        $this->assertEquals(0.125, $metric->compute($row));
    }

    public function testComputeUsesTheMetricColumnByIdWhenColumnsAreNotYetReplaced()
    {
        $metric = new PercentOfReportTotal('nb_visits', 'Visits', 200);

        $row = new Row([Row::COLUMNS => ['label' => 'row1', Metrics::INDEX_NB_VISITS => 50]]);

        $this->assertEquals(0.25, $metric->compute($row));
    }

    public function testComputeFindsRevenueByIdDespiteAmbiguousIdToNameMapping()
    {
        // 'revenue' maps to both INDEX_REVENUE and INDEX_ECOMMERCE_ITEM_REVENUE, the flipped
        // mapping used by Metric::getMetric() only resolves to one of them
        $metric = new PercentOfReportTotal('revenue', 'Revenue', 1000);

        $row = new Row([Row::COLUMNS => ['label' => 'row1', Metrics::INDEX_REVENUE => 100]]);
        $this->assertEquals(0.1, $metric->compute($row));

        $row = new Row([Row::COLUMNS => ['label' => 'row1', Metrics::INDEX_ECOMMERCE_ITEM_REVENUE => 250]]);
        $this->assertEquals(0.25, $metric->compute($row));
    }

    public function testComputeReturnsFalseWhenTheMetricIsMissingOrNotNumeric()
    {
        $metric = new PercentOfReportTotal('nb_visits', 'Visits', 200);

        $this->assertFalse($metric->compute(new Row([Row::COLUMNS => ['label' => 'row1']])));
        $this->assertFalse($metric->compute(new Row([Row::COLUMNS => ['label' => 'row1', 'nb_visits' => 'one']])));
    }

    public function testComputeReturnsZeroWhenTheTotalIsZero()
    {
        $metric = new PercentOfReportTotal('nb_visits', 'Visits', 0);

        $row = new Row([Row::COLUMNS => ['label' => 'row1', 'nb_visits' => 25]]);

        $this->assertEquals(0, $metric->compute($row));
    }

    public function testFormatReturnsPrettyPercent()
    {
        Fixture::loadAllTranslations();

        try {
            $metric = new PercentOfReportTotal('nb_visits', 'Visits', 200);

            $this->assertEquals('12.5%', $metric->format(0.125, new Formatter()));
        } finally {
            Fixture::resetTranslations();
        }
    }

    public function testAddMetricsToTableRegistersMetricsForEligibleTotalsOnly()
    {
        $table = $this->makeTableWithTotals([
            'nb_visits' => 200,
            'revenue' => 1000,
            'bounce_rate' => 0.5, // rate metrics have no meaningful percent of total
            'sum_visit_length' => 400, // not in the list of metrics to process report totals
        ]);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals(
            ['nb_visits_percent_of_total', 'revenue_percent_of_total'],
            $this->getRegisteredMetricNames($table)
        );
    }

    public function testAddMetricsToTableSkipsNonAdditiveMetrics()
    {
        // unique visitor/user totals are plain sums of non-summable values, so their
        // percentages would be meaningless (and can differ between the flat and the
        // hierarchical Actions record a report is served from)
        $table = $this->makeTableWithTotals([
            'nb_visits' => 200,
            'nb_uniq_visitors' => 150,
            'nb_users' => 30,
            'exit_nb_uniq_visitors' => 20,
            'sum_daily_nb_uniq_visitors' => 400,
        ]);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals(
            ['nb_visits_percent_of_total'],
            $this->getRegisteredMetricNames($table)
        );
    }

    public function testAddMetricsToTableRegistersMetricsTheReportProcessesTotalsFor()
    {
        $table = $this->makeTableWithTotals(['nb_visits' => 200, 'my_custom_metric' => 50]);

        $report = new class () extends Report {
            public function getMetricNamesToProcessReportTotals()
            {
                return ['my_custom_metric' => 'my_custom_metric'];
            }

            public function getMetrics()
            {
                return ['my_custom_metric' => 'My Custom Metric'];
            }
        };

        PercentOfReportTotal::addMetricsToTable($table, $report);

        $this->assertEquals(
            ['nb_visits_percent_of_total', 'my_custom_metric_percent_of_total'],
            $this->getRegisteredMetricNames($table)
        );
    }

    public function testAddMetricsToTableSkipsMetricsTheApiMethodAlreadyExpressesAsAPercentage()
    {
        // eg DevicePlugins.getPlugin, which registers a VisitsPercent with a denominator of its own
        $table = $this->makeTableWithTotals(['nb_visits' => 200, 'revenue' => 1000]);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new VisitsPercent(150)]);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals(
            ['nb_visits_percentage', 'revenue_percent_of_total'],
            $this->getRegisteredMetricNames($table)
        );
    }

    public function testAddMetricsToTableSkipsMetricsTheReportAlreadyExpressesAsAPercentage()
    {
        // eg VisitorInterest.getNumberOfVisitsByVisitCount, which declares a VisitsPercent instance
        $table = $this->makeTableWithTotals(['nb_visits' => 200, 'revenue' => 1000]);

        $report = new class () extends Report {
            protected function init()
            {
                $this->processedMetrics = [new VisitsPercent()];
            }
        };

        PercentOfReportTotal::addMetricsToTable($table, $report);

        $this->assertEquals(['revenue_percent_of_total'], $this->getRegisteredMetricNames($table));
    }

    public function testAddMetricsToTableSkipsPercentageMetricsTheReportDeclaresByName()
    {
        // eg DevicePlugins.getPlugin, whose report lists the metric name instead of an instance
        $table = $this->makeTableWithTotals(['nb_visits' => 200, 'revenue' => 1000]);

        $report = new class () extends Report {
            protected function init()
            {
                $this->processedMetrics = ['nb_visits_percentage'];
            }
        };

        PercentOfReportTotal::addMetricsToTable($table, $report);

        $this->assertEquals(['revenue_percent_of_total'], $this->getRegisteredMetricNames($table));
    }

    public function testAddMetricsToTableSupportsTotalsKeyedByMetricId()
    {
        // totals stay keyed by metric id when queued filters are disabled for the request,
        // eg the requests DataComparisonFilter uses to fetch compared series
        $table = $this->makeTableWithTotals([Metrics::INDEX_NB_VISITS => 200, Metrics::INDEX_REVENUE => 1000]);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals(
            ['nb_visits_percent_of_total', 'revenue_percent_of_total'],
            $this->getRegisteredMetricNames($table)
        );
    }

    public function testAddMetricsToTableIgnoresNonNumericTotals()
    {
        $table = $this->makeTableWithTotals(['nb_visits' => '12%', 'goals' => ['idgoal=1' => ['nb_conversions' => 2]]]);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals([], $this->getRegisteredMetricNames($table));
    }

    public function testAddMetricsToTableDoesNothingWithoutTotalsMetadata()
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => 'row1', 'nb_visits' => 25]);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals([], $this->getRegisteredMetricNames($table));
    }

    public function testAddMetricsToTableDoesNotAddMetricsTwice()
    {
        $table = $this->makeTableWithTotals(['nb_visits' => 200]);

        PercentOfReportTotal::addMetricsToTable($table, null);
        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals(['nb_visits_percent_of_total'], $this->getRegisteredMetricNames($table));
    }

    public function testAddMetricsToTableRegistersMetricsOnSubtables()
    {
        $table = $this->makeTableWithTotals(['nb_visits' => 200]);

        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(['label' => 'subrow1', 'nb_visits' => 5]);
        $table->getFirstRow()->setSubtable($subtable);

        PercentOfReportTotal::addMetricsToTable($table, null);

        $this->assertEquals(['nb_visits_percent_of_total'], $this->getRegisteredMetricNames($subtable));
    }

    private function makeTableWithTotals(array $totals): DataTable
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => 'row1', 'nb_visits' => 25]);
        $table->setMetadata('totalsUnformatted', $totals);
        return $table;
    }

    private function getRegisteredMetricNames(DataTable $table): array
    {
        $metrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];

        $names = [];
        foreach ($metrics as $metric) {
            $names[] = $metric->getName();
        }
        return $names;
    }
}
