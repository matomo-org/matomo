<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once "ArchiveProcessing.php";
require_once "ArchiveProcessing/Day.php";

class Test_Piwik_ArchiveProcessing_Day extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	function test_generateDataTable_simple()
	{
		$row1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page1', 'visits' => 1, 'actions' => 2, '666' => 'evil' )));
							
		$input = array(
			'page1' => $row1,
					);
					
		$table = new Piwik_DataTable;
		$table->addRow($row1);
		
		$tableGenerated = Piwik_ArchiveProcessing_Day::generateDataTable($input);

		$this->assertTrue(Piwik_DataTable::isEqual($table,$tableGenerated));
	}
	
	
	function test_generateDataTable_2rows()
	{
		$row1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page1', 'visits' => 1, 'actions' => 2)));
		$row2 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page2', 'visits' => 3, 'actions' => 5)));
							
		$input = array(
			'page1' => $row1,
			'page2' => $row2,
					);
					
		$table = new Piwik_DataTable;
		$table->addRow($row1);
		$table->addRow($row2);
		
		$tableGenerated = Piwik_ArchiveProcessing_Day::generateDataTable($input);
//		dump($tableGenerated);
//		dump($table);
		
		$this->assertTrue(Piwik_DataTable::isEqual($table,$tableGenerated));
	}
	
	function test_generateDataTable_1row2level()
	{
		$row1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'cat1', 'visits' => 3, 'actions' => 5 )));
		
		$rowLevel2 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page1', 'visits' => 3, 'actions' => 5)));
		$subtable = new Piwik_DataTable;
		$subtable->addRow($rowLevel2);
		$row1->addSubtable($subtable);			
		
		$table = new Piwik_DataTable;
		$table->addRow($row1);
		
		$input = array(
		'cat1' => array(
				'page1' => $rowLevel2,
					)
				);				
		
		$tableGenerated = Piwik_ArchiveProcessing_Day::generateDataTable($input);
		
		$r1 = new Piwik_DataTable_Renderer_Console();
	  	$r1->setTable($table);
		$r2 = new Piwik_DataTable_Renderer_Console();
		$r2->setTable($tableGenerated);
//		echo "r1=".$r1;
//		echo "r2=".$r2;
		
		$this->assertTrue(Piwik_DataTable::isEqual($table,$tableGenerated));
	}
	
	function test_generateDataTable_2rows2level()
	{
		$table = new Piwik_DataTable;
		
		//FIRST ROW + SUBTABLE
		$row1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'cat1', 'visits' => 3, 'actions' => 5 )));
		
		$rowLevel2a = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page1', 'visits' => 3, 'actions' => 5)));
		$subtable = new Piwik_DataTable;
		$subtable->addRow($rowLevel2a);
		$row1->addSubtable($subtable);		
		
		//-- add
		$table->addRow($row1);
			
		//SECOND ROW + SUBTABLE MULTI ROWS
		$row1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'cat2', 'visits' => 13, 'actions' => 9 )));
		
		$rowLevel2b1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page2a', 'visits' => 6, 'actions' => 8)));
		
		$rowLevel2b2 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'page2b', 'visits' => 7, 'actions' => 1)));
		$subtable = new Piwik_DataTable;
		$subtable->addRow($rowLevel2b1);
		$subtable->addRow($rowLevel2b2);
		$row1->addSubtable($subtable);			
		
		//-- add
		$table->addRow($row1);
		
		// WHAT WE TEST
		$input = array(
		'cat1' => array(
				'page1' => $rowLevel2a,
					),
		'cat2' => array(
				'page2a' => $rowLevel2b1,
				'page2b' => $rowLevel2b2,
					)
				);				
		$tableGenerated = Piwik_ArchiveProcessing_Day::generateDataTable($input);
		
		$r1 = new Piwik_DataTable_Renderer_Console();
		$r1->setTable($table);
		$r2 = new Piwik_DataTable_Renderer_Console();
		$r2->setTable($tableGenerated);
//		echo "r1=".$r1;
//		echo "r2=".$r2;
		
		$this->assertTrue(Piwik_DataTable::isEqual($table,$tableGenerated));
	}
	
	function test_generateDataTable_1row4levelMultiRows()
	{
		$table = new Piwik_DataTable;
		
		//FIRST ROW + SUBTABLE
		$rowcat2 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => '456', 'visits' => 3, 'actions' => 5 )));
		
		$cat2 =  new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'cat2', 'visits' => 3, 'actions' => 5 )));

		$rowcat1 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'pagecat1', 'visits' => 6, 'actions' => 4)));
		
		$cat1 =  new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'cat1', 'visits' => 9, 'actions' => 9 )));
		
		$subtablecat2 = new Piwik_DataTable;
		$subtablecat2->addRow($rowcat2);
		$cat2->addSubtable($subtablecat2);		
		
		$subtablecat1 = new Piwik_DataTable;
		$subtablecat1->addRow($rowcat1);
		$subtablecat1->addRow($cat2);	
		
		$cat1->addSubtable($subtablecat1);
		
		//-- add
		$table->addRow($cat1);
		
		// WHAT WE TEST
		$input = array(
		'cat1' => array(
				'pagecat1' => $rowcat1,
				'cat2' => array(
					'pagecat2' => $rowcat2,
					),
				),
		);				
		$tableGenerated = Piwik_ArchiveProcessing_Day::generateDataTable($input);
		
		$r1 = new Piwik_DataTable_Renderer_Console();
		$r1->setTable($table);
		$r2 = new Piwik_DataTable_Renderer_Console();
		$r2->setTable($tableGenerated);
//		echo "r1=".$r1;
//		echo "r2=".$r2;
		
		$this->assertTrue(Piwik_DataTable::isEqual($table,$tableGenerated));
	}
	
	
	function test_generateDataTable_1row4level()
	{
		$table = new Piwik_DataTable;
		
		$rowpagecat3 = new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => '123123', 'visits' => 3, 'actions' => 5 )));
		
		$rowcat3 =  new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => '789.654', 'visits' => 3, 'actions' => 5 )));
		$rowcat2 =  new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => 'cat2', 'visits' => 3, 'actions' => 5 )));
		$rowcat1 =  new Piwik_DataTable_Row( array( Piwik_DataTable_Row::COLUMNS => 
							array(	'label' => '&*()', 'visits' => 3, 'actions' => 5 )));

		$subtablerowpagecat3 = new Piwik_DataTable;
		$subtablerowpagecat3->addRow($rowpagecat3);
		$rowcat3->addSubtable($subtablerowpagecat3);
		
		$subtablecat2 = new Piwik_DataTable;
		$subtablecat2->addRow($rowcat3);
		$rowcat2->addSubtable($subtablecat2);		
		
		
		$subtablecat1 = new Piwik_DataTable;
		$subtablecat1->addRow($rowcat2);
		$rowcat1->addSubtable($subtablecat1);		
		
		//-- add
		$table->addRow($rowcat1);
		
		// WHAT WE TEST
		$input = array(
			'&*()' => array(
				'cat2' => array(
					'789.654' => array(
						'123123' => $rowpagecat3,
					),
				),
			),
		);				
		
		$tableGenerated = Piwik_ArchiveProcessing_Day::generateDataTable($input);
		
		$r1 = new Piwik_DataTable_Renderer_Console();
		$r1->setTable($table);
		$r2 = new Piwik_DataTable_Renderer_Console();
		$r2->setTable($tableGenerated);
		$this->assertTrue(Piwik_DataTable::isEqual($table,$tableGenerated));
	}
}
