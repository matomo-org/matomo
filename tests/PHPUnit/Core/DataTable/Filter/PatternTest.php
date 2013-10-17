<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable;
use Piwik\DataTable\Row;

class DataTable_Filter_PatternTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testFilterPattern
     */
    public function getTestData()
    {
        return array(
            array(array('ask', array(1))),
            array(array('oo', array(0, 3))),
            array(array('^yah', array(3))),
            array(array('\*', array(6))),
            array(array('2/4', array(5))),
            array(array('amazon|yahoo', array(3, 4))),
        );
    }

    /**
     * Test to filter a column with a pattern
     *
     * @group Core
     * _Pattern
     * @dataProvider getTestData
     */
    public function testFilterPattern($test)
    {
        $table = new DataTable;

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google')),
            array($idcol => array('label' => 'ask')),
            array($idcol => array('label' => 'piwik')),
            array($idcol => array('label' => 'yahoo')),
            array(Row::METADATA => array('label' => 'amazon')),
            array($idcol => array('label' => '2389752/47578949')),
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))'))
        );
        $table->addRowsFromArray($rows);
        $rowIds = array_keys($rows);

        $pattern = $test[0];
        $expectedRows = $test[1];
        $rowToDelete = array_diff($rowIds, $expectedRows);
        $expectedtable = clone $table;
        $expectedtable->deleteRows($rowToDelete);
        $filteredTable = clone $table;
        $filteredTable->filter('Pattern', array('label', $pattern));
        $this->assertEquals($expectedtable->getRows(), $filteredTable->getRows());
    }
}
