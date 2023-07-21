<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\test\Unit;

use Piwik\DataTable;
use Piwik\Plugins\API\Renderer\Csv;

/**
 * @group Plugin
 * @group API
 */
class CsvRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Csv
     */
    private $builder;

    public function setUp(): void
    {
        $this->builder = $this->makeBuilder(array('method' => 'MultiSites_getAll', 'convertToUnicode' => 0));
    }

    public function test_renderSuccess_shouldIncludeMessage()
    {
        $response = $this->builder->renderSuccess('ok');

        $this->assertEquals("message
ok", $response);
    }

    public function test_renderException_shouldIncludeTheMessageAndNotExceptionMessage()
    {
        $response = $this->builder->renderException("The error message", new \Exception('The other message'));

        $this->assertEquals('Error: The error message', $response);
    }

    public function test_renderException_shouldRespectNewlines()
    {
        $response = $this->builder->renderException("The\nerror\nmessage", new \Exception('The other message'));

        $this->assertEquals('Error: The
error
message', $response);
    }

    public function test_renderObject_shouldReturAnError()
    {
        $response = $this->builder->renderObject(new \stdClass());

        $this->assertEquals('Error: The API cannot handle this data structure.', $response);
    }

    public function test_renderResource_shouldReturAnError()
    {
        $response = $this->builder->renderResource(new \stdClass());

        $this->assertEquals('Error: The API cannot handle this data structure.', $response);
    }

    public function test_renderScalar_shouldConvertToUnicodeByDefault()
    {
        $builder  = $this->makeBuilder(array('method' => 'MultiSites_getAll'));
        $response = $builder->renderScalar(true);

        $this->assertStringStartsWith(chr(255) . chr(254), $response);
    }

    public function test_renderScalar_shouldReturnABooleanAsIntegerWrappedInTable()
    {
        $response = $this->builder->renderScalar(true);

        $this->assertEquals('value
1', $response);
    }

    public function test_renderScalar_shouldReturnAnIntegerWrappedInTable()
    {
        $response = $this->builder->renderScalar(5);

        $this->assertEquals('value
5', $response);
    }

    public function test_renderScalar_shouldReturnAStringWrappedInValue()
    {
        $response = $this->builder->renderScalar('The Output');

        $this->assertEquals('value
The Output', $response);
    }

    public function test_renderScalar_shouldNotRemoveLineBreaks()
    {
        $response = $this->builder->renderScalar('The\nOutput');

        $this->assertEquals('value
The\nOutput', $response);
    }

    /**
     * @dataProvider getCellValuesToPrefixOrNot
     */
    public function test_renderScalar_whenCellValueIsFormula_shouldPrefixWithQuote($value, $expectedCsvValue)
    {
        $response = $this->builder->renderScalar($value);

        $this->assertEquals("value\n$expectedCsvValue", $response);
    }

    public function getCellValuesToPrefixOrNot()
    {
        // input, expected csv output
        return array(
            // we prefix with quotes
            array('=test()', '\'=test()'),
            array('=test()%%', '\'=test()%%'),
            array('=1+1', '\'=1+1'),
            array('@1+1', '\'@1+1'),
            array('+1+1', '\'+1+1'),
            array('+1+test()', '\'+1+test()'),
            array('-1+1', '\'-1+1'),
            array('@-1+1', '\'@-1+1'),
            array('-test()', '\'-test()'),
            array('-te,st()', '"\'-te,st()"'),
            array('-te"st()', '"\'-te""st()"'),

            // we do not need to prefix with quote
            array('1', '1'),
            array('2', '2'),
            array('2@', '2@'),
            array('20000000', '20000000'),
            array('10%', '10%'),
            array('%%', '%%'),
            array('10.5%', '10.5%'),
            array('-10.5%', '-10.5%'),
            array('+10,5%', '"\'+10,5%"'),
            array('10,5', '"10,5"'),
            array('1+test()', '1+test()'),
            array('1+test@', '1+test@'),
            array('%10%5', '%10%5'),
            array('', ''),
            array(0, '0'),
            array(2.2, '2.2'),
            array(-2.2, '-2.2'),
            array(false, '0'),
            array(null, ''),
        );
    }

    /**
     * @dataProvider getCellValuesToPrefixOrNot
     */
    public function test_renderDataTable_shouldRenderFormulas($value, $expectedValue)
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => $value));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals("nb_visits,nb_random\n5,$expectedValue", $response);
    }

    public function test_renderDataTable_shouldRenderABasicDataTable()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('nb_visits,nb_random
5,10', $response);
    }

    public function test_renderDataTable_shouldNotRenderSubtables_AsItIsNotSupportedYet()
    {
        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(array('nb_visits' => 2, 'nb_random' => 6));

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));
        $dataTable->getFirstRow()->setSubtable($subtable);

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('nb_visits,nb_random
5,10', $response);
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

        $this->assertEquals('defaultKeyName,nb_visits,nb_random
table1,5,10
table2,3,6', $response);
    }

    public function test_renderDataTable_shouldRenderSimpleDataTable()
    {
        $dataTable = new DataTable\Simple();
        $dataTable->addRowsFromArray(array('nb_visits' => 3, 'nb_random' => 6));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertEquals('nb_visits,nb_random
3,6', $response);
    }

    public function test_renderArray_ShouldConvertSimpleArrayToJson()
    {
        $input = array(1, 2, 5, 'string', 10);

        $response = $this->builder->renderArray($input);

        $this->assertEquals('1
2
5
string
10', $response);
    }

    public function test_renderArray_ShouldRenderAnEmptyArray()
    {
        $response = $this->builder->renderArray(array());

        $this->assertEquals('No data available', $response);
    }

    public function test_renderArray_ShouldConvertAssociativeArrayToJson()
    {
        $input = array('nb_visits' => 6, 'nb_random' => 8);

        $response = $this->builder->renderArray($input);

        $this->assertEquals('nb_visits,nb_random
6,8', $response);
    }

    public function test_renderArray_ShouldConvertsIndexedAssociativeArrayToJson()
    {
        $input = array(
            array('nb_visits' => 6, 'nb_random' => 8),
            array('nb_visits' => 3, 'nb_random' => 4)
        );

        $response = $this->builder->renderArray($input);

        $this->assertEquals('nb_visits,nb_random
6,8
3,4', $response);
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
        $this->assertEquals('0,1,2
firstElement,,
firstElement,secondElement,
,,thirdElement', $actual);
    }

    public function test_renderArray_ShouldConvertMultiDimensionalAssociativeArrayToJson()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data structure returned is not convertible in the requested format: Only integer keys supported for base level.');

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
        $this->expectExceptionMessage('Data structure returned is not convertible in the requested format: Multidimensional column values not supported.');

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
        $this->expectExceptionMessage('Data structure returned is not convertible in the requested format: Only integer keys supported for base level.');

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
        return new Csv($request);
    }
}
