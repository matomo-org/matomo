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

class ColumnDeleteTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'ColumnDelete';

    protected function makeDataTable($appendRowWithSubtable = true)
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row1', 'visits' => 0, 'arrayColumn' => array('visits' => 0, 'columnWithin' => 10))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row2', 'visits' => 1, 'arrayColumn' => array('visits' => 1, 'columnWithin' => 11))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row3', 'visits' => 2, 'arrayColumn' => array('visits' => 2, 'columnWithin' => 12))));

        if ($appendRowWithSubtable) {
            $subTable = $this->makeDataTable($appendRowWithSubtable = false);
            $table->addRowFromArray(array(
                Row::COLUMNS => array('label' => 'row4', 'visits' => 3, 'arrayColumn' => array('visits' => 3, 'columnWithin' => 13)),
                Row::DATATABLE_ASSOCIATED => $subTable
            ));
        }
        return $table;
    }

    protected function makeDataTableWithoutVisitsColumn($appendRowWithSubtable = true)
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row1', 'arrayColumn' => array('columnWithin' => 10))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row2', 'arrayColumn' => array('columnWithin' => 11))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row3', 'arrayColumn' => array('columnWithin' => 12))));

        if ($appendRowWithSubtable) {
            $subTable = $this->makeDataTableWithoutVisitsColumn($appendRowWithSubtable = false);
            $table->addRowFromArray(array(
                Row::COLUMNS => array('label' => 'row4', 'arrayColumn' => array('columnWithin' => 13)),
                Row::DATATABLE_ASSOCIATED => $subTable
            ));
        }
        return $table;
    }

    protected function makeDataTableShowOnlyVisitColumn($appendRowWithSubtable = true)
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array( 'label' => 'row1', 'visits' => 0)));
        $table->addRowFromArray(array(Row::COLUMNS => array( 'label' => 'row2', 'visits' => 1)));
        $table->addRowFromArray(array(Row::COLUMNS => array( 'label' => 'row3', 'visits' => 2)));

        if ($appendRowWithSubtable) {
            $subTable = $this->makeDataTableShowOnlyVisitColumn($appendRowWithSubtable = false);
            $table->addRowFromArray(array(
                Row::COLUMNS => array('label' => 'row4', 'visits' => 3),
                Row::DATATABLE_ASSOCIATED => $subTable
            ));
        }
        return $table;
    }

    protected function assertSameDataTable(DataTable $table1, DataTable $table2)
    {
        $this->assertTrue(DataTable::isEqual($table1, $table2), var_export($table1->getRows(), true) . ' different from ' . var_export($table2, true));
    }

    public function testFilterDataTableRemoveNonExistingColumn()
    {
        $table = $this->makeDataTable();
        $table->filter($this->filter, array('does-not-exist'));

        $this->assertSameDataTable($this->makeDataTable(), $table);
    }

    public function testFilterDataTableRemoveExistingColumn()
    {
        $table = $this->makeDataTable();
        $table->filter($this->filter, array('visits', array(), false, true));

        $this->assertSameDataTable($this->makeDataTableWithoutVisitsColumn(), $table);
    }

    public function testFilterDataTableKeepColumn()
    {
        $table = $this->makeDataTable();
        $table->filter($this->filter, array($hide = '', $show = 'visits'));

        $this->assertSameDataTable($this->makeDataTableShowOnlyVisitColumn(), $table);
    }

    public function testFilterArrayRemoveNonExistingColumn()
    {
        $array = $this->makeArray();

        $columnDelete = new DataTable\Filter\ColumnDelete(new DataTable(), $hideColumns = 'non-existing-column', $showColumns = array());
        $filteredArray = $columnDelete->filter($array);

        $this->assertSame($array, $filteredArray);
    }

    public function testFilterArrayRemoveExistingColumn()
    {
        $columnDelete = new DataTable\Filter\ColumnDelete(new DataTable(), $hideColumns = 'visits', $showColumns = array(), false, true);
        $filteredArray = $columnDelete->filter($this->makeArray());

        $this->assertSame($this->makeArrayWithoutVisitsColumns(), $filteredArray);
    }

    public function testFilterArrayKeepColumn()
    {
        $columnDelete = new DataTable\Filter\ColumnDelete(new DataTable(), $hideColumns = '', $showColumns = 'visits');
        $filteredArray = $columnDelete->filter($this->makeArray());

        $this->assertSame($this->makeArrayShowVisitsColumns(), $filteredArray);
    }

    /**
     * @return array
     */
    protected function makeArray()
    {
        $array = array(
            array('label' => 'row1', 'visits' => 1, 'arrayColumn' => array('visits' => 0, 'columnWithin' => 10)),
            array('label' => 'row2', 'visits' => 2, 'arrayColumn' => array('visits' => 1, 'columnWithin' => 11)),
            array('label' => 'row3', 'visits' => 3, 'arrayColumn' => array('visits' => 2, 'columnWithin' => 12)),
        );
        return $array;
    }

    /**
     * @return array
     */
    protected function makeArrayWithoutVisitsColumns()
    {
        return array(
            array('label' => 'row1', 'arrayColumn' => array('columnWithin' => 10)),
            array('label' => 'row2', 'arrayColumn' => array('columnWithin' => 11)),
            array('label' => 'row3', 'arrayColumn' => array('columnWithin' => 12)),
        );
    }

    /**
     * @return array
     */
    protected function makeArrayShowVisitsColumns()
    {
        return array(
            array('label' => 'row1', 'visits' => 1),
            array('label' => 'row2', 'visits' => 2),
            array('label' => 'row3', 'visits' => 3),
        );
    }
}
