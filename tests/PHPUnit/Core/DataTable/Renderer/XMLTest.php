<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Renderer\Xml;
use Piwik\DataTable\Simple;
use Piwik\DataTable\Row;

class DataTable_Renderer_XMLTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Manager::getInstance()->deleteAll();
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
        $dataTable = new DataTable();

        $arraySubTableForRow2 = array(
            array(Row::COLUMNS => array('label' => 'sub1', 'count' => 1, 'bool' => false)),
            array(Row::COLUMNS => array('label' => 'sub2', 'count' => 2, 'bool' => true)),
        );
        $subDataTableForRow2 = new DataTable();
        $subDataTableForRow2->addRowsFromArray($arraySubTableForRow2);

        $array = array(
            array(Row::COLUMNS  => array('label' => 'Google&copy;', 'bool' => false, 'goals' => array('idgoal=1' => array('revenue' => 5.5, 'nb_conversions' => 10)), 'nb_uniq_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9),
                  Row::METADATA => array('url' => 'http://www.google.com/display"and,properly', 'logo' => './plugins/Referrers/images/searchEngines/www.google.com.png'),
            ),
            array(Row::COLUMNS              => array('label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'bool' => true, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90),
                  Row::METADATA             => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referrers/images/searchEngines/www.yahoo.com.png'),
                  Row::DATATABLE_ASSOCIATED => $subDataTableForRow2,
            )
        );
        $dataTable->addRowsFromArray($array);
        return $dataTable;
    }

    protected function _getDataTableSimpleTest()
    {
        $array = array('max_actions' => 14.0, 'nb_uniq_visitors' => 57.0, 'nb_visits' => 66.0, 'nb_actions' => 151.0, 'sum_visit_length' => 5118.0, 'bounce_count' => 44.0,);

        $table = new Simple;
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function _getDataTableSimpleOneRowTest()
    {
        $array = array('nb_visits' => 14.0);

        $table = new Simple;
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function _getDataTableEmpty()
    {
        $table = new DataTable;
        return $table;
    }

    protected function _getDataTableSimpleOneZeroRowTest()
    {
        $array = array('nb_visits' => 0);
        $table = new Simple;
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function _getDataTableSimpleOneFalseRowTest()
    {
        $array = array('is_excluded' => false);
        $table = new Simple;
        $table->addRowsFromArray($array);
        return $table;
    }


    /**
     * @group Core
     */
    public function testXMLTest1()
    {
        $dataTable = $this->_getDataTableTest();
        $render = new Xml();
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
		<logo>./plugins/Referrers/images/searchEngines/www.google.com.png</logo>
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
		<logo>./plugins/Referrers/images/searchEngines/www.yahoo.com.png</logo>
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
     */
    public function testXMLTest2()
    {
        $dataTable = $this->_getDataTableSimpleTest();
        $render = new Xml();
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
     */
    public function testXMLTest3()
    {
        $dataTable = $this->_getDataTableSimpleOneRowTest();
        $render = new Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>14</result>';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testXMLTest4()
    {
        $dataTable = $this->_getDataTableEmpty();
        $render = new Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result />';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testXMLTest5()
    {
        $dataTable = $this->_getDataTableSimpleOneZeroRowTest();
        $render = new Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testXMLTest6()
    {
        $dataTable = $this->_getDataTableSimpleOneFalseRowTest();
        $render = new Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>';
        $this->assertEquals($expected, $render->render());
    }


    /**
     * DATA OF DATATABLE_ARRAY
     * -------------------------
     */

    protected function _getDataTableMapTest()
    {
        $array1 = array(
            array(Row::COLUMNS  => array('label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11,),
                  Row::METADATA => array('url' => 'http://www.google.com', 'logo' => './plugins/Referrers/images/searchEngines/www.google.com.png'),
            ),
            array(Row::COLUMNS  => array('label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151,),
                  Row::METADATA => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Referrers/images/searchEngines/www.yahoo.com.png'),
            )
        );
        $table1 = new DataTable();
        $table1->addRowsFromArray($array1);


        $array2 = array(
            array(Row::COLUMNS  => array('label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110,),
                  Row::METADATA => array('url' => 'http://www.google.com1', 'logo' => './plugins/Referrers/images/searchEngines/www.google.com.png1'),
            ),
            array(Row::COLUMNS  => array('label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510,),
                  Row::METADATA => array('url' => 'http://www.yahoo.com1', 'logo' => './plugins/Referrers/images/searchEngines/www.yahoo.com.png1'),
            )
        );
        $table2 = new DataTable();
        $table2->addRowsFromArray($array2);

        $table3 = new DataTable();


        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'date1');
        $table->addTable($table2, 'date2');
        $table->addTable($table3, 'date3');

        return $table;
    }

    protected function _getDataTableSimpleMapTest()
    {
        $array1 = array('max_actions' => 14.0, 'nb_uniq_visitors' => 57.0,);
        $table1 = new Simple;
        $table1->addRowsFromArray($array1);

        $array2 = array('max_actions' => 140.0, 'nb_uniq_visitors' => 570.0,);
        $table2 = new Simple;
        $table2->addRowsFromArray($array2);

        $table3 = new Simple;

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected function _getDataTableSimpleOneRowMapTest()
    {
        $array1 = array('nb_visits' => 14.0);
        $table1 = new Simple;
        $table1->addRowsFromArray($array1);
        $array2 = array('nb_visits' => 15.0);
        $table2 = new Simple;
        $table2->addRowsFromArray($array2);

        $table3 = new Simple;

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected function _getDataTableMap_containsDataTableMap_normal()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->_getDataTableMapTest(), 'idSite');
        return $table;
    }

    protected function _getDataTableMap_containsDataTableMap_simple()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->_getDataTableSimpleMapTest(), 'idSite');
        return $table;
    }

    protected function _getDataTableMap_containsDataTableMap_simpleOneRow()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->_getDataTableSimpleOneRowMapTest(), 'idSite');
        return $table;
    }

    /**
     * @group Core
     */
    public function testXMLMapTest1()
    {
        $dataTable = $this->_getDataTableMapTest();
        $render = new Xml();
        $render->setTable($dataTable);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="date1">
		<row>
			<label>Google</label>
			<nb_uniq_visitors>11</nb_uniq_visitors>
			<nb_visits>11</nb_visits>
			<url>http://www.google.com</url>
			<logo>./plugins/Referrers/images/searchEngines/www.google.com.png</logo>
		</row>
		<row>
			<label>Yahoo!</label>
			<nb_uniq_visitors>15</nb_uniq_visitors>
			<nb_visits>151</nb_visits>
			<url>http://www.yahoo.com</url>
			<logo>./plugins/Referrers/images/searchEngines/www.yahoo.com.png</logo>
		</row>
	</result>
	<result testKey="date2">
		<row>
			<label>Google1©</label>
			<nb_uniq_visitors>110</nb_uniq_visitors>
			<nb_visits>110</nb_visits>
			<url>http://www.google.com1</url>
			<logo>./plugins/Referrers/images/searchEngines/www.google.com.png1</logo>
		</row>
		<row>
			<label>Yahoo!1</label>
			<nb_uniq_visitors>150</nb_uniq_visitors>
			<nb_visits>1510</nb_visits>
			<url>http://www.yahoo.com1</url>
			<logo>./plugins/Referrers/images/searchEngines/www.yahoo.com.png1</logo>
		</row>
	</result>
	<result testKey="date3" />
</results>';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testXMLArrayIsMadeOfMapTest1()
    {
        $dataTable = $this->_getDataTableMap_containsDataTableMap_normal();

        $render = new Xml();
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
				<logo>./plugins/Referrers/images/searchEngines/www.google.com.png</logo>
			</row>
			<row>
				<label>Yahoo!</label>
				<nb_uniq_visitors>15</nb_uniq_visitors>
				<nb_visits>151</nb_visits>
				<url>http://www.yahoo.com</url>
				<logo>./plugins/Referrers/images/searchEngines/www.yahoo.com.png</logo>
			</row>
		</result>
		<result testKey="date2">
			<row>
				<label>Google1©</label>
				<nb_uniq_visitors>110</nb_uniq_visitors>
				<nb_visits>110</nb_visits>
				<url>http://www.google.com1</url>
				<logo>./plugins/Referrers/images/searchEngines/www.google.com.png1</logo>
			</row>
			<row>
				<label>Yahoo!1</label>
				<nb_uniq_visitors>150</nb_uniq_visitors>
				<nb_visits>1510</nb_visits>
				<url>http://www.yahoo.com1</url>
				<logo>./plugins/Referrers/images/searchEngines/www.yahoo.com.png1</logo>
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
     */
    public function testXMLMapTest2()
    {
        $dataTable = $this->_getDataTableSimpleMapTest();
        $render = new Xml();
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
     */
    public function testXMLArrayIsMadeOfMapTest2()
    {
        $dataTable = $this->_getDataTableMap_containsDataTableMap_simple();
        $render = new Xml();
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
     */
    public function testXMLMapTest3()
    {
        $dataTable = $this->_getDataTableSimpleOneRowMapTest();
        $render = new Xml();
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
     */
    public function testXMLArrayIsMadeOfMapTest3()
    {
        $dataTable = $this->_getDataTableMap_containsDataTableMap_simpleOneRow();
        $render = new Xml();
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
     */
    public function testRenderArray1()
    {
        $data = array();

        $render = new Xml();
        $render->setTable($data);
        $expected = '<?xml version="1.0" encoding="utf-8" ?>
<result />';

        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testRenderArray2()
    {
        $data = array("firstElement",
                      array("firstElement",
                            "secondElement"),
                      "thirdElement");

        $render = new Xml();
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
     */
    public function testRenderArray3()
    {
        $data = array('a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g');

        $render = new Xml();
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
     */
    public function testRenderArray4()
    {
        $data = array('c' => array(1, 2, 3, 4), 'e' => array('f' => 'g', 'h' => 'i', 'j' => 'k'));

        $render = new Xml();
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
