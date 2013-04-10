<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Renderer_XMLTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Piwik_DataTable_Manager::getInstance()->deleteAll();
    }

    /**
     * DATA TESTS
     * -----------------------
     * for each renderer we test the case
     * - datatableSimple
     * - normal datatable  with 2 row (including columns and metadata)
     */
    protected function _getDataTableTest()
    {
        $dataTable = new Piwik_DataTable();

        $arraySubTableForRow2 = array(
            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'sub1', 'count' => 1, 'bool' => false)),
            array(Piwik_DataTable_Row::COLUMNS => array('label' => 'sub2', 'count' => 2, 'bool' => true)),
        );
        $subDataTableForRow2 = new Piwik_DataTable();
        $subDataTableForRow2->addRowsFromArray($arraySubTableForRow2);

        $array = array(
            array(Piwik_DataTable_Row::COLUMNS  => array('label' => 'Google&copy;', 'bool' => false, 'goals' => array('idgoal=1' => array('revenue' => 5.5, 'nb_conversions' => 10)), 'nb_uniq_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9),
                  Piwik_DataTable_Row::METADATA => array('url' => 'http://www.google.com/display"and,properly', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png'),
            ),
            array(Piwik_DataTable_Row::COLUMNS              => array('label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'bool' => true, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90),
                  Piwik_DataTable_Row::METADATA             => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png'),
                  Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subDataTableForRow2,
            )
        );
        $dataTable->addRowsFromArray($array);
        return $dataTable;
    }

    protected function _getDataTableSimpleTest()
    {
        $array = array('max_actions' => 14.0, 'nb_uniq_visitors' => 57.0, 'nb_visits' => 66.0, 'nb_actions' => 151.0, 'sum_visit_length' => 5118.0, 'bounce_count' => 44.0,);

        $table = new Piwik_DataTable_Simple;
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function _getDataTableSimpleOneRowTest()
    {
        $array = array('nb_visits' => 14.0);

        $table = new Piwik_DataTable_Simple;
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function _getDataTableEmpty()
    {
        $table = new Piwik_DataTable;
        return $table;
    }

    protected function _getDataTableSimpleOneZeroRowTest()
    {
        $array = array('nb_visits' => 0);
        $table = new Piwik_DataTable_Simple;
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function _getDataTableSimpleOneFalseRowTest()
    {
        $array = array('is_excluded' => false);
        $table = new Piwik_DataTable_Simple;
        $table->addRowsFromArray($array);
        return $table;
    }


    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLTest1()
    {
        $dataTable = $this->_getDataTableTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $render->setRenderSubTables(true);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>Google©</label>
		<bool>0</bool>
		<goals>
			<row idgoal=\'1\'>
				<revenue>5.5</revenue>
				<nb_conversions>10</nb_conversions>
			</row>
		</goals>
		<nb_uniq_visitors>11</nb_uniq_visitors>
		<nb_visits>11</nb_visits>
		<nb_actions>17</nb_actions>
		<max_actions>5</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>9</bounce_count>
		<url>http://www.google.com/display&quot;and,properly</url>
		<logo>./plugins/Referers/images/searchEngines/www.google.com.png</logo>
	</row>
	<row>
		<label>Yahoo!</label>
		<nb_uniq_visitors>15</nb_uniq_visitors>
		<bool>1</bool>
		<nb_visits>151</nb_visits>
		<nb_actions>147</nb_actions>
		<max_actions>50</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>90</bounce_count>
		<url>http://www.yahoo.com</url>
		<logo>./plugins/Referers/images/searchEngines/www.yahoo.com.png</logo>
		<idsubdatatable>2</idsubdatatable>
		<subtable>
			<row>
				<label>sub1</label>
				<count>1</count>
				<bool>0</bool>
			</row>
			<row>
				<label>sub2</label>
				<count>2</count>
				<bool>1</bool>
			</row>
		</subtable>
	</row>
</result>';
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLTest2()
    {
        $dataTable = $this->_getDataTableSimpleTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<max_actions>14</max_actions>
	<nb_uniq_visitors>57</nb_uniq_visitors>
	<nb_visits>66</nb_visits>
	<nb_actions>151</nb_actions>
	<sum_visit_length>5118</sum_visit_length>
	<bounce_count>44</bounce_count>
</result>';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLTest3()
    {
        $dataTable = $this->_getDataTableSimpleOneRowTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>14</result>';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLTest4()
    {
        $dataTable = $this->_getDataTableEmpty();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result />';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLTest5()
    {
        $dataTable = $this->_getDataTableSimpleOneZeroRowTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLTest6()
    {
        $dataTable = $this->_getDataTableSimpleOneFalseRowTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>';
        $this->assertEquals($expected, $render->render());
    }


    /**
     * DATA OF DATATABLE_ARRAY
     * -------------------------
     */

    protected function _getDataTableArrayTest()
    {
        $array1 = array(
            array(Piwik_DataTable_Row::COLUMNS  => array('label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11,),
                  Piwik_DataTable_Row::METADATA => array('url' => 'http://www.google.com', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png'),
            ),
            array(Piwik_DataTable_Row::COLUMNS  => array('label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151,),
                  Piwik_DataTable_Row::METADATA => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png'),
            )
        );
        $table1 = new Piwik_DataTable();
        $table1->addRowsFromArray($array1);


        $array2 = array(
            array(Piwik_DataTable_Row::COLUMNS  => array('label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110,),
                  Piwik_DataTable_Row::METADATA => array('url' => 'http://www.google.com1', 'logo' => './plugins/Referers/images/searchEngines/www.google.com.png1'),
            ),
            array(Piwik_DataTable_Row::COLUMNS  => array('label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510,),
                  Piwik_DataTable_Row::METADATA => array('url' => 'http://www.yahoo.com1', 'logo' => './plugins/Referers/images/searchEngines/www.yahoo.com.png1'),
            )
        );
        $table2 = new Piwik_DataTable();
        $table2->addRowsFromArray($array2);

        $table3 = new Piwik_DataTable();


        $table = new Piwik_DataTable_Array();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'date1');
        $table->addTable($table2, 'date2');
        $table->addTable($table3, 'date3');

        return $table;
    }

    protected function _getDataTableSimpleArrayTest()
    {
        $array1 = array('max_actions' => 14.0, 'nb_uniq_visitors' => 57.0,);
        $table1 = new Piwik_DataTable_Simple;
        $table1->addRowsFromArray($array1);

        $array2 = array('max_actions' => 140.0, 'nb_uniq_visitors' => 570.0,);
        $table2 = new Piwik_DataTable_Simple;
        $table2->addRowsFromArray($array2);

        $table3 = new Piwik_DataTable_Simple;

        $table = new Piwik_DataTable_Array();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected function _getDataTableSimpleOneRowArrayTest()
    {
        $array1 = array('nb_visits' => 14.0);
        $table1 = new Piwik_DataTable_Simple;
        $table1->addRowsFromArray($array1);
        $array2 = array('nb_visits' => 15.0);
        $table2 = new Piwik_DataTable_Simple;
        $table2->addRowsFromArray($array2);

        $table3 = new Piwik_DataTable_Simple;

        $table = new Piwik_DataTable_Array();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected function _getDataTableArray_containsDataTableArray_normal()
    {
        $table = new Piwik_DataTable_Array();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->_getDataTableArrayTest(), 'idSite');
        return $table;
    }

    protected function _getDataTableArray_containsDataTableArray_simple()
    {
        $table = new Piwik_DataTable_Array();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->_getDataTableSimpleArrayTest(), 'idSite');
        return $table;
    }

    protected function _getDataTableArray_containsDataTableArray_simpleOneRow()
    {
        $table = new Piwik_DataTable_Array();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->_getDataTableSimpleOneRowArrayTest(), 'idSite');
        return $table;
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLArrayTest1()
    {
        $dataTable = $this->_getDataTableArrayTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
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
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLArrayIsMadeOfArrayTest1()
    {
        $dataTable = $this->_getDataTableArray_containsDataTableArray_normal();

        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
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
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLArrayTest2()
    {
        $dataTable = $this->_getDataTableSimpleArrayTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);

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
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLArrayIsMadeOfArrayTest2()
    {
        $dataTable = $this->_getDataTableArray_containsDataTableArray_simple();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
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
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLArrayTest3()
    {
        $dataTable = $this->_getDataTableSimpleOneRowArrayTest();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);

        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="row1">14</result>
	<result testKey="row2">15</result>
	<result testKey="row3" />
</results>';
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testXMLArrayIsMadeOfArrayTest3()
    {
        $dataTable = $this->_getDataTableArray_containsDataTableArray_simpleOneRow();
        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="row1">14</result>
		<result testKey="row2">15</result>
		<result testKey="row3" />
	</result>
</results>';
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testRenderArray1()
    {
        $data = array();

        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($data);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result />';

        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testRenderArray2()
    {
        $data = array("firstElement",
                      array("firstElement",
                            "secondElement"),
                      "thirdElement");

        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($data);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>firstElement</row>
	<row>
		<row>firstElement</row>
		<row>secondElement</row>
	</row>
	<row>thirdElement</row>
</result>';

        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testRenderArray3()
    {
        $data = array('a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g');

        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($data);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<a>b</a>
		<c>d</c>
		<e>f</e>
		<row key="5">g</row>
	</row>
</result>';

        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_XML
     */
    public function testRenderArray4()
    {
        $data = array('c' => array(1, 2, 3, 4), 'e' => array('f' => 'g', 'h' => 'i', 'j' => 'k'));

        $render = new Piwik_DataTable_Renderer_Xml();
        $render->setTable($data);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<c>
		<row>1</row>
		<row>2</row>
		<row>3</row>
		<row>4</row>
	</c>
	<e>
		<f>g</f>
		<h>i</h>
		<j>k</j>
	</e>
</result>';

        $this->assertEquals($expected, $render->render());
    }
}
