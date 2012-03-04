<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once 'DataTable.php';
require_once 'DataTable/Array.php';

class Test_Piwik_DataTable_Array extends UnitTestCase
{
	public function setUp()
	{
		parent::setUp();
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();	
		Piwik_DataTable_Manager::getInstance()->deleteAll();
	}
	
	public function tearDown()
	{
		parent::tearDown();
	}

	private function createTestDataTable()
	{
		$result = new Piwik_DataTable();

		$result->addRowsFromArray(array(
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'row1', 'col1' => 1)),
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'row2', 'col1' => 2))
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
		$subDataTableArray1->metadata['metadataKey1'] = 'metadataValue1';
		$dataTable->addTable($subDataTableArray1, 'subArray1');
		
		$subDataTableArray2 = $this->createInstanceWithDataTables();
		$dataTable->addTable($subDataTableArray2, 'subArray2');
		
		return $dataTable;
	}
	
	/** Tests that Piwik_DataTable_Array::mergeChildren works when the DataTable_Array contains DataTables. */
	public function test_mergeChildren_DataTable()
	{
		$dataTable = $this->createInstanceWithDataTables();
		
		$result = $dataTable->mergeChildren();
		
		// check that the result is a DataTable w/ 4 rows
		$this->assertTrue($result instanceof Piwik_DataTable);
		$this->assertEqual(4, $result->getRowsCount());
		
		// check that the first two rows have 'subDataTable1' as the label
		$this->mergeChildren_checkRow($result->getRowFromId(0), 'subDataTable1', 1);
		$this->mergeChildren_checkRow($result->getRowFromId(1), 'subDataTable1', 2);
		
		// check that the last two rows have 'subDataTable2' as the label
		$this->mergeChildren_checkRow($result->getRowFromId(2), 'subDataTable2', 1);
		$this->mergeChildren_checkRow($result->getRowFromId(3), 'subDataTable2', 2);
	}

	private function mergeChildren_checkRow($row, $expectedLabel, $expectedColumnValue)
	{
		$this->assertEqual($expectedLabel, $row->getColumn('label'));
		$this->assertEqual($expectedColumnValue, $row->getColumn('col1'));
	}
	
	/** Tests that Piwik_DataTable_Array::mergeChildren works when the DataTable_Array contains DataTable_Arrays. */
	public function test_mergeChildren_DataTableArray()
	{
		$dataTable = $this->createInstanceWithDataTableArrays();
		
		$result = $dataTable->mergeChildren();
		
		// check that the result is a DataTable_Array w/ two DataTable children
		$this->assertTrue($result instanceof Piwik_DataTable_Array);
		$this->assertEqual(2, $result->getRowsCount());

		// check that the result has one metadata, 'metadataKey1' => 'metadataValue1'
		$this->assertEqual(1, count($result->metadata));
		$this->assertEqual('metadataValue1', $result->metadata['metadataKey1']);

		// check that the first sub-DataTable is a DataTable with 4 rows
		$subDataTable1 = $result->getTable('subDataTable1');
		$this->assertTrue($subDataTable1 instanceof Piwik_DataTable);
		$this->assertEqual(4, $subDataTable1->getRowsCount());
		
		// check that the first two rows of the first sub-table have 'subArray1' as the label
		$this->mergeChildren_checkRow($subDataTable1->getRowFromId(0), 'subArray1', 1);
		$this->mergeChildren_checkRow($subDataTable1->getRowFromId(1), 'subArray1', 2);

		// check that the last two rows of the first sub-table have 'subArray2' as the label
		$this->mergeChildren_checkRow($subDataTable1->getRowFromId(2), 'subArray2', 1);
		$this->mergeChildren_checkRow($subDataTable1->getRowFromId(3), 'subArray2', 2);

		// check that the second sub-DataTable is a DataTable with 4 rows
		$subDataTable2 = $result->getTable('subDataTable2');
		$this->assertTrue($subDataTable2 instanceof Piwik_DataTable);
		$this->assertEqual(4, $subDataTable2->getRowsCount());
		
		// check that the first two rows of the second sub-table have 'subArray1' as the label
		$this->mergeChildren_checkRow($subDataTable2->getRowFromId(0), 'subArray1', 1);
		$this->mergeChildren_checkRow($subDataTable2->getRowFromId(1), 'subArray1', 2);

		// check that the last two rows of the second sub-table have 'subArray2' as the label
		$this->mergeChildren_checkRow($subDataTable2->getRowFromId(2), 'subArray2', 1);
		$this->mergeChildren_checkRow($subDataTable2->getRowFromId(3), 'subArray2', 2);
	}
}
