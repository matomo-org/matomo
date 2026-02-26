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
        $row->addColumn('bounce_rate', 0.5);
        $row->addColumn('avg_time_on_page', 123);
        $row->addColumn('revenue', 99.99);
        $table->addRow($row);

        $table->setMetadata('totals', [
            'nb_actions' => 21,
            'bounce_rate' => 0.2,
            'avg_time_on_page' => 65,
            'revenue' => 10.50,
        ]);

        DataRounding::roundCountMetrics($table);

        $firstRow = $table->getFirstRow();
        $this->assertSame(10, $firstRow->getColumn('nb_actions'));
        $this->assertSame(0.5, $firstRow->getColumn('bounce_rate'));
        $this->assertSame(123, $firstRow->getColumn('avg_time_on_page'));
        $this->assertSame(99.99, $firstRow->getColumn('revenue'));

        $totals = $table->getMetadata('totals');
        $this->assertSame(20, $totals['nb_actions']);
        $this->assertSame(0.2, $totals['bounce_rate']);
        $this->assertSame(65, $totals['avg_time_on_page']);
        $this->assertSame(10.50, $totals['revenue']);
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
