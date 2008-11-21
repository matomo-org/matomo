<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../../../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT . "/tests/config_test.php";
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
}