<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\DataTable;
use Piwik\Plugins\API\Renderer\Rss;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugin
 * @group API
 */
class RssRendererTest extends IntegrationTestCase
{
    /**
     * @var Rss
     */
    private $builder;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;

        $idSite = Fixture::createWebsite('2014-01-01 00:00:00');

        $this->builder = $this->makeBuilder(array('method' => 'MultiSites_getAll', 'idSite' => $idSite));
    }

    public function testRenderSuccessShouldIncludeMessage()
    {
        $response = $this->builder->renderSuccess('ok');

        $this->assertEquals('Success:ok', $response);
    }

    public function testRenderExceptionShouldIncludeTheMessageAndNotExceptionMessage()
    {
        $response = $this->builder->renderException("The error message", new \Exception('The other message'));

        $this->assertEquals('Error: The error message', $response);
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

    public function testRenderScalarShouldFailForBooleanScalar()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');
        $this->builder->renderScalar(true);
    }

    public function testRenderScalarShouldFailForIntegerScalar()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');
        $this->builder->renderScalar(5);
    }

    public function testRenderScalarShouldFailForStringScalar()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');
        $this->builder->renderScalar('string');
    }

    public function testRenderDataTableShouldFailForDataTable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');
        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $this->builder->renderDataTable($dataTable);
    }

    public function testRenderDataTableShouldFailForSubtables()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');

        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(array('nb_visits' => 2, 'nb_random' => 6));

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));
        $dataTable->getFirstRow()->setSubtable($subtable);

        $this->builder->renderDataTable($dataTable);
    }

    public function testRenderDataTableShouldFailIfKeynameIsNotDate()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');

        $map = new DataTable\Map();

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $map->addTable($dataTable, 'table1');
        $map->addTable($dataTable, 'table2');

        $this->builder->renderDataTable($map);
    }

    public function testRenderDataTableShouldRenderDataTableMapsIfKeynameIsDate()
    {
        $map = new DataTable\Map();
        $map->setKeyName('date');
        $_GET['period'] = 'day';

        $response = $this->builder->renderDataTable($map);

        unset($_GET['period']);

        $response = preg_replace(array('/<pubDate>(.*)<\/pubDate>/','/<lastBuildDate>(.*)<\/lastBuildDate>/'), '', $response);

        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>matomo statistics - RSS</title>
    <link>https://matomo.org</link>
    <description>Matomo RSS feed</description>
    
    <generator>matomo</generator>
    <language>en</language>
    
	</channel>
</rss>', $response);
    }

    public function testRenderDataTableShouldFailForSimpleDataTable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');

        $dataTable = new DataTable\Simple();
        $dataTable->addRowsFromArray(array('nb_visits' => 3, 'nb_random' => 6));

        $this->builder->renderDataTable($dataTable);
    }

    public function testRenderArrayShouldFailForArrays()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSS feeds can be generated for one specific website');

        $input = array(1, 2, 5, 'string', 10);

        $this->builder->renderArray($input);
    }

    private function makeBuilder($request)
    {
        return new Rss($request);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
