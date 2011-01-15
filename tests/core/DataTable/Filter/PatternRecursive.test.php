<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../../tests/config_test.php";
}
require_once 'DataTable.php';

class Test_Piwik_DataTable_Filter_PatternRecursive extends UnitTestCase
{	
	protected function getTable()
	{
		$subtableAskPath1 = new Piwik_DataTable();
		$subtableAskPath1->addRowsFromArray(array (
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'path1-index-page.html') ),
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'another-page') ),
		));
		
		$subtableAsk = new Piwik_DataTable();
		$subtableAsk->addRowsFromArray(array (
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'path1'),
					Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtableAskPath1),
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'index.html') ),
		));
		
	 	$table = new Piwik_DataTable;
	  	$rows = array(
	  		array(  Piwik_DataTable_Row::COLUMNS => array('label'=>'http://www.ask.com'),
	  				Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtableAsk),
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>'yahoo')),
	  	);
	  	$table->addRowsFromArray( $rows );
	  	return $table;
	}
	/**
	 * Test to filter a column with a pattern
	 */
	 function test_filter_Pattern()
	 {
	  	$tests = array(
	  		// level 0 
	  		array('hoo', array(1)),
	  		// level 1
	  		array('path1', array(0)),
	  		// level 2
	  		array('path1-index-page', array(0)),
	  		// not found 
	  		array('not found', array()),
	  	);
	  	
	  	foreach($tests as $test) {
	  		$table = $this->getTable();
	  		$rowIds = array_keys($table->getRows());
	  		$pattern = $test[0];
	  		$expectedRows = $test[1];
	  		$rowToDelete = array_diff($rowIds, $expectedRows);
	  		$expectedtable = clone $table;
		 	$expectedtable->deleteRows($rowToDelete);
	  		$filteredTable = clone $table;
	  		$filteredTable->filter('PatternRecursive', array('label', $pattern));
		  	$this->assertEqual($filteredTable->getRows(), $expectedtable->getRows(), 
		  						"pattern search failed for pattern $pattern | Got table: " . $filteredTable);
	  	}
	 }
}
