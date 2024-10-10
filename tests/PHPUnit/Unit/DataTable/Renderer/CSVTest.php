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
use Piwik\DataTable\Renderer\Csv;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;

/**
 * @group DataTableTest
 */
class CSVTest extends \PHPUnit\Framework\TestCase
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

    protected function getDataTableHavingAnArrayInRowMetadata()
    {
        $array = array(
            array(Row::COLUMNS => array('label' => 'sub1', 'count' => 1)),
            array(Row::COLUMNS => array('label' => 'sub2', 'count' => 2), Row::METADATA => array('test' => 'render')),
            array(Row::COLUMNS => array('label' => 'sub3', 'count' => 2), Row::METADATA => array('test' => 'renderMe', 'testArray' => 'ignore')),
            array(Row::COLUMNS => array('label' => 'sub4', 'count' => 6), Row::METADATA => array('testArray' => array('do not render'))),
            array(Row::COLUMNS => array('label' => 'sub5', 'count' => 2), Row::METADATA => array('testArray' => 'do ignore', 'mymeta' => 'should be rendered')),
            array(Row::COLUMNS => array('label' => 'sub6', 'count' => 3), Row::METADATA => array('mymeta' => 'renderrrrrr')),
        );

        $table = new DataTable();
        $table->addRowsFromArray($array);

        return $table;
    }

    public function testCSVTest1()
    {
        $dataTable = $this->getDataTableTest();

        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "label,bool,goals_idgoal=1_revenue,goals_idgoal=1_nb_conversions,nb_uniq_visitors,nb_visits,nb_actions,max_actions,sum_visit_length,bounce_count,metadata_url,metadata_logo\n" .
            "Google©,0,5.5,10,11,11,17,5,517,9,\"http://www.google.com/display\"\"and,properly\",./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "Yahoo!,1,,,15,151,147,50,517,90,http://www.yahoo.com,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png";

        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVTest2()
    {
        $dataTable = $this->getDataTableSimpleTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "max_actions,nb_uniq_visitors,nb_visits,nb_actions,sum_visit_length,bounce_count\n14,57,66,151,5118,44";

        $this->assertEquals($expected, $render->render());
    }


    public function testCSVTest3()
    {
        $dataTable = $this->getDataTableSimpleOneRowTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "value\n14";
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVTest5()
    {
        $dataTable = $this->getDataTableSimpleOneZeroRowTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "value\n0";
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVTest4()
    {
        $dataTable = $this->getDataTableEmpty();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = 'No data available';
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVTest6()
    {
        $dataTable = $this->getDataTableSimpleOneFalseRowTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "value\n0";
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVRendererCorrectlyEscapesHeadersAndValues()
    {
        $dataTable = $this->getDataTableSimpleWithCommasInCells();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->setSeparator('#');
        $render->convertToUnicode = false;

        $expected = '"col,1"#"col;2"
"val""1"#"val"",2"
val#"val#2"';
        $actual = $render->render();
        $this->assertEquals($expected, $actual);
    }

    public function testCSVTest7ShouldNotRenderMetadataThatContainsAnArray()
    {
        $dataTable = $this->getDataTableHavingAnArrayInRowMetadata();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;

        // the column "testArray" should be ignored and not rendered, all other columns should be assigned correctly
        $expected = "label,count,metadata_test,metadata_mymeta
sub1,1,,
sub2,2,render,
sub3,2,renderMe,
sub4,6,,
sub5,2,,should be rendered
sub6,3,,renderrrrrr";
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
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

    protected function getDataTableMapContainsDataTableMapNormal()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->getDataTableMapTest(), 'idSite');
        return $table;
    }

    protected function getDataTableMapContainsDataTableMapSimple()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->getDataTableSimpleMapTest(), 'idSite');
        return $table;
    }

    protected function getDataTableMapContainsDataTableMapSimpleOneRow()
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable($this->getDataTableSimpleOneRowMapTest(), 'idSite');
        return $table;
    }


    public function testCSVMapTest1()
    {
        $dataTable = $this->getDataTableMapTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "testKey,label,nb_uniq_visitors,nb_visits,metadata_url,metadata_logo\n" .
            "date1,Google,11,11,http://www.google.com,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "date1,Yahoo!,15,151,http://www.yahoo.com,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png\n" .
            "date2,Google1©,110,110,http://www.google.com1,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1\n" .
            "date2,Yahoo!1,150,1510,http://www.yahoo.com1,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1";

        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVMapTest2()
    {
        $dataTable = $this->getDataTableSimpleMapTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "testKey,max_actions,nb_uniq_visitors\nrow1,14,57\nrow2,140,570";

        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVMapTest3()
    {
        $dataTable = $this->getDataTableSimpleOneRowMapTest();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "testKey,value\nrow1,14\nrow2,15";
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVMapisMadeOfMapTest1()
    {
        $dataTable = $this->getDataTableMapContainsDataTableMapNormal();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "parentArrayKey,testKey,label,nb_uniq_visitors,nb_visits,metadata_url,metadata_logo\n" .
            "idSite,date1,Google,11,11,http://www.google.com,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "idSite,date1,Yahoo!,15,151,http://www.yahoo.com,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png\n" .
            "idSite,date2,Google1©,110,110,http://www.google.com1,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1\n" .
            "idSite,date2,Yahoo!1,150,1510,http://www.yahoo.com1,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1";

        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVMapIsMadeOfMapTest2()
    {
        $dataTable = $this->getDataTableMapContainsDataTableMapSimple();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "parentArrayKey,testKey,max_actions,nb_uniq_visitors\nidSite,row1,14,57\nidSite,row2,140,570";

        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testCSVMapIsMadeOfMapTest3()
    {
        $dataTable = $this->getDataTableMapContainsDataTableMapSimpleOneRow();
        $render = new Csv();
        $render->setTable($dataTable);
        $render->convertToUnicode = false;
        $expected = "parentArrayKey,testKey,value\nidSite,row1,14\nidSite,row2,15";
        $rendered = $render->render();
        $this->assertEquals($expected, $rendered);
    }


    public function testRenderArray1()
    {
        $data = array();

        $render = new Csv();
        $render->setTable($data);
        $render->convertToUnicode = false;
        $expected = 'No data available';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray2()
    {
        $data = array('a', 'b', 'c');

        $render = new Csv();
        $render->setTable($data);
        $render->convertToUnicode = false;
        $expected = 'a
b
c';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray3()
    {
        $data = array('a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g');

        $render = new Csv();
        $render->setTable($data);
        $render->convertToUnicode = false;
        $expected = 'a,c,e,5
b,d,f,g';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray4()
    {
        $data = array('a' => 'b');

        $render = new Csv();
        $render->setTable($data);
        $render->convertToUnicode = false;
        $expected = "a\nb";

        $this->assertEquals($expected, $render->render());
    }

    /**
     * @dataProvider getFormulaExpressions
     */
    public function testRendersFormulasAndNullBytesCorrectly($input, $expectedOutput)
    {
        $render = new Csv();
        $render->setTable($input);
        $render->convertToUnicode = false;
        $expected = $expectedOutput;

        $this->assertEquals($expected, $render->render());
    }

    public function getFormulaExpressions(): iterable
    {
        yield "formula starting with =, should be escaped with leading '" => [
            ['=SUM(A)' => '=SUM(A;B)'], "'=SUM(A)\n\"'=SUM(A;B)\""
        ];

        yield "formula starting with +, should be escaped with leading '" => [
            ['+A1' => '+A2,B3'], "'+A1\n\"'+A2,B3\""
        ];

        yield "formula starting with -, should be escaped with leading '" => [
            ['-A1' => '-A2,B3'], "'-A1\n\"'-A2,B3\""
        ];

        yield "formula with leading null byte, should still be escaped with leading '" => [
            ["\0-A1" => '%00=SUM(A)'], "'\0-A1\n'%00=SUM(A)"
        ];

        yield "formula with leading null bytes, should still be escaped with leading '" => [
            ["\0%00\0%00=@A1" => "%00\0%00%00=SUM(A)"], "'\0%00\0%00=@A1\n'%00\0%00%00=SUM(A)"
        ];
    }

    private function getDataTableSimpleWithCommasInCells()
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray(array(
            array("col,1" => "val\"1", "col;2" => "val\",2"),
            array("col,1" => "val", "col;2" => "val#2")
        ));
        return $table;
    }
}
