<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
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

        $this->assertInstanceOf(DataTable\Map::class, $result, 'Expected a Map of period DataTables');

        $childTables = $result->getDataTables();
        $this->assertNotEmpty($childTables, 'Expected at least one child DataTable in the period Map');

        $firstTable = reset($childTables);
        $this->assertInstanceOf(DataTable::class, $firstTable);

        $firstRow = $firstTable->getFirstRow();
        $this->assertNotFalse($firstRow, 'Expected a row for the selected day');

        $comparisons = $firstRow->getComparisons();
        $this->assertInstanceOf(DataTable::class, $comparisons, 'Expected comparison subtable on the main row');

        $rows = $comparisons->getRows();
        $this->assertGreaterThanOrEqual(2, count($rows), 'Expected at least main + one comparison row');

        $mainSeriesRow = $rows[0];
        $mainRevenue   = $mainSeriesRow->getColumn('revenue');

        $this->assertIsNumeric(
            $mainRevenue,
            'Main-series revenue must be numeric (not a formatted currency string) so the evolution chart can plot it'
        );
        $this->assertGreaterThan(
            0,
            $mainRevenue,
            'Main-series revenue must reflect the day\'s tracked orders, not 0'
        );

        // The comparison row may legitimately be 0 (the fixture has no orders on 2011-04-04),
        // but it must still be numeric — the original bug formatted it into a currency string too.
        $comparisonRow = $rows[1];
        $this->assertIsNumeric(
            $comparisonRow->getColumn('revenue'),
            'Comparison-series revenue must also be numeric, not a formatted currency string'
        );
    }
}

EcommerceEvolutionComparisonTest::$fixture = new TwoSitesEcommerceOrderWithItems();
