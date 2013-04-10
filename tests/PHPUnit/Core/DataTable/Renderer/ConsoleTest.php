<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class DataTable_Renderer_ConsoleTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Piwik_DataTable_Manager::getInstance()->deleteAll();
    }

    /**
     *  test with a row without child
     *               a row with a child that has a child
     *               a row with w child
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_Console
     */
    public function testConsole2SubLevelAnd2Different()
    {
        $table = new Piwik_DataTable;
        $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS  => array('visits' => 245, 'visitors' => 245),
                                      Piwik_DataTable_Row::METADATA => array('logo' => 'test.png'),)

        );

        $subsubtable = new Piwik_DataTable;
        $idsubsubtable = $subsubtable->getId();
        $subsubtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array('visits' => 2)));

        $subtable = new Piwik_DataTable;
        $idsubtable1 = $subtable->getId();
        $subtable->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS              => array('visits' => 1),
                                         Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subsubtable));

        $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS              => array('visits' => 3),
                                      Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable)
        );

        $subtable2 = new Piwik_DataTable;
        $idsubtable2 = $subtable2->getId();
        $subtable2->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS => array('visits' => 5),));

        $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS              => array('visits' => 9),
                                      Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $subtable2)
        );

        $expected = "- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br />\n- 2 ['visits' => 3] [] [idsubtable = $idsubtable1]<br />\n*- 1 ['visits' => 1] [] [idsubtable = $idsubsubtable]<br />\n**- 1 ['visits' => 2] [] [idsubtable = ]<br />\n- 3 ['visits' => 9] [] [idsubtable = $idsubtable2]<br />\n*- 1 ['visits' => 5] [] [idsubtable = ]<br />\n";

        $render = new Piwik_DataTable_Renderer_Console();
        $render->setTable($table);
        $render->setPrefixRow('*');
        $rendered = $render->render();

        $this->assertEquals($expected, $rendered);
    }


    /**
     *  test with a row without child
     *
     * @group Core
     * @group DataTable
     * @group DataTable_Renderer
     * @group DataTable_Renderer_Console
     */
    public function testConsoleSimple()
    {
        $table = new Piwik_DataTable;
        $table->addRowFromArray(array(Piwik_DataTable_Row::COLUMNS  => array('visits' => 245, 'visitors' => 245),
                                      Piwik_DataTable_Row::METADATA => array('logo' => 'test.png'),)

        );

        $expected = "- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br />\n";

        $render = new Piwik_DataTable_Renderer_Console();
        $render->setTable($table);
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

        $render = new Piwik_DataTable_Renderer_Console();
        $render->setTable($data);
        $expected = 'Empty table<br />
';

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
        $data = array('a', 'b', 'c');

        $render = new Piwik_DataTable_Renderer_Console();
        $render->setTable($data);
        $expected = "- 1 ['0' => 'a'] [] [idsubtable = ]<br />
- 2 ['0' => 'b'] [] [idsubtable = ]<br />
- 3 ['0' => 'c'] [] [idsubtable = ]<br />
";

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

        $render = new Piwik_DataTable_Renderer_Console();
        $render->setTable($data);
        $expected = "- 1 ['a' => 'b', 'c' => 'd', 'e' => 'f', '5' => 'g'] [] [idsubtable = ]<br />
";

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
        $data = array('a' => 'b');

        $render = new Piwik_DataTable_Renderer_Console();
        $render->setTable($data);
        $expected = "- 1 ['0' => 'b'] [] [idsubtable = ]<br />
";

        $this->assertEquals($expected, $render->render());
    }
}
