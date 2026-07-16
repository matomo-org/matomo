<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Unit;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Plugin\Report;
use Piwik\Plugins\Contents\Columns\Metrics\InteractionRate;
use Piwik\Plugins\PrivacyManager\DataRounding;

/**
 * @group PrivacyManager
 * @group Plugins
 */
class DataRoundingTest extends \PHPUnit\Framework\TestCase
{
    public function testRoundCountMetricsUsesExpectedThresholds(): void
    {
        $table = new DataTable();

        foreach ([0, 1, 14, 15, 24, 25] as $value) {
            $row = new Row();
            $row->addColumn('nb_visits', $value);
            $table->addRow($row);
        }

        DataRounding::roundCountMetrics($table);

        $actual = [];
        foreach ($table->getRows() as $row) {
            $actual[] = $row->getColumn('nb_visits');
        }

        $this->assertSame([0, 10, 10, 20, 20, 30], $actual);
    }

    public function testRoundCountMetricsSkipsRatesDurationsAndMoneyAndRoundsTotals(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('nb_actions', 13);
        $row->addColumn('nb_conversions_page_rate', 0.31);
        $row->addColumn('bounce_rate', 0.5);
        $row->addColumn('avg_time_on_page', 123);
        $row->addColumn('revenue', 99.99);
        $table->addRow($row);

        $table->setMetadata('totals', [
            'nb_actions' => 21,
            'nb_conversions_page_rate' => 0.29,
            'bounce_rate' => 0.2,
            'avg_time_on_page' => 65,
            'revenue' => 10.50,
        ]);

        DataRounding::roundCountMetrics($table);

        $firstRow = $table->getFirstRow();
        $this->assertSame(10, $firstRow->getColumn('nb_actions'));
        $this->assertSame(0.31, $firstRow->getColumn('nb_conversions_page_rate'));
        $this->assertSame(0.5, $firstRow->getColumn('bounce_rate'));
        $this->assertSame(123, $firstRow->getColumn('avg_time_on_page'));
        $this->assertSame(99.99, $firstRow->getColumn('revenue'));

        $totals = $table->getMetadata('totals');
        $this->assertSame(20, $totals['nb_actions']);
        $this->assertSame(0.29, $totals['nb_conversions_page_rate']);
        $this->assertSame(0.2, $totals['bounce_rate']);
        $this->assertSame(65, $totals['avg_time_on_page']);
        $this->assertSame(10.50, $totals['revenue']);
    }

    public function testRoundCountMetricsRoundsTotalsRowCountColumns(): void
    {
        $table = new DataTable();

        $row = new Row();
        $row->addColumn('nb_visits', 13);
        $row->addColumn('avg_time_on_page', 123);
        $row->addColumn('revenue', 99.99);
        $table->addRow($row);

        $totalsRow = new Row();
        $totalsRow->addColumn('label', 'Totals');
        $totalsRow->addColumn('nb_visits', 21);
        $totalsRow->addColumn('avg_time_on_page', 65);
        $totalsRow->addColumn('revenue', 10.50);
        $table->setTotalsRow($totalsRow);

        DataRounding::roundCountMetrics($table);

        $actualTotalsRow = $table->getTotalsRow();
        $this->assertNotNull($actualTotalsRow);
        $this->assertSame(20, $actualTotalsRow->getColumn('nb_visits'));
        $this->assertSame(65, $actualTotalsRow->getColumn('avg_time_on_page'));
        $this->assertSame(10.50, $actualTotalsRow->getColumn('revenue'));
        $this->assertSame('Totals', $actualTotalsRow->getColumn('label'));
    }

    public function testRoundCountMetricsClearsStaleRowPercentagesAndRoundsTotalsUnformatted(): void
    {
        $table = new DataTable();

        $row = new Row();
        $row->addColumn('nb_uniq_visitors', 376);
        $row->setMetadata('nb_uniq_visitors_row_percentage', '46.7%');
        $row->setMetadata('nb_uniq_visitors_site_total_percentage', '24.4%');
        $table->addRow($row);

        $table->setMetadata('totals', [
            'nb_uniq_visitors' => 805,
        ]);
        $table->setMetadata('totalsUnformatted', [
            'nb_uniq_visitors' => 805,
        ]);

        DataRounding::roundCountMetrics($table);

        $actualRow = $table->getFirstRow();
        $this->assertSame(380, $actualRow->getColumn('nb_uniq_visitors'));

        $this->assertFalse($actualRow->getMetadata('nb_uniq_visitors_row_percentage'));
        $this->assertFalse($actualRow->getMetadata('nb_uniq_visitors_site_total_percentage'));

        $totals = $table->getMetadata('totals');
        $this->assertSame(810, $totals['nb_uniq_visitors']);

        $totalsUnformatted = $table->getMetadata('totalsUnformatted');
        $this->assertSame(810, $totalsUnformatted['nb_uniq_visitors']);
    }

    public function testRoundCountMetricsConstantRowsCountUsesSumOfRoundedBucketsForTotalsAndPercentages(): void
    {
        $table = new DataTable();

        foreach ([1, 1, 1, 1, 1, 0] as $value) {
            $row = new Row();
            $row->addColumn('nb_conversions', $value);
            $row->setMetadata('nb_conversions_row_percentage', '25%');
            $row->setMetadata('nb_conversions_site_total_percentage', '10%');
            $table->addRow($row);
        }

        $table->setMetadata('totals', ['nb_conversions' => 40]);
        $table->setMetadata('totalsUnformatted', ['nb_conversions' => 40]);

        $totalsRow = new Row();
        $totalsRow->addColumn('label', 'Totals');
        $totalsRow->addColumn('nb_conversions', 40);
        $table->setTotalsRow($totalsRow);

        $constantRowsCountReport = new class extends Report {
            protected function init()
            {
                $this->constantRowsCount = true;
                $this->metrics = ['nb_conversions'];
            }
        };

        DataRounding::roundCountMetrics($table, $constantRowsCountReport);

        $totals = $table->getMetadata('totals');
        $totalsUnformatted = $table->getMetadata('totalsUnformatted');

        $this->assertSame(50, $totals['nb_conversions']);
        $this->assertSame(50, $totalsUnformatted['nb_conversions']);
        $this->assertSame(50, $table->getTotalsRow()->getColumn('nb_conversions'));

        foreach ($table->getRows() as $index => $row) {
            $this->assertFalse($row->getMetadata('nb_conversions_site_total_percentage'));
            $this->assertFalse($row->getMetadata('nb_conversions_row_percentage'));

            $value = $row->getColumn('nb_conversions');
            if ($index < 5) {
                $this->assertSame(10, $value);
            } else {
                $this->assertSame(0, $value);
            }
        }
    }

    public function testRoundCountMetricsNonConstantRowsCountKeepsExistingTotalsSemantics(): void
    {
        $table = new DataTable();

        foreach ([1, 1, 1, 1, 1] as $value) {
            $row = new Row();
            $row->addColumn('nb_conversions', $value);
            $row->setMetadata('nb_conversions_row_percentage', '25%');
            $table->addRow($row);
        }

        $table->setMetadata('totals', ['nb_conversions' => 40]);
        $table->setMetadata('totalsUnformatted', ['nb_conversions' => 40]);

        $nonConstantRowsCountReport = new class extends Report {
            protected function init()
            {
                $this->constantRowsCount = false;
                $this->metrics = ['nb_conversions'];
            }
        };

        DataRounding::roundCountMetrics($table, $nonConstantRowsCountReport);

        $totals = $table->getMetadata('totals');
        $totalsUnformatted = $table->getMetadata('totalsUnformatted');
        $this->assertSame(40, $totals['nb_conversions']);
        $this->assertSame(40, $totalsUnformatted['nb_conversions']);

        foreach ($table->getRows() as $row) {
            $this->assertSame(10, $row->getColumn('nb_conversions'));
            $this->assertFalse($row->getMetadata('nb_conversions_row_percentage'));
        }
    }

    public function testRoundCountMetricsRoundsSubtableRows(): void
    {
        $table = new DataTable();

        $row = new Row();
        $row->addColumn('nb_visits', 13);

        $subtable = new DataTable();
        $subRow = new Row();
        $subRow->addColumn('nb_visits', 24);
        $subtable->addRow($subRow);
        $row->setSubtable($subtable);

        $table->addRow($row);

        DataRounding::roundCountMetrics($table);

        $this->assertSame(10, $table->getFirstRow()->getColumn('nb_visits'));
        $this->assertSame(20, $table->getFirstRow()->getSubtable()->getFirstRow()->getColumn('nb_visits'));
    }

    public function testRoundCountMetricsRoundsNestedArrayValuesInRowColumns(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('nb_visits', 13);
        $row->addColumn('goals', [
            '1' => [
                'idgoal' => 11,
                'nb_conversions' => 24,
                'revenue' => 99.99,
                'sum_bandwidth' => 7148,
                'nb_hits_with_bandwidth' => 2,
                'items' => 3,
            ],
            'all' => [
                'nb_visits_converted' => 1,
            ],
        ]);
        $table->addRow($row);

        DataRounding::roundCountMetrics($table);

        $goals = $table->getFirstRow()->getColumn('goals');
        $this->assertSame(20, $goals['1']['nb_conversions']);
        $this->assertSame(11, $goals['1']['idgoal']);
        $this->assertSame(99.99, $goals['1']['revenue']);
        $this->assertSame(7148, $goals['1']['sum_bandwidth']);
        $this->assertSame(2, $goals['1']['nb_hits_with_bandwidth']);
        $this->assertSame(10, $goals['1']['items']);
        $this->assertSame(10, $goals['all']['nb_visits_converted']);
    }

    public function testRoundCountMetricsRoundsNestedDataTableValuesInRowColumns(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('nb_visits', 13);

        $goals = new DataTable();
        $goalRow = new Row();
        $goalRow->addColumn('idgoal', 11);
        $goalRow->addColumn('nb_conversions', 2);
        $goalRow->addColumn('nb_visits_converted', 4);
        $goalRow->addColumn('revenue', 99.99);
        $goals->addRow($goalRow);

        $row->addColumn('goals', $goals);
        $table->addRow($row);

        DataRounding::roundCountMetrics($table);

        /** @var DataTable $roundedGoals */
        $roundedGoals = $table->getFirstRow()->getColumn('goals');
        $roundedGoalRow = $roundedGoals->getFirstRow();

        $this->assertSame(10, $table->getFirstRow()->getColumn('nb_visits'));
        $this->assertSame(11, $roundedGoalRow->getColumn('idgoal'));
        $this->assertSame(10, $roundedGoalRow->getColumn('nb_conversions'));
        $this->assertSame(10, $roundedGoalRow->getColumn('nb_visits_converted'));
        $this->assertSame(99.99, $roundedGoalRow->getColumn('revenue'));
    }

    public function testRoundCountMetricsRoundsRawGoalMetricArraysInRowColumns(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('nb_visits', 13);
        $row->addColumn((string) Metrics::INDEX_GOALS, [
            1 => Metrics::makeGoalColumnsRow(1, [
                Metrics::INDEX_GOAL_NB_CONVERSIONS => 7,
                Metrics::INDEX_GOAL_NB_VISITS_CONVERTED => 7,
                Metrics::INDEX_GOAL_REVENUE => 35.0,
            ]),
            2 => Metrics::makeGoalColumnsRow(2, [
                Metrics::INDEX_GOAL_NB_CONVERSIONS => 3,
                Metrics::INDEX_GOAL_NB_VISITS_CONVERTED => 3,
                Metrics::INDEX_GOAL_REVENUE => 15.0,
            ]),
        ]);
        $table->addRow($row);

        DataRounding::roundCountMetrics($table);

        $goals = $table->getFirstRow()->getColumn((string) Metrics::INDEX_GOALS);

        $this->assertSame(10, $table->getFirstRow()->getColumn('nb_visits'));
        $this->assertSame(10, $goals[1][Metrics::INDEX_GOAL_NB_CONVERSIONS]);
        $this->assertSame(10, $goals[1][Metrics::INDEX_GOAL_NB_VISITS_CONVERTED]);
        $this->assertSame(35.0, $goals[1][Metrics::INDEX_GOAL_REVENUE]);
        $this->assertSame(10, $goals[2][Metrics::INDEX_GOAL_NB_CONVERSIONS]);
        $this->assertSame(10, $goals[2][Metrics::INDEX_GOAL_NB_VISITS_CONVERTED]);
        $this->assertSame(15.0, $goals[2][Metrics::INDEX_GOAL_REVENUE]);
    }

    public function testRoundCountMetricsRoundsMixedNamedAndRawTopLevelVisitMetrics(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('label', 'Thursday');
        $row->addColumn('nb_visits', 10);
        $row->addColumn(Metrics::INDEX_NB_UNIQ_VISITORS, 11);
        $row->addColumn(Metrics::INDEX_NB_ACTIONS, 22);
        $row->addColumn(Metrics::INDEX_NB_USERS, 0);
        $row->addColumn(Metrics::INDEX_SUM_VISIT_LENGTH, 22);
        $row->addColumn(Metrics::INDEX_BOUNCE_COUNT, 0);
        $row->addColumn(Metrics::INDEX_NB_VISITS_CONVERTED, 11);
        $row->addColumn('day_of_week', 4);
        $table->addRow($row);

        DataRounding::roundCountMetrics($table);

        $actual = $table->getFirstRow();
        $this->assertSame(10, $actual->getColumn('nb_visits'));
        $this->assertSame(10, $actual->getColumn(Metrics::INDEX_NB_UNIQ_VISITORS));
        $this->assertSame(20, $actual->getColumn(Metrics::INDEX_NB_ACTIONS));
        $this->assertSame(10, $actual->getColumn(Metrics::INDEX_NB_VISITS_CONVERTED));
        $this->assertSame(22, $actual->getColumn(Metrics::INDEX_SUM_VISIT_LENGTH));
        $this->assertSame(4, $actual->getColumn('day_of_week'));
    }

    public function testRoundCountArrayValuesRoundsRawGoalMetricArrays(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            (string) Metrics::INDEX_GOALS => [
                1 => Metrics::makeGoalColumnsRow(1, [
                    Metrics::INDEX_GOAL_NB_CONVERSIONS => 1,
                    Metrics::INDEX_GOAL_NB_VISITS_CONVERTED => 4,
                    Metrics::INDEX_GOAL_REVENUE => 5.0,
                ]),
            ],
        ]);

        $this->assertSame(10, $rounded[(string) Metrics::INDEX_GOALS][1][Metrics::INDEX_GOAL_NB_CONVERSIONS]);
        $this->assertSame(10, $rounded[(string) Metrics::INDEX_GOALS][1][Metrics::INDEX_GOAL_NB_VISITS_CONVERTED]);
        $this->assertSame(5.0, $rounded[(string) Metrics::INDEX_GOALS][1][Metrics::INDEX_GOAL_REVENUE]);
    }

    public function testRoundCountMetricsRoundsCountColumnsPresentOnlyInLaterRows(): void
    {
        $table = new DataTable();

        $firstRow = new Row();
        $firstRow->addColumn('nb_visits', 13);
        $table->addRow($firstRow);

        $secondRow = new Row();
        $secondRow->addColumn('nb_visits', 17);
        $secondRow->addColumn('nb_conversions', 1);
        $table->addRow($secondRow);

        DataRounding::roundCountMetrics($table);

        $rows = $table->getRows();
        $this->assertSame(10, $rows[0]->getColumn('nb_visits'));
        $this->assertSame(20, $rows[1]->getColumn('nb_visits'));
        $this->assertSame(10, $rows[1]->getColumn('nb_conversions'));
    }

    public function testRoundCountArrayValuesSkipsIdentifierLikeFields(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'idsite' => 13,
            'idgoal' => 27,
            'id_dimension' => 24,
            'nb_visits' => 13,
        ]);

        $this->assertSame(13, $rounded['idsite']);
        $this->assertSame(27, $rounded['idgoal']);
        $this->assertSame(24, $rounded['id_dimension']);
        $this->assertSame(10, $rounded['nb_visits']);
    }

    public function testRoundCountArrayValuesRespectsMetricSemanticTypes(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'nb_actions' => 21,
            'revenue' => 18.75,
        ], [
            'nb_actions' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'revenue' => \Piwik\Columns\Dimension::TYPE_MONEY,
        ]);

        $this->assertSame(20, $rounded['nb_actions']);
        $this->assertSame(18.75, $rounded['revenue']);
    }

    public function testRoundCountArrayValuesExcludesSpecificMaxMetricsButStillRoundsMaxEventValue(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'max_actions' => 21,
            'max_actions_returning' => 17,
            'max_actions_human' => 19,
            'max_time_network' => 33,
            'max_time_generation' => 27,
            'max_bandwidth' => 41,
            'max_event_value' => 21,
            'nb_actions' => 21,
        ], [
            'max_actions' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'max_actions_returning' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'max_actions_human' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'max_time_network' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'max_time_generation' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'max_bandwidth' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'max_event_value' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'nb_actions' => \Piwik\Columns\Dimension::TYPE_NUMBER,
        ]);

        $this->assertSame(21, $rounded['max_actions']);
        $this->assertSame(17, $rounded['max_actions_returning']);
        $this->assertSame(19, $rounded['max_actions_human']);
        $this->assertSame(33, $rounded['max_time_network']);
        $this->assertSame(27, $rounded['max_time_generation']);
        $this->assertSame(41, $rounded['max_bandwidth']);
        $this->assertSame(20, $rounded['max_event_value']);
        $this->assertSame(20, $rounded['nb_actions']);
    }

    public function testRoundCountArrayValuesRoundsNestedCountValues(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'nb_visits' => 6,
            'totals' => [
                'nb_actions' => 24,
                'child' => [
                    'nb_users' => 1,
                    'avg_time_on_page' => 99,
                ],
            ],
        ]);

        $this->assertSame(10, $rounded['nb_visits']);
        $this->assertSame(20, $rounded['totals']['nb_actions']);
        $this->assertSame(10, $rounded['totals']['child']['nb_users']);
        $this->assertSame(99, $rounded['totals']['child']['avg_time_on_page']);
    }

    public function testRoundCountArrayValuesSkipsChangeColumns(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'nb_visits' => 16,
            'nb_visits_change' => 16,
            'NB_ACTIONS_CHANGE' => 17,
        ]);

        $this->assertSame(20, $rounded['nb_visits']);
        $this->assertSame(16, $rounded['nb_visits_change']);
        $this->assertSame(17, $rounded['NB_ACTIONS_CHANGE']);
    }

    public function testRoundCountMetricsUsesDefaultMetricSemanticTypesWhenNoReportProvided(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('hits', 16);
        $row->addColumn('max_actions', 21);
        $row->addColumn('max_time_generation', 27);
        $row->addColumn('max_bandwidth', 41);
        $row->addColumn('max_event_value', 21);
        $row->addColumn('revenue', 17.55);
        $row->addColumn('bounce_rate', 0.42);
        $row->addColumn('avg_time_on_page', 99);
        $table->addRow($row);

        DataRounding::roundCountMetrics($table);

        $actual = $table->getFirstRow();
        $this->assertSame(20, $actual->getColumn('hits'));
        $this->assertSame(21, $actual->getColumn('max_actions'));
        $this->assertSame(27, $actual->getColumn('max_time_generation'));
        $this->assertSame(41, $actual->getColumn('max_bandwidth'));
        $this->assertSame(21, $actual->getColumn('max_event_value'));
        $this->assertSame(17.55, $actual->getColumn('revenue'));
        $this->assertSame(0.42, $actual->getColumn('bounce_rate'));
        $this->assertSame(99, $actual->getColumn('avg_time_on_page'));
    }

    public function testRoundCountMetricsRecomputesProcessedPercentMetricsFromRoundedValues(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('nb_impressions', 4);
        $row->addColumn('nb_interactions', 1);
        $row->addColumn('interaction_rate', 0.25);
        $table->addRow($row);

        $totalsRow = new Row();
        $totalsRow->addColumn('label', 'Totals');
        $totalsRow->addColumn('nb_impressions', 4);
        $totalsRow->addColumn('nb_interactions', 1);
        $totalsRow->addColumn('interaction_rate', 0.25);
        $table->setTotalsRow($totalsRow);

        $report = new class extends Report {
            protected function init()
            {
                $this->metrics = ['nb_impressions', 'nb_interactions'];
                $this->processedMetrics = [new InteractionRate()];
                $this->metricSemanticTypes = [
                    'nb_impressions' => Dimension::TYPE_NUMBER,
                    'nb_interactions' => Dimension::TYPE_NUMBER,
                    'interaction_rate' => Dimension::TYPE_PERCENT,
                ];
            }
        };

        DataRounding::roundCountMetrics($table, $report);

        $actual = $table->getFirstRow();
        $this->assertSame(10, $actual->getColumn('nb_impressions'));
        $this->assertSame(10, $actual->getColumn('nb_interactions'));
        $this->assertSame(1.0, $actual->getColumn('interaction_rate'));

        $actualTotalsRow = $table->getTotalsRow();
        $this->assertNotNull($actualTotalsRow);
        $this->assertSame(10, $actualTotalsRow->getColumn('nb_impressions'));
        $this->assertSame(10, $actualTotalsRow->getColumn('nb_interactions'));
        $this->assertSame(1.0, $actualTotalsRow->getColumn('interaction_rate'));
    }

    public function testRoundCountArrayValuesUsesReducedNameFallbackOnlyForCountLikeMetrics(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'custom_nb_sessions' => 17,
            'custom_count' => 21,
            'custom_rate' => 0.17,
            'custom_percentage' => 0.18,
            'custom_metric' => 19,
        ]);

        $this->assertSame(20, $rounded['custom_nb_sessions']);
        $this->assertSame(20, $rounded['custom_count']);
        $this->assertSame(0.17, $rounded['custom_rate']);
        $this->assertSame(0.18, $rounded['custom_percentage']);
        $this->assertSame(19, $rounded['custom_metric']);
    }

    public function testRoundCountArrayValuesExcludesVisitLengthEvenWithNumberSemanticType(): void
    {
        $rounded = DataRounding::roundCountArrayValues([
            'entry_sum_visit_length' => 14,
            'sum_bandwidth' => 7148,
            'nb_visits' => 16,
        ], [
            'entry_sum_visit_length' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'sum_bandwidth' => \Piwik\Columns\Dimension::TYPE_NUMBER,
            'nb_visits' => \Piwik\Columns\Dimension::TYPE_NUMBER,
        ]);

        $this->assertSame(14, $rounded['entry_sum_visit_length']);
        $this->assertSame(7148, $rounded['sum_bandwidth']);
        $this->assertSame(20, $rounded['nb_visits']);
    }

    public function testExtractRequestedSiteIdsReturnsAllRequestedSiteIds(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => '1,2',
            'segment' => 'visitCount>=1',
        ]]);

        $this->assertSame([1, 2], $actual);
    }

    public function testExtractRequestedSiteIdsHandlesIdSiteArray(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => [1, 2],
            'segment' => 'visitCount>=1',
        ]]);

        $this->assertSame([1, 2], $actual);
    }

    public function testExtractRequestedSiteIdsHandlesIdSiteArrayOfStrings(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => ['1', '2'],
        ]]);

        $this->assertSame([1, 2], $actual);
    }

    public function testExtractRequestedSiteIdsHandlesCommaSeparatedString(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => '1,3',
        ]]);

        $this->assertSame([1, 3], $actual);
    }

    public function testExtractRequestedSiteIdsFiltersAndDeduplicatesCommaSeparatedString(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => '1, foo, 1, 3, 0, -2',
        ]]);

        $this->assertSame([1, 3], array_values($actual));
    }

    public function testExtractRequestedSiteIdsIgnoresInvalidEntriesInArray(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => ['1', 'foo', '', '0', '-3', '2'],
        ]]);

        $this->assertSame([1, 2], $actual);
    }

    public function testExtractRequestedSiteIdsReturnsEmptyForEmptyString(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => '',
        ]]);

        $this->assertSame([], $actual);
    }

    public function testExtractRequestedSiteIdsReturnsEmptyWhenIdSiteMissing(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'segment' => 'visitCount>=1',
        ]]);

        $this->assertSame([], $actual);
    }

    public function testExtractRequestedSiteIdsReturnsEmptyForBoolean(): void
    {
        $actual = $this->invokeDataRoundingMethod('extractRequestedSiteIds', [[
            'idSite' => true,
        ]]);

        $this->assertSame([], $actual);
    }

    public function testRoundCountMetricsForRequestRoundsOnlyEnabledSitesWithinSiteKeyedMap(): void
    {
        $siteOneTable = new DataTable();
        $siteOneRow = new Row();
        $siteOneRow->addColumn('nb_visits', 13);
        $siteOneTable->addRow($siteOneRow);

        $siteTwoTable = new DataTable();
        $siteTwoRow = new Row();
        $siteTwoRow->addColumn('nb_visits', 13);
        $siteTwoTable->addRow($siteTwoRow);

        $map = new DataTable\Map();
        $map->setKeyName('site');
        $map->addTable($siteOneTable, '1');
        $map->addTable($siteTwoTable, '2');

        $this->invokeDataRoundingMethod('roundCountMetricsForRequestedSites', [
            $map,
            [1, 2],
            null,
            null,
            function (int $siteId): bool {
                return $siteId === 1;
            },
        ]);

        $this->assertSame(10, $siteOneTable->getFirstRow()->getColumn('nb_visits'));
        $this->assertSame(13, $siteTwoTable->getFirstRow()->getColumn('nb_visits'));
    }

    public function testRoundCountMetricsForRequestRoundsCombinedMultiSiteTableWhenAnyRequestedSiteHasRoundingEnabled(): void
    {
        $table = new DataTable();
        $row = new Row();
        $row->addColumn('nb_visits', 13);
        $table->addRow($row);

        $this->invokeDataRoundingMethod('roundCountMetricsForRequestedSites', [
            $table,
            [1, 2],
            null,
            null,
            function (int $siteId): bool {
                return $siteId === 1;
            },
        ]);

        $this->assertSame(10, $table->getFirstRow()->getColumn('nb_visits'));
    }

    public function testRoundCountMetricsForRequestRoundsOnlyEnabledRowsWithinMergedMultiSiteTable(): void
    {
        $table = new DataTable();

        $siteOneRow = new Row();
        $siteOneRow->addColumn('idsite', 1);
        $siteOneRow->addColumn('nb_visits', 13);
        $table->addRow($siteOneRow);

        $siteTwoRow = new Row();
        $siteTwoRow->addColumn('idsite', 2);
        $siteTwoRow->addColumn('nb_visits', 13);
        $table->addRow($siteTwoRow);

        $this->invokeDataRoundingMethod('roundCountMetricsForRequestedSites', [
            $table,
            [1, 2],
            null,
            null,
            function (int $siteId): bool {
                return $siteId === 1;
            },
        ]);

        $this->assertSame(10, $table->getRows()[0]->getColumn('nb_visits'));
        $this->assertSame(13, $table->getRows()[1]->getColumn('nb_visits'));
    }

    public function testRoundCountMetricsForRequestRoundsOnlyEnabledRowsWithinMergedMultiSiteTableWhenIdSiteIsMetadata(): void
    {
        $table = new DataTable();

        $siteOneRow = new Row();
        $siteOneRow->setMetadata('idsite', 1);
        $siteOneRow->addColumn('nb_visits', 13);
        $table->addRow($siteOneRow);

        $siteTwoRow = new Row();
        $siteTwoRow->setMetadata('idsite', 2);
        $siteTwoRow->addColumn('nb_visits', 13);
        $table->addRow($siteTwoRow);

        $this->invokeDataRoundingMethod('roundCountMetricsForRequestedSites', [
            $table,
            [1, 2],
            null,
            null,
            function (int $siteId): bool {
                return $siteId === 1;
            },
        ]);

        $this->assertSame(10, $table->getRows()[0]->getColumn('nb_visits'));
        $this->assertSame(13, $table->getRows()[1]->getColumn('nb_visits'));
    }

    public function testRoundCountArrayValuesForRequestRoundsOnlyEnabledSitesWithinMultiSiteArrayPayload(): void
    {
        $rounded = $this->invokeDataRoundingMethod('roundArrayValuesForRequestedSites', [[
            [
                'idsite' => 1,
                'nb_visits' => 13,
                'nb_actions' => 17,
            ],
            [
                'idsite' => 2,
                'nb_visits' => 13,
                'nb_actions' => 17,
            ],
        ], [1, 2], null, function (int $siteId): bool {
            return $siteId === 1;
        }]);

        $this->assertSame(10, $rounded[0]['nb_visits']);
        $this->assertSame(20, $rounded[0]['nb_actions']);
        $this->assertSame(13, $rounded[1]['nb_visits']);
        $this->assertSame(17, $rounded[1]['nb_actions']);
    }

    public function testRoundCountArrayValuesForRequestRoundsCombinedArrayPayloadWhenAnyRequestedSiteHasRoundingEnabled(): void
    {
        $rounded = $this->invokeDataRoundingMethod('roundArrayValuesForRequestedSites', [[
            'nb_visits' => 13,
            'nb_actions' => 17,
        ], [1, 2], null, function (int $siteId): bool {
            return $siteId === 1;
        }]);

        $this->assertSame(10, $rounded['nb_visits']);
        $this->assertSame(20, $rounded['nb_actions']);
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function invokeDataRoundingMethod(string $methodName, array $arguments)
    {
        $reflectionMethod = new \ReflectionMethod(DataRounding::class, $methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs(null, $arguments);
    }
}
