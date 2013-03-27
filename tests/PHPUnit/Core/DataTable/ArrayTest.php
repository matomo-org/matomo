<?php
class Test_Piwik_DataTable_Array extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Piwik::createConfigObject();
        Piwik_Config::getInstance()->setTestEnvironment();
        Piwik_DataTable_Manager::getInstance()->deleteAll();
    }

    private function createTestDataTable()
    {
        $result = new Piwik_DataTable();

        $result->addRowsFromArray(array(
                                       array(Piwik_DataTable_Row::COLUMNS => array('label' => 'row1', 'col1' => 1)),
                                       array(Piwik_DataTable_Row::COLUMNS => array('label' => 'row2', 'col1' => 2))
                                  ));

        return $result;
    }

    private function createInstanceWithDataTables()
    {
        $dataTable = new Piwik_DataTable_Array();

        $subDataTable1 = $this->createTestDataTable();
        $dataTable->addTable($subDataTable1, 'subDataTable1');

        $subDataTable2 = $this->createTestDataTable();
        $dataTable->addTable($subDataTable2, 'subDataTable2');

        return $dataTable;
    }

    private function createInstanceWithDataTableArrays()
    {
        $dataTable = new Piwik_DataTable_Array();

        $subDataTableArray1 = $this->createInstanceWithDataTables();
        $dataTable->addTable($subDataTableArray1, 'subArray1');

        $subDataTableArray2 = $this->createInstanceWithDataTables();
        $dataTable->addTable($subDataTableArray2, 'subArray2');

        return $dataTable;
    }

    /**
     * Tests that Piwik_DataTable_Array::mergeChildren works when the DataTable_Array contains DataTables.
     * @group Core
     * @group DataTable
     * @group DataTable_Array
     */
    public function test_MergeChildrenDataTable()
    {
        $dataTable = $this->createInstanceWithDataTables();

        $result = $dataTable->mergeChildren();

        // check that the result is a DataTable w/ 4 rows
        $this->assertInstanceOf('Piwik_DataTable', $result);
        $this->assertEquals(4, $result->getRowsCount());

        // check that the first two rows have 'subDataTable1' as the label
        $this->mergeChildren_checkRow($result->getRowFromId(0), 'subDataTable1', 1);
        $this->mergeChildren_checkRow($result->getRowFromId(1), 'subDataTable1', 2);

        // check that the last two rows have 'subDataTable2' as the label
        $this->mergeChildren_checkRow($result->getRowFromId(2), 'subDataTable2', 1);
        $this->mergeChildren_checkRow($result->getRowFromId(3), 'subDataTable2', 2);
    }

    private function mergeChildren_checkRow($row, $expectedLabel, $expectedColumnValue)
    {
        $this->assertEquals($expectedLabel, $row->getColumn('label'));
        $this->assertEquals($expectedColumnValue, $row->getColumn('col1'));
    }

    /**
     * Tests that Piwik_DataTable_Array::mergeChildren works when the DataTable_Array contains DataTable_Arrays.
     * @group Core
     * @group DataTable
     * @group DataTable_Array
     */
    public function testMergeChildrenDataTableArray()
    {
        $dataTable = $this->createInstanceWithDataTableArrays();

        $result = $dataTable->mergeChildren();

        // check that the result is a DataTable_Array w/ two DataTable children
        $this->assertInstanceOf('Piwik_DataTable_Array', $result);
        $this->assertEquals(2, $result->getRowsCount());

        // check that the first sub-DataTable is a DataTable with 4 rows
        $subDataTable1 = $result->getTable('subDataTable1');
        $this->assertTrue($subDataTable1 instanceof Piwik_DataTable);
        $this->assertEquals(4, $subDataTable1->getRowsCount());

        // check that the first two rows of the first sub-table have 'subArray1' as the label
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(0), 'subArray1', 1);
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(1), 'subArray1', 2);

        // check that the last two rows of the first sub-table have 'subArray2' as the label
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(2), 'subArray2', 1);
        $this->mergeChildren_checkRow($subDataTable1->getRowFromId(3), 'subArray2', 2);

        // check that the second sub-DataTable is a DataTable with 4 rows
        $subDataTable2 = $result->getTable('subDataTable2');
        $this->assertTrue($subDataTable2 instanceof Piwik_DataTable);
        $this->assertEquals(4, $subDataTable2->getRowsCount());

        // check that the first two rows of the second sub-table have 'subArray1' as the label
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(0), 'subArray1', 1);
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(1), 'subArray1', 2);

        // check that the last two rows of the second sub-table have 'subArray2' as the label
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(2), 'subArray2', 1);
        $this->mergeChildren_checkRow($subDataTable2->getRowFromId(3), 'subArray2', 2);
    }
}
