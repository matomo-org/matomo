<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Filter;

use Piwik\DataTable\Filter\Truncate;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class TruncateTest extends \PHPUnit\Framework\TestCase
{

    public function testUnrelatedDataTableNotFiltered()
    {
        // remark: this unit test would become invalid and would need to be rewritten if
        // Truncate filter stops calling getRowsCount() on the DataTable being filtered.
        $mockedDataTable = $this->createPartialMock('\Piwik\DataTable', array('getRowsCount'));
        $mockedDataTable->expects($this->never())->method('getRowsCount');

        $dataTableBeingFiltered = new DataTable();
        $rowBeingFiltered = new Row();
        $dataTableBeingFiltered->addRow($rowBeingFiltered);

        // we simulate the fact that the value of Row::DATATABLE_ASSOCIATED retrieved
        // from the database is in conflict with one of the Manager managed table identifiers.
        // This is a rare but legitimate case as identifiers are not thoroughly synchronized
        // when the expanded parameter is false.
        $rowBeingFiltered->subtableId = $mockedDataTable->getId();

        $filter = new Truncate($dataTableBeingFiltered, 1);
        $filter->filter($dataTableBeingFiltered);
    }


    public function testForInfiniteRecursion()
    {
        $dataTableBeingFiltered = new DataTable();

        // remark: this unit test would become invalid and would need to be rewritten if
        // Truncate filter stops calling getIdSubDataTable() on rows associated with a SubDataTable
        $rowBeingFiltered = $this->createPartialMock('\Piwik\DataTable\Row', array('getIdSubDataTable'));
        $rowBeingFiltered->expects($this->never())->method('getIdSubDataTable');

        $dataTableBeingFiltered->addRow($rowBeingFiltered);

        // we simulate a legitimate but rare circular reference between a Row and its
        // enclosing DataTable.
        // This can happen because identifiers are not thoroughly synchronized when the expanded parameter
        // is false.
        $rowBeingFiltered->subtableId = $dataTableBeingFiltered->getId();

        $filter = new Truncate($dataTableBeingFiltered, 1);
        $filter->filter($dataTableBeingFiltered);
    }


    public function testOffsetIsCountSummaryRowShouldBeTheRow()
    {
        $table = $this->getDataTableCount5();
        $filter = new Truncate($table, 5);
        $filter->filter($table);
        $this->assertEquals(5, $table->getRowsCount());
        $this->assertTrue(Row::isEqual($table->getLastRow(), $this->getRow4()));
    }


    public function testOffsetIsLessThanCountSummaryRowShouldBeTheSum()
    {
        $table = $this->getDataTableCount5();
        $filter = new Truncate($table, 2);
        $filter->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $expectedRow = new Row(array(Row::COLUMNS => array('label' => DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
        $this->assertTrue(Row::isEqual($table->getLastRow(), $expectedRow));
        // check that column 'label' is forced to be first in summary row
        $this->assertEquals(array_keys($table->getLastRow()->getColumns()), array_keys($expectedRow->getColumns()));
    }


    public function testOffsetIsMoreThanCountShouldNotTruncate()
    {
        $table = $this->getDataTableCount5();
        $filter = new Truncate($table, 6);
        $filter->filter($table);
        $this->assertEquals(5, $table->getRowsCount());
        $this->assertTrue(Row::isEqual($table->getLastRow(), $this->getRow4()));
    }


    public function testWhenThereIsAlreadyASummaryRowShouldReplaceTheSummaryRow()
    {
        $table = $this->getDataTableCount5();
        $filter1 = new Truncate($table, 3);
        $filter1->filter($table);
        $filter2 = new Truncate($table, 2);
        $filter2->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $expectedRow = new Row(array(Row::COLUMNS => array('label' => DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
        $this->assertTrue(Row::isEqual($table->getLastRow(), $expectedRow));
    }


    public function testSumTablesWithSummaryRowShouldSumTheSummaryRow()
    {
        // row0, row1, row2, rowSummary1
        $table1 = $this->getDataTableCount5();
        $filter = new Truncate($table1, 3);
        $filter->filter($table1);

        // row0, row1, rowSummary2
        $table2 = $this->getDataTableCount5();
        $filter = new Truncate($table2, 2);
        $filter->filter($table2);

        // we expect row0+row0, row1+row1, row2, rowSummary1+rowSummary2
        $expectedTable = new DataTable();
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => 'amazon', 'nb' => 20000))));
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => 'yahoo', 'nb' => 2000))));
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => 'piwik', 'nb' => 100))));
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => DataTable::LABEL_SUMMARY_ROW, 'nb' => 122))));

        $table1->addDataTable($table2);
        $this->assertTrue(DataTable::isEqual($expectedTable, $table1));
    }


    public function testAddOneTableWithSummaryRow()
    {
        // row0, row1, row2, rowSummary1
        $table1 = $this->getDataTableCount5();
        $filter = new Truncate($table1, 3);
        $filter->filter($table1);

        // row0, row1, row2, row3, row4
        $table2 = $this->getDataTableCount5();

        // we expect row0+row0, row1+row1, row2+row2, row3, row4, rowSummary1
        $expectedTable = new DataTable();
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => 'amazon', 'nb' => 20000))));
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => 'yahoo', 'nb' => 2000))));
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => 'piwik', 'nb' => 200))));
        $expectedTable->addRow($this->getRow3());
        $expectedTable->addRow($this->getRow4());
        $expectedTable->addRow(new Row(array(Row::COLUMNS => array('label' => DataTable::LABEL_SUMMARY_ROW, 'nb' => 11))));

        $table1->addDataTable($table2);
        $this->assertTrue(DataTable::isEqual($expectedTable, $table1));

    }


    public function testWhenRowsInRandomOrderButSortSpecifiedShouldComputeSummaryRowAfterSort()
    {
        $table = new DataTable();
        $table->addRow($this->getRow3());
        $table->addRow($this->getRow2());
        $table->addRow($this->getRow4());
        $table->addRow($this->getRow1());
        $table->addRow($this->getRow0());

        $filter = new Truncate($table, 2, DataTable::LABEL_SUMMARY_ROW, $columnToSortBy = 'nb');
        $filter->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $expectedRow = new Row(array(Row::COLUMNS => array('label' => DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
        $this->assertTrue(Row::isEqual($table->getLastRow(), $expectedRow));
    }

    /**
     * Returns table used for the tests
     *
     * @return DataTable
     */
    protected function getDataTableCount5()
    {
        $table = new DataTable();
        $table->addRow($this->getRow0());
        $table->addRow($this->getRow1());
        $table->addRow($this->getRow2());
        $table->addRow($this->getRow3());
        $table->addRow($this->getRow4());
        return $table;
    }

    protected function getRow0()
    {
        return new Row(array(Row::COLUMNS => array('nb' => 10000, 'label' => 'amazon')));
    }

    protected function getRow1()
    {
        return new Row(array(Row::COLUMNS => array('label' => 'yahoo', 'nb' => 1000)));
    }

    protected function getRow2()
    {
        return new Row(array(Row::COLUMNS => array('label' => 'piwik', 'nb' => 100)));
    }

    protected function getRow3()
    {
        return new Row(array(Row::COLUMNS => array('label' => 'ask', 'nb' => 10)));
    }

    protected function getRow4()
    {
        return new Row(array(Row::COLUMNS => array('nb' => 1, 'label' => 'google')));
    }
}
