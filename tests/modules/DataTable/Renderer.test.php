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
	}
	
	public function tearDown()
	{
	}
	
	
	/**
	 * for each renderer we test the case
	 * - datatableSimple
	 * - normal datatable  with 2 row (including columns and details)	 *
	 */
	protected function getTableTest1()
	{
		$array = array ( 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Google', 'nb_unique_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9), 
						Piwik_DataTable_Row::DETAILS => array('url' => 'http://www.google.com', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png'), 
					 ), 
			array ( Piwik_DataTable_Row::COLUMNS => array( 'label' => 'Yahoo!', 'nb_unique_visitors' => 15, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90), 
						Piwik_DataTable_Row::DETAILS => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png'), 
					 )
			);
		$dataTable = new Piwik_DataTable();
		$dataTable->loadFromArray($array);
		return $dataTable;
	}
	protected function getTableTest2()
	{
		$array = array ( 'max_actions' => 14.0, 'nb_uniq_visitors' => 57.0, 'nb_visits' => 66.0, 'nb_actions' => 151.0, 'sum_visit_length' => 5118.0, 'bounce_count' => 44.0, );
		
		$table = new Piwik_DataTable_Simple;
		$table->loadFromArray($array);
		return $table;
	}

	function test_XML_test1()
	{
		$dataTable = $this->getTableTest1();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<result>
	<row>
		<label>Google</label>
		<nb_unique_visitors>11</nb_unique_visitors>
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
		<nb_unique_visitors>15</nb_unique_visitors>
		<nb_visits>151</nb_visits>
		<nb_actions>147</nb_actions>
		<max_actions>50</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>90</bounce_count>
		<url>http://www.yahoo.com</url>
		<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png</logo>
	</row>
</result>';
		$this->assertEqual( $expected,$render->render());
	}

	function test_XML_test2()
	{
		$dataTable = $this->getTableTest2();
	  	$render = new Piwik_DataTable_Renderer_Xml($dataTable);
		$expected = '<result>
	<max_actions>14</max_actions>
	<nb_uniq_visitors>57</nb_uniq_visitors>
	<nb_visits>66</nb_visits>
	<nb_actions>151</nb_actions>
	<sum_visit_length>5118</sum_visit_length>
	<bounce_count>44</bounce_count>
</result>';
		$this->assertEqual( $expected,$render->render());
	}
	function test_CSV_test1()
	{
		$dataTable = $this->getTableTest1();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
		$expected = 'label,nb_unique_visitors,nb_visits,nb_actions,max_actions,sum_visit_length,bounce_count,detail_url,detail_logo
Google,11,11,17,5,517,9,http://www.google.com,./plugins/Referers/images/searchEngines/www.google.com.png
Yahoo!,15,151,147,50,517,90,http://www.yahoo.com,./plugins/Referers/images/searchEngines/www.yahoo.com.png';

		$this->assertEqual( $expected,$render->render());
	}
	function test_CSV_test2()
	{
		$dataTable = $this->getTableTest2();
	  	$render = new Piwik_DataTable_Renderer_Csv($dataTable);
		$expected = 'label,value
max_actions,14
nb_uniq_visitors,57
nb_visits,66
nb_actions,151
sum_visit_length,5118
bounce_count,44';

		$this->assertEqual( $expected,$render->render());
	}

	function test_JSON_test1()
	{
		$dataTable = $this->getTableTest1();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
		$expected = '[{"label":"Google","nb_unique_visitors":11,"nb_visits":11,"nb_actions":17,"max_actions":"5","sum_visit_length":517,"bounce_count":9,"url":"http:\/\/www.google.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_unique_visitors":15,"nb_visits":151,"nb_actions":147,"max_actions":"50","sum_visit_length":517,"bounce_count":90,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Referers\/images\/searchEngines\/www.yahoo.com.png"}]';

		$this->assertEqual( $expected,$render->render());
	}
	function test_JSON_test2()
	{
		$dataTable = $this->getTableTest2();
	  	$render = new Piwik_DataTable_Renderer_Json($dataTable);
		$expected = '{"max_actions":14,"nb_uniq_visitors":57,"nb_visits":66,"nb_actions":151,"sum_visit_length":5118,"bounce_count":44}';

		$this->assertEqual( $expected,$render->render());
	}

	function test_PHP_test1()
	{
		$dataTable = $this->getTableTest1();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
		$expected = 'a:2:{i:0;a:9:{s:5:"label";s:6:"Google";s:18:"nb_unique_visitors";i:11;s:9:"nb_visits";i:11;s:10:"nb_actions";i:17;s:11:"max_actions";s:1:"5";s:16:"sum_visit_length";i:517;s:12:"bounce_count";i:9;s:3:"url";s:21:"http://www.google.com";s:4:"logo";s:58:"./plugins/Referers/images/searchEngines/www.google.com.png";}i:1;a:9:{s:5:"label";s:6:"Yahoo!";s:18:"nb_unique_visitors";i:15;s:9:"nb_visits";i:151;s:10:"nb_actions";i:147;s:11:"max_actions";s:2:"50";s:16:"sum_visit_length";i:517;s:12:"bounce_count";i:90;s:3:"url";s:20:"http://www.yahoo.com";s:4:"logo";s:57:"./plugins/Referers/images/searchEngines/www.yahoo.com.png";}}';
		$this->assertEqual( $expected,$render->render());
	}
	function test_PHP_test2()
	{
		$dataTable = $this->getTableTest2();
	  	$render = new Piwik_DataTable_Renderer_Php($dataTable);
		$expected = 'a:6:{s:11:"max_actions";d:14;s:16:"nb_uniq_visitors";d:57;s:9:"nb_visits";d:66;s:10:"nb_actions";d:151;s:16:"sum_visit_length";d:5118;s:12:"bounce_count";d:44;}';
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
}