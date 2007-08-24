<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

require_once 'DataTable.php';

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
	  	$useless1->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 13,),));
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
	  	 * create some fake tables to make sure 
	  	 * that the serialized array of the first TABLE
	  	 * does not take in consideration those tables
	  	 * (yes theres a story of an ID given by some DataTable_Manager
	  	 *  we check this module is not messing around)
	  	 */
	  	$useless2 = new Piwik_DataTable;
	  	$useless1->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 8487,),));
	  	$useless3 = new Piwik_DataTable;
	  	$useless3->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 8487,),));
		/*
		 * end fake tables
		 */
		
		$row = array(Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,	1 => 42,	2 => 657,3 => 155744,),
	  				Piwik_DataTable_Row::DETAILS => array('logo' => 'test.png'));
	  	$row = new Piwik_DataTable_Row($row);
	  	
	  	$table->addRow($row);
	  	$table->addRowFromArray(array( Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,1 => 42,),
	  						Piwik_DataTable_Row::DETAILS => array('url' => 'piwik.org')));
		
	  	$table->addRowFromArray(array( Piwik_DataTable_Row::COLUMNS => array( 0 => 787877888787,),
	  						Piwik_DataTable_Row::DETAILS => array('url' => 'OUPLA ADDED'),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable));
		  	
		/*
		 * SUB TABLE
		 */				
	  									
		
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,),
	  						Piwik_DataTable_Row::DETAILS => array('searchengine' => 'google'),
	  					);
	  	$subtable->addRowFromArray($row);
	  	
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 84894,),
	  						Piwik_DataTable_Row::DETAILS => array('searchengine' => 'yahoo'),
	  					);
	  	$subtable->addRowFromArray($row);
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 4898978989,),
	  						Piwik_DataTable_Row::DETAILS => array('searchengine' => 'ask'),
	  					);	  	
	  	$subtable->addRowFromArray($row);
	  	
	  	
	  	/*
	  	 * SUB SUB TABLE
	  	 */
	  	$subsubtable = new Piwik_DataTable;
	  	$subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 245),
	  						Piwik_DataTable_Row::DETAILS => array('yes' => 'subsubdetail1'),)
	  						);  	
	  						
	  	$subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 13,),
	  						Piwik_DataTable_Row::DETAILS => array('yes' => 'subsubdetail2'),)
	  						);
	  						
		$row = array( 	Piwik_DataTable_Row::COLUMNS => array( 0 => 666666666666666,),
	  						Piwik_DataTable_Row::DETAILS => array('url' => 'NEW ROW ADDED'),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subsubtable);
	  	
	  	$subtable->addRowFromArray($row);
		
		$idsubsubtable = $subsubtable->getId();
	  	
	  	
	  	$serialized = ($table->getSerialized());
	  	
		$this->assertEqual(array_keys($serialized), array($idsubsubtable,$idsubtable,0));
		$tableAfter = new Piwik_DataTable;
		$tableAfter->loadFromSerialized($serialized[0]);
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
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::DETAILS => array('logo' => 'test.png'),)
	  	
	  	);  	
	  	
	  	$expected="- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br>\n";
	  	
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
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::DETAILS => array('logo' => 'test.png'),)
	  	
	  	);  
	  	
	  		
	  	$subsubtable = new Piwik_DataTable;
	  	$idsubsubtable = $subsubtable->getId();
	  	$subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>2)));
	  		
	  	$subtable = new Piwik_DataTable;
	  	$idsubtable1 = $subtable->getId();
	  	$subtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>1),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subsubtable));
	  	
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>3),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable)
	  						);  
	  	
	  	$subtable2 = new Piwik_DataTable;
	  	$idsubtable2 = $subtable2->getId();
	  	$subtable2->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>5),));
	  	
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>9),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable2)
	  						);  
	  	
	  	
	  	$expected=
"- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br>
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
	 * Simple test of the DataTable_Row
	 */
	function test_sumRow()
	{
		Zend_Loader::loadClass('Piwik_Timer');
		$columns = array('test_int'=> 145,
						'test_float'=> 145.5,
						'test_float3'=> 1.5,
						'test_stringint'=> "145",
						"test" => 'string fake',
						'super'=>array('this column has an array value, amazing')
						);
		$details = array('logo'=> 'piwik.png',
						'super'=>array('this column has an array value, amazing'));
		$arrayRow = array(
			Piwik_DataTable_Row::COLUMNS => $columns,
	  		Piwik_DataTable_Row::DETAILS => $details,
	  		'fake useless key'=>38959,
	  		43905724897=>'value');
		$row1 = new Piwik_DataTable_Row($arrayRow);
		
		$columns2 = array('test_int'=> 5,
						'test_float'=> 4.5,
						'test_float2'=> 14.5,
						'test_stringint'=> "5",
						0925824 => 'toto',
						'super'=>array('this column has geagaean array value, amazing'));
		$finalRow = new Piwik_DataTable_Row( array(Piwik_DataTable_Row::COLUMNS => $columns2));

		$finalRow->sumRow($row1);


		$columnsWanted = array('test_int'=> 150,
						'test_float'=> 150.0,
						'test_float2'=> 14.5,
						'test_float3'=> 1.5,
						'test_stringint'=> "150", //add also strings!!
						'super'=>array('this column has geagaean array value, amazing'),
						0925824 => 'toto',
				);
		
		$rowWanted = new Piwik_DataTable_Row( array(Piwik_DataTable_Row::COLUMNS => $columnsWanted));

//		dump($rowWanted);
//		dump($finalRow);
		$this->assertTrue( Piwik_DataTable_Row::isEqual($rowWanted, $finalRow));
				
	}
	
	/**
	 * Test serialize with an infinite recursion (a row linked to a table in the parent hierarchy)
	 * After 100 recursion must throw an exception
	 */
	function test_serializeWithInfiniteRecursion()
	{
		
	  	$table = new Piwik_DataTable;
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
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
	 * Test to filter a column with a offset, limit
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
	  	
	  	$table->loadFromArray( $rows );
	  		  	
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
	  	
	  	$table->loadFromArray( $rows );
	  		  	
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
	  	
	  	$table->loadFromArray( $rows );
	  		  	
	 	$expectedtable = new Piwik_DataTable;
	  	
	  	$filter = new Piwik_DataTable_Filter_Limit($table, 8, 15);
	  	
	  	$colAfter=$colExpected=array();
	  	foreach($table->getRows() as $row) $colAfter[] = $row->getColumn('label');
	  	foreach($expectedtable->getRows() as $row) $colExpected[] = $row->getColumn('label');
	  	
	  	$this->assertEqual(array_values($table->getRows()), array_values($expectedtable->getRows()));
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
	  	$table->loadFromArray( $rows );
	  	
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
	  	$expectedtable->loadFromArray( $rows );
	  	
	  	$expectedtableReverse = new Piwik_DataTable;
	  	$expectedtableReverse->loadFromArray(array_reverse($rows));
	  		  	
	 	$filter = new Piwik_DataTable_Filter_Sort($table, 'label', 'asc');
	 	$this->assertTrue(Piwik_DataTable::isEqual($expectedtable,$table));
	  	
	  	$filter = new Piwik_DataTable_Filter_Sort($table, 'label', 'desc');
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtableReverse));
	 
	 }
	
	/**
	 * Test to sort by label queing the filter
	 */
	 function test_filter_Queue_SortString()
	 {
	 	
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	
	  	$table = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'tsk')),//1
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))')),//2
	  		);
	  	$table->loadFromArray( $rows );
	  	
	  	$expectedtable = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google')),//0
	  		array( $idcol => array('label'=>'Q*(%&*("$&%*(&"$*")"))')),//2
	  		array( $idcol => array('label'=>'tsk')),//1
	  		);
	  	$expectedtable->loadFromArray( $rows );
	  	
	  	$expectedtableReverse = new Piwik_DataTable;
	  	$expectedtableReverse->loadFromArray(array_reverse($rows));
	  	
		$tableCopy = clone $table;
		$this->assertTrue(Piwik_DataTable::isEqual($tableCopy, $table));
		
		// queue the filter and check the table didnt change
		$table->queueFilter("Piwik_DataTable_Filter_Sort", array('label', 'asc'));
		$this->assertTrue(Piwik_DataTable::isEqual($tableCopy, $table));
		
		// apply filter and check the table is sorted
		$table->applyQueuedFilters();
		$this->assertTrue(Piwik_DataTable::isEqual($expectedtable, $table));
		
		// apply one more filter check it hasnt changed
		$table->queueFilter("Piwik_DataTable_Filter_Sort", array('label', 'desc'));
		$this->assertTrue(Piwik_DataTable::isEqual($expectedtable, $table));
		
		// now apply the second sort and check it is correctly sorted
		$table->applyQueuedFilters();
		$this->assertTrue(Piwik_DataTable::isEqual($expectedtableReverse, $table));
		
		// do one more time to make sure it doesnt change
		$table->applyQueuedFilters();
		$this->assertTrue(Piwik_DataTable::isEqual($expectedtableReverse, $table));
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
	  	$table->loadFromArray( $rows );
	  	
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
	  	$expectedtable->loadFromArray( $rows );
	  	
	  	$expectedtableReverse = new Piwik_DataTable;
	  	$expectedtableReverse->loadFromArray(array_reverse($rows));
	  		  	
	 	$filter = new Piwik_DataTable_Filter_Sort($table, 'nb_visits', 'asc');
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtable));
	  	
	  	$filter = new Piwik_DataTable_Filter_Sort($table, 'nb_visits', 'desc');
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtableReverse));
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
	  	$table->loadFromArray( $rows );
	  	
	  	$expectedtable = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'google', 	'nb_visits' => 897)),//0
	 		array( $idcol => array('label'=>'piwik', 	'nb_visits' => 1.5)),//2
	  		array( $idcol => array('label'=>'piwik2', 	'nb_visits' => 1.4)),//2
	  		array( $idcol => array('label'=>'yahoo', 	'nb_visits' => 154)),//3
	  		array( $idcol => array('label'=>'amazon', 	'nb_visits' => 30)),//4
		);
	  	$expectedtable->loadFromArray( $rows );
	  	
	 	$filter = new Piwik_DataTable_Filter_ExcludeLowPopulation($table, 'nb_visits', 1.4);

//	  	echo $table;
//	  	echo $expectedtable;
//	  	dump($table);
	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtable));
	 }
	
	
	/**
	 * for all datatable->addDatatable tests we check that
	 * - row uniqueness is based on the label + presence of the SUBTABLE id
	 * 		=> the label is the criteria used to match 2 rows in 2 datatable
	 * - no details are lost in the first datatable rows that have been changed
	 * - when a subtable
	 */
	 
	 
	/**
     * add an empty datatable to a normal datatable
     */
    public function test_addSimpleNoRowTable2()
	{
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
		
		$rows = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  
	 	$tableEmpty = new Piwik_DataTable;
	  	
	  	$tableAfter = clone $table;
	  	$tableAfter->addDataTable($tableEmpty);
	  	$this->assertTrue( Piwik_DataTable::isEqual($table, $tableAfter) );
	}
	
	/**
     * add a normal datatable to an empty datatable
     */
    public function test_addSimpleNoRowTable1()
	{ 	
		$idcol = Piwik_DataTable_Row::COLUMNS;
		
		$rows = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  
	 	$tableEmpty = new Piwik_DataTable;
	  	
	  	$tableAfter = clone $tableEmpty;
	  	$tableEmpty->addDataTable($table);
	  	$this->assertTrue( Piwik_DataTable::isEqual($tableEmpty, $table) );
	}
	
	/**
     * add to the datatable another datatable// they don't have any row in common
     */
    public function test_addSimpleNoCommonRow()
	{
		$idcol = Piwik_DataTable_Row::COLUMNS;
		
		$rows = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	
	  	
		$rows2 = array(
	  		array( $idcol => array('label'=>'test', 'visits' => 1)),
	  		array( $idcol => array('label'=>' google ', 'visits' => 3)),
	  		array( $idcol => array('label'=>'123a', 'visits' => 2)),
  		);	  	
	 	$table2 = new Piwik_DataTable;
	  	$table2->loadFromArray( $rows2 );
	  
	  	$table->addDataTable($table2);
	  
	  	$rowsExpected = array_merge($rows,$rows2);
	  	$tableExpected = new Piwik_DataTable;
	  	$tableExpected->loadFromArray( $rowsExpected );
	  	
	  	$this->assertTrue( Piwik_DataTable::isEqual($table, $tableExpected) );
	}
	
	/**
     * add 2 datatable with some common rows 
     */
    public function test_addSimpleSomeCommonRow()
	{
		
		$idcol = Piwik_DataTable_Row::COLUMNS;
		
		$rows = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	
	  	
		$rows2 = array(
	  		array( $idcol => array('label'=>'test', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 111)),
	  		array( $idcol => array('label'=>' google ', 'visits' => 5)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table2 = new Piwik_DataTable;
	  	$table2->loadFromArray( $rows2 );
	  
	  	$table->addDataTable($table2);
	  
		$rowsExpected = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 113)),
	  		array( $idcol => array('label'=>'123', 'visits' => 4)),
	  		array( $idcol => array('label'=>'test', 'visits' => 1)),
	  		array( $idcol => array('label'=>' google ', 'visits' => 5)),
  		);	  	
	  	$tableExpected = new Piwik_DataTable;
	  	$tableExpected->loadFromArray( $rowsExpected );
	  	
	  	$this->assertTrue( Piwik_DataTable::isEqual($table, $tableExpected) );
	}
	
	/**
     * add 2 datatable with only common rows
     */
    public function test_addSimpleAllCommonRow()
	{
		$idcol = Piwik_DataTable_Row::COLUMNS;
		
		$rows = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	
	  	
		$rows2 = array(
	  		array( $idcol => array('label'=>'google', 'visits' => -1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 0)),
	  		array( $idcol => array('label'=>'123', 'visits' => 1.5)),
  		);	  	
	 	$table2 = new Piwik_DataTable;
	  	$table2->loadFromArray( $rows2 );
	  
	  	$table->addDataTable($table2);
	  
		$rowsExpected = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 0)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 3.5)),
  		);	  	
	  	$tableExpected = new Piwik_DataTable;
	  	$tableExpected->loadFromArray( $rowsExpected );
	  	
	  	$this->assertTrue( Piwik_DataTable::isEqual($table, $tableExpected) );
	}
	
	/**
	 * test add 2 different tables to the same table
	 */
	 
    public function test_addDataTable2times()
	{
	 
		$idcol = Piwik_DataTable_Row::COLUMNS;
		
		$rows = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 0)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	
	  	
		$rows2 = array(
	  		array( $idcol => array('label'=>'google2', 'visits' => -1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 100)),
	  		array( $idcol => array('label'=>'123456', 'visits' => 1.5)),
  		);	  	
	 	$table2 = new Piwik_DataTable;
	  	$table2->loadFromArray( $rows2 );
	  
	  	
		$rows3 = array(
	  		array( $idcol => array('label'=>'google2', 'visits' => -1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => -10)),
	  		array( $idcol => array('label'=>'123ab', 'visits' => 1.5)),
  		);	  	
	 	$table3 = new Piwik_DataTable;
	  	$table3->loadFromArray( $rows3 );
	  	
		// add the 2 tables
	  	$table->addDataTable($table2);
	  	$table->addDataTable($table3);
	  
		$rowsExpected = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 90)),
	  		array( $idcol => array('label'=>'123', 'visits' => 2)),
	  		array( $idcol => array('label'=>'google2', 'visits' => -2)),
	  		array( $idcol => array('label'=>'123456', 'visits' => 1.5)),
	  		array( $idcol => array('label'=>'123ab', 'visits' => 1.5)),
  		);
	  	$tableExpected = new Piwik_DataTable;
	  	$tableExpected->loadFromArray( $rowsExpected );
	  	
	  	$this->assertTrue( Piwik_DataTable::isEqual($table, $tableExpected) );
	}
}