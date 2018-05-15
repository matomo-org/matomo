<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\DataTable;
use Piwik\Plugins\API\Renderer\Php;

/**
 * @group Plugin
 * @group API
 * @group PhpRendererTest
 */
class PhpRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Php
     */
    private $builder;

    public function setUp()
    {
        $this->builder = $this->makeBuilder(array('serialize' => 0));
        DataTable\Manager::getInstance()->deleteAll();
    }

    public function test_renderSuccess_shouldAlwaysReturnTrueAndIgnoreMessage()
    {
        $response = $this->builder->renderSuccess('ok');
        $expected = array('result' => 'success', 'message' => 'ok');

        $this->assertEquals($expected, $response);
    }

    public function test_renderSuccess_shouldSerializeByDefault()
    {
        $response = $this->makeBuilder(array())->renderSuccess('ok');
        $expected = 'a:2:{s:6:"result";s:7:"success";s:7:"message";s:2:"ok";}';

        $this->assertEquals($expected, $response);
    }

    public function test_renderException_shouldReturnTheMessageAndNotTheExceptionMessage()
    {
        $response = $this->builder->renderException('This message should be ignored', new \BadMethodCallException('The other message'));
        $expected = array('result' => 'error', 'message' => 'This message should be ignored');

        $this->assertEquals($expected, $response);
    }

    public function test_renderException_shouldSerializeByDefault()
    {
        $response = $this->makeBuilder(array())->renderException('This message should be ignored', new \BadMethodCallException('The other message'));
        $expected = 'a:2:{s:6:"result";s:5:"error";s:7:"message";s:30:"This message should be ignored";}';

        $this->assertEquals($expected, $response);
    }

    public function test_renderScalar_shouldReturnTheSameValue()
    {
        $response = $this->builder->renderScalar(true);
        $this->assertEquals(true, $response);

        $response = $this->builder->renderScalar(5);
        $this->assertEquals(5, $response);

        $response = $this->builder->renderScalar('string');
        $this->assertEquals('string', $response);
    }

    public function test_renderObject_shouldReturnTheSameValue()
    {
        $response = $this->builder->renderObject($this);
        $expected = array('result' => 'error', 'message' => 'The API cannot handle this data structure.');

        $this->assertEquals($expected, $response);
    }

    public function test_renderResource_shouldReturnTheSameValue()
    {
        $resource = curl_init();
        $response = $this->builder->renderResource($resource);
        $expected = array('result' => 'error', 'message' => 'The API cannot handle this data structure.');

        $this->assertEquals($expected, $response);
    }

    public function test_renderDataTable_shouldNotSerializeIfDisabled()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $this->builder->renderDataTable($dataTable);
        $expected = array(
            array('nb_visits' => 5, 'nb_random' => 10)
        );
        $this->assertEquals($expected, $response);
    }

    public function test_renderDataTable_shouldSerializeByDefault()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $builder  = $this->makeBuilder(array());
        $response = $builder->renderDataTable($dataTable);

        $expected = 'a:1:{i:0;a:2:{s:9:"nb_visits";i:5;s:9:"nb_random";i:10;}}';
        $this->assertSame($expected, $response);
    }

    public function test_renderDataTable_shouldRenderSubtables()
    {
        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(array('nb_visits' => 2, 'nb_random' => 6));

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));
        $dataTable->getFirstRow()->setSubtable($subtable);

        $response = $this->builder->renderDataTable($dataTable);
        $expected = array(array('nb_visits' => 5, 'nb_random' => 10, 'idsubdatatable' => 1));

        $this->assertEquals($expected, $response);
    }

    public function test_renderDataTable_shouldRenderDataTableMaps()
    {
        $map = new DataTable\Map();

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $dataTable2 = new DataTable();
        $dataTable2->addRowFromSimpleArray(array('nb_visits' => 3, 'nb_random' => 6));

        $map->addTable($dataTable, 'table1');
        $map->addTable($dataTable2, 'table2');

        $response = $this->builder->renderDataTable($map);
        $expected = array(
            'table1' => array(array('nb_visits' => 5, 'nb_random' => 10)),
            'table2' => array(array('nb_visits' => 3, 'nb_random' => 6))
        );

        $this->assertEquals($expected, $response);
    }

    public function test_renderDataTable_shouldRenderSimpleDataTable()
    {
        $dataTable = new DataTable\Simple();
        $dataTable->addRowsFromArray(array('nb_visits' => 3, 'nb_random' => 6));

        $response = $this->builder->renderDataTable($dataTable);
        $expected = array('nb_visits' => 3, 'nb_random' => 6);

        $this->assertEquals($expected, $response);
    }

    public function test_renderArray_ShouldReturnSameArrayNotSerialize()
    {
        $input = array(1, 2, 5, 'string', 10);

        // builder has serialize=0
        $response = $this->builder->renderArray($input);

        $this->assertSame($input, $response);
    }

    public function test_renderArray_ShouldSerializeByDefault()
    {
        $builder  = $this->makeBuilder(array());
        $input    = array(1, 2, 5, 'string', 10);

        $response = $builder->renderArray($input);

        $this->assertSame('a:5:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;s:6:"string";i:4;i:10;}', $response);
    }

    public function test_renderArray_ShouldSerializeByDefaulMultiDimensionalArray()
    {
        $input = array(
            "firstElement"  => "isFirst",
            "secondElement" => array(
                "firstElement"  => "isFirst",
                "secondElement" => "isSecond",
            ),
            "thirdElement"  => "isThird");

        $builder  = $this->makeBuilder(array());
        $actual = $builder->renderArray($input);
        $this->assertSame( serialize($input), $actual);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalAssociativeArrayToJson()
    {
        $input = array(
            "firstElement"  => "isFirst",
            "secondElement" => array(
                "firstElement"  => "isFirst",
                "secondElement" => "isSecond",
            ),
            "thirdElement"  => "isThird");

        $actual = $this->builder->renderArray($input);
        $this->assertSame($input, $actual);
    }

    private function makeBuilder($request)
    {
        return new Php($request);
    }
}
