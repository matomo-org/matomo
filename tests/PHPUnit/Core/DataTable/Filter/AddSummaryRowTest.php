<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable;
use Piwik\DataTable\Filter\AddSummaryRow;
use Piwik\DataTable\Row;

class DataTable_Filter_AddSummaryRowTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group AddSummaryRow
     * @group Core
     */
    public function testAddSummaryRow()
    {
        $dataTable = $this->getDataTableCount5();
        $dataTable->filter('AddSummaryRow');
        $this->assertEquals(6, $dataTable->getRowsCount());
        $expectedColumns = array(
            'label' => '-1',
            'nb' => 11111
        );
        $this->assertEquals($expectedColumns, $dataTable->getLastRow()->getColumns());
    }

    /**
     * Returns table used for the tests
     *
     * @return DataTable
     */
    protected function getDataTableCount5()
    {
        $table = new DataTable;
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