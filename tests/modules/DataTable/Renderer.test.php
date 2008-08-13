<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once "../../../tests/config_test.php";
}

require_once 'DataTable.php';
require_once 'DataTable/Simple.php';
require_once 'DataTable/Array.php';
require_once 'DataTable/Renderer/Xml.php';
require_once 'DataTable/Renderer/Csv.php';
require_once 'DataTable/Renderer/Json.php';
require_once 'DataTable/Renderer/Php.php';

class Test_Piwik_DataTable_Renderer extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
		Piwik_DataTable_Manager::getInstance()->deleteAll();
	}
	
	public function tearDown()
	{
	}
	
	
	/**
	 * DATA TESTS 
	 * -----------------------
	 * for each renderer we test the case
	 * - datatableSimple
	 * - normal datatable  with 2 row (including columns and metadata)	 *
	 */
	protected function getDataTableTest()
	{
		$arraySubTableForRow2 = array ( 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'sub1', 'count' => 1) ), 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'sub2', 'count' => 2) ), 
		);
		$subDataTableForRow2 = new Piwik_DataTable();
		$subDataTableForRow2->loadFromArray($arraySubTableForRow2);
		
		$subtable = 
		$array = array ( 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Google&copy;', 'nb_uniq_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9), 
						Piwik_DataTable_Row::METADATA => array('url' => 'http://www.google.com', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png'), 
					 ), 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90), 
						Piwik_DataTable_Row::METADATA => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png'),
						Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subDataTableForRow2,
					 )
			);
		$dataTable = new Piwik_DataTable();
		$dataTable->loadFromArray($array);
		return $dataTable;
	}
	protected function getDataTableSimpleTest()
	{
		$array = array ( 'max_actions' => 14.0, 'nb_uniq_visitors' => 57.0, 'nb_visits' => 66.0, 'nb_actions' => 151.0, 'sum_visit_length' => 5118.0, 'bounce_count' => 44.0, );
		
		$table = new Piwik_DataTable_Simple;
		$table->loadFromArray($array);
		return $table;
	}
	protected function getDataTableSimpleOneRowTest()
	{
		$array = array ( 'nb_visits' => 14.0 );
		
		$table = new Piwik_DataTable_Simple;
		$table->loadFromArray($array);
		return $table;
	}
	protected function getDataTableEmpty()
	{
		$table = new Piwik_DataTable;
		return $table;
	}
	protected function getDataTableSimpleOneZeroRowTest()
	{
		$array = array ( 'nb_visits' => 0 );
		
		$table = new Piwik_DataTable_Simple;
		$table->loadFromArray($array);
		return $table;
	}
	
	
	/**
	 * START TESTS
	 * -----------------------
	 *
	 */

	function test_XML_test1()
	{
		$dataTable = $this->getDataTableTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable, true);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>Google©</label>
		<nb_uniq_visitors>11</nb_uniq_visitors>
		<nb_visits>11</nb_visits>
		<nb_actions>17</nb_actions>
		<max_actions>5</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>9</bounce_count>
		<url>http://www.google.com</url>
		<logo>./plugins/Referers/images/searchEngines/www.google.com.png</logo>
	</row>
	<row>
		<label>Yahoo!</label>
		<nb_uniq_visitors>15</nb_uniq_visitors>
		<nb_visits>151</nb_visits>
		<nb_actions>147</nb_actions>
		<max_actions>50</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>90</bounce_count>
		<url>http://www.yahoo.com</url>
		<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png</logo>
		<idsubdatatable>0</idsubdatatable>
		<subtable>
			<row>
				<label>sub1</label>
				<count>1</count>
			</row>
			<row>
				<label>sub2</label>
				<count>2</count>
			</row>
		</subtable>
	</row>
</result>';
		$rendered = $render->render();
		$this->assertEqual( $expected,$rendered);
	}

	function test_XML_test2()
	{
		$dataTable = $this->getDataTableSimpleTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<max_actions>14</max_actions>
	<nb_uniq_visitors>57</nb_uniq_visitors>
	<nb_visits>66</nb_visits>
	<nb_actions>151</nb_actions>
	<sum_visit_length>5118</sum_visit_length>
	<bounce_count>44</bounce_count>
</result>';
		$this->assertEqual( $expected,$render->render());
	}
	function test_XML_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>14</result>';
		$this->assertEqual( $expected,$render->render());
	}
	function test_XML_test4()
	{
		$dataTable = $this->getDataTableEmpty();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<result />';
		$this->assertEqual( $expected,$render->render());
	}
	
	function test_XML_test5()
	{
		$dataTable = $this->getDataTableSimpleOneZeroRowTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>';
		$this->assertEqual( $expected,$render->render());
	}
	
	
	function test_CSV_test1()
	{
		$dataTable = $this->getDataTableTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'label,nb_uniq_visitors,nb_visits,nb_actions,max_actions,sum_visit_length,bounce_count,metadata_url,metadata_logo
Google©,11,11,17,5,517,9,http://www.google.com,./plugins/Referers/images/searchEngines/www.google.com.png
Yahoo!,15,151,147,50,517,90,http://www.yahoo.com,./plugins/Referers/images/searchEngines/www.yahoo.com.png';

		$this->assertEqual( $expected,$render->render());
	}
	function test_CSV_test2()
	{
		$dataTable = $this->getDataTableSimpleTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'label,value
max_actions,14
nb_uniq_visitors,57
nb_visits,66
nb_actions,151
sum_visit_length,5118
bounce_count,44';

		$this->assertEqual( $expected,$render->render());
	}

	function test_CSV_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = "value\n14";
		$this->assertEqual( $expected,$render->render());
	}

	function test_CSV_test4()
	{
		$dataTable = $this->getDataTableEmpty();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'No data available';
		$this->assertEqual( $expected,$render->render());
	}

	function test_CSV_test5()
	{
		$dataTable = $this->getDataTableSimpleOneZeroRowTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = "value\n0";
		$this->assertEqual( $expected,$render->render());
	}
	
	function test_JSON_test1()
	{
		$dataTable = $this->getDataTableTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable, true);
		$expected = '[{"label":"Google&copy;","nb_uniq_visitors":11,"nb_visits":11,"nb_actions":17,"max_actions":"5","sum_visit_length":517,"bounce_count":9,"url":"http:\/\/www.google.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"nb_actions":147,"max_actions":"50","sum_visit_length":517,"bounce_count":90,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.yahoo.com.png","idsubdatatable":0,"subtable":[{"label":"sub1","count":1},{"label":"sub2","count":2}]}]';
		$rendered = $render->render();
		
		$this->assertEqual( $expected,$rendered);
	}
	function test_JSON_test2()
	{
		$dataTable = $this->getDataTableSimpleTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
		$expected = '{"max_actions":14,"nb_uniq_visitors":57,"nb_visits":66,"nb_actions":151,"sum_visit_length":5118,"bounce_count":44}';

		$this->assertEqual( $expected,$render->render());
	}

	function test_JSON_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
		$expected = '{"value":14}';
		$this->assertEqual( $expected,$render->render());
	}

	function test_JSON_test4()
	{
		$dataTable = $this->getDataTableEmpty();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
		$expected = '[]';
		$this->assertEqual( $expected,$render->render());
	}

	function test_JSON_test5()
	{
		$dataTable = $this->getDataTableSimpleOneZeroRowTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
		$expected = '{"value":0}';
		$this->assertEqual( $expected,$render->render());
	}
	
	function test_PHP_test1()
	{
		$dataTable = $this->getDataTableTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable, true);
		$expected = serialize(array (
					  0 => 
					  array (
					    'label' => 'Google&copy;',
					    'nb_uniq_visitors' => 11,
					    'nb_visits' => 11,
					    'nb_actions' => 17,
					    'max_actions' => '5',
					    'sum_visit_length' => 517,
					    'bounce_count' => 9,
					    'url' => 'http://www.google.com',
					    'logo' => './plugins/Referers/images/searchEngines/www.google.com.png',
					  ),
					  1 => 
					  array (
					    'label' => 'Yahoo!',
					    'nb_uniq_visitors' => 15,
					    'nb_visits' => 151,
					    'nb_actions' => 147,
					    'max_actions' => '50',
					    'sum_visit_length' => 517,
					    'bounce_count' => 90,
					    'url' => 'http://www.yahoo.com',
					    'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png',
					  	'idsubdatatable' => 0,
					    'subtable' => 
						    array (
						      0 => 
						      array (
						        'label' => 'sub1',
						        'count' => 1,
						      ),
						      1 => 
						      array (
						        'label' => 'sub2',
						        'count' => 2,
						      ),
					    ),
					  ),
					));
		$rendered = $render->render(null);
		$this->assertEqual( $expected,$rendered);
	}
	function test_PHP_test2()
	{
		$dataTable = $this->getDataTableSimpleTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
		$expected = serialize(array (
				  'max_actions' => 14.0,
				  'nb_uniq_visitors' => 57.0,
				  'nb_visits' => 66.0,
				  'nb_actions' => 151.0,
				  'sum_visit_length' => 5118.0,
				  'bounce_count' => 44.0,
				));
		$this->assertEqual( $expected,$render->render());
	}
	function test_PHP_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
		$expected = serialize(14.0);
		$this->assertEqual( $expected,$render->render());
	}
	function test_PHP_test4()
	{
		$dataTable = $this->getDataTableEmpty();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
		$expected = serialize(array());
		$this->assertEqual( $expected,$render->render());
	}
	function test_PHP_test5()
	{
		$dataTable = $this->getDataTableSimpleOneZeroRowTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
		$expected = serialize(0);
		$this->assertEqual( $expected,$render->render());
	}
	
	
	
	
	
	/**
	 * DATA OF DATATABLE_ARRAY
	 * -------------------------
	 */
	

	protected function getDataTableArrayTest()
	{
		$array1 = array ( 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11, ), 
						Piwik_DataTable_Row::METADATA => array('url' => 'http://www.google.com', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png'), 
					 ), 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151, ), 
						Piwik_DataTable_Row::METADATA => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png'), 
					 )
			);
		$table1 = new Piwik_DataTable();
		$table1->loadFromArray($array1);
		
		
		$array2 = array ( 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110,), 
						Piwik_DataTable_Row::METADATA => array('url' => 'http://www.google.com1', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png1'), 
					 ), 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510,), 
						Piwik_DataTable_Row::METADATA => array('url' => 'http://www.yahoo.com1', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png1'), 
					 )
			);
		$table2 = new Piwik_DataTable();
		$table2->loadFromArray($array2);
		
		$table3 = new Piwik_DataTable();
		
		
		$table = new Piwik_DataTable_Array();
		$table->setKeyName('testKey');
		$table->addTable($table1, 'date1');
		$table->addTable($table2, 'date2');
		$table->addTable($table3, 'date3');
		
		return $table;
	}

	protected function getDataTableSimpleArrayTest()
	{
		$array1 = array ( 'max_actions' => 14.0, 'nb_uniq_visitors' => 57.0,  );
		$table1 = new Piwik_DataTable_Simple;
		$table1->loadFromArray($array1);
				
		$array2 = array ( 'max_actions' => 140.0, 'nb_uniq_visitors' => 570.0,  );
		$table2 = new Piwik_DataTable_Simple;
		$table2->loadFromArray($array2);
		
		$table3 = new Piwik_DataTable_Simple;
		
		$table = new Piwik_DataTable_Array();
		$table->setKeyName('testKey');
		$table->addTable($table1, 'row1');
		$table->addTable($table2, 'row2');
		$table->addTable($table3, 'row3');
		
		return $table;
	}

	protected function getDataTableSimpleOneRowArrayTest()
	{
		$array1 = array ( 'nb_visits' => 14.0 );
		$table1 = new Piwik_DataTable_Simple;
		$table1->loadFromArray($array1);
		$array2 = array ( 'nb_visits' => 15.0 );
		$table2 = new Piwik_DataTable_Simple;
		$table2->loadFromArray($array2);
		
		$table3 = new Piwik_DataTable_Simple;
		
		$table = new Piwik_DataTable_Array();
		$table->setKeyName('testKey');
		$table->addTable($table1, 'row1');
		$table->addTable($table2, 'row2');
		$table->addTable($table3, 'row3');
		
		return $table;
	}
	
	protected function getDataTableArray_containsDataTableArray_normal()
	{
		$table = new Piwik_DataTable_Array();
		$table->setKeyName('parentArrayKey');
		$table->addTable($this->getDataTableArrayTest(), 'idSite');
		return $table;
	}
	
	protected function getDataTableArray_containsDataTableArray_simple()
	{	
		$table = new Piwik_DataTable_Array();
		$table->setKeyName('parentArrayKey');
		$table->addTable($this->getDataTableSimpleArrayTest(), 'idSite');
		return $table;
	}
	
	protected function getDataTableArray_containsDataTableArray_simpleOneRow()
	{
		$table = new Piwik_DataTable_Array();
		$table->setKeyName('parentArrayKey');
		$table->addTable($this->getDataTableSimpleOneRowArrayTest(), 'idSite');
		return $table;
	}
	

	/**
	 * START TESTS DATATABLE_ARRAY
	 * ---------------
	 * 
	 * XML
	 * 
	 * PHP
	 * 
	 *
	 */
	function test_XML_Array_test1()
	{
		$dataTable = $this->getDataTableArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="date1">
		<row>
			<label>Google</label>
			<nb_uniq_visitors>11</nb_uniq_visitors>
			<nb_visits>11</nb_visits>
			<url>http://www.google.com</url>
			<logo>./plugins/Referers/images/searchEngines/www.google.com.png</logo>
		</row>
		<row>
			<label>Yahoo!</label>
			<nb_uniq_visitors>15</nb_uniq_visitors>
			<nb_visits>151</nb_visits>
			<url>http://www.yahoo.com</url>
			<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png</logo>
		</row>
	</result>
	<result testKey="date2">
		<row>
			<label>Google1©</label>
			<nb_uniq_visitors>110</nb_uniq_visitors>
			<nb_visits>110</nb_visits>
			<url>http://www.google.com1</url>
			<logo>./plugins/Referers/images/searchEngines/www.google.com.png1</logo>
		</row>
		<row>
			<label>Yahoo!1</label>
			<nb_uniq_visitors>150</nb_uniq_visitors>
			<nb_visits>1510</nb_visits>
			<url>http://www.yahoo.com1</url>
			<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png1</logo>
		</row>
	</result>
	<result testKey="date3" />
</results>';
		$this->assertEqual( $expected,$render->render());
	}
	

	function test_XML_Array_isMadeOfArray_test1()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_normal();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="date1">
			<row>
				<label>Google</label>
				<nb_uniq_visitors>11</nb_uniq_visitors>
				<nb_visits>11</nb_visits>
				<url>http://www.google.com</url>
				<logo>./plugins/Referers/images/searchEngines/www.google.com.png</logo>
			</row>
			<row>
				<label>Yahoo!</label>
				<nb_uniq_visitors>15</nb_uniq_visitors>
				<nb_visits>151</nb_visits>
				<url>http://www.yahoo.com</url>
				<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png</logo>
			</row>
		</result>
		<result testKey="date2">
			<row>
				<label>Google1©</label>
				<nb_uniq_visitors>110</nb_uniq_visitors>
				<nb_visits>110</nb_visits>
				<url>http://www.google.com1</url>
				<logo>./plugins/Referers/images/searchEngines/www.google.com.png1</logo>
			</row>
			<row>
				<label>Yahoo!1</label>
				<nb_uniq_visitors>150</nb_uniq_visitors>
				<nb_visits>1510</nb_visits>
				<url>http://www.yahoo.com1</url>
				<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png1</logo>
			</row>
		</result>
		<result testKey="date3" />
	</result>
</results>';
		$rendered = $render->render();
		$this->assertEqual( $expected, $rendered);
	}
	

	function test_XML_Array_test2()
	{
		$dataTable = $this->getDataTableSimpleArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="row1">
		<max_actions>14</max_actions>
		<nb_uniq_visitors>57</nb_uniq_visitors>
	</result>
	<result testKey="row2">
		<max_actions>140</max_actions>
		<nb_uniq_visitors>570</nb_uniq_visitors>
	</result>
	<result testKey="row3" />
</results>';
		$this->assertEqual( $expected,$render->render());
	}
	
	function test_XML_Array_isMadeOfArray_test2()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simple();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="row1">
			<max_actions>14</max_actions>
			<nb_uniq_visitors>57</nb_uniq_visitors>
		</result>
		<result testKey="row2">
			<max_actions>140</max_actions>
			<nb_uniq_visitors>570</nb_uniq_visitors>
		</result>
		<result testKey="row3" />
	</result>
</results>';
		$rendered = $render->render();
//		echo "$rendered\n$expected";exit;
		$this->assertEqual( $expected,$rendered);
	}

	function test_XML_Array_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="row1">14</result>
	<result testKey="row2">15</result>
	<result testKey="row3" />
</results>';
		$rendered = $render->render();
		$this->assertEqual( $expected,$rendered);
	}
	
	function test_XML_Array_isMadeOfArray_test3()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simpleOneRow();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="row1">14</result>
		<result testKey="row2">15</result>
		<result testKey="row3" />
	</result>
</results>';
		$rendered = $render->render();
//		echo "$rendered\n$expected";exit;
		$this->assertEqual( $expected,$rendered);
	}
	
	

	function test_PHP_Array_test1()
	{
		$dataTable = $this->getDataTableArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = serialize(array (
				  'date1' => 
				  array (
				    0 => 
				    array (
				      'label' => 'Google',
				      'nb_uniq_visitors' => 11,
				      'nb_visits' => 11,
				      'url' => 'http://www.google.com',
				      'logo' => './plugins/Referers/images/searchEngines/www.google.com.png',
				    ),
				    1 => 
				    array (
				      'label' => 'Yahoo!',
				      'nb_uniq_visitors' => 15,
				      'nb_visits' => 151,
				      'url' => 'http://www.yahoo.com',
				      'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png',
				    ),
				  ),
				  'date2' => 
				  array (
				    0 => 
				    array (
				      'label' => 'Google1&copy;',
				      'nb_uniq_visitors' => 110,
				      'nb_visits' => 110,
				      'url' => 'http://www.google.com1',
				      'logo' => './plugins/Referers/images/searchEngines/www.google.com.png1',
				    ),
				    1 => 
				    array (
				      'label' => 'Yahoo!1',
				      'nb_uniq_visitors' => 150,
				      'nb_visits' => 1510,
				      'url' => 'http://www.yahoo.com1',
				      'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png1',
				    ),
				),
				  'date3' => array (),
				  ));		
		$this->assertEqual( $expected,$rendered);
	}
	function test_PHP_Array_test2()
	{
		$dataTable = $this->getDataTableSimpleArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = serialize(array (
			  'row1' => 
			  array (
			    'max_actions' => 14.0,
			    'nb_uniq_visitors' => 57.0,
			  ),
			  'row2' => 
			  array (
			    'max_actions' => 140.0,
			    'nb_uniq_visitors' => 570.0,
			  ),
			  'row3' => 
			  array (
			  ),
			));
		$this->assertEqual( $expected,$rendered);
	}
	function test_PHP_Array_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);	  	
	  	$rendered = $render->render();
	  
		$expected = serialize(array (
				  'row1' => 14.0,
				  'row2' => 15.0,
				  'row3' => array(),
				));
		$this->assertEqual( $expected,$rendered);
	}
	
	function test_PHP_Array_isMadeOfArray_test1()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_normal();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = serialize(array('idSite'=> 
			array (
				  'date1' => 
				  array (
				    0 => 
				    array (
				      'label' => 'Google',
				      'nb_uniq_visitors' => 11,
				      'nb_visits' => 11,
				      'url' => 'http://www.google.com',
				      'logo' => './plugins/Referers/images/searchEngines/www.google.com.png',
				    ),
				    1 => 
				    array (
				      'label' => 'Yahoo!',
				      'nb_uniq_visitors' => 15,
				      'nb_visits' => 151,
				      'url' => 'http://www.yahoo.com',
				      'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png',
				    ),
				  ),
				  'date2' => 
				  array (
				    0 => 
				    array (
				      'label' => 'Google1&copy;',
				      'nb_uniq_visitors' => 110,
				      'nb_visits' => 110,
				      'url' => 'http://www.google.com1',
				      'logo' => './plugins/Referers/images/searchEngines/www.google.com.png1',
				    ),
				    1 => 
				    array (
				      'label' => 'Yahoo!1',
				      'nb_uniq_visitors' => 150,
				      'nb_visits' => 1510,
				      'url' => 'http://www.yahoo.com1',
				      'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png1',
				    ),
				),
				  'date3' => array (),
				  )));
				  
		$this->assertEqual( $expected,$rendered);
	}
	function test_PHP_Array_isMadeOfArray_test2()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simple();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = serialize(array ('idSite'=> 
			array(
			  'row1' => 
			  array (
			    'max_actions' => 14.0,
			    'nb_uniq_visitors' => 57.0,
			  ),
			  'row2' => 
			  array (
			    'max_actions' => 140.0,
			    'nb_uniq_visitors' => 570.0,
			  ),
			  'row3' => 
			  array (
			  ),
			)));
		$this->assertEqual( $expected,$rendered);
	}
	function test_PHP_Array_isMadeOfArray_test3()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simpleOneRow();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);	  	
	  	$rendered = $render->render();
	  
		$expected = serialize(array ('idSite'=>  
			array(
				  'row1' => 14.0,
				  'row2' => 15.0,
				  'row3' => array(),
				)));
		$this->assertEqual( $expected,$rendered);
	}
	


	function test_JSON_Array_test1()
	{
		$dataTable = $this->getDataTableArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
	  	$rendered = $render->render();
	  	$expected = '{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11,"url":"http:\/\/www.google.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.yahoo.com.png"}],"date2":[{"label":"Google1&copy;","nb_uniq_visitors":110,"nb_visits":110,"url":"http:\/\/www.google.com1","logo":".\/plugins\/Referers\/images\/searchEngines\/www.google.com.png1"},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510,"url":"http:\/\/www.yahoo.com1","logo":".\/plugins\/Referers\/images\/searchEngines\/www.yahoo.com.png1"}],"date3":[]}';

		$this->assertEqual( $expected,$rendered);
	}
	function test_JSON_Array_test2()
	{
		$dataTable = $this->getDataTableSimpleArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = '{"row1":{"max_actions":14,"nb_uniq_visitors":57},"row2":{"max_actions":140,"nb_uniq_visitors":570},"row3":[]}';

		$this->assertEqual( $expected,$rendered);
	}

	function test_JSON_Array_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = '{"row1":14,"row2":15,"row3":[]}';
		$this->assertEqual( $expected,$rendered);
	}
	
	function test_JSON_Array_isMadeOfArray_test1()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_normal();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
	  	$rendered = $render->render();
	  	$expected = '{"idSite":{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11,"url":"http:\/\/www.google.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.yahoo.com.png"}],"date2":[{"label":"Google1&copy;","nb_uniq_visitors":110,"nb_visits":110,"url":"http:\/\/www.google.com1","logo":".\/plugins\/Referers\/images\/searchEngines\/www.google.com.png1"},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510,"url":"http:\/\/www.yahoo.com1","logo":".\/plugins\/Referers\/images\/searchEngines\/www.yahoo.com.png1"}],"date3":[]}}';
		$this->assertEqual( $expected,$rendered);
	}
	function test_JSON_Array_isMadeOfArray_test2()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simple();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = '{"idSite":{"row1":{"max_actions":14,"nb_uniq_visitors":57},"row2":{"max_actions":140,"nb_uniq_visitors":570},"row3":[]}}';

		$this->assertEqual( $expected,$rendered);
	}

	function test_JSON_Array_isMadeOfArray_test3()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simpleOneRow();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
	  	$rendered = $render->render();
	  	
		$expected = '{"idSite":{"row1":14,"row2":15,"row3":[]}}';
		$this->assertEqual( $expected,$rendered);
	}
	
	function test_CSV_Array_test1()
	{
		$dataTable = $this->getDataTableArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'testKey,label,nb_uniq_visitors,nb_visits,metadata_url,metadata_logo
date1,Google,11,11,http://www.google.com,./plugins/Referers/images/searchEngines/www.google.com.png
date1,Yahoo!,15,151,http://www.yahoo.com,./plugins/Referers/images/searchEngines/www.yahoo.com.png
date2,Google1©,110,110,http://www.google.com1,./plugins/Referers/images/searchEngines/www.google.com.png1
date2,Yahoo!1,150,1510,http://www.yahoo.com1,./plugins/Referers/images/searchEngines/www.yahoo.com.png1';

		$this->assertEqual( $expected,$render->render());
	}
	function test_CSV_Array_test2()
	{
		$dataTable = $this->getDataTableSimpleArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'testKey,label,value
row1,max_actions,14
row1,nb_uniq_visitors,57
row2,max_actions,140
row2,nb_uniq_visitors,570';

		$this->assertEqual( $expected,$render->render());
	}

	function test_CSV_Array_test3()
	{
		$dataTable = $this->getDataTableSimpleOneRowArrayTest();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = "testKey,value
row1,14
row2,15";
		$this->assertEqual( $expected,$render->render());
	}
	
	
	function test_CSV_Array_isMadeOfArray_test1()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_normal();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'parentArrayKey,testKey,label,nb_uniq_visitors,nb_visits,metadata_url,metadata_logo
idSite,date1,Google,11,11,http://www.google.com,./plugins/Referers/images/searchEngines/www.google.com.png
idSite,date1,Yahoo!,15,151,http://www.yahoo.com,./plugins/Referers/images/searchEngines/www.yahoo.com.png
idSite,date2,Google1©,110,110,http://www.google.com1,./plugins/Referers/images/searchEngines/www.google.com.png1
idSite,date2,Yahoo!1,150,1510,http://www.yahoo.com1,./plugins/Referers/images/searchEngines/www.yahoo.com.png1';

		$this->assertEqual( $expected,$render->render());
	}
	function test_CSV_Array_isMadeOfArray_test2()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simple();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = 'parentArrayKey,testKey,label,value
idSite,row1,max_actions,14
idSite,row1,nb_uniq_visitors,57
idSite,row2,max_actions,140
idSite,row2,nb_uniq_visitors,570';

		$this->assertEqual( $expected,$render->render());
	}

	function test_CSV_Array_isMadeOfArray_test3()
	{
		$dataTable = $this->getDataTableArray_containsDataTableArray_simpleOneRow();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
	  	$render->convertToUnicode = false;
		$expected = "parentArrayKey,testKey,value
idSite,row1,14
idSite,row2,15";
		$this->assertEqual( $expected,$render->render());
	}
	
	
	
	
	
	/**
	 *  test with a row without child
	 * 			  a row with a child that has a child
	 * 			  a row with w child
	 */
	function test_Console_2SubLevelAnd2Different()
	{
		
	  	$table = new Piwik_DataTable;
	  	$idtable = $table->getId();
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::METADATA => array('logo' => 'test.png'),)
	  	
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
"- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br>\n- 2 ['visits' => 3] [] [idsubtable = $idsubtable1]<br>\n*- 1 ['visits' => 1] [] [idsubtable = $idsubsubtable]<br>\n**- 1 ['visits' => 2] [] [idsubtable = ]<br>\n- 3 ['visits' => 9] [] [idsubtable = $idsubtable2]<br>\n*- 1 ['visits' => 5] [] [idsubtable = ]<br>\n";
	  	/*
	  	 * RENDER
	  	 */
	  	$render = new Piwik_DataTable_Renderer_Console ($table);
	  	$render->setPrefixRow('*');
		$rendered = $render->render();
	  	
//		var_dump($expected);
//		var_dump($rendered);
	  	$this->assertEqual($expected,$rendered);
	}
	

	/**
	 *  test with a row without child
	 */
	function test_Console_Simple()
	{
		
	  	$table = new Piwik_DataTable;
	  	$table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array( 'visits'=>245,'visitors'=>245),
	  						Piwik_DataTable_Row::METADATA => array('logo' => 'test.png'),)
	  	
	  	);  	
	  	
	  	$expected="- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br>\n";
	  	
	  	/*
	  	 * RENDER
	  	 */
	  	$render = new Piwik_DataTable_Renderer_Console ($table);
	  	$rendered = $render->render();
	  	
	  	$this->assertEqual($expected,$rendered);
	  	
	}
}