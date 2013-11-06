<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\DataTable\Manager;
use Piwik\DataTable\Renderer\Php;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\DataTable\Simple;

class DataTable_Renderer_PHPTest extends PHPUnit_Framework_TestCase
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
    public function testPHPTest1()
    {
        $dataTable = $this->_getDataTableTest();
        $render = new Php();
        $render->setTable($dataTable);
        $render->setRenderSubTables(true);

        $expected = serialize(array(
                                   0 =>
                                   array(
                                       'label'            => 'Google&copy;',
                                       'bool'             => false,
                                       'goals'            => array(
                                           'idgoal=1' => array(
                                               'revenue'        => 5.5,
                                               'nb_conversions' => 10,
                                           ),
                                       ),
                                       'nb_uniq_visitors' => 11,
                                       'nb_visits'        => 11,
                                       'nb_actions'       => 17,
                                       'max_actions'      => '5',
                                       'sum_visit_length' => 517,
                                       'bounce_count'     => 9,
                                       'url'              => 'http://www.google.com/display"and,properly',
                                       'logo'             => './plugins/Referrers/images/searchEngines/www.google.com.png',
                                   ),
                                   1 =>
                                   array(
                                       'label'            => 'Yahoo!',
                                       'nb_uniq_visitors' => 15,
                                       'bool'             => true,
                                       'nb_visits'        => 151,
                                       'nb_actions'       => 147,
                                       'max_actions'      => '50',
                                       'sum_visit_length' => 517,
                                       'bounce_count'     => 90,
                                       'url'              => 'http://www.yahoo.com',
                                       'logo'             => './plugins/Referrers/images/searchEngines/www.yahoo.com.png',
                                       'idsubdatatable'   => 2,
                                       'subtable'         =>
                                       array(
                                           0 =>
                                           array(
                                               'label' => 'sub1',
                                               'count' => 1,
                                               'bool'  => false,
                                           ),
                                           1 =>
                                           array(
                                               'label' => 'sub2',
                                               'count' => 2,
                                               'bool'  => true,
                                           ),
                                       ),
                                   ),
                              ));
        $rendered = $render->render(null);
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     */
    public function testPHPTest2()
    {
        $dataTable = $this->_getDataTableSimpleTest();
        $render = new Php();
        $render->setTable($dataTable);
        $expected = serialize(array(
                                   'max_actions'      => 14.0,
                                   'nb_uniq_visitors' => 57.0,
                                   'nb_visits'        => 66.0,
                                   'nb_actions'       => 151.0,
                                   'sum_visit_length' => 5118.0,
                                   'bounce_count'     => 44.0,
                              ));
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testPHPTest3()
    {
        $dataTable = $this->_getDataTableSimpleOneRowTest();
        $render = new Php();
        $render->setTable($dataTable);
        $expected = serialize(14.0);
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testPHPTest4()
    {
        $dataTable = $this->_getDataTableEmpty();
        $render = new Php();
        $render->setTable($dataTable);
        $expected = serialize(array());
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testPHPTest5()
    {
        $dataTable = $this->_getDataTableSimpleOneZeroRowTest();
        $render = new Php();
        $render->setTable($dataTable);
        $expected = serialize(0);
        $this->assertEquals($expected, $render->render());
    }

    /**
     * @group Core
     */
    public function testPHPTest6()
    {
        $dataTable = $this->_getDataTableSimpleOneFalseRowTest();
        $render = new Php();
        $render->setTable($dataTable);
        $expected = serialize(false);
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
    public function testPHPMapTest1()
    {
        $dataTable = $this->_getDataTableMapTest();
        $render = new Php();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = serialize(array(
                                   'date1' =>
                                   array(
                                       0 =>
                                       array(
                                           'label'            => 'Google',
                                           'nb_uniq_visitors' => 11,
                                           'nb_visits'        => 11,
                                           'url'              => 'http://www.google.com',
                                           'logo'             => './plugins/Referrers/images/searchEngines/www.google.com.png',
                                       ),
                                       1 =>
                                       array(
                                           'label'            => 'Yahoo!',
                                           'nb_uniq_visitors' => 15,
                                           'nb_visits'        => 151,
                                           'url'              => 'http://www.yahoo.com',
                                           'logo'             => './plugins/Referrers/images/searchEngines/www.yahoo.com.png',
                                       ),
                                   ),
                                   'date2' =>
                                   array(
                                       0 =>
                                       array(
                                           'label'            => 'Google1&copy;',
                                           'nb_uniq_visitors' => 110,
                                           'nb_visits'        => 110,
                                           'url'              => 'http://www.google.com1',
                                           'logo'             => './plugins/Referrers/images/searchEngines/www.google.com.png1',
                                       ),
                                       1 =>
                                       array(
                                           'label'            => 'Yahoo!1',
                                           'nb_uniq_visitors' => 150,
                                           'nb_visits'        => 1510,
                                           'url'              => 'http://www.yahoo.com1',
                                           'logo'             => './plugins/Referrers/images/searchEngines/www.yahoo.com.png1',
                                       ),
                                   ),
                                   'date3' => array(),
                              ));
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     */
    public function testPHPMapTest2()
    {
        $dataTable = $this->_getDataTableSimpleMapTest();
        $render = new Php();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = serialize(array(
                                   'row1' =>
                                   array(
                                       'max_actions'      => 14.0,
                                       'nb_uniq_visitors' => 57.0,
                                   ),
                                   'row2' =>
                                   array(
                                       'max_actions'      => 140.0,
                                       'nb_uniq_visitors' => 570.0,
                                   ),
                                   'row3' =>
                                   array(),
                              ));
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     */
    public function testPHPMapTest3()
    {
        $dataTable = $this->_getDataTableSimpleOneRowMapTest();
        $render = new Php();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = serialize(array(
                                   'row1' => 14.0,
                                   'row2' => 15.0,
                                   'row3' => array(),
                              ));
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     */
    public function testPHPMapIsMadeOfMapTest1()
    {
        $dataTable = $this->_getDataTableMap_containsDataTableMap_normal();
        $render = new Php();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = serialize(array('idSite' =>
                                    array(
                                        'date1' =>
                                        array(
                                            0 =>
                                            array(
                                                'label'            => 'Google',
                                                'nb_uniq_visitors' => 11,
                                                'nb_visits'        => 11,
                                                'url'              => 'http://www.google.com',
                                                'logo'             => './plugins/Referrers/images/searchEngines/www.google.com.png',
                                            ),
                                            1 =>
                                            array(
                                                'label'            => 'Yahoo!',
                                                'nb_uniq_visitors' => 15,
                                                'nb_visits'        => 151,
                                                'url'              => 'http://www.yahoo.com',
                                                'logo'             => './plugins/Referrers/images/searchEngines/www.yahoo.com.png',
                                            ),
                                        ),
                                        'date2' =>
                                        array(
                                            0 =>
                                            array(
                                                'label'            => 'Google1&copy;',
                                                'nb_uniq_visitors' => 110,
                                                'nb_visits'        => 110,
                                                'url'              => 'http://www.google.com1',
                                                'logo'             => './plugins/Referrers/images/searchEngines/www.google.com.png1',
                                            ),
                                            1 =>
                                            array(
                                                'label'            => 'Yahoo!1',
                                                'nb_uniq_visitors' => 150,
                                                'nb_visits'        => 1510,
                                                'url'              => 'http://www.yahoo.com1',
                                                'logo'             => './plugins/Referrers/images/searchEngines/www.yahoo.com.png1',
                                            ),
                                        ),
                                        'date3' => array(),
                                    )));

        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     */
    public function testPHPMapIsMadeOfMapTest2()
    {
        $dataTable = $this->_getDataTableMap_containsDataTableMap_simple();
        $render = new Php();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = serialize(array('idSite' =>
                                    array(
                                        'row1' =>
                                        array(
                                            'max_actions'      => 14.0,
                                            'nb_uniq_visitors' => 57.0,
                                        ),
                                        'row2' =>
                                        array(
                                            'max_actions'      => 140.0,
                                            'nb_uniq_visitors' => 570.0,
                                        ),
                                        'row3' =>
                                        array(),
                                    )));
        $this->assertEquals($expected, $rendered);
    }

    /**
     * @group Core
     */
    public function testPHPMapIsMadeOfMapTest3()
    {
        $dataTable = $this->_getDataTableMap_containsDataTableMap_simpleOneRow();
        $render = new Php();
        $render->setTable($dataTable);
        $rendered = $render->render();

        $expected = serialize(array('idSite' =>
                                    array(
                                        'row1' => 14.0,
                                        'row2' => 15.0,
                                        'row3' => array(),
                                    )));
        $this->assertEquals($expected, $rendered);
    }
}
