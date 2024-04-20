<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Renderer\Json;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;

/**
 * @group DataTableTest
 */
class JSONTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
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
    protected function getDataTableTest()
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
                  Row::METADATA => array('url' => 'http://www.google.com/display"and,properly', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'),
            ),
            array(Row::COLUMNS              => array('label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'bool' => true, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90),
                  Row::METADATA             => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'),
                  Row::DATATABLE_ASSOCIATED => $subDataTableForRow2,
            )
        );
        $dataTable->addRowsFromArray($array);
        return $dataTable;
    }

    protected function getDataTableSimpleTest()
    {
        $array = array('max_actions' => 14.0, 'nb_uniq_visitors' => 57.0, 'nb_visits' => 66.0, 'nb_actions' => 151.0, 'sum_visit_length' => 5118.0, 'bounce_count' => 44.0,);

        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function getDataTableSimpleOneRowTest()
    {
        $array = array('nb_visits' => 14.0);

        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function getDataTableEmpty()
    {
        $table = new DataTable();
        return $table;
    }

    protected function getDataTableSimpleOneZeroRowTest()
    {
        $array = array('nb_visits' => 0);
        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected function getDataTableSimpleOneFalseRowTest()
    {
        $array = array('is_excluded' => false);
        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }


    public function testJSONTest1()
    {
        $dataTable = $this->getDataTableTest();
        $render = new Json();
        $render->setTable($dataTable);
        $render->setRenderSubTables(true);
        $expected = '[{"label":"Google\u00a9","bool":false,"goals":{"idgoal=1":{"revenue":5.5,"nb_conversions":10}},"nb_uniq_visitors":11,"nb_visits":11,"nb_actions":17,"max_actions":"5","sum_visit_length":517,"bounce_count":9,"url":"http:\/\/www.google.com\/display\"and,properly","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"bool":true,"nb_visits":151,"nb_actions":147,"max_actions":"50","sum_visit_length":517,"bounce_count":90,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png","idsubdatatable":2,"subtable":[{"label":"sub1","count":1,"bool":false},{"label":"sub2","count":2,"bool":true}]}]';
        $rendered = $render->render();

        $this->assertEquals($expected, $rendered);
    }


    public function testJSONTest2()
    {
        $dataTable = $this->getDataTableSimpleTest();
        $render = new Json();
        $render->setTable($dataTable);
        $expected = '{"max_actions":14,"nb_uniq_visitors":57,"nb_visits":66,"nb_actions":151,"sum_visit_length":5118,"bounce_count":44}';

        $this->assertEquals($expected, $render->render());
    }


    public function testJSONTest3()
    {
        $dataTable = $this->getDataTableSimpleOneRowTest();
        $render = new Json();
        $render->setTable($dataTable);
        $expected = '{"value":14}';
        $this->assertEquals($expected, $render->render());
    }


    public function testJSONTest4()
    {
        $dataTable = $this->getDataTableEmpty();
        $render = new Json();
        $render->setTable($dataTable);
        $expected = '[]';
        $this->assertEquals($expected, $render->render());
    }


    public function testJSONTest5()
    {
        $dataTable = $this->getDataTableSimpleOneZeroRowTest();
        $render = new Json();
        $render->setTable($dataTable);
        $expected = '{"value":0}';
        $this->assertEquals($expected, $render->render());
    }


    public function testJSONTest6()
    {
        $dataTable = $this->getDataTableSimpleOneFalseRowTest();
        $render = new Json();
        $render->setTable($dataTable);
        $expected = '{"value":false}';
        $this->assertEquals($expected, $render->render());
    }

    /**
     * DATA OF DATATABLE_ARRAY
     * -------------------------
     */

    protected function getDataTableMapTest()
    {
        $array1 = array(
            array(Row::COLUMNS  => array('label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11,),
                  Row::METADATA => array('url' => 'http://www.google.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'),
            ),
            array(Row::COLUMNS  => array('label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151,),
                  Row::METADATA => array('url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'),
            )
        );
        $table1 = new DataTable();
        $table1->addRowsFromArray($array1);

        $array2 = array(
            array(Row::COLUMNS  => array('label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110,),
                  Row::METADATA => array('url' => 'http://www.google.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1'),
            ),
            array(Row::COLUMNS  => array('label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510,),
                  Row::METADATA => array('url' => 'http://www.yahoo.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1'),
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

    protected function getDataTableSimpleMapTest()
    {
        $array1 = array('max_actions' => 14.0, 'nb_uniq_visitors' => 57.0,);
        $table1 = new Simple();
        $table1->addRowsFromArray($array1);

        $array2 = array('max_actions' => 140.0, 'nb_uniq_visitors' => 570.0,);
        $table2 = new Simple();
        $table2->addRowsFromArray($array2);

        $table3 = new Simple();

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected function getDataTableSimpleOneRowMapTest()
    {
        $array1 = array('nb_visits' => 14.0);
        $table1 = new Simple();
        $table1->addRowsFromArray($array1);
        $array2 = array('nb_visits' => 15.0);
        $table2 = new Simple();
        $table2->addRowsFromArray($array2);

        $table3 = new Simple();

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected function getDataTableMap_containsDataTableMap_normal()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->getDataTableMapTest(), 'idSite');
        return $table;
    }

    protected function getDataTableMap_containsDataTableMap_simple()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->getDataTableSimpleMapTest(), 'idSite');
        return $table;
    }

    protected function getDataTableMap_containsDataTableMap_simpleOneRow()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->getDataTableSimpleOneRowMapTest(), 'idSite');
        return $table;
    }


    public function testJSONArrayTest1()
    {
        $dataTable = $this->getDataTableMapTest();
        $render = new Json();
        $render->setTable($dataTable);
        $rendered = $render->render();
        $expected = '{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11,"url":"http:\/\/www.google.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png"}],"date2":[{"label":"Google1\u00a9","nb_uniq_visitors":110,"nb_visits":110,"url":"http:\/\/www.google.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png1"},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510,"url":"http:\/\/www.yahoo.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png1"}],"date3":[]}';

        $this->assertEquals($expected, $rendered);
    }


    public function testJSONMapTest2()
    {
        $dataTable = $this->getDataTableSimpleMapTest();
        $render = new Json();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = '{"row1":{"max_actions":14,"nb_uniq_visitors":57},"row2":{"max_actions":140,"nb_uniq_visitors":570},"row3":[]}';

        $this->assertEquals($expected, $rendered);
    }


    public function testJSONMapTest3()
    {
        $dataTable = $this->getDataTableSimpleOneRowMapTest();
        $render = new Json();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = '{"row1":14,"row2":15,"row3":[]}';
        $this->assertEquals($expected, $rendered);
    }


    public function testJSONMapIsMadeOfMapTest1()
    {
        $dataTable = $this->getDataTableMap_containsDataTableMap_normal();
        $render = new Json();
        $render->setTable($dataTable);
        $rendered = $render->render();
        $expected = '{"idSite":{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11,"url":"http:\/\/www.google.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png"}],"date2":[{"label":"Google1\u00a9","nb_uniq_visitors":110,"nb_visits":110,"url":"http:\/\/www.google.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png1"},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510,"url":"http:\/\/www.yahoo.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png1"}],"date3":[]}}';
        $this->assertEquals($expected, $rendered);
    }


    public function testJSONMapIsMadeOfMapTest2()
    {
        $dataTable = $this->getDataTableMap_containsDataTableMap_simple();
        $render = new Json();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = '{"idSite":{"row1":{"max_actions":14,"nb_uniq_visitors":57},"row2":{"max_actions":140,"nb_uniq_visitors":570},"row3":[]}}';

        $this->assertEquals($expected, $rendered);
    }


    public function testJSONMapIsMadeOfMapTest3()
    {
        $dataTable = $this->getDataTableMap_containsDataTableMap_simpleOneRow();
        $render = new Json();
        $render->setTable($dataTable);

        $expected = '{"idSite":{"row1":14,"row2":15,"row3":[]}}';
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testRenderArray1()
    {
        $data = array();

        $render = new Json();
        $render->setTable($data);
        $expected = '[]';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray2()
    {
        $data = array('a', 'b', 'c', array('a' => 'b'), array(1, 2));

        $render = new Json();
        $render->setTable($data);
        $expected = '["a","b","c",{"a":"b"},[1,2]]';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray3()
    {
        $data = array('a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g');

        $render = new Json();
        $render->setTable($data);
        $expected = '[{"a":"b","c":"d","e":"f","5":"g"}]';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray4()
    {
        $data = array('a' => 'b', 'c' => array(1, 2, 3, 4), 'e' => array('f' => 'g', 'h' => 'i', 'j' => 'k'));

        $render = new Json();
        $render->setTable($data);
        $expected = '{"a":"b","c":[1,2,3,4],"e":{"f":"g","h":"i","j":"k"}}';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray5()
    {
        $data = array('a' => 'b');

        $render = new Json();
        $render->setTable($data);
        $expected = '[{"a":"b"}]';

        $this->assertEquals($expected, $render->render());
    }

    public function test_render_withRowsWithDataTableMetadata()
    {
        $dataTable = new DataTable();

        $row = new DataTable\Row();
        $row->addColumn('nb_visits', 5);
        $row->addColumn('nb_random', 10);

        $otherDataTable = new DataTable();
        $otherDataTable->addRowsFromSimpleArray([
            ['nb_visits' => 6, 'nb_random' => 7],
            ['nb_visits' => 8, 'nb_random' => 9],
        ]);
        $row->setComparisons($otherDataTable);

        $dataTable->addRow($row);

        $render = new Json();
        $render->setTable($dataTable);
        $actual = $render->render();

        $expected = '[{"nb_visits":5,"nb_random":10,"comparisons":[{"nb_visits":6,"nb_random":7},{"nb_visits":8,"nb_random":9}]}]';

        $this->assertEquals($expected, $actual);
    }

    public function test_render_withRowsWithDataTableMetadataInSimpleTable()
    {
        $dataTable = new Simple();

        $row = new DataTable\Row();
        $row->addColumn('nb_visits', 5);
        $row->addColumn('nb_random', 10);

        $otherDataTable = new DataTable();
        $otherDataTable->addRowsFromSimpleArray([
            ['nb_visits' => 6, 'nb_random' => 7],
            ['nb_visits' => 8, 'nb_random' => 9],
        ]);
        $row->setComparisons($otherDataTable);

        $dataTable->addRow($row);

        $render = new Json();
        $render->setTable($dataTable);
        $actual = $render->render();

        $expected = '{"nb_visits":5,"nb_random":10,"comparisons":[{"nb_visits":6,"nb_random":7},{"nb_visits":8,"nb_random":9}]}';

        $this->assertEquals($expected, $actual);
    }
}
