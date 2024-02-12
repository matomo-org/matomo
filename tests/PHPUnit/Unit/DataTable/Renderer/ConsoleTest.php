<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Renderer\Console;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Manager::getInstance()->deleteAll();
    }

    /**
     *  test with a row without child
     *               a row with a child that has a child
     *               a row with w child
     *
     * @group Core
     */
    public function testConsole2SubLevelAnd2Different()
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS  => array('visits' => 245, 'visitors' => 245),
                                      Row::METADATA => array('logo' => 'test.png'),));

        $subsubtable = new DataTable();
        $idsubsubtable = $subsubtable->getId();
        $subsubtable->addRowFromArray(array(Row::COLUMNS => array('visits' => 2)));

        $subtable = new DataTable();
        $idsubtable1 = $subtable->getId();
        $subtable->addRowFromArray(array(Row::COLUMNS              => array('visits' => 1),
                                         Row::DATATABLE_ASSOCIATED => $subsubtable));

        $table->addRowFromArray(array(Row::COLUMNS              => array('visits' => 3),
                                      Row::DATATABLE_ASSOCIATED => $subtable));

        $subtable2 = new DataTable();
        $idsubtable2 = $subtable2->getId();
        $subtable2->addRowFromArray(array(Row::COLUMNS => array('visits' => 5),));

        $table->addRowFromArray(array(Row::COLUMNS              => array('visits' => 9),
                                      Row::DATATABLE_ASSOCIATED => $subtable2));

        $expected = "- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br />\n- 2 ['visits' => 3] [] [idsubtable = $idsubtable1]<br />\n*- 1 ['visits' => 1] [] [idsubtable = $idsubsubtable]<br />\n**- 1 ['visits' => 2] [] [idsubtable = ]<br />\n- 3 ['visits' => 9] [] [idsubtable = $idsubtable2]<br />\n*- 1 ['visits' => 5] [] [idsubtable = ]<br />\n";

        $render = new Console();
        $render->setTable($table);
        $render->setPrefixRow('*');
        $rendered = $render->render();

        $this->assertEquals($expected, $rendered);
    }

    /**
     *  test with a row without child
     *
     * @group Core
     */
    public function testConsoleSimple()
    {
        $table = new DataTable();
        $table->addRowFromArray(array(Row::COLUMNS  => array('visits' => 245, 'visitors' => 245),
                                      Row::METADATA => array('logo' => 'test.png'),));

        $expected = "- 1 ['visits' => 245, 'visitors' => 245] ['logo' => 'test.png'] [idsubtable = ]<br />\n";

        $render = new Console();
        $render->setTable($table);
        $rendered = $render->render();

        $this->assertEquals($expected, $rendered);
    }


    public function testRenderArray1()
    {
        $data = array();

        $render = new Console();
        $render->setTable($data);
        $expected = 'Empty table<br />
';

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray2()
    {
        $data = array('a', 'b', 'c');

        $render = new Console();
        $render->setTable($data);
        $expected = "- 1 ['0' => 'a'] [] [idsubtable = ]<br />
- 2 ['0' => 'b'] [] [idsubtable = ]<br />
- 3 ['0' => 'c'] [] [idsubtable = ]<br />
";

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray3()
    {
        $data = array('a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g');

        $render = new Console();
        $render->setTable($data);
        $expected = "- 1 ['a' => 'b', 'c' => 'd', 'e' => 'f', '5' => 'g'] [] [idsubtable = ]<br />
";

        $this->assertEquals($expected, $render->render());
    }


    public function testRenderArray4()
    {
        $data = array('a' => 'b');

        $render = new Console();
        $render->setTable($data);
        $expected = "- 1 ['a' => 'b'] [] [idsubtable = ]<br />
";

        $this->assertEquals($expected, $render->render());
    }
}
