<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
require_once 'DataTable.php';

class Test_Piwik_DataTable_Filter_AddSummaryRow extends UnitTestCase
{	
	public function test_offsetIsCount_summaryRowShouldBeTheRow()
	{
		$table = $this->getDataTableCount5();
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 5);
		$this->assertEqual($table->getRowsCount(), 5);
		$this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $this->getRow4()));
	}
	
	public function test_offsetIsLessThanCount_SummaryRowShouldBeTheSum()
	{
		$table = $this->getDataTableCount5();
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 2);
		$this->assertEqual($table->getRowsCount(), 3);
		$expectedRow = new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
		$this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $expectedRow));
		// check that column 'label' is forced to be first in summary row
		$this->assertEqual(array_keys($table->getLastRow()->getColumns()), array_keys($expectedRow->getColumns()));
	}
	
	public function test_offsetIsMoreThanCount_shouldNotAddSummaryRow()
	{
		$table = $this->getDataTableCount5();
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 6);
		$this->assertEqual($table->getRowsCount(), 5);
		$this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $this->getRow4()));
	}
	
	public function test_whenThereIsAlreadyASummaryRow_shouldReplaceTheSummaryRow()
	{
		$table = $this->getDataTableCount5();
		$filter1 = new Piwik_DataTable_Filter_AddSummaryRow($table, 3);
		$filter2 = new Piwik_DataTable_Filter_AddSummaryRow($table, 2);
		$this->assertEqual($table->getRowsCount(), 3);
		$expectedRow = new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
		$this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $expectedRow));
	}
	
	public function test_sumTablesWithSummaryRow_shouldSumTheSummaryRow()
	{
		// row0, row1, row2, rowSummary1
		$table1 = $this->getDataTableCount5();
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table1, 3);
		
		// row0, row1, rowSummary2
		$table2 = $this->getDataTableCount5();
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table2, 2);
		
		// we expect row0+row0, row1+row1, row2, rowSummary1+rowSummary2
		$expectedTable = new Piwik_DataTable;
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'amazon', 'nb' => 20000) )));
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'yahoo', 'nb' => 2000) )));
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'piwik', 'nb' => 100) )));
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 122) )));
		
		$table1->addDataTable($table2);
		$this->assertTrue(Piwik_DataTable::isEqual($expectedTable, $table1));
	}
	
	public function test_addOneTableWithSummaryRow()
	{
		// row0, row1, row2, rowSummary1
		$table1 = $this->getDataTableCount5();
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table1, 3);
		
		// row0, row1, row2, row3, row4
		$table2 = $this->getDataTableCount5();
		
		// we expect row0+row0, row1+row1, row2+row2, row3, row4, rowSummary1
		$expectedTable = new Piwik_DataTable;
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'amazon', 'nb' => 20000) )));
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'yahoo', 'nb' => 2000) )));
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'piwik', 'nb' => 200) )));
		$expectedTable->addRow( $this->getRow3());
		$expectedTable->addRow( $this->getRow4());
		$expectedTable->addRow( new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 11))));
		
		$table1->addDataTable($table2);
		$this->assertTrue(Piwik_DataTable::isEqual($expectedTable, $table1));
		
	}
	
	public function test_whenRowsInRandomOrderButSortSpecified_shouldComputeSummaryRowAfterSort()
	{
		$table = new Piwik_DataTable;
		$table->addRow( $this->getRow3() );
		$table->addRow( $this->getRow2() );
		$table->addRow( $this->getRow4() );
		$table->addRow( $this->getRow1() );
		$table->addRow( $this->getRow0() );
		
		$filter = new Piwik_DataTable_Filter_AddSummaryRow($table, 2, Piwik_DataTable::LABEL_SUMMARY_ROW, $columnToSortBy = 'nb');
		$this->assertEqual($table->getRowsCount(), 3);
		$expectedRow = new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'nb' => 111)));
		$this->assertTrue(Piwik_DataTable_Row::isEqual($table->getLastRow(), $expectedRow));
	}
	
	/**
	 * Returns table used for the tests
	 *
	 * @return Piwik_DataTable
	 */
	protected function getDataTableCount5()
	{
		$table = new Piwik_DataTable;
		$table->addRow( $this->getRow0() );
		$table->addRow( $this->getRow1() );
		$table->addRow( $this->getRow2() );
		$table->addRow( $this->getRow3() );
		$table->addRow( $this->getRow4() );
	  	return $table;
	}
	protected function getRow0()
	{
		return new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('nb' => 10000, 'label'=>'amazon')));
	}
	protected function getRow1()
	{
		return new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'yahoo', 'nb' => 1000)));
	}
	protected function getRow2()
	{
		return new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'piwik', 'nb' => 100)));
	}
	protected function getRow3()
	{
		return new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('label'=>'ask', 'nb' => 10)));
	}
	protected function getRow4()
	{
		return new Piwik_DataTable_Row(array( Piwik_DataTable_Row::COLUMNS => array('nb' => 1, 'label'=>'google')));
	}
}
