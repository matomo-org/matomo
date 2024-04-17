<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class PatternRecursiveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Returns a data table for testing
     * @return DataTable
     */
    protected function getTable()
    {
        $subtableAskPath1 = new DataTable();
        $subtableAskPath1->addRowsFromArray(array(
                                                 array(Row::COLUMNS => array('label' => 'path1-index-page.html')),
                                                 array(Row::COLUMNS => array('label' => 'another-page')),
                                            ));

        $subtableAsk = new DataTable();
        $subtableAsk->addRowsFromArray(array(
                                            array(Row::COLUMNS              => array('label' => 'path1'),
                                                  Row::DATATABLE_ASSOCIATED => $subtableAskPath1),
                                            array(Row::COLUMNS => array('label' => 'index.html')),
                                       ));

        $table = new DataTable();
        $rows = array(
            array(Row::COLUMNS              => array('label' => 'http://www.ask.com'),
                  Row::DATATABLE_ASSOCIATED => $subtableAsk),
            array(Row::COLUMNS => array('label' => 'yahoo')),
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
