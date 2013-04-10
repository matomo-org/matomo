<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Filter_AddSummaryRowTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testOffsetIsCountSummaryRowShouldBeTheRow()
    {
        $table = $this->getDataTableCount5();
        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 5);
        $filter->filter($table);
        $this->assertEquals(5, $table->getRowsCount());
        $this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $this->getRow4()));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testOffsetIsLessThanCountSummaryRowShouldBeTheSum()
    {
        $table = $this->getDataTableCount5();
        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 2);
        $filter->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $expectedRow = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
        $this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $expectedRow));
        // check that column 'label' is forced to be first in summary row
        $this->assertEquals(array_keys($table->getLastRow()->getColumns()), array_keys($expectedRow->getColumns()));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testOffsetIsMoreThanCountShouldNotAddSummaryRow()
    {
        $table = $this->getDataTableCount5();
        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 6);
        $filter->filter($table);
        $this->assertEquals(5, $table->getRowsCount());
        $this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $this->getRow4()));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testWhenThereIsAlreadyASummaryRowShouldReplaceTheSummaryRow()
    {
        $table = $this->getDataTableCount5();
        $filter1 = new Piwik_DataTable_Filter_AddSummaryRow($table, 3);
        $filter1->filter($table);
        $filter2 = new Piwik_DataTable_Filter_AddSummaryRow($table, 2);
        $filter2->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $expectedRow = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
        $this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $expectedRow));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testSumTablesWithSummaryRowShouldSumTheSummaryRow()
    {
        // row0, row1, row2, rowSummary1
        $table1 = $this->getDataTableCount5();
        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table1, 3);
        $filter->filter($table1);

        // row0, row1, rowSummary2
        $table2 = $this->getDataTableCount5();
        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table2, 2);
        $filter->filter($table2);

        // we expect row0+row0, row1+row1, row2, rowSummary1+rowSummary2
        $expectedTable = new Piwik_DataTable;
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'amazon', 'nb' => 20000))));
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'nb' => 2000))));
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'piwik', 'nb' => 100))));
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 122))));

        $table1->addDataTable($table2);
        $this->assertTrue(Piwik_DataTable::isEqual($expectedTable, $table1));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testAddOneTableWithSummaryRow()
    {
        // row0, row1, row2, rowSummary1
        $table1 = $this->getDataTableCount5();
        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table1, 3);
        $filter->filter($table1);

        // row0, row1, row2, row3, row4
        $table2 = $this->getDataTableCount5();

        // we expect row0+row0, row1+row1, row2+row2, row3, row4, rowSummary1
        $expectedTable = new Piwik_DataTable;
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'amazon', 'nb' => 20000))));
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'nb' => 2000))));
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'piwik', 'nb' => 200))));
        $expectedTable->addRow($this->getRow3());
        $expectedTable->addRow($this->getRow4());
        $expectedTable->addRow(new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 11))));

        $table1->addDataTable($table2);
        $this->assertTrue(Piwik_DataTable::isEqual($expectedTable, $table1));

    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_AddSummaryRow
     */
    public function testWhenRowsInRandomOrderButSortSpecifiedShouldComputeSummaryRowAfterSort()
    {
        $table = new Piwik_DataTable;
        $table->addRow($this->getRow3());
        $table->addRow($this->getRow2());
        $table->addRow($this->getRow4());
        $table->addRow($this->getRow1());
        $table->addRow($this->getRow0());

        $filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 2, Piwik_DataTable::LABEL_SUMMARY_ROW, $columnToSortBy = 'nb');
        $filter->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $expectedRow = new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
        $this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $expectedRow));
    }

    /**
     * Returns table used for the tests
     *
     * @return Piwik_DataTable
     */
    protected function getDataTableCount5()
    {
        $table = new Piwik_DataTable;
        $table->addRow($this->getRow0());
        $table->addRow($this->getRow1());
        $table->addRow($this->getRow2());
        $table->addRow($this->getRow3());
        $table->addRow($this->getRow4());
        return $table;
    }

    protected function getRow0()
    {
        return new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('nb' => 10000, 'label' => 'amazon')));
    }

    protected function getRow1()
    {
        return new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'nb' => 1000)));
    }

    protected function getRow2()
    {
        return new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'piwik', 'nb' => 100)));
    }

    protected function getRow3()
    {
        return new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'nb' => 10)));
    }

    protected function getRow4()
    {
        return new Piwik_DataTable_Row(array(Piwik_DataTable_Row::COLUMNS => array('nb' => 1, 'label' => 'google')));
    }
}
