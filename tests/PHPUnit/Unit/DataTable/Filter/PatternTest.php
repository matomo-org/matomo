<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 * @group Core
 */
class PatternTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Dataprovider for testFilterPattern
     */
    public function getTestData()
    {
        return array(
            array('ask', array(1)),
            array('oo', array(0, 3)),
            array('^yah', array(3)),
            array('\*', array(6)),
            array('2/4', array(5)),
            array('amazon|yahoo', array(3, 4)),
        );
    }

    /**
     * Test to filter a column with a pattern
     *
     * _Pattern
     * @dataProvider getTestData
     */
    public function testFilterPattern($pattern, $expectedRows)
    {
        $table = new DataTable();

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

        $rowToDelete = array_diff($rowIds, $expectedRows);
        $expectedtable = clone $table;
        $expectedtable->deleteRows($rowToDelete);
        $filteredTable = clone $table;
        $filteredTable->filter('Pattern', array('label', $pattern));
        $this->assertEquals($expectedtable->getRows(), $filteredTable->getRows());
    }

    /**
     * @dataProvider getTestData
     */
    public function testFilterArrayPattern_OneColumn($pattern, $expectedRows)
    {
        $rows = array(
            array('label' => 'google'),
            array('label' => 'ask'),
            array('label' => 'piwik'),
            array('label' => 'yahoo'),
            array('label' => 'amazon'),
            array('label' => '2389752/47578949'),
            array('label' => 'Q*(%&*("$&%*(&"$*")"))')
        );

        $filter = new DataTable\Filter\Pattern(new DataTable(), array('label'), $pattern);
        $filteredRows = $filter->filterArray($rows);

        $this->assertSame($expectedRows, array_keys($filteredRows));
    }

    /**
     * @dataProvider getTestDataTwoColumns
     */
    public function testFilterArrayPattern_ShouldBeAbleToFilterByTwoColumns_IfMultipleColumnsAreGiven($pattern, $expectedRows)
    {
        $rows = array(
            0 => array('randomcolumn' => 'ask', 'name' => 'google', 'url' => 'www.google.com'),
            1 => array('randomcolumn' => 'goo', 'name' => 'ask', 'url' => 'www.ask.com'),
            2 => array('randomcolumn' => 'com', 'name' => 'piwik', 'url' => 'piwik.org'),
            3 => array('randomcolumn' => 'com', 'name' => 'yahoo', 'url' => 'nz.yahoo.com'),
            4 => array('randomcolumn' => 'nz1', 'name' => 'amazon', 'url' => 'amazon.com'),
            5 => array('randomcolumn' => 'com', 'url' => 'nz.piwik.org'),
            6 => array('randomcolumn' => 'com', 'name' => 'Piwik')
        );

        $filter = new DataTable\Filter\Pattern(new DataTable(), array('name', 'url'), $pattern);
        $filteredRows = $filter->filterArray($rows);

        $this->assertSame($expectedRows, array_keys($filteredRows));
    }

    /**
     * Dataprovider for testFilterPattern
     */
    public function getTestDataTwoColumns()
    {
        return array(
            array('ask', array(1)),
            array('oo', array(0, 3)),
            array('^yah', array(3)),
            array('nz', array(3, 5)),
            array('amazon|yahoo', array(3, 4)),
            array('piwik', array(2, 5, 6)),
            array('com', array(0, 1, 3, 4)),
            array('org', array(2, 5)),
        );
    }
}
