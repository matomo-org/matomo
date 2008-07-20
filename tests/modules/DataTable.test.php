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
	  				Piwik_DataTable_Row::METADATA => array('logo' => 'test.png'));
	  	$row = new Piwik_DataTable_Row($row);
	  	
	  	$table->addRow($row);
	  	$table->addRowFromArray(array( Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,1 => 42,),
	  						Piwik_DataTable_Row::METADATA => array('url' => 'piwik.org')));
		
	  	$table->addRowFromArray(array( Piwik_DataTable_Row::COLUMNS => array( 0 => 787877888787,),
	  						Piwik_DataTable_Row::METADATA => array('url' => 'OUPLA ADDED'),
	  						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable));
		  	
		/*
		 * SUB TABLE
		 */				
	  									
		
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 1554,),
	  						Piwik_DataTable_Row::METADATA => array('searchengine' => 'google'),
	  					);
	  	$subtable->addRowFromArray($row);
	  	
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 84894,),
	  						Piwik_DataTable_Row::METADATA => array('searchengine' => 'yahoo'),
	  					);
	  	$subtable->addRowFromArray($row);
		$row = array( 		Piwik_DataTable_Row::COLUMNS => array( 0 => 4898978989,),
	  						Piwik_DataTable_Row::METADATA => array('searchengine' => 'ask'),
	  					);	  	
	  	$subtable->addRowFromArray($row);
	  	
	  	
	  	/*
	  	 * SUB SUB TABLE
	  	 */
	  	$subsubtable = new Piwik_DataTable;
	  	$subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 245),
	  						Piwik_DataTable_Row::METADATA => array('yes' => 'subsubmetadata1'),)
	  						);  	
	  						
	  	$subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 13,),
	  						Piwik_DataTable_Row::METADATA => array('yes' => 'subsubmetadata2'),)
	  						);
	  						
		$row = array( 	Piwik_DataTable_Row::COLUMNS => array( 0 => 666666666666666,),
	  						Piwik_DataTable_Row::METADATA => array('url' => 'NEW ROW ADDED'),
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
	 * we test the count rows and the count rows recursive version
	 * on a Simple array (1 level only)
	 */
	function test_countRowsSimple()
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
	  	
	  	$this->assertEqual( $table->getRowsCount(), count($rows));
	  	$this->assertEqual( $table->getRowsCountRecursive(), count($rows));
	}
	/**
	 * we test the count rows and the count rows recursive version
	 * on a Complex array (rows with 2 and 3 levels only)
	 * 
	 * the recursive count returns 
	 * 		the sum of the number of rows of all the subtables 
	 * 		+ the number of rows in the parent table
	 */
	function test_countRowsComplex()
	{
		
	 	$idcol = Piwik_DataTable_Row::COLUMNS;
	 	$idsubtable = Piwik_DataTable_Row::DATATABLE_ASSOCIATED;
	 	
		// table to go in the SUB table of RoW1
	 	$tableSubOfSubOfRow1 = new Piwik_DataTable;
	 	$rows1sub = array(
	  		array( $idcol => array('label'=>'google')),
	  		array( $idcol => array('label'=>'google78')),
	  		array( $idcol => array('label'=>'googlaegge')),
	  		array( $idcol => array('label'=>'gogeoggle')),
	  		array( $idcol => array('label'=>'goaegaegaogle')),
	  		array( $idcol => array('label'=>'ask')),
	  		array( $idcol => array('label'=>'238975247578949')),
	  	);
	  	$tableSubOfSubOfRow1->loadFromArray( $rows1sub );
	  	
		// table to go in row1
	 	$tableSubOfRow1 = new Piwik_DataTable;
	 	$rows1 = array(
	  		array( $idcol => array('label'=>'google'), $idsubtable =>$tableSubOfSubOfRow1),
	  		array( $idcol => array('label'=>'ask')),
	  		array( $idcol => array('label'=>'238975247578949')),
	  	);
	  	$tableSubOfRow1->loadFromArray( $rows1 );
	  	
		// table to go in row2
	 	$tableSubOfRow2 = new Piwik_DataTable;
	 	$rows2 = array(
	  		array( $idcol => array('label'=>'google')),
	  		array( $idcol => array('label'=>'ask')),
	  		array( $idcol => array('label'=>'238975247578949')),
	  		array( $idcol => array('label'=>'agaegaesk')),
	  		array( $idcol => array('label'=>'23g  8975247578949')),
	  	);
	  	$tableSubOfRow2->loadFromArray( $rows2 );
	  	
	 	// main parent table
	 	$table = new Piwik_DataTable;
	 	$rows = array(
	  		array( $idcol => array('label'=>'row1')),
	  		array( $idcol => array('label'=>'row2'), 
	  					$idsubtable => $tableSubOfRow1),
	  		array( $idcol => array('label'=>'row3'), 
	  					$idsubtable => $tableSubOfRow2),
	  	);
	  	$table->loadFromArray( $rows );
		
	  	
	  	$this->assertEqual( $table->getRowsCount(), count($rows));
	  	$countAllRows =  count($rows)+count($rows1)+count($rows2) + count($rows1sub);
	  	$this->assertEqual( $table->getRowsCountRecursive(),$countAllRows);
	}
	
	/**
	 * Simple test of the DataTable_Row
	 */
	function test_Row()
	{
		$columns = array('test_column'=> 145,
						092582495 => new Piwik_Timer,
						'super'=>array('this column has an array value, amazing'));
		$metadata = array('logo'=> 'piwik.png',
						'super'=>array('this column has an array value, amazing'));
		$arrayRow = array(
			Piwik_DataTable_Row::COLUMNS => $columns,
	  		Piwik_DataTable_Row::METADATA => $metadata,
	  		'fake useless key'=>38959,
	  		43905724897=>'value');
		$row = new Piwik_DataTable_Row($arrayRow);
		
		$this->assertEqual($row->getColumns(), $columns);
		$this->assertEqual($row->getMetadata(), $metadata);
		$this->assertEqual($row->getIdSubDataTable(), null);
		
	}
	/**
	 * Simple test of the DataTable_Row
	 */
	function test_sumRow()
	{
		$columns = array('test_int'=> 145,
						'test_float'=> 145.5,
						'test_float3'=> 1.5,
						'test_stringint'=> "145",
						"test" => 'string fake',
						'super'=>array('this column has an array value, amazing')
						);
		$metadata = array('logo'=> 'piwik.png',
						'super'=>array('this column has an array value, amazing'));
		$arrayRow = array(
			Piwik_DataTable_Row::COLUMNS => $columns,
	  		Piwik_DataTable_Row::METADATA => $metadata,
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
	  	
	  	$table->loadFromArray( $rows );
	  	
	  	
	 	$expectedtable = clone $table;
	 	$expectedtable->deleteRows(array(0,1,2,3,4,5));
	  	
	  	$filter = new Piwik_DataTable_Filter_Pattern($table, 'label', '*');
	  		  	
	  	$this->assertEqual($table->getRows(), $expectedtable->getRows());
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

	  	$this->assertTrue(Piwik_DataTable::isEqual($table, $expectedtable));
	 }
	
	
	/**
	 * for all datatable->addDatatable tests we check that
	 * - row uniqueness is based on the label + presence of the SUBTABLE id
	 * 		=> the label is the criteria used to match 2 rows in 2 datatable
	 * - no metadata are lost in the first datatable rows that have been changed
	 * - when a subtable
	 */
	 
	 
	/**
     * add an empty datatable to a normal datatable
     */
    public function test_addSimpleNoRowTable2()
	{
		$table = $this->getDataTable1ForTest();
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
		$table = $this->getDataTable1ForTest();
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
		$table1 = $this->getDataTable1ForTest();
		$table2 = $this->getDataTable2ForTest();
	  
	  	$table1->addDataTable($table2);
	  
	  	$rowsExpected = array_merge($this->getRowsDataTable1ForTest(),$this->getRowsDataTable2ForTest());
	  	$tableExpected = new Piwik_DataTable;
	  	$tableExpected->loadFromArray( $rowsExpected );
	  	
	  	$this->assertTrue( Piwik_DataTable::isEqual($table1, $tableExpected) );
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
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
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
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
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
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 7))
  		);	  	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	
	  	
		$rows2 = array(
	  		array( $idcol => array('label'=>'google', 'visits' => -1)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 0)),
	  		array( $idcol => array('label'=>'123', 'visits' => 1.5)),
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 8))
  		);	  	
	 	$table2 = new Piwik_DataTable;
	  	$table2->loadFromArray( $rows2 );
	  
	  	$table->addDataTable($table2);
	  
		$rowsExpected = array(
	  		array( $idcol => array('label'=>'google', 'visits' => 0)),
	  		array( $idcol => array('label'=>'ask', 'visits' => 2)),
	  		array( $idcol => array('label'=>'123', 'visits' => 3.5)),
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 15))
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
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 1))
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
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 3))
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
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( $idcol  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 4))
  		);
	  	$tableExpected = new Piwik_DataTable;
	  	$tableExpected->loadFromArray( $rowsExpected );
	  	
	  	$this->assertTrue( Piwik_DataTable::isEqual($table, $tableExpected) );
	}
	
	protected function getDataTable1ForTest()
	{
		$rows = $this->getRowsDataTable1ForTest();
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	return $table;
	}

	protected function getDataTable2ForTest()
	{
		$rows = $this->getRowsDataTable2ForTest();	
	 	$table = new Piwik_DataTable;
	  	$table->loadFromArray( $rows );
	  	return $table;
	}
	
	protected function getRowsDataTable1ForTest()
	{
		$rows = array(
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>'google', 'visits' => 1)),
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>'ask', 'visits' => 2)),
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>'123', 'visits' => 2)),
	  		Piwik_DataTable::ID_SUMMARY_ROW => array( Piwik_DataTable_Row::COLUMNS  => array('label'=>Piwik_DataTable::LABEL_SUMMARY_ROW, 'visits' => 4))
	  		
  		);
  		return $rows;	  	
	}

	protected function getRowsDataTable2ForTest()
	{
		$rows = array(
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>'test', 'visits' => 1)),
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>' google ', 'visits' => 3)),
	  		array( Piwik_DataTable_Row::COLUMNS => array('label'=>'123a', 'visits' => 2)),
  		);
  		return $rows;	  	
	}
	

}