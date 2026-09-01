<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PagePerformance\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\PagePerformance\PagePerformance;
use PHPUnit\Framework\TestCase;

/**
 * @group PagePerformance
 * @group Plugins
 */
class PagePerformanceTest extends TestCase
{
    public function testTruncatedSummaryRowAggregatesPagePerformanceAveragesAsWeightedAverages(): void
    {
        $table = new DataTable();
        $table->addRow($this->buildPerformanceRow('first', 100, 2));
        $table->addRow($this->buildPerformanceRow('second', 90, 1));

        $plugin = new PagePerformance();
        $plugin->enrichApi($table, ['module' => 'CustomReports', 'action' => 'getCustomReport']);

        $table->filter('Truncate', [0]);

        $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);

        self::assertInstanceOf(Row::class, $summaryRow);
        self::assertSame(190, $summaryRow->getColumn('sum_time_network'));
        self::assertSame(3, $summaryRow->getColumn('nb_hits_with_time_network'));
        self::assertSame(63.333, $summaryRow->getColumn('avg_time_network'));
    }

    private function buildPerformanceRow(string $label, int $sumTimeNetwork, int $hits): Row
    {
        $average = round($sumTimeNetwork / $hits, 3);

        return new Row([
            Row::COLUMNS => [
                'label' => $label,
                'sum_time_network' => $sumTimeNetwork,
                'nb_hits_with_time_network' => $hits,
                'avg_time_network' => $average,
                'avg_page_load_time' => $average,
            ],
        ]);
    }
}
