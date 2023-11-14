<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\DataTable;
use Piwik\Date;
use Piwik\Plugins\API\Renderer\Html;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;

/**
 * @group Plugin
 * @group API
 */
class HtmlRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Html
     */
    private $builder;

    public function setUp(): void
    {
        $this->builder = $this->makeBuilder(array('method' => 'MultiSites_getAll'));
        DataTable\Manager::getInstance()->deleteAll();
    }

    public function test_renderSuccess_shouldIncludeMessage()
    {
        $response = $this->builder->renderSuccess('ok');

        $this->assertEquals('<!-- Success: ok -->', $response);
    }

    public function test_renderException_shouldIncludeTheMessageAndNotExceptionMessage()
    {
        $response = $this->builder->renderException("The error message", new \Exception('The other message'));

        $this->assertEquals('The error message', $response);
    }

    public function test_renderException_shouldConvertNewLinesToBr()
    {
        $response = $this->builder->renderException("The\nerror\nmessage", new \Exception('The other message'));

        $this->assertEquals('The<br />
error<br />
message', $response);
    }

    public function test_renderObject_shouldReturAnError()
    {
        $response = $this->builder->renderObject(new \stdClass());

        $this->assertEquals('The API cannot handle this data structure.', $response);
    }

    public function test_renderResource_shouldReturAnError()
    {
        $response = $this->builder->renderResource(new \stdClass());

        $this->assertEquals('The API cannot handle this data structure.', $response);
    }

    public function test_renderScalar_shouldReturnABooleanAsIntegerWrappedInTable()
    {
        $response = $this->builder->renderScalar(true);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>value</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>1</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderScalar_shouldReturnAnIntegerWrappedInTable()
    {
        $response = $this->builder->renderScalar(5);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>value</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>5</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderScalar_shouldReturnAStringWrappedInValue()
    {
        $response = $this->builder->renderScalar('The Output');

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>value</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>The Output</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderScalar_shouldNotRemoveLineBreaks()
    {
        $response = $this->builder->renderScalar('The\nOutput');

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>value</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>The\nOutput</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderDataTable_shouldRenderABasicDataTable()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>nb_visits</th>
		<th>nb_random</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>5</td>
		<td>10</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderDataTable_shouldRenderSubtables()
    {
        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(array('nb_visits' => 2, 'nb_random' => 6));

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));
        $dataTable->getFirstRow()->setSubtable($subtable);

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>nb_visits</th>
		<th>nb_random</th>
		<th>_idSubtable</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>5</td>
		<td>10</td>
		<td>1</td>
	</tr>
</tbody>
</table>
', $response);
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

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>_defaultKeyName</th>
		<th>nb_visits</th>
		<th>nb_random</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>table1</td>
		<td>5</td>
		<td>10</td>
	</tr>
	<tr>
		<td>table2</td>
		<td>3</td>
		<td>6</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderDataTable_shouldRenderSimpleDataTable()
    {
        $dataTable = new DataTable\Simple();
        $dataTable->addRowsFromArray(array('nb_visits' => 3, 'nb_random' => 6));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>nb_visits</th>
		<th>nb_random</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>3</td>
		<td>6</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderDataTable_shouldRenderDataTableWithComplexMetadata()
    {
        $dataTable = new DataTable\Simple();
        $row = new DataTable\Row();
        $row->setColumn('nb_visits', 3);
        $row->setColumn('nb_random', 6);
        $row->setMetadata('processedRows', [
            new AverageTimeOnSite(),
            new \stdClass(),
            Date::factory('2016-01-01 00:00:00')
        ]);
        $dataTable->addRow($row);

        $response = $this->builder->renderDataTable($dataTable);

        $isPHP82orNewer = version_compare(PHP_VERSION, 8.2, '>=');

        $stdClass = version_compare(PHP_VERSION, 7.3, '>=') ?
            "(object) array(\n  )," :
            "stdClass::__set_state(array(\n  )),";

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>nb_visits</th>
		<th>nb_random</th>
		<th>_metadata</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>3</td>
		<td>6</td>
		<td>\'processedRows\' =&gt; array (
  0 =&gt; 
  ' . ($isPHP82orNewer ? '\\' : '') . 'Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite::__set_state(array(
  )),
  1 =&gt; 
  ' . $stdClass . '
  2 =&gt; 
  ' . ($isPHP82orNewer ? '\\' : '') . 'Piwik\Date::__set_state(array(
     \'timestamp\' =&gt; 1451606400,
     \'timezone\' =&gt; \'UTC\',
  )),
)</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderArray_ShouldConvertSimpleArrayToJson()
    {
        $input = array(1, 2, 5, 'string', 10);

        $response = $this->builder->renderArray($input);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>value</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>1</td>
	</tr>
	<tr>
		<td>2</td>
	</tr>
	<tr>
		<td>5</td>
	</tr>
	<tr>
		<td>string</td>
	</tr>
	<tr>
		<td>10</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderArray_ShouldRenderAnEmptyArray()
    {
        $response = $this->builder->renderArray(array());

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
	</tr>
</thead>
<tbody>
</tbody>
</table>
', $response);
    }

    public function test_renderArray_ShouldConvertAssociativeArrayToJson()
    {
        $input = array('nb_visits' => 6, 'nb_random' => 8);

        $response = $this->builder->renderArray($input);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>nb_visits</th>
		<th>nb_random</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>6</td>
		<td>8</td>
	</tr>
</tbody>
</table>
', $response);
    }

    public function test_renderArray_ShouldConvertsIndexedAssociativeArrayToJson()
    {
        $input = array(
            array('nb_visits' => 6, 'nb_random' => 8),
            array('nb_visits' => 3, 'nb_random' => 4)
        );

        $response = $this->builder->renderArray($input);

        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>nb_visits</th>
		<th>nb_random</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>6</td>
		<td>8</td>
	</tr>
	<tr>
		<td>3</td>
		<td>4</td>
	</tr>
</tbody>
</table>
', $response);
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
        $this->assertEquals('<table id="MultiSites_getAll" border="1">
<thead>
	<tr>
		<th>value</th>
		<th>1</th>
		<th>2</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>firstElement</td>
		<td>-</td>
		<td>-</td>
	</tr>
	<tr>
		<td>firstElement</td>
		<td>secondElement</td>
		<td>-</td>
	</tr>
	<tr>
		<td>-</td>
		<td>-</td>
		<td>thirdElement</td>
	</tr>
</tbody>
</table>
', $actual);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalAssociativeArrayToJson()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data structure returned is not convertible in the requested format');

        $input = array(
            "firstElement"  => "isFirst",
            "secondElement" => array(
                "firstElement"  => "isFirst",
                "secondElement" => "isSecond",
            ),
            "thirdElement"  => "isThird");

        $this->builder->renderArray($input);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalIndexArrayToJson()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data structure returned is not convertible in the requested format');

        $input = array(array("firstElement",
            array(
                "firstElement",
                "secondElement",
            ),
            "thirdElement"));

        $this->builder->renderArray($input);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalMixedArrayToJson()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data structure returned is not convertible in the requested format');

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

        $this->builder->renderArray($input);
    }

    private function makeBuilder($request)
    {
        return new Html($request);
    }
}
