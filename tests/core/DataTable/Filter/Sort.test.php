<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
require_once 'DataTable.php';

class Test_Piwik_DataTable_Filter_Sort extends UnitTestCase
{	
	public function test_normalSortDescending()
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArray(
			array(
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'ask', 		'count' => 100)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'nintendo', 	'count' => 0)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'yahoo',		'count' => 10))
				)
			);
		$filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'desc');
		$expectedOrder = array('ask', 'yahoo', 'nintendo');
		$this->assertEqual($table->getColumn('label'), $expectedOrder);
	}
	
	public function test_normalSortAscending()
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArray(
			array(
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'ask', 		'count' => 100.5)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'nintendo', 	'count' => 0.5)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'yahoo',		'count' => 10.5))
				)
			);
		$filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'asc');
		$expectedOrder = array('nintendo', 'yahoo', 'ask');
		$this->assertEqual($table->getColumn('label'), $expectedOrder);
	}
	
	public function test_missingColumnValues_shouldAppearLast_afterSortAsc()
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArray(
			array(
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'nintendo', 	'count' => 1)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'nocolumn' 	)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'nocolumnbis' 	)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'ask', 		'count' => 2)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'amazing' 	)),
				Piwik_DataTable::ID_SUMMARY_ROW => array( Piwik_DataTable_Row::COLUMNS => array('label'=>'summary',	'count' => 10))
				)
			);
//		echo $table;
//		echo "<hr>";
		$filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'asc');
//		echo $table;
//		echo "<hr>";
		$expectedOrder = array('nintendo', 'ask', 'amazing', 'nocolumnbis', 'nocolumn', 'summary');
		$this->assertEqual($table->getColumn('label'), $expectedOrder);
	}
	
	public function test_missingColumnValues_shouldAppearLast_afterSortDesc()
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArray(
			array(
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'nintendo', 	'count' => 1)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'ask', 		'count' => 2)),
				array(Piwik_DataTable_Row::COLUMNS => array('label'=>'amazing' 	)),
				Piwik_DataTable::ID_SUMMARY_ROW => array( Piwik_DataTable_Row::COLUMNS => array('label'=>'summary',	'count' => 10))
				)
			);
		$filter = new Piwik_DataTable_Filter_Sort($table, 'count', 'desc');
		$expectedOrder = array('ask', 'nintendo', 'amazing', 'summary');
		$this->assertEqual($table->getColumn('label'), $expectedOrder);
	}
	

	/**
	 * Test to sort by label
	 */
	 function test_filter_SortString()
	 {
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$table = new Piwik_DataTable;
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
	 	$rows = array(
	  		array( $idcol => array('label'=>'238975247578949')),//5
	  		array( $idcol => array('label'=>'amazon')),//4
	  		array( $idcol => array('label'=>'ask')),//1
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'piwik')),//2
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))')),//6
	  		array( $idcol => array('label'=>'yahoo')),//3
	  		);
	  	$expectedtable->addRowsFromArray( $rows );
	  	
	  	$expectedtableReverse = new Piwik_DataTable;
	  	$expectedtableReverse->addRowsFromArray(array_reverse($rows));
	  		  	
	 	$filter = new Piwik_DataTable_Filter_Sort($table, 'label', 'asc');
	 	$this->assertTrue(Piwik_DataTable::isEqual($expectedtable,$table));
	  	
	  	$filter = new Piwik_DataTable_Filter_Sort($table, 'label', 'desc');
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtableReverse));
	 
	 }
	 

	/**
	 * Test to sort by visit
	 */
	 function test_filter_SortNumeric()
	 {
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$table = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google', 	'nb_visits' => 897)),//0
	  		array( $idcol => array('label'=>'ask', 		'nb_visits' => -152)),//1
	  		array( $idcol => array('label'=>'piwik', 	'nb_visits' => 1.5)),//2
	  		array( $idcol => array('label'=>'yahoo', 	'nb_visits' => 154)),//3
	  		array( $idcol => array('label'=>'amazon', 	'nb_visits' => 30)),//4
	  		array( $idcol => array('label'=>'238949', 	'nb_visits' => 0)),//5
	  		array( $idcol => array('label'=>'Q*(%&*', 	'nb_visits' => 1)),//6
	  		);
	  	$table->addRowsFromArray( $rows );
	  	
	  	$expectedtable = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'ask', 		'nb_visits' => -152)),//1
	  		array( $idcol => array('label'=>'238949', 	'nb_visits' => 0)),//5
	  		array( $idcol => array('label'=>'Q*(%&*', 	'nb_visits' => 1)),//6
	  		array( $idcol => array('label'=>'piwik', 	'nb_visits' => 1.5)),//2
	  		array( $idcol => array('label'=>'amazon', 	'nb_visits' => 30)),//4
	  		array( $idcol => array('label'=>'yahoo', 	'nb_visits' => 154)),//3
	  		array( $idcol => array('label'=>'google', 	'nb_visits' => 897)),//0
	  		);
	  	$expectedtable->addRowsFromArray( $rows );
	  	
	  	$expectedtableReverse = new Piwik_DataTable;
	  	$expectedtableReverse->addRowsFromArray(array_reverse($rows));
	  		  	
	 	$filter = new Piwik_DataTable_Filter_Sort($table, 'nb_visits', 'asc');
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtable));
	  	
	  	$filter = new Piwik_DataTable_Filter_Sort($table, 'nb_visits', 'desc');
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtableReverse));
	 }
}
