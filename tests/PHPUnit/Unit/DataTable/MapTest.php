<?php

namespace Piwik\Tests\Unit\DataTable;

use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class MapTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Manager::getInstance()->deleteAll();
    }

    private function createTestDataTable()
    {
        $result = new DataTable();

        $result->addRowsFromArray(array(
                                       array(Row::COLUMNS => array('label' => 'row1', 'col1' => 1)),
                                       array(Row::COLUMNS => array('label' => 'row2', 'col1' => 2))
                                  ));

        return $result;
    }

    private function createInstanceWithDataTables()
    {
        $dataTable = new DataTable\Map();

        $subDataTable1 = $this->createTestDataTable();
        $dataTable->addTable($subDataTable1, 'subDataTable1');

        $subDataTable2 = $this->createTestDataTable();
        $dataTable->addTable($subDataTable2, 'subDataTable2');

        return $dataTable;
    }

    private function createInstanceWithDataTableMaps()
    {
        $dataTable = new DataTable\Map();

        $subDataTableMap1 = $this->createInstanceWithDataTables();
        $dataTable->addTable($subDataTableMap1, 'subArray1');

        $subDataTableMap2 = $this->createInstanceWithDataTables();
        $dataTable->addTable($subDataTableMap2, 'subArray2');

        return $dataTable;
    }

    /**
     * Tests that Set::mergeChildren works when the DataTable\Map contains DataTables.
     * @group Core
     */
    public function test_MergeChildrenDataTable()
    {
        $dataTable = $this->createInstanceWithDataTables();

        $result = $dataTable->mergeChildren();

        // check that the result is a DataTable w/ 4 rows
        $this->assertInstanceOf('\Piwik\DataTable', $result);
        $this->assertEquals(4, $result->getRowsCount());

        // check that the first two rows have 'subDataTable1' as the label
        $this->mergeChildren_checkRow($result->getRowFromId(0), 'subDataTable1', 1);
        $this->mergeChildren_checkRow($result->getRowFromId(1), 'subDataTable1', 2);

        // check that the last two rows have 'subDataTable2' as the label
        $this->mergeChildren_checkRow($result->getRowFromId(2), 'subDataTable2', 1);
        $this->mergeChildren_checkRow($result->getRowFromId(3), 'subDataTable2', 2);
    }

    private function mergeChildren_checkRow(Row $row, $expectedLabel, $expectedColumnValue)
    {
        $this->assertEquals($expectedLabel, $row->getColumn('label'));
        $this->assertEquals($expectedColumnValue, $row->getColumn('col1'));
    }

    /**
     * Tests that Set::mergeChildren works when the DataTable\Map contains DataTable\Maps.
     * @group Core
     */
    public function testMergeChildrenDataTableMap()
    {
        $dataTable = $this->createInstanceWithDataTableMaps();

        $result = $dataTable->mergeChildren();

        // check that the result is a DataTable\Map w/ two DataTable children
        $this->assertInstanceOf('\Piwik\DataTable\Map', $result);
        $this->assertEquals(2, $result->getRowsCount());

        // check that the first sub-DataTable is a DataTable with 4 rows
        $subDataTable1 = $result->getTable('subDataTable1');
        $this->assertTrue($subDataTable1 instanceof DataTable);
        $this->assertEquals(4, $subDataTable1->getRowsCount());

        // check that the first two rows of the first sub-table have 'subArray1' as the label
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(0), 'subArray1', 1);
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(1), 'subArray1', 2);

        // check that the last two rows of the first sub-table have 'subArray2' as the label
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(2), 'subArray2', 1);
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(3), 'subArray2', 2);

        // check that the second sub-DataTable is a DataTable with 4 rows
        $subDataTable2 = $result->getTable('subDataTable2');
        $this->assertTrue($subDataTable2 instanceof DataTable);
        $this->assertEquals(4, $subDataTable2->getRowsCount());

        // check that the first two rows of the second sub-table have 'subArray1' as the label
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(0), 'subArray1', 1);
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(1), 'subArray1', 2);

        // check that the last two rows of the second sub-table have 'subArray2' as the label
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(2), 'subArray2', 1);
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(3), 'subArray2', 2);
    }
}
