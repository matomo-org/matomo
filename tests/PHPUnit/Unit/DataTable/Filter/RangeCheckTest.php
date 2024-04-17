<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Filter;

use Piwik\DataTable\Filter\RangeCheck;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class RangeCheckTest extends \PHPUnit\Framework\TestCase
{
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

    public function testRangeCheckOnMetadata()
    {
        $table = new DataTable();
        $table->addRowsFromArray(array(
            array(
                Row::COLUMNS  => array('label' => 'foo'),
                Row::METADATA => array('count' => 5),
            ),
            array(
                Row::COLUMNS  => array('label' => 'bar'),
                Row::METADATA => array('count' => 10),
            ),
            array(
                Row::COLUMNS  => array('label' => 'bar'),
                Row::METADATA => array('count' => 15),
            ),
        ));

        $filter = new RangeCheck($table, 'count', 0, 10);
        $filter->filter($table);

        $this->assertEquals(array(5, 10, 10), $table->getRowsMetadata('count'));
    }
}
