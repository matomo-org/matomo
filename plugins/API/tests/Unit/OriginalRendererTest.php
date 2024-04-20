<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\DataTable;
use Piwik\Plugins\API\Renderer\Original;

/**
 * @group Plugin
 * @group API
 */
class OriginalRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Original
     */
    private $builder;

    public function setUp(): void
    {
        $this->builder = $this->makeBuilder(array());
    }

    public function test_renderSuccess_shouldAlwaysReturnTrueAndIgnoreMessage()
    {
        $response = $this->builder->renderSuccess('ok');

        $this->assertTrue($response);
    }

    public function test_renderException_shouldThrowTheException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The other message');

        $this->builder->renderException('This message should be ignored', new \BadMethodCallException('The other message'));
    }

    public function test_renderScalar_shouldReturnTheSameValue()
    {
        $response = $this->builder->renderScalar(true);
        $this->assertSame(true, $response);

        $response = $this->builder->renderScalar(5);
        $this->assertSame(5, $response);

        $response = $this->builder->renderScalar('string');
        $this->assertSame('string', $response);
    }

    public function test_renderObject_shouldReturnTheSameValue()
    {
        $response = $this->builder->renderObject($this);
        $this->assertSame($this, $response);

        $stdObject = (object) array('test' => 5);
        $response = $this->builder->renderObject($stdObject);
        $this->assertSame($stdObject, $response);
    }

    public function test_renderResource_shouldReturnTheSameValue()
    {
        $resource = curl_init();
        $response = $this->builder->renderResource($resource);
        $this->assertSame($resource, $response);
    }

    public function test_renderDataTable_shouldReturnSameInstanceAndNotSerializeByDefault()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertSame($dataTable, $response);
    }

    public function test_renderDataTable_shouldSerializeIfEnabled()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $builder  = $this->makeBuilder(array('serialize' => 1));
        $response = $builder->renderDataTable($dataTable);

        $builder  = $this->makeBuilder(array('serialize' => 0));
        $original = $builder->renderDataTable($dataTable);

        $expected = serialize($original);
        $this->assertEquals($expected, $response);
    }

    public function test_renderArray_ShouldReturnSameArrayAndNotSerializeByDefault()
    {
        $input = array(1, 2, 5, 'string', 10);

        $response = $this->builder->renderArray($input);

        $this->assertSame($input, $response);
    }

    public function test_renderArray_ShouldSerializeIfEnabled()
    {
        $builder  = $this->makeBuilder(array('serialize' => 1));
        $input    = array(1, 2, 5, 'string', 10);

        $response = $builder->renderArray($input);

        $this->assertSame('a:5:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;s:6:"string";i:4;i:10;}', $response);
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
        return new Original($request);
    }
}
