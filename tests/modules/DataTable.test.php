<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";

Zend_Loader::loadClass('Piwik_DataTable');
Zend_Loader::loadClass('Piwik_DataTable_Row');
Zend_Loader::loadClass('Piwik_DataTable_Filter');

class Test_Piwik_DataTable extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
	}
	
	public function tearDown()
	{
	}
	
	/**
	 * General tests that tries to test the normal behaviour of DataTable
	 * 
	 * We create some tables, add rows, some of the rows link to sub tables
	 * 
	 * Then we serialize everything, and we check that the unserialize give the same object back
	 */
	function test_general()
	{
	  	/*
	  	 * create some fake tables to make sure that the serialized array of the first TABLE
	  	 * does not take in consideration those tables
	  	 */
	  	$useless1 = new Piwik_DataTable;
	  	$useless1->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 13,),));
		/*
		 * end fake tables
		 */
		
		/*
		 * MAIN TABLE
		 */
		$table = new Piwik_DataTable;
		$subtable = new Piwik_DataTable;
		$idtable = $table->getId();
		$idsubtable = $subtable->getId();
		
	  	/*
	  	 * create some fake tables to make sure that the serialized array of the first TABLE
	  	 * does not take in consideration those tables
	  	 */
	  	$useless2 = new Piwik_DataTable;
	  	$useless1->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 8487,),));
	  	$useless3 = new Piwik_DataTable;
	  	$useless3->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 8487,),));
		/*
		 * end fake tables
		 */
		
		$row = array(Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,	1 => 42,	2 => 657,3 => 155744,),
	  				Piwik_DataTable_Row::DETAILS => array('logo' => 'test.png'));
	  	$row = new Piwik_DataTable_Row($row);
	  	
	  	$table->addRow($row);
	  	$table->addRow(array( Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,1 => 42,),
	  						Piwik_DataTable_Row::DETAILS => array('url' => 'piwik.org')));
		
	  	$table->addRow(array( Piwik_DataTable_Row::COLUMNS => array( 0 => 787877888787,),
	  						Piwik_DataTable_Row::DETAILS => array('url' => 'OUPLA ADDED'),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable));
		  	
		/*
		 * SUB TABLE
		 */				
	  									
		
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,),
	  						Piwik_DataTable_Row::DETAILS => array('searchengine' => 'google'),
	  					);
	  	$subtable->addRow($row);
	  	
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 84894,),
	  						Piwik_DataTable_Row::DETAILS => array('searchengine' => 'yahoo'),
	  					);
	  	$subtable->addRow($row);
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 4898978989,),
	  						Piwik_DataTable_Row::DETAILS => array('searchengine' => 'ask'),
	  					);	  	
	  	$subtable->addRow($row);
	  	
	  	
	  	/*
	  	 * SUB SUB TABLE
	  	 */
	  	$subsubtable = new Piwik_DataTable;
	  	$subsubtable->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 245),
	  						Piwik_DataTable_Row::DETAILS => array('yes' => 'subsubdetail1'),)
	  						);  	
	  						
	  	$subsubtable->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 13,),
	  						Piwik_DataTable_Row::DETAILS => array('yes' => 'subsubdetail2'),)
	  						);
	  						
		$row = array( 	Piwik_DataTable_Row::COLUMNS => array( 0 => 666666666666666,),
	  						Piwik_DataTable_Row::DETAILS => array('url' => 'NEW ROW ADDED'),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subsubtable);
	  	
	  	$subtable->addRow($row);
		
		$idsubsubtable = $subsubtable->getId();
	  	
	  	
	  	$serialized = ($table->getSerialized());
	  	
		$this->assertEqual(array_keys($serialized), array($idsubsubtable,$idsubtable,$idtable));
		$tableAfter = new Piwik_DataTable;
		$tableAfter->loadFromSerialized($serialized[$idtable]);
		$this->assertEqual($table->getRows(),$tableAfter->getRows());

		$subsubtableAfter = new Piwik_DataTable;
		$subsubtableAfter->loadFromSerialized($serialized[$idsubsubtable]);
		$this->assertEqual($subsubtable->getRows(),$subsubtableAfter->getRows());
		
		
		$this->assertEqual($table, Piwik_DataTable_Manager::getInstance()->getTable($idtable));
		$this->assertEqual($subsubtable, Piwik_DataTable_Manager::getInstance()->getTable($idsubsubtable));
		
	}
	
	
	/**
	 *  test with a row without child
	 */
	function test_rendererConsoleSimple()
	{
		
	  	$table = new Piwik_DataTable;
	  	$table->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::DETAILS => array('logo' => 'test.png'),)
	  	
	  	);  	
	  	
	  	$expected="- 1 ['visits' => 245, 'visitors' => 245] ['logo' => test.png] [idsubtable = ]<br>\n";
	  	
	  	/*
	  	 * RENDER
	  	 */
	  	$render = new Piwik_DataTable_Renderer_Console ($table);
	  	$rendered = $render->render();
	  	
	  	$this->assertEqual($expected,$rendered);
	  	
	}
	
	/**
	 *  test with a row without child
	 * 			  a row with a child that has a child
	 * 			  a row with w child
	 */
	function test_rendererConsole2SubLevelAnd2Different()
	{
		
	  	$table = new Piwik_DataTable;
	  	$idtable = $table->getId();
	  	$table->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::DETAILS => array('logo' => 'test.png'),)
	  	
	  	);  
	  	
	  		
	  	$subsubtable = new Piwik_DataTable;
	  	$idsubsubtable = $subsubtable->getId();
	  	$subsubtable->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>2)));
	  		
	  	$subtable = new Piwik_DataTable;
	  	$idsubtable1 = $subtable->getId();
	  	$subtable->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>1),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subsubtable));
	  	
	  	$table->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>3),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable)
	  						);  
	  	
	  	$subtable = new Piwik_DataTable;
	  	$idsubtable2 = $subtable->getId();
	  	$subtable->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>5),));
	  	
	  	$table->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>9),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable)
	  						);  
	  	
	  	
	  	$expected=
"- 1 ['visits' => 245, 'visitors' => 245] ['logo' => test.png] [idsubtable = ]<br>
- 2 ['visits' => 3] [] [idsubtable = $idsubtable1]<br>
*- 1 ['visits' => 1] [] [idsubtable = $idsubsubtable]<br>
**- 1 ['visits' => 2] [] [idsubtable = ]<br>
- 3 ['visits' => 9] [] [idsubtable = $idsubtable2]<br>
*- 1 ['visits' => 5] [] [idsubtable = ]<br>
";
	  	/*
	  	 * RENDER
	  	 */
	  	$render = new Piwik_DataTable_Renderer_Console ($table);
	  	$render->setPrefixRow('*');
	  	$rendered = $render->render();
	  	
	  	$this->assertEqual($expected,$rendered);
	}
	
	/**
	 * Simple test of the DataTable_Row
	 */
	function test_Row()
	{
		Zend_Loader::loadClass('Piwik_Timer');
		$columns = array('test_column'=> 145,
						092582495 => new Piwik_Timer,
						'super'=>array('this column has an array value, amazing'));
		$details = array('logo'=> 'piwik.png',
						'super'=>array('this column has an array value, amazing'));
		$arrayRow = array(
			Piwik_DataTable_Row::COLUMNS => $columns,
	  		Piwik_DataTable_Row::DETAILS => $details,
	  		'fake useless key'=>38959,
	  		43905724897=>'value');
		$row = new Piwik_DataTable_Row($arrayRow);
		
		$this->assertEqual($row->getColumns(), $columns);
		$this->assertEqual($row->getDetails(), $details);
		$this->assertEqual($row->getIdSubDataTable(), null);
		
	}
	
	/**
	 * Test serialize with an infinite recursion (a row linked to a table in the parent hierarchy)
	 * After 100 recursion must throw an exception
	 */
	function test_serializeWithInfiniteRecursion()
	{
		
	  	$table = new Piwik_DataTable;
	  	$table->addRow(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $table,));
	  						
	  	
    	try {
    		$table->getSerialized();
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
            return;
        }
	}
	
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
	  	
	  	$table->loadFromArray( $rows );
	  	
	  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(1,2,4,5,6));
	  	
	  	$filter = new Piwik_DataTable_Filter_Pattern($table, 'label', '(oo)');
	  		  	
	  	$this->assertEqual($table->getRows(), $expectedtable->getRows());
	 }
	/**
	 * Test to filter a column with a pattern
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
	  	
	  	$table->loadFromArray( $rows );
	  		  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(0,1,5,6));
	  	
	  	$filter = new Piwik_DataTable_Filter_Limit($table, 2, 4);
	  	$this->assertEqual(array_values($table->getRows()), array_values($expectedtable->getRows()));
	 }
	
}