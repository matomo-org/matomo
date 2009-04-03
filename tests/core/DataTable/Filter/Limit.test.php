<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
require_once 'DataTable.php';

class Test_Piwik_DataTable_Filter_Limit extends UnitTestCase
{	
	/**
	 * Returns table used for the tests
	 *
	 * @return Piwik_DataTable
	 */
	protected function getDataTableCount10()
	{
		$table = new Piwik_DataTable;
		$idcol = Piwik_DataTable_Row::COLUMNS;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google', 	'idRow' => 0)),
	  		array( $idcol => array('label'=>'ask', 		'idRow' => 1)),
	  		array( $idcol => array('label'=>'piwik', 	'idRow' => 2)),
	  		array( $idcol => array('label'=>'yahoo', 	'idRow' => 3)),
	  		array( $idcol => array('label'=>'amazon', 	'idRow' => 4)),
	  		array( $idcol => array('label'=>'238949', 	'idRow' => 5)),
	  		array( $idcol => array('label'=>'test', 	'idRow' => 6)),
	  		array( $idcol => array('label'=>'amazing', 	'idRow' => 7)),
	  		array( $idcol => array('label'=>'great', 	'idRow' => 8)),
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol => array('label'=>'summary row',	'idRow' => 9)),
	  		);
	  	$table->addRowsFromArray( $rows );
	  	return $table;
	}
	
	public function test_normal()
	{
		$offset = 2;
		$limit = 3;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 3);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 2);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 4);
	}
	
	public function test_limitLessThanCount_shouldReturnCountLimit()
	{
		$offset = 2;
		$limit = 7;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 7);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 2);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 8);
	}
	
	public function test_limitIsCount_shouldNotDeleteAnything()
	{
		$offset = 0;
		$limit = 10;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 10);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 0);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 9);
	}
	
	public function test_limitGreaterThanCount_shouldReturnCountUntilCount()
	{
		$offset = 5;
		$limit = 20;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 5);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 5);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 9);
	}
	
	public function test_limitIsNull_shouldReturnCountIsOffset()
	{
		$offset = 1;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset);
		$this->assertEqual($table->getRowsCount(), 9);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 1);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 9);
	}
	
	public function test_offsetJustBeforeSummaryRow_shouldJustReturnSummaryRow()
	{
		$offset = 9;
		$limit = 1;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 1);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 9);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 9);
	}
	
	public function test_offsetJustBeforeSummaryRowWithBigLimit_shouldJustReturnSummaryRow()
	{
		$offset = 9;
		$limit = 100;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 1);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 9);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 9);
	}

	public function test_offsetBeforeSummaryRow_shouldJustReturnRowAndSummaryRow()
	{
		$offset = 8;
		$limit = 3;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 2);
		$this->assertEqual($table->getFirstRow()->getColumn('idRow'), 8);
		$this->assertEqual($table->getLastRow()->getColumn('idRow'), 9);
	}
	
	public function test_offsetGreaterThanCount_shouldReturnEmptyTable()
	{
		$offset = 10;
		$limit = 10;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 0);
	}
	
	public function test_limitIsZero_shouldReturnEmptyTable()
	{
		$offset = 0;
		$limit = 0;
		$table = $this->getDataTableCount10();
		$filter = new Piwik_DataTable_Filter_Limit($table, $offset, $limit);
		$this->assertEqual($table->getRowsCount(), 0);
	}
	
		/**
	 * Test to filter a table with a offset, limit
	 */
	 function test_filter_OffsetLimit()
	 {
	 	$table = new Piwik_DataTable;
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$rows = array(
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'ask')),//1
	  		array( $idcol => array('label'=>'piwik')),//2
	  		array( $idcol => array('label'=>'yahoo')),//3
	  		array( $idcol => array('label'=>'amazon')),//4
	  		array( $idcol => array('label'=>'238975247578949')),//5
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))'))//6
	  		);
	  	
	  	$table->addRowsFromArray( $rows );
	  		  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(0,1,6));
	  	
	  	$filter = new Piwik_DataTable_Filter_Limit($table, 2, 4);
	  	
	  	$colAfter=$colExpected=array();
	  	foreach($table->getRows() as $row) $colAfter[] = $row->getColumn('label');
	  	foreach($expectedtable->getRows() as $row) $colExpected[] = $row->getColumn('label');
	  	
	  	$this->assertEqual(array_values($table->getRows()), array_values($expectedtable->getRows()),
	  		implode(", ",array_values($colAfter)) ." does not match the expected ".implode(", ",array_values($colExpected)) );
	 }
	/**
	 * Test to filter a column with a offset, limit off bound
	 */
	 function test_filter_OffsetLimitOffbound()
	 {
	 	$table = new Piwik_DataTable;
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$rows = array(
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'ask')),//1
	  		array( $idcol => array('label'=>'piwik')),//2
	  		array( $idcol => array('label'=>'yahoo')),//3
	  		array( $idcol => array('label'=>'amazon')),//4
	  		array( $idcol => array('label'=>'238975247578949')),//5
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))'))//6
	  		);
	  	
	  	$table->addRowsFromArray( $rows );
	  		  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(0,1,3,4,5,6));
	  	
	  	$filter = new Piwik_DataTable_Filter_Limit($table, 2, 1);
	  	
	  	$colAfter=$colExpected=array();
	  	foreach($table->getRows() as $row) $colAfter[] = $row->getColumn('label');
	  	foreach($expectedtable->getRows() as $row) $colExpected[] = $row->getColumn('label');
	  	
	  	$this->assertEqual(array_values($table->getRows()), array_values($expectedtable->getRows()));
	 }
	/**
	 * Test to filter a column with a offset, limit 2
	 */
	 function test_filter_OffsetLimit2()
	 {
	 	$table = new Piwik_DataTable;
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$rows = array(
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'ask')),//1
	  		array( $idcol => array('label'=>'piwik')),//2
	  		array( $idcol => array('label'=>'yahoo')),//3
	  		array( $idcol => array('label'=>'amazon')),//4
	  		array( $idcol => array('label'=>'238975247578949')),//5
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))'))//6
	  		);
	  	
	  	$table->addRowsFromArray( $rows );
	  		  	
	 	$expectedtable = clone $table;
	  	
	  	$filter = new Piwik_DataTable_Filter_Limit($table, 0, 15);
	  	
	  	$colAfter=$colExpected=array();
	  	foreach($table->getRows() as $row) $colAfter[] = $row->getColumn('label');
	  	foreach($expectedtable->getRows() as $row) $colExpected[] = $row->getColumn('label');
	  	
	  	$this->assertEqual(array_values($table->getRows()), array_values($expectedtable->getRows()));
	 }
	
	/**
	 * Test to filter a column with a offset, limit 3
	 */
	 function test_filter_OffsetLimit3()
	 {
	 	$table = new Piwik_DataTable;
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$rows = array(
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'ask')),//1
	  		array( $idcol => array('label'=>'piwik')),//2
	  		array( $idcol => array('label'=>'yahoo')),//3
	  		array( $idcol => array('label'=>'amazon')),//4
	  		array( $idcol => array('label'=>'238975247578949')),//5
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))'))//6
	  		);
	  	
	  	$table->addRowsFromArray( $rows );
	  		  	
	 	$expectedtable = new Piwik_DataTable;
	  	
	  	$filter = new Piwik_DataTable_Filter_Limit($table, 8, 15);
	  	
	  	$colAfter=$colExpected=array();
	  	foreach($table->getRows() as $row) $colAfter[] = $row->getColumn('label');
	  	foreach($expectedtable->getRows() as $row) $colExpected[] = $row->getColumn('label');
	  	
	  	$this->assertEqual(array_values($table->getRows()), array_values($expectedtable->getRows()));
	 }
	 
}
