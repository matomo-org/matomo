<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API;

use Piwik\API\DataTableGenericFilter;
use Piwik\DataTable;

class DataTableGenericFilterTest extends \PHPUnit\Framework\TestCase
{
    public function testGenericFiltersToDisableMetadataShouldBeRespected()
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['nb_visits' => 2, 'nb_actions' => 3],
            ['nb_visits' => 4, 'nb_actions' => 5],
        ]);
        $dataTable->setMetadata(DataTable::GENERIC_FILTERS_TO_DISABLE_METADATA_NAME, ['Limit']);

        $genericFilter = new DataTableGenericFilter(['filter_limit' => 1], null);
        $genericFilter->filter($dataTable);

        $this->assertEquals(2, $dataTable->getRowsCount());
    }

    public function testCallbackBeforeRowLimitingFiltersSeesEveryMatchingRow()
    {
        $dataTable = $this->makeDataTable();

        $rowCounts = [];
        $genericFilter = new DataTableGenericFilter(['filter_pattern' => 'a', 'filter_limit' => 1], null);
        $genericFilter->setCallbackBeforeRowLimitingFilters(function (DataTable $table) use (&$rowCounts) {
            $rowCounts[] = $table->getRowsCount();
        });
        $genericFilter->filter($dataTable);

        $this->assertSame([2], $rowCounts);
        $this->assertSame(1, $dataTable->getRowsCount());
    }

    public function testCallbackBeforeRowLimitingFiltersIsCalledWhenTheRowLimitingFiltersAreDisabled()
    {
        $dataTable = $this->makeDataTable();

        $rowCounts = [];
        $genericFilter = new DataTableGenericFilter(['filter_limit' => 1], null);
        $genericFilter->disableFilters(['Truncate', 'Limit']);
        $genericFilter->setCallbackBeforeRowLimitingFilters(function (DataTable $table) use (&$rowCounts) {
            $rowCounts[] = $table->getRowsCount();
        });
        $genericFilter->filter($dataTable);

        $this->assertSame([3], $rowCounts);
    }

    public function testCallbackBeforeRowLimitingFiltersIsCalledForEveryTableOfAMap()
    {
        $map = new DataTable\Map();
        $map->addTable($this->makeDataTable(), 'first');
        $map->addTable($this->makeDataTable(), 'second');

        $callCount = 0;
        $genericFilter = new DataTableGenericFilter(['filter_limit' => 1], null);
        $genericFilter->setCallbackBeforeRowLimitingFilters(function () use (&$callCount) {
            $callCount++;
        });
        $genericFilter->filter($map);

        $this->assertSame(2, $callCount);
    }

    private function makeDataTable(): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray([
            ['label' => 'apple', 'nb_visits' => 2],
            ['label' => 'banana', 'nb_visits' => 4],
            ['label' => 'cherry', 'nb_visits' => 6],
        ]);

        return $dataTable;
    }
}
