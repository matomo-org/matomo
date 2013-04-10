<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Filter_RangeCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_RangeCheck
     */
    public function testRangeCheckNormalDataTable()
    {
        $table = new Piwik_DataTable();
        $table->addRowsFromArray(array(
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'count' => 3)), // --> 5
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nintendo', 'count' => 5)), // --> 5
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'test', 'count' => 7.5)), // --> 7.5
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'google', 'count' => 9)), // --> 9
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'count' => 10.1) // --> 10
                                      )));
        $filter = new Piwik_DataTable_Filter_RangeCheck($table, 'count', 5, 10);
        $filter->filter($table);
        $expectedOrder = array(5, 5, 7.5, 9, 10);

        $this->assertEquals($expectedOrder, $table->getColumn('count'));
    }

    /**
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Filter
     * @group DataTable_Filter_RangeCheck
     */
    public function testRangeCheckNormalDataTableNonIntegerValues()
    {
        $table = new Piwik_DataTable();
        $table->addRowsFromArray(array(
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'ask', 'count' => '3')), // 3 is below minimum
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'nintendo', 'count' => 'test')), // no number is below minimum
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'test', 'count' => 0x1232)), // number is over maximum
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'piwik', 'count' => 0x005)), // converted to 5 is ok
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'google', 'count' => '9test')), // converted to 9 is ok, so string will be kept
                                      array(Piwik_DataTable_Row::COLUMNS => array('label' => 'yahoo', 'count' => 'test4') // can't be converted to number
                                      )));
        $filter = new Piwik_DataTable_Filter_RangeCheck($table, 'count', 3.97, 10);
        $filter->filter($table);
        $expectedOrder = array(3.97, 3.97, 10, 5, '9test', 3.97);

        $this->assertEquals($expectedOrder, $table->getColumn('count'));
    }
}
