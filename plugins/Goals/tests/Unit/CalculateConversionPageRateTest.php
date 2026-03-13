<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;

/**
 * @group CalculateConversionPageRateTest
 * @group DataTable
 * @group Filter
 * @group Goals
 */
class CalculateConversionPageRateTest extends \PHPUnit\Framework\TestCase
{
    public function testFilterUsesNbConversionsFallbackIncludingSummaryRow()
    {
        $table = new DataTable();
        $table->addRowsFromArray([
            [Row::COLUMNS => [
                Metrics::INDEX_GOALS => [
                    '1' => [
                        Metrics::INDEX_GOAL_NB_CONVERSIONS => 3,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_ENTRY => 30,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ => 1,
                    ],
                ],
            ]],
            [Row::COLUMNS => [
                Metrics::INDEX_GOALS => [
                    '1' => [
                        Metrics::INDEX_GOAL_NB_CONVERSIONS => 3,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_ENTRY => 30,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ => 2,
                    ],
                ],
            ]],
            DataTable::ID_SUMMARY_ROW => [Row::COLUMNS => [
                Metrics::INDEX_GOALS => [
                    '1' => [
                        Metrics::INDEX_GOAL_NB_CONVERSIONS => 4,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_ENTRY => 40,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ => 3,
                    ],
                ],
            ]],
        ]);

        $table->filter('Piwik\Plugins\Goals\DataTable\Filter\CalculateConversionPageRate');

        $firstRowGoalMetrics = $table->getRowFromId(0)->getColumn(Metrics::INDEX_GOALS)['1'];
        $secondRowGoalMetrics = $table->getRowFromId(1)->getColumn(Metrics::INDEX_GOALS)['1'];
        $this->assertSame(0.1, $firstRowGoalMetrics[Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE]);
        $this->assertSame(0.2, $secondRowGoalMetrics[Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE]);
    }

    public function testFilterCapsPageConversionRateAtOneWhenFallbackTotalsAreUsed()
    {
        $table = new DataTable();
        $table->addRowsFromArray([
            [Row::COLUMNS => [
                Metrics::INDEX_GOALS => [
                    '1' => [
                        Metrics::INDEX_GOAL_NB_CONVERSIONS => 1,
                        Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ => 3,
                    ],
                ],
            ]],
        ]);

        $table->filter('Piwik\Plugins\Goals\DataTable\Filter\CalculateConversionPageRate');

        $goalMetrics = $table->getFirstRow()->getColumn(Metrics::INDEX_GOALS)['1'];
        $this->assertSame(1, $goalMetrics[Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE]);
    }
}
