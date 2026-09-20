<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\API;

use Piwik\API\DataTableManipulator\ReportTotalsCalculator;
use Piwik\API\DataTablePostProcessor;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Tests that the totals row totals the rows matching the table search of the request instead of
 * every row of the report.
 *
 * @group Core
 * @group FilteredTotals
 */
class FilteredTotalsTest extends IntegrationTestCase
{
    private const API_MODULE = 'UserCountry';

    private const API_METHOD = 'getCountry';

    public function testTotalsRowIsNotChangedWithoutATableSearch()
    {
        $dataTable = $this->processReport();

        $this->assertFalse($dataTable->getMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME));
        $this->assertSame(Piwik::translate('General_Totals'), $dataTable->getTotalsRow()->getColumn('label'));
        $this->assertSame(60, $dataTable->getTotalsRow()->getColumn('nb_visits'));
    }

    public function testTotalsRowOnlyTotalsTheRowsMatchingTheTableSearch()
    {
        $dataTable = $this->processReport(['filter_pattern' => 'land']);

        $this->assertTrue($dataTable->getMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME));
        $this->assertSame(Piwik::translate('General_FilteredTotal'), $dataTable->getTotalsRow()->getColumn('label'));

        // Finland (10) + Poland (20), not Germany (30)
        $this->assertSame(30, $dataTable->getTotalsRow()->getColumn('nb_visits'));
    }

    public function testTotalsRowOnlyTotalsTheRowsMatchingARecursiveTableSearch()
    {
        // reports with subtables, eg the page urls one, search recursively
        $dataTable = $this->processReport([
            'filter_column_recursive' => 'label',
            'filter_pattern_recursive' => 'land',
        ]);

        $this->assertTrue($dataTable->getMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME));
        $this->assertSame(30, $dataTable->getTotalsRow()->getColumn('nb_visits'));
    }

    public function testReportTotalsStayAvailableNextToTheFilteredTotals()
    {
        $dataTable = $this->processReport(['filter_pattern' => 'land']);

        $totals = $dataTable->getMetadata('totals');
        $this->assertSame(60, $totals['nb_visits']);
        $this->assertSame(30, $dataTable->getTotalsRow()->getColumn('nb_visits'));
    }

    public function testFilteredTotalsCoverEveryMatchingRowAndNotJustTheRequestedPage()
    {
        $dataTable = $this->processReport(['filter_pattern' => 'land', 'filter_limit' => 1]);

        $this->assertSame(1, $dataTable->getRowsCount());
        $this->assertSame(30, $dataTable->getTotalsRow()->getColumn('nb_visits'));
    }

    public function testProcessedMetricsOfTheFilteredTotalsAreRecalculatedInsteadOfSummed()
    {
        $dataTable = $this->processReport(['filter_pattern' => 'land', 'format_metrics' => 0]);

        // bounce_count 5 + 5 of nb_visits 10 + 20, not the sum of the row bounce rates (0.5 + 0.25)
        $this->assertSame(0.33, $dataTable->getTotalsRow()->getColumn('bounce_rate'));
    }

    public function testProcessedMetricsAreRecalculatedWhenTheyWereAlreadyComputedForAGenericFilter()
    {
        $dataTable = $this->processReport([
            'filter_pattern' => 'land',
            'filter_sort_column' => 'bounce_rate',
            'format_metrics' => 0,
        ]);

        $this->assertSame(0.33, $dataTable->getTotalsRow()->getColumn('bounce_rate'));
    }

    public function testTotalsRowIsNotChangedWhenTheTotalsRowIsNotRequested()
    {
        $dataTable = $this->processReport(['filter_pattern' => 'land', 'keep_totals_row' => 0]);

        $this->assertNull($dataTable->getTotalsRow());
        $this->assertFalse($dataTable->getMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME));
    }

    public function testTotalsRowIsNotFilteredWhileComparing()
    {
        $dataTable = $this->makeDataTable();
        $request = $this->makeRequest(['filter_pattern' => 'land', 'compare' => 1]);

        // the totals row the report totals calculator would have added before the generic filters ran
        $totalsRow = new DataTable\Row([DataTable\Row::COLUMNS => ['label' => 'Totals', 'nb_visits' => 60]]);
        $dataTable->setTotalsRow($totalsRow);

        $calculator = new ReportTotalsCalculator(
            self::API_MODULE,
            self::API_METHOD,
            $request,
            ReportsProvider::factory(self::API_MODULE, self::API_METHOD)
        );
        $calculator->calculateFilteredTotals($dataTable);

        $this->assertSame($totalsRow, $dataTable->getTotalsRow());
        $this->assertFalse($dataTable->getMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME));
    }

    private function processReport(array $requestOverrides = []): DataTable
    {
        $request = $this->makeRequest($requestOverrides);

        $processor = new DataTablePostProcessor(self::API_MODULE, self::API_METHOD, $request);

        /** @var DataTable $dataTable */
        $dataTable = $processor->process($this->makeDataTable());

        return $dataTable;
    }

    private function makeRequest(array $requestOverrides = []): array
    {
        return array_merge(
            [
                'idSite' => 1,
                'period' => 'day',
                'date' => '2026-01-01',
                'method' => self::API_MODULE . '.' . self::API_METHOD,
                'totals' => 1,
                'keep_totals_row' => 1,
                'filter_limit' => -1,
                'format_metrics' => 1,
            ],
            $requestOverrides
        );
    }

    private function makeDataTable(): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'Finland', 'nb_visits' => 10, 'nb_actions' => 20, 'bounce_count' => 5, 'sum_visit_length' => 100],
            ['label' => 'Poland', 'nb_visits' => 20, 'nb_actions' => 60, 'bounce_count' => 5, 'sum_visit_length' => 400],
            ['label' => 'Germany', 'nb_visits' => 30, 'nb_actions' => 30, 'bounce_count' => 30, 'sum_visit_length' => 300],
        ]);

        return $dataTable;
    }
}
