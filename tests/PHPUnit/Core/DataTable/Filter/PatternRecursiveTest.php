<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Filter_PatternRecursiveTest extends PHPUnit_Framework_TestCase
{
    /**
     * Returns a data table for testing
     * @return Piwik_DataTable
     */
    protected function getTable()
    {
        $subtableAskPath1 = new Piwik_DataTable();
        $subtableAskPath1->addRowsFromArray(array(
                                                 array(Piwik_DataTable_Row::COLUMNS => array('label' => 'path1-index-page.html')),
                                                 array(Piwik_DataTable_Row::COLUMNS => array('label' => 'another-page')),
                                            ));

        $subtableAsk = new Piwik_DataTable();
        $subtableAsk->addRowsFromArray(array(
                                            array(Piwik_DataTable_Row::COLUMNS              => array('label' => 'path1'),
                                                  Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtableAskPath1),
                                            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'index.html')),
                                       ));

        $table = new Piwik_DataTable;
        $rows = array(
            array(Piwik_DataTable_Row::COLUMNS              => array('label' => 'http://www.ask.com'),
                  Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtableAsk),
            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo')),
        );
        $table->addRowsFromArray($rows);
        return $table;
    }

    /**
     * Dataprovider for testFilterPattern
     */
    public function getTestData()
    {
        return array(
            // level 0
            array(array('hoo', array(1))),
            // level 1
            array(array('path1', array(0))),
            // level 2
            array(array('path1-index-page', array(0))),
            // not found
            array(array('not found', array())),
        );
    }

    /**
     * Test to filter a column with a pattern
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_PatternRecursive
     * @dataProvider getTestData
     */
    public function testFilterPattern($test)
    {
        $table = $this->getTable();
        $rowIds = array_keys($table->getRows());
        $pattern = $test[0];
        $expectedRows = $test[1];
        $rowToDelete = array_diff($rowIds, $expectedRows);
        $expectedtable = clone $table;
        $expectedtable->deleteRows($rowToDelete);
        $filteredTable = clone $table;
        $filteredTable->filter('PatternRecursive', array('label', $pattern));
        $this->assertEquals($expectedtable->getRows(), $filteredTable->getRows());
    }
}
