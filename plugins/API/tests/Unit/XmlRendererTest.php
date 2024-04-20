<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\DataTable;
use Piwik\Plugins\API\Renderer\Xml;

/**
 * @group Plugin
 * @group API
 */
class XmlRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Xml
     */
    private $builder;

    public function setUp(): void
    {
        $this->builder = $this->makeBuilder(array());
        DataTable\Manager::getInstance()->deleteAll();
    }

    public function tearDown(): void
    {
        DataTable\Manager::getInstance()->deleteAll();
    }

    public function test_renderSuccess_shouldIncludeMessage()
    {
        $response = $this->builder->renderSuccess('ok');

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<success message="ok" />
</result>', $response);
    }

    public function test_renderException_shouldIncludeTheMessageAndNotExceptionMessage()
    {
        $response = $this->builder->renderException("The error message", new \Exception('The other message'));

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<error message="The error message" />
</result>', $response);
    }

    public function test_renderObject_shouldReturAnError()
    {
        $response = $this->builder->renderObject(new \stdClass());

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<error message="The API cannot handle this data structure." />
</result>', $response);
    }

    public function test_renderResource_shouldReturAnError()
    {
        $response = $this->builder->renderResource(new \stdClass());

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<error message="The API cannot handle this data structure." />
</result>', $response);
    }

    public function test_renderScalar_shouldReturnABooleanAsIntegerWrappedInResult()
    {
        $response = $this->builder->renderScalar(true);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>1</result>', $response);
    }

    public function test_renderScalar_shouldReturnAnIntegerWrappedInResult()
    {
        $response = $this->builder->renderScalar(5);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>5</result>', $response);
    }

    public function test_renderScalar_shouldReturnAStringWrappedInValue()
    {
        $response = $this->builder->renderScalar('The Output');

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>The Output</result>', $response);
    }

    public function test_renderScalar_shouldNotRemoveLineBreaks()
    {
        $response = $this->builder->renderScalar('The\nOutput');

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>The\nOutput</result>', $response);
    }

    public function test_renderDataTable_shouldRenderABasicDataTable()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>5</nb_visits>
		<nb_random>10</nb_random>
	</row>
</result>', $response);
    }

    public function test_renderDataTable_shouldRenderSubtables()
    {
        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(array('nb_visits' => 2, 'nb_random' => 6));

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));
        $dataTable->getFirstRow()->setSubtable($subtable);

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>5</nb_visits>
		<nb_random>10</nb_random>
		<idsubdatatable>1</idsubdatatable>
	</row>
</result>', $response);
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

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result defaultKeyName="table1">
		<row>
			<nb_visits>5</nb_visits>
			<nb_random>10</nb_random>
		</row>
	</result>
	<result defaultKeyName="table2">
		<row>
			<nb_visits>3</nb_visits>
			<nb_random>6</nb_random>
		</row>
	</result>
</results>', $response);
    }

    public function test_renderDataTable_shouldRenderSimpleDataTable()
    {
        $dataTable = new DataTable\Simple();
        $dataTable->addRowsFromArray(array('nb_visits' => 3, 'nb_random' => 6));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<nb_visits>3</nb_visits>
	<nb_random>6</nb_random>
</result>', $response);
    }

    public function test_renderArray_ShouldConvertSimpleArrayToJson()
    {
        $input = array(1, 2, 5, 'string', 10);

        $response = $this->builder->renderArray($input);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>1</row>
	<row>2</row>
	<row>5</row>
	<row>string</row>
	<row>10</row>
</result>', $response);
    }

    public function test_renderArray_ShouldRenderAnEmptyArray()
    {
        $response = $this->builder->renderArray(array());

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result />', $response);
    }

    public function test_renderArray_ShouldConvertAssociativeArrayToJson()
    {
        $input = array('nb_visits' => 6, 'nb_random' => 8);

        $response = $this->builder->renderArray($input);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>6</nb_visits>
		<nb_random>8</nb_random>
	</row>
</result>', $response);
    }

    public function test_renderArray_ShouldConvertsIndexedAssociativeArrayToJson()
    {
        $input = array(
            array('nb_visits' => 6, 'nb_random' => 8),
            array('nb_visits' => 3, 'nb_random' => 4)
        );

        $response = $this->builder->renderArray($input);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>6</nb_visits>
		<nb_random>8</nb_random>
	</row>
	<row>
		<nb_visits>3</nb_visits>
		<nb_random>4</nb_random>
	</row>
</result>', $response);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalStandardArrayToJson()
    {
        $input = array("firstElement",
            array(
                "firstElement",
                "secondElement",
            ),
            "thirdElement");

        $actual = $this->builder->renderArray($input);
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>firstElement</row>
	<row>
		<row>firstElement</row>
		<row>secondElement</row>
	</row>
	<row>thirdElement</row>
</result>', $actual);
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
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<firstElement>isFirst</firstElement>
	<secondElement>
		<firstElement>isFirst</firstElement>
		<secondElement>isSecond</secondElement>
	</secondElement>
	<thirdElement>isThird</thirdElement>
</result>', $actual);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalIndexArrayToJson()
    {
        $input = array(array("firstElement",
            array(
                "firstElement",
                "secondElement",
            ),
            "thirdElement"));

        $actual = $this->builder->renderArray($input);
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<row>firstElement</row>
		<row>
			<row>firstElement</row>
			<row>secondElement</row>
		</row>
		<row>thirdElement</row>
	</row>
</result>', $actual);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalMixedArrayToJson()
    {
        $input = array(
            "firstElement" => "isFirst",
            array(
                "firstElement",
                "secondElement",
            ),
            "thirdElement" => array(
                "firstElement"  => "isFirst",
                "secondElement" => "isSecond",
            )
        );

        $actual = $this->builder->renderArray($input);
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<firstElement>isFirst</firstElement>
	<row key="0">
		<row>firstElement</row>
		<row>secondElement</row>
	</row>
	<thirdElement>
		<firstElement>isFirst</firstElement>
		<secondElement>isSecond</secondElement>
	</thirdElement>
</result>', $actual);
    }

    private function makeBuilder($request)
    {
        return new Xml($request);
    }
}
