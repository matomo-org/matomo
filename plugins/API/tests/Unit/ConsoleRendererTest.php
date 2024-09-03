<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\DataTable;
use Piwik\Date;
use Piwik\Plugins\API\Renderer\Console;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;

/**
 * @group Plugin
 * @group API
 */
class ConsoleRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Console
     */
    private $builder;

    public function setUp(): void
    {
        $this->builder = $this->makeBuilder(array());
        DataTable\Manager::getInstance()->deleteAll();
    }

    public function testRenderSuccessShouldAlwaysReturnTrueAndIgnoreMessage()
    {
        $response = $this->builder->renderSuccess('ok');

        $this->assertEquals('Success:ok', $response);
    }

    public function testRenderExceptionShouldThrowTheException()
    {
        $response = $this->builder->renderException('This message should be used', new \BadMethodCallException('The other message'));

        $this->assertEquals('Error: This message should be used', $response);
    }

    public function testRenderScalarShouldReturnTheSameValue()
    {
        $response = $this->builder->renderScalar(true);
        $this->assertSame("- 1 ['0' => 1] [] [idsubtable = ]<br />
", $response);

        $response = $this->builder->renderScalar(5);
        $this->assertSame("- 1 ['0' => 5] [] [idsubtable = ]<br />
", $response);

        $response = $this->builder->renderScalar('string');
        $this->assertSame("- 1 ['0' => 'string'] [] [idsubtable = ]<br />
", $response);
    }

    public function testRenderObjectShouldReturAnError()
    {
        $response = $this->builder->renderObject(new \stdClass());

        $this->assertEquals('Error: The API cannot handle this data structure.', $response);
    }

    public function testRenderResourceShouldReturAnError()
    {
        $response = $this->builder->renderResource(new \stdClass());

        $this->assertEquals('Error: The API cannot handle this data structure.', $response);
    }

    public function testRenderDataTableShouldReturnResult()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertSame("- 1 ['nb_visits' => 5, 'nb_random' => 10] [] [idsubtable = ]<br />
", $response);
    }

    public function testRenderDataTableWithObjectsShouldReturnResult()
    {
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));
        $dataTable->setMetadata('processedRows', [
            new AverageTimeOnSite(),
            new \stdClass(),
            Date::factory('2016-01-01 00:00:00')
        ]);

        $response = $this->builder->renderDataTable($dataTable);

        $this->assertSame("- 1 ['nb_visits' => 5, 'nb_random' => 10] [] [idsubtable = ]<br />
<hr />Metadata<br /><br /> <b>processedRows</b><br />0 => Object [Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite]1 => Object [stdClass]2 => 2016-01-01", $response);
    }

    public function testRenderArrayShouldReturnConsoleResult()
    {
        $input = array(1, 2, 5, 'string', 10);

        $response = $this->builder->renderArray($input);

        $this->assertSame("- 1 ['0' => 1] [] [idsubtable = ]<br />
- 2 ['0' => 2] [] [idsubtable = ]<br />
- 3 ['0' => 5] [] [idsubtable = ]<br />
- 4 ['0' => 'string'] [] [idsubtable = ]<br />
- 5 ['0' => 10] [] [idsubtable = ]<br />
", $response);
    }

    public function testRenderArrayShouldConvertMultiDimensionalAssociativeArrayToJson()
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

        $actual = $this->builder->renderArray($input);
        $this->assertSame($input, $actual);
    }

    private function makeBuilder($request)
    {
        return new Console($request);
    }
}
