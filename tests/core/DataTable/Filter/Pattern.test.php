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
	  		array( $idcol => array('label'=>'238975247578949')),
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))')));
	  	
	  	$table->addRowsFromArray( $rows );
	  	
	  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(1,2,4,5,6));
	  	
	  	$filter = new Piwik_DataTable_Filter_Pattern($table, 'label', 'oo');
	  		  	
	  	$this->assertEqual($table->getRows(), $expectedtable->getRows());
	 }
	/**
	 * Test to filter a column with a pattern
	 */
	 function test_filter_Pattern2()
	 {
	 	$table = new Piwik_DataTable;
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$rows = array(
	  		array( $idcol => array('label'=>'google')),
	  		array( $idcol => array('label'=>'ask')),
	  		array( $idcol => array('label'=>'piwik')),
	  		array( $idcol => array('label'=>'yahoo')),
	  		array( $idcol => array('label'=>'amazon')),
	  		array( $idcol => array('label'=>'238975247578949')),
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))')));
	  	
	  	$table->addRowsFromArray( $rows );
	  	
	  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(0,1,2,3,4,5));
	  	
	  	$filter = new Piwik_DataTable_Filter_Pattern($table, 'label', '*');
	  		  	
	  	$this->assertEqual($table->getRows(), $expectedtable->getRows());
	 }
}
