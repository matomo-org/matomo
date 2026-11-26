<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\DataTable;
use Piwik\Plugins\API\RowEvolution;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group RowEvolutionTest
 * @group Plugins
 */
class RowEvolutionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    public function testGetRowEvolutionShouldTriggerAnExceptionIfReportHasNoDimension()
    {
        $this->expectException(\Exception::class);
        $this->expectDeprecationMessage("Reports like VisitsSummary.get which do not have a dimension are not supported by row evolution");
        $rowEvolution = new RowEvolution();
        $rowEvolution->getRowEvolution(1, 'day', 'last7', 'VisitsSummary', 'get');
    }

    public function testGetRowEvolutionShouldNotTriggerAnExceptionIfReportHasADimension()
    {
        $rowEvolution = new RowEvolution();
        $table = $rowEvolution->getRowEvolution(1, 'day', 'last7', 'Actions', 'getPageUrls');
        $this->assertNotEmpty($table);
    }

    /**
     * @dataProvider getMinMaxSeries
     * @param float[] $values
     */
    public function testMinAndMaxSkipOnlyTheLeadingZeros(array $values, float $expectedMin, float $expectedMax): void
    {
        $map = new DataTable\Map();
        foreach ($values as $index => $value) {
            $map->addTable(DataTable::makeFromSimpleArray([['metric' => $value]]), (string) $index);
        }
        $metadata = ['metrics' => ['metric' => 'Metric']];

        $method = new \ReflectionMethod(RowEvolution::class, 'enhanceRowEvolutionMetaData');
        $method->setAccessible(true);
        $method->invokeArgs(new RowEvolution(), [&$metadata, $map]);

        $this->assertSame($expectedMin, $metadata['metrics']['metric']['min']);
        $this->assertSame($expectedMax, $metadata['metrics']['metric']['max']);
    }

    public function getMinMaxSeries(): iterable
    {
        yield 'positive values after a zero' => [[0, 5, 0, 3], 0.0, 5.0];
        yield 'never above zero' => [[0, -800, -229], -800.0, -229.0];
        yield 'negative values before the first positive one' => [[-4, 2, 6], -4.0, 6.0];
    }
}
