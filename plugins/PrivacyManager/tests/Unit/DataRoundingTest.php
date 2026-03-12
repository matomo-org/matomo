<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugin\Report;
use Piwik\Plugins\PrivacyManager\DataRounding;

class DataRoundingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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
        $this->assertSame(10, $goals['all']['nb_visits_converted']);
    }

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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

    /**
     * @group Plugins
     */
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
}
