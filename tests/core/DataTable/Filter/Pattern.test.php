<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
require_once 'DataTable.php';

class Test_Piwik_DataTable_Filter_Pattern extends UnitTestCase
{	
	/**
	 * Test to filter a column with a pattern
	 */
	 function test_filter_Pattern()
	 {
	 	$table = new Piwik_DataTable;
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$rows = array(
	  		array( $idcol => array('label'=>'google')),
	  		array( $idcol => array('label'=>'ask')),
	  		array( $idcol => array('label'=>'piwik')),
	  		array( $idcol => array('label'=>'yahoo')),
	  		array( $idcol => array('label'=>'amazon')),
	  		array( $idcol => array('label'=>'2389752/47578949')),
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))'))
	  	);
	  	$table->addRowsFromArray( $rows );
	  	$rowIds = array_keys($rows);
	  	
	  	$tests = array(
	  		array('ask', array(1)),
	  		array('oo', array(0,3)),
	  		array('^yah', array(3)),
	  		array('\*', array(6)),
	  		array('2/4', array(5)),
	  		array('amazon|yahoo', array(3,4)),
	  	);
	  	
	  	foreach($tests as $test) {
	  		$pattern = $test[0];
	  		$expectedRows = $test[1];
	  		$rowToDelete = array_diff($rowIds, $expectedRows);
	  		$expectedtable = clone $table;
		 	$expectedtable->deleteRows($rowToDelete);
	  		$filteredTable = clone $table;
	  		$filteredTable->filter('Pattern', array('label', $pattern));
		  	$this->assertEqual($filteredTable->getRows(), $expectedtable->getRows(), "pattern search failed for pattern $pattern");
	  	}
	 }
}