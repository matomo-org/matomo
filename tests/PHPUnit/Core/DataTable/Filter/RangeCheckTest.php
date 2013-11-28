<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable\Filter\RangeCheck;
use Piwik\DataTable;
use Piwik\DataTable\Row;

class DataTable_Filter_RangeCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testRangeCheckNormalDataTable()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
                                      array(Row::COLUMNS => array('label' => 'ask', 'count' => 3)), // --> 5
                                      array(Row::COLUMNS => array('label' => 'nintendo', 'count' => 5)), // --> 5
                                      array(Row::COLUMNS => array('label' => 'test', 'count' => 7.5)), // --> 7.5
                                      array(Row::COLUMNS => array('label' => 'google', 'count' => 9)), // --> 9
                                      array(Row::COLUMNS => array('label' => 'yahoo', 'count' => 10.1) // --> 10
                                      )));
        $filter = new RangeCheck($table, 'count', 5, 10);
        $filter->filter($table);
        $expectedOrder = array(5, 5, 7.5, 9, 10);

        $this->assertEquals($expectedOrder, $table->getColumn('count'));
    }

    /**
     * @group Core
     */
    public function testRangeCheckNormalDataTableNonIntegerValues()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
                                      array(Row::COLUMNS => array('label' => 'ask', 'count' => '3')), // 3 is below minimum
                                      array(Row::COLUMNS => array('label' => 'nintendo', 'count' => 'test')), // no number is below minimum
                                      array(Row::COLUMNS => array('label' => 'test', 'count' => 0x1232)), // number is over maximum
                                      array(Row::COLUMNS => array('label' => 'piwik', 'count' => 0x005)), // converted to 5 is ok
                                      array(Row::COLUMNS => array('label' => 'google', 'count' => '9test')), // converted to 9 is ok, so string will be kept
                                      array(Row::COLUMNS => array('label' => 'yahoo', 'count' => 'test4') // can't be converted to number
                                      )));
        $filter = new RangeCheck($table, 'count', 3.97, 10);
        $filter->filter($table);
        $expectedOrder = array(3.97, 3.97, 10, 5, '9test', 3.97);

        $this->assertEquals($expectedOrder, $table->getColumn('count'));
    }
}
