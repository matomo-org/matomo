<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Unit;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group AppendNameToColumnNamesTest
 * @group DataTable
 * @group Filter
 * @group Goals
 */
class AppendNameToColumnNamesTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'Piwik\Plugins\Goals\DataTable\Filter\AppendNameToColumnNames';

    /**
     * @var DataTable
     */
    private $table;

    public function setUp(): void
    {
        $this->table = new DataTable\Simple();
        $this->addRow(array('nb_visits' => 1, 'nb_conversions' => 5, 'revenue' => 10, 'conversion_rate' => 20));
    }

    private function addRow($columns)
    {
        $this->table->addRow($this->buildRow($columns));
    }

    private function buildRow($columns)
    {
        return new Row(array(Row::COLUMNS => $columns));
    }

    public function test_filter_shouldNotAppendAnything_IfNameToReplaceIsEmpty()
    {
        $columnNamesBefore = array('nb_visits', 'nb_conversions', 'revenue', 'conversion_rate');

        $this->table->filter($this->filter, array(''));
        $this->table->filter($this->filter, array(null));
        $this->table->filter($this->filter, array(false));

        $columnNamesAfter = array_keys($this->table->getFirstRow()->getColumns());
        $this->assertSame($columnNamesBefore, $columnNamesAfter);
    }

    public function test_filter_shoulAppendGivenStringToAllColumns_IfSet()
    {
        $nameToAppend = '_new_visit';
        $this->table->filter($this->filter, array($nameToAppend));

        $expected = array(
            'nb_visits' . $nameToAppend => 1,
            'nb_conversions' . $nameToAppend => 5,
            'revenue' . $nameToAppend => 10,
            'conversion_rate' . $nameToAppend => 20
        );

        $this->assertColumnsOfRowIdEquals($expected, $rowId = 0);
    }

    public function test_filter_shoulAppendGivenStringToAllColumnsOfAllRows_EvenIfTheyHaveDifferentColumns()
    {
        $this->addRow(array('nb_visits' => 49));

        $nameToAppend = '_new_visit';
        $this->table->filter($this->filter, array($nameToAppend));

        $expectedRow1 = array(
            'nb_visits' . $nameToAppend => 1,
            'nb_conversions' . $nameToAppend => 5,
            'revenue' . $nameToAppend => 10,
            'conversion_rate' . $nameToAppend => 20
        );

        $expectedRow2 = array(
            'nb_visits' . $nameToAppend => 49,
        );

        $this->assertColumnsOfRowIdEquals($expectedRow1, $rowId = 0);
        $this->assertColumnsOfRowIdEquals($expectedRow2, $rowId = 1);
    }

    private function assertColumnsOfRowIdEquals($expectedColumns, $rowId)
    {
        $this->assertSame($expectedColumns, $this->table->getRowFromId($rowId)->getColumns());
    }
}
