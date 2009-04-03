<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
require_once 'DataTable.php';

class Test_Piwik_DataTable_Filter_ExcludeLowPopulation extends UnitTestCase
{	
	protected function getTestDataTable()
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArray(
		array(
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'zero', 	'count' => 0)),
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'one', 	'count' => 1)),
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'onedotfive', 	'count' => 1.5)),
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'ten',		'count' => 10)),
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'ninety',	'count' => 90)),
			array(Piwik_DataTable_Row::COLUMNS => array('label'=>'hundred', 'count' => 100)),
			)
		);
		return $table;
	}
	public function test_standardTable()
	{
		$table = $this->getTestDataTable();	
		$filter = new Piwik_DataTable_Filter_ExcludeLowPopulation($table, 'count', 1.1);
		$this->assertEqual($table->getRowsCount(), 4);
		$this->assertEqual($table->getColumn('count'), array(1.5, 10, 90, 100));
	}
	public function test_filterEqualOne_doesFilter()
	{
		$table = $this->getTestDataTable();	
		$filter = new Piwik_DataTable_Filter_ExcludeLowPopulation($table, 'count', 1);
		$this->assertEqual($table->getRowsCount(), 5);
	}
	public function test_filterEqualZero_doesFilter()
	{
		$table = $this->getTestDataTable();	
		$filter = new Piwik_DataTable_Filter_ExcludeLowPopulation($table, 'count', 0);
		$this->assertEqual($table->getRowsCount(), 3);
		$this->assertEqual($table->getColumn('count'), array(10, 90, 100));
	}
	public function test_filterSpecifyExcludeLowPopulationThreshold_doesFilter()
	{
		$table = $this->getTestDataTable();	
		$filter = new Piwik_DataTable_Filter_ExcludeLowPopulation($table, 'count', 0, 0.4); //40%
		$this->assertEqual($table->getRowsCount(), 2);
		$this->assertEqual($table->getColumn('count'), array(90, 100));
	}
	

	
	/**
	 * Test to exclude low population filter
	 */
	 function test_filter_Lowpop1()
	 {
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$table = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google', 	'nb_visits' => 897)),//0
	  		array( $idcol => array('label'=>'ask', 		'nb_visits' => -152)),//1
	  		array( $idcol => array('label'=>'piwik', 	'nb_visits' => 1.5)),//2
	  		array( $idcol => array('label'=>'piwik2', 	'nb_visits' => 1.4)),//2
	  		array( $idcol => array('label'=>'yahoo', 	'nb_visits' => 154)),//3
	  		array( $idcol => array('label'=>'amazon', 	'nb_visits' => 30)),//4
	  		array( $idcol => array('label'=>'238949', 	'nb_visits' => 0)),//5
	  		array( $idcol => array('label'=>'Q*(%&*', 	'nb_visits' => 1)),//6
	  		array( $idcol => array('label'=>'Q*(%&*2', 	'nb_visits' => -1.5)),//6
	  		);
	  	$table->addRowsFromArray( $rows );
	  	
	  	$expectedtable = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google', 	'nb_visits' => 897)),//0
	 		array( $idcol => array('label'=>'piwik', 	'nb_visits' => 1.5)),//2
	  		array( $idcol => array('label'=>'piwik2', 	'nb_visits' => 1.4)),//2
	  		array( $idcol => array('label'=>'yahoo', 	'nb_visits' => 154)),//3
	  		array( $idcol => array('label'=>'amazon', 	'nb_visits' => 30)),//4
		);
	  	$expectedtable->addRowsFromArray( $rows );
	  	
	 	$filter = new Piwik_DataTable_Filter_ExcludeLowPopulation($table, 'nb_visits', 1.4);

	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtable));
	 }
}