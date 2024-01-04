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

class ColumnDeleteTest extends \PHPUnit\Framework\TestCase
{
    private $filter = 'ColumnDelete';

    protected function makeDataTable($appendRowWithSubtable = true)
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row1', 'visits' => 0, 'arrayColumn' => array('visits' => 0, 'columnWithin' => 10))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row2', 'visits' => 1, 'arrayColumn' => array('visits' => 1, 'columnWithin' => 11))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row3', 'visits' => 2, 'arrayColumn' => array('visits' => 2, 'columnWithin' => 12))));

        if($appendRowWithSubtable) {
            $subTable = $this->makeDataTable( $appendRowWithSubtable = false );
            $table->addRowFromArray(array(
                Row::COLUMNS => array('label' => 'row4', 'visits' => 3, 'arrayColumn' => array('visits' => 3, 'columnWithin' => 13)),
                Row::DATATABLE_ASSOCIATED => $subTable
            ));
        }
        return $table;
    }

    protected function makeDataTable_withoutVisitsColumn($appendRowWithSubtable = true)
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row1', 'arrayColumn' => array('columnWithin' => 10))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row2', 'arrayColumn' => array('columnWithin' => 11))));
        $table->addRowFromArray(array(Row::COLUMNS => array('label' => 'row3', 'arrayColumn' => array('columnWithin' => 12))));

        if($appendRowWithSubtable) {
            $subTable = $this->makeDataTable_withoutVisitsColumn( $appendRowWithSubtable = false );
            $table->addRowFromArray(array(
                Row::COLUMNS => array('label' => 'row4', 'arrayColumn' => array('columnWithin' => 13)),
                Row::DATATABLE_ASSOCIATED => $subTable
            ));
        }
        return $table;
    }

    protected function makeDataTable_showOnlyVisitColumn($appendRowWithSubtable = true)
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS => array( 'label' => 'row1', 'visits' => 0)));
        $table->addRowFromArray(array(Row::COLUMNS => array( 'label' => 'row2', 'visits' => 1)));
        $table->addRowFromArray(array(Row::COLUMNS => array( 'label' => 'row3', 'visits' => 2)));

        if($appendRowWithSubtable) {
            $subTable = $this->makeDataTable_showOnlyVisitColumn( $appendRowWithSubtable = false );
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

    public function test_filter_DataTable_removeNonExistingColumn()
    {
        $table = $this->makeDataTable();
        $table->filter($this->filter, array('does-not-exist'));

        $this->assertSameDataTable($this->makeDataTable(), $table);
    }

    public function test_filter_DataTable_removeExistingColumn()
    {
        $table = $this->makeDataTable();
        $table->filter($this->filter, array('visits', array(), false, true));

        $this->assertSameDataTable($this->makeDataTable_withoutVisitsColumn(), $table);
    }

    public function test_filter_DataTable_keepColumn()
    {
        $table = $this->makeDataTable();
        $table->filter($this->filter, array($hide = '', $show = 'visits'));

        $this->assertSameDataTable($this->makeDataTable_showOnlyVisitColumn(), $table);
    }

    public function test_filter_array_removeNonExistingColumn()
    {
        $array = $this->makeArray();

        $columnDelete = new DataTable\Filter\ColumnDelete(new DataTable(), $hideColumns = 'non-existing-column', $showColumns = array());
        $filteredArray = $columnDelete->filter($array);

        $this->assertSame($array, $filteredArray);
    }

    public function test_filter_array_removeExistingColumn()
    {
        $columnDelete = new DataTable\Filter\ColumnDelete(new DataTable(), $hideColumns = 'visits', $showColumns = array(), false, true);
        $filteredArray = $columnDelete->filter($this->makeArray());

        $this->assertSame($this->makeArray_withoutVisitsColumns(), $filteredArray);
    }

    public function test_filter_array_keepColumn()
    {
        $columnDelete = new DataTable\Filter\ColumnDelete(new DataTable(), $hideColumns = '', $showColumns = 'visits');
        $filteredArray = $columnDelete->filter($this->makeArray());

        $this->assertSame($this->makeArray_showVisitsColumns(), $filteredArray);
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
    protected function makeArray_withoutVisitsColumns()
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
    protected function makeArray_showVisitsColumns()
    {
        return array(
            array('label' => 'row1', 'visits' => 1),
            array('label' => 'row2', 'visits' => 2),
            array('label' => 'row3', 'visits' => 3),
        );
    }
}
