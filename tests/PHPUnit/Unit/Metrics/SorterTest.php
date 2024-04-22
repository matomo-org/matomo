<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Sorter;
use Piwik\Metrics\Sorter\Config;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

/**
 * @group Core
 * @group sort
 */
class SorterTest extends UnitTestCase
{
    /**
     * @var Sorter
     */
    private $sorter;

    /**
     * @var Config
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = new Config();
        $this->config->primaryColumnToSort = 'nb_visits';
        $this->config->primarySortOrder = SORT_DESC;
        $this->config->primarySortFlags = SORT_NUMERIC;
        $this->sorter = new Sorter($this->config);
    }

    public function testGetPrimarySortOrderShouldReturnDescByDefault()
    {
        $this->assertSame(SORT_DESC, $this->sorter->getPrimarySortOrder(null));
        $this->assertSame(SORT_DESC, $this->sorter->getPrimarySortOrder('whatever'));
        $this->assertSame(SORT_DESC, $this->sorter->getPrimarySortOrder('desc'));
    }

    public function testGetPrimarySortOrderShouldReturnAscIfRequestedLowerCase()
    {
        $this->assertSame(SORT_ASC, $this->sorter->getPrimarySortOrder('asc'));
        $this->assertSame(SORT_DESC, $this->sorter->getPrimarySortOrder('AsC')); // we require 'asc' to be lowercase
    }

    public function testGetSecondarySortOrderShouldReturnInvertedOrderIfColumnIsLabel()
    {
        $this->assertSame(SORT_DESC, $this->sorter->getSecondarySortOrder('asc', 'label'));
        $this->assertSame(SORT_ASC, $this->sorter->getSecondarySortOrder('whatever', 'label'));
        $this->assertSame(SORT_ASC, $this->sorter->getSecondarySortOrder('desc', 'label'));
        $this->assertSame(SORT_ASC, $this->sorter->getSecondarySortOrder('AsC', 'label'));
    }
    public function testGetPrimarySortOrderShouldReturnDescByDefaultIfNotLabelColumnIsRequested()
    {
        $this->assertSame(SORT_DESC, $this->sorter->getSecondarySortOrder(null, 'nb_visits'));
        $this->assertSame(SORT_DESC, $this->sorter->getSecondarySortOrder('whatever', 'nb_visits'));
        $this->assertSame(SORT_DESC, $this->sorter->getSecondarySortOrder('desc', 'nb_visits'));
    }

    public function testGetSecondarySortOrderShouldReturnAscIfRequestedLowerCaseIfNotLabelColumnIsRequested()
    {
        $this->assertSame(SORT_ASC, $this->sorter->getSecondarySortOrder('asc', 'nb_visits'));
        $this->assertSame(SORT_DESC, $this->sorter->getSecondarySortOrder('AsC', 'nb_visits')); // we require 'asc' to be lowercase
    }

    /**
     * @dataProvider getPrimaryColumnsToSort
     */
    public function testGetPrimaryColumnToSortShouldPickCorrectPrimaryColumnAndMapMetricNameToIdIfNeededAndReverse($expectedUsedColumn, $columnToSortBy)
    {
        $table = $this->createDataTable(array(
            array('label' => 'nintendo', 'nb_visits' => false, 'nb_hits' => 0, Metrics::INDEX_NB_VISITS_CONVERTED => false, Metrics::INDEX_BOUNCE_COUNT => 5)
        ));

        $this->assertSame($expectedUsedColumn, $this->sorter->getPrimaryColumnToSort($table, $columnToSortBy));
    }

    public function getPrimaryColumnsToSort()
    {
        return array(
            array('nb_visits', 'nb_visits'), // it is present in the row and should be used even though the value is false
            array('nb_hits', 'nb_hits'),      // it is present in the row and should be used even though the value is zero
            array(Metrics::INDEX_NB_VISITS_CONVERTED, 'nb_visits_converted'), // the column name is not present but it should find the column id even though the value is false
            array(Metrics::INDEX_NB_VISITS_CONVERTED, Metrics::INDEX_NB_VISITS_CONVERTED),  // if a column is present as id it should still be able to find it
            array(Metrics::INDEX_BOUNCE_COUNT, 'bounce_count'),  // should resolve column name to id, column has a value
            array(Metrics::INDEX_BOUNCE_COUNT, Metrics::INDEX_BOUNCE_COUNT), // should find a column with a value
        );
    }

    public function testGetPrimaryColumnToSortShouldFallbackToNbVisitsIfPossible()
    {
        $table = $this->createDataTable(array(
            array('label' => 'nintendo', 'nb_visits' => false)
        ));

        $this->assertSame('nb_visits', $this->sorter->getPrimaryColumnToSort($table, 'any_random_column_that_doesnt_exist'));
    }

    public function testGetPrimaryColumnToSortShouldFallbackToThePassedColumnNameIfColumnCannotBeFoundAndNbVisitsDoesNotExist()
    {
        $table = $this->createDataTable(array(array('label' => 'nintendo')));

        $this->assertSame('any_random_column_that_doesnt_exist', $this->sorter->getPrimaryColumnToSort($table, 'any_random_column_that_doesnt_exist'));
    }

    public function testGetSecondaryColumnToSortShouldNotFindASecondaryColumnToSortIfSortedByLabelButNoVisitsColumnPresent()
    {
        $row = $this->createRow(array('label' => 'nintendo'));

        $this->assertNull($this->sorter->getSecondaryColumnToSort($row, 'label'));
    }

    public function testGetSecondaryColumnToSortShouldPreferVisitsColumnIfColumnIsPresentEvenIfValueIsFalse()
    {
        $row = $this->createRow(array('label' => 'nintendo', 'nb_visits' => false, 'nb_hits' => 10));

        $this->assertSame('nb_visits', $this->sorter->getSecondaryColumnToSort($row, 'nb_hits'));
        $this->assertSame('nb_visits', $this->sorter->getSecondaryColumnToSort($row, 'label'));
    }

    public function testGetSecondaryColumnToSortShouldPreferVisitsColumnIfColumnIsPresentEvenIfVisitsColumnIsId()
    {
        $row = $this->createRow(array('label' => 'nintendo', Metrics::INDEX_NB_VISITS => false, 'nb_hits' => 10));

        $this->assertSame(Metrics::INDEX_NB_VISITS, $this->sorter->getSecondaryColumnToSort($row, 'nb_hits'));
        $this->assertSame(Metrics::INDEX_NB_VISITS, $this->sorter->getSecondaryColumnToSort($row, 'label'));
    }

    public function testGetSecondaryColumnToSortShouldUseLabelColumnIfColumnIsPresentButNotNbVisitsColumn()
    {
        $row = $this->createRow(array('label' => 'nintendo', 'nb_hits' => 10));

        $this->assertSame('label', $this->sorter->getSecondaryColumnToSort($row, 'nb_hits'));
    }

    public function testGetSecondaryColumnToSortShouldUseLabelColumnIfPrimaryColumnIsNbVisitsColumn()
    {
        $row = $this->createRow(array('label' => 'nintendo', 'nb_visits' => 10));

        $this->assertSame('label', $this->sorter->getSecondaryColumnToSort($row, 'nb_visits'));
        $this->assertSame('label', $this->sorter->getSecondaryColumnToSort($row, Metrics::INDEX_NB_VISITS));
    }

    public function testGetSecondaryColumnToSortShouldNotBeAbleToFallbackIfVisitsColumnIsUsedButThereIsNoLabelColumn()
    {
        $row = $this->createRow(array('nb_visits' => 10, 'nb_hits' => 10));

        $this->assertNull($this->sorter->getSecondaryColumnToSort($row, 'nb_visits'));
    }

    public function testGetSecondaryColumnToSortShouldUseVisitsAsSecondaryColumnIfLabelIsUsedAsPrimaryColumn()
    {
        $row = $this->createRow(array('label' => 'nintendo', 'nb_visits' => false));

        $this->assertSame('nb_visits', $this->sorter->getSecondaryColumnToSort($row, 'label'));
    }

    /**
     * @dataProvider getLabelsForNaturalSortTest
     */
    public function testGetBestSortFlagsShouldAlwaysPickStringOrNaturalSortCaseInsensitive($label)
    {
        $table = $this->createDataTable(array(array('label' => $label)));

        $this->config->naturalSort = false; // even if natural sort is not preferred it should be still used
        $this->assertSame(SORT_STRING | SORT_FLAG_CASE, $this->sorter->getBestSortFlags($table, 'label'));

        $this->config->naturalSort = true;
        $this->assertSame(SORT_NATURAL | SORT_FLAG_CASE, $this->sorter->getBestSortFlags($table, 'label'));
    }

    public function getLabelsForNaturalSortTest()
    {
        return array(array('nintendo'), array('2015'), array('240.4'), array(2015), array('/test'));
    }

    /**
     * @dataProvider getColumnsForBestSortFlagsTest
     */
    public function testGetBestSortFlags($expectedSortFlags, $columnToReadFrom, $naturalSort = false)
    {
        $this->config->naturalSort = $naturalSort;

        $table = $this->createDataTable(array(
            array('label' => 'nintendo1', 'nb_visits' => false, 'nb_hits' => 0, Metrics::INDEX_NB_VISITS_CONVERTED => false, Metrics::INDEX_BOUNCE_COUNT => 5),
            array('label' => 'nintendo2', 'nb_visits' => 100, 'nb_pageviews' => 100, Metrics::INDEX_NB_VISITS_CONVERTED => null, 'sum_visit_length' => '5.5s'),
            array('label' => 'nintendo2', Metrics::INDEX_NB_VISITS_CONVERTED => array(), 'min_time_generation' => '5.5')
        ));

        $this->assertSame($expectedSortFlags, $this->sorter->getBestSortFlags($table, $columnToReadFrom));
    }

    public function getColumnsForBestSortFlagsTest()
    {
        return array(
            array(SORT_NUMERIC, 'nb_visits'), // should find a numeric value in the first row
            array(SORT_NUMERIC, 'nb_pageviews'), // should find a numeric value in the second row
            array(SORT_STRING | SORT_FLAG_CASE, Metrics::INDEX_NB_VISITS_CONVERTED), // should not find any value in any row and use default value
            array(SORT_NATURAL | SORT_FLAG_CASE, Metrics::INDEX_NB_VISITS_CONVERTED, true), // should not find any value in any row and use default value, natural preferred
            array(SORT_STRING | SORT_FLAG_CASE, 'sum_visit_length'), // it is not numeric so should use string as natural is disabled
            array(SORT_NATURAL | SORT_FLAG_CASE, 'sum_visit_length', true), // it is not numeric but natural is preferred so should use natural sort
            array(SORT_NUMERIC, 'min_time_generation') // value is a string but numeric so should use numeric
        );
    }

    public function testSortShouldNotFailIfNoRowsAreSet()
    {
        $table = $this->createDataTable(array());

        $this->sorter->sort($table);

        $this->assertSame(0, $table->getRowsCount());
    }

    public function testSortShouldSetTheSortedColumnNameOnTheTable()
    {
        $table = $this->createDataTable(array(array('nb_test' => 5)));
        $this->config->primaryColumnToSort = 'nb_test';

        $this->sorter->sort($table);

        $this->assertSame('nb_test', $table->getSortedByColumnName());
    }

    public function testSortShouldKeepTheAmountOfColumns()
    {
        $table = $this->createDataTableFromValues(array(5, null));
        $table->addSummaryRow($this->createRow(array('nb_test' => 10)));

        $this->sorter->sort($table);

        $this->assertSame(3, $table->getRowsCount());
        $this->assertSame(2, $table->getRowsCountWithoutSummaryRow());
    }

    public function testSortShouldNotSortOrChangeTheSummaryRow()
    {
        $table = $this->createDataTableFromValues(array(5, null));
        $table->addSummaryRow($this->createRow(array('nb_test' => 10)));

        $this->sorter->sort($table);

        $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);

        $this->assertSame(10, $summaryRow->getColumn('nb_test'));
    }

    public function testSortShouldSortNumericAndShouldAddEmptyValuesAlwaysAtTheEnd()
    {
        $table = $this->createDataTableFromValues(array(5, null, 61, array(), 10, false, 20, 15));

        $this->config->primarySortFlags = SORT_NUMERIC;
        $this->config->primarySortOrder = SORT_ASC;
        $this->sorter->sort($table);

        $expected = array(5, 10, 15, 20, 61, false, array(), false);
        $this->assertExpectedRowsOrder($expected, $table);

        $this->config->primarySortOrder = SORT_DESC;
        $this->sorter->sort($table);

        $expected = array(61, 20, 15, 10, 5, false, array(), false);
        $this->assertExpectedRowsOrder($expected, $table);
    }

    public function testSortSortNaturalShoudAddEmptyValuesAlwaysAtTheEnd()
    {
        $table = $this->createDataTableFromValues(array('nintendo', null, 'abc', array(), 'DeF', 'def', false, '1210', 'piwik'));

        $this->config->primarySortFlags = SORT_NATURAL;
        $this->config->primarySortOrder = SORT_ASC;
        $this->sorter->sort($table);

        $expected = array('1210', 'DeF', 'abc', 'def', 'nintendo', 'piwik', false, array(), false);
        $this->assertExpectedRowsOrder($expected, $table);

        $this->config->primarySortOrder = SORT_DESC;
        $this->sorter->sort($table);

        $expected = array('piwik', 'nintendo', 'def', 'abc', 'DeF', '1210', false, array(), false);
        $this->assertExpectedRowsOrder($expected, $table);
    }

    public function testSortShoudIgnoreASecondColumnSortIfDisabled()
    {
        $table = $this->createDataTableFromValues(array('abc', 'abc', 'abc', 'abc', 'abc'));

        $this->config->primarySortFlags = SORT_NATURAL;
        $this->config->isSecondaryColumnSortEnabled = false;
        $this->sorter->sort($table);

        // we make sure the labels order did not change neither when ASC nor DESC
        $expected = array('My Label 0', 'My Label 1', 'My Label 2', 'My Label 3', 'My Label 4');
        $this->assertExpectedRowsOrder($expected, $table, 'label');

        $this->config->secondarySortOrder = SORT_DESC;
        $this->sorter->sort($table);

        $this->assertExpectedRowsOrder($expected, $table, 'label');
    }

    public function testSortShoudIgnoreASecondColumnSortIfSortIsNumericButNoSecondaryColumnIsSet()
    {
        $table = $this->createDataTableFromValues(array('abc', 'abc', 'abc', 'abc', 'abc'));

        $this->config->primarySortFlags = SORT_NUMERIC;
        $this->sorter->sort($table);

        // we make sure the labels order did not change neither when ASC nor DESC
        $expected = array('My Label 0', 'My Label 1', 'My Label 2', 'My Label 3', 'My Label 4');
        $this->assertExpectedRowsOrder($expected, $table, 'label');

        $this->config->secondarySortOrder = SORT_DESC;
        $this->sorter->sort($table);

        $this->assertExpectedRowsOrder($expected, $table, 'label');
    }

    public function testSortShoudSortBySecondColumnIfSortedNumeric()
    {
        $table = $this->createDataTableFromValues(array('abc', 'abc', 'abc', 'abc', 'abc'));

        $this->config->primarySortFlags = SORT_NUMERIC;
        $this->config->secondaryColumnToSort = 'label';
        $this->config->secondarySortOrder = SORT_ASC;
        $this->config->secondarySortFlags = SORT_NATURAL;

        $this->sorter->sort($table);

        // we make sure the labels order did not change neither when ASC nor DESC
        $expected = array('My Label 0', 'My Label 1', 'My Label 2', 'My Label 3', 'My Label 4');
        $this->assertExpectedRowsOrder($expected, $table, 'label');

        $this->config->secondarySortOrder = SORT_DESC;
        $this->sorter->sort($table);

        $expected = array_reverse($expected);
        $this->assertExpectedRowsOrder($expected, $table, 'label');
    }

    public function testSortShoudSortEmptyValuesBySecondColumnIfSortedNumeric()
    {
        $table = $this->createDataTableFromValues(array(null, null, null, null, null));

        $this->config->primarySortFlags = SORT_NUMERIC;
        $this->config->secondaryColumnToSort = 'label';
        $this->config->secondarySortOrder = SORT_ASC;
        $this->config->secondarySortFlags = SORT_NATURAL;

        $this->sorter->sort($table);

        // we make sure the labels order did not change neither when ASC nor DESC
        $expected = array('My Label 0', 'My Label 1', 'My Label 2', 'My Label 3', 'My Label 4');
        $this->assertExpectedRowsOrder($expected, $table, 'label');

        $this->config->secondarySortOrder = SORT_DESC;
        $this->sorter->sort($table);

        $expected = array_reverse($expected);
        $this->assertExpectedRowsOrder($expected, $table, 'label');
    }

    private function assertExpectedRowsOrder($expectedValuesOrder, $table, $column = 'nb_visits')
    {
        foreach ($table->getRows() as $index => $row) {
            $this->assertSame($expectedValuesOrder[$index], $row->getColumn($column));
        }
    }

    private function createDataTableFromValues($values)
    {
        $rows = array();
        foreach ($values as $index => $value) {
            $rows[] = array('nb_visits' => $value, 'label' => 'My Label ' . $index);
        }

        return $this->createDataTable($rows);
    }

    private function createDataTable($rows)
    {
        $table = new DataTable();
        foreach ($rows as $columns) {
            $table->addRow($this->createRow($columns));
        }
        return $table;
    }

    private function createRow($columns)
    {
        return new Row(array(Row::COLUMNS => $columns));
    }
}
