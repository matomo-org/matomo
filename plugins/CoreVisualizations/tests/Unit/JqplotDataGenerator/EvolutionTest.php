<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\tests\Unit\JqplotDataGenerator;

use PHPUnit\Framework\TestCase;
use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Evolution;
use Piwik\Site;
use ReflectionClass;

/**
 * @group CoreVisualizations
 * @group Evolution
 * @group JqplotDataGenerator
 */
class EvolutionTest extends TestCase
{
    /**
     * @dataProvider getLeadingZeroSamplesTestData
     * @param array<int, float> $samples
     * @param array<int, float> $expected
     */
    public function testRemoveLeadingZeroSamples(array $samples, array $expected): void
    {
        $result = $this->invokePrivateMethod(
            $this->createGenerator(),
            'removeLeadingZeroSamples',
            [$samples]
        );

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{array<int, float>, array<int, float>}>
     */
    public function getLeadingZeroSamplesTestData(): iterable
    {
        yield 'empty' => [
            [],
            [],
        ];

        yield 'all zeros' => [
            [0.0, 0.0, 0.0],
            [],
        ];

        yield 'leading zeros only are removed' => [
            [0.0, 0.0, 5.0, 0.0, 2.0],
            [5.0, 0.0, 2.0],
        ];

        yield 'first value non zero' => [
            [3.0, 0.0, 1.0],
            [3.0, 0.0, 1.0],
        ];
    }

    /**
     * @dataProvider getForecastVisibilityTestData
     */
    public function testShouldRenderForecastValue(float $forecastValue, float $currentValue, bool $expected): void
    {
        $result = $this->invokePrivateMethod(
            $this->createGenerator(),
            'shouldRenderForecastValue',
            [$forecastValue, $currentValue]
        );

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{float, float, bool}>
     */
    public function getForecastVisibilityTestData(): iterable
    {
        yield 'higher than current' => [12.5, 10.0, true];
        yield 'equal to current' => [10.0, 10.0, true];
        yield 'below current' => [9.99, 10.0, false];
    }

    public function testBuildForecastDataUsesPriorForecastAsFirstNoDataBaseline(): void
    {
        $generator = $this->createGenerator();
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-11', $site),
            $this->createDataTableForDay('2026-04-12', $site),
            $this->createDataTableForDay('2026-04-13', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
            $this->createDataTableForDay('2026-04-18', $site, '2026-04-18 00:00:00'),
        ];

        $forecastData = $this->invokePrivateMethod(
            $generator,
            'buildForecastData',
            [[
                'Visits' => [80.0, 100.0, 140.0, 60.0, 20.0, 0.0],
            ], $dataTables, [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ], [
                'Visits' => false,
            ]]
        );

        self::assertSame([[null, null, null, null, 47.9996, 58.3997]], $forecastData);
    }

    public function testBuildForecastDataKeepsPercentSeriesOnDisplayScale(): void
    {
        $generator = $this->createGenerator();
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        $forecastData = $this->invokePrivateMethod(
            $generator,
            'buildForecastData',
            [[
                'Bounce rate' => [80.0, 20.0],
            ], $dataTables, [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ], [
                'Bounce rate' => '%',
            ]]
        );

        self::assertSame([[null, 47.9996]], $forecastData);
    }

    public function testBuildForecastDataDoesNotUseSyntheticZeroAsPercentForecastSeed(): void
    {
        $generator = $this->createGenerator();
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-05', $site),
            $this->createDataTableForDay('2026-04-12', $site, '2026-04-12 00:00:00'),
            $this->createDataTableForDay('2026-04-19', $site, '2026-04-19 00:00:00'),
        ];

        $forecastData = $this->invokePrivateMethod(
            $generator,
            'buildForecastData',
            [[
                'Bounce rate' => [69.0, 0.0, 0.0],
            ], $dataTables, [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ], [
                'Bounce rate' => '%',
            ], [
                'Bounce rate' => [true, false, false],
            ]]
        );

        self::assertSame([[null, 69.0, 69.0]], $forecastData);
    }

    public function testBuildForecastDataDoesNotUseUnavailableHistoricalSamples(): void
    {
        $generator = $this->createGenerator();
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-03-27', $site),
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        $forecastData = $this->invokePrivateMethod(
            $generator,
            'buildForecastData',
            [[
                'Visits' => [80.0, 0.0, 100.0, 20.0],
            ], $dataTables, [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ], [
                'Visits' => false,
            ], [
                'Visits' => [true, false, true, true],
            ]]
        );

        self::assertSame([[null, null, null, 59.9997]], $forecastData);
    }

    public function testBuildForecastDataReusesForecastAsSyntheticDataForLaterNoDataDaysAndRecalculatesWhenDataReturns(): void
    {
        $generator = $this->createGenerator();
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-11', $site),
            $this->createDataTableForDay('2026-04-12', $site),
            $this->createDataTableForDay('2026-04-13', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
            $this->createDataTableForDay('2026-04-18', $site, '2026-04-18 00:00:00'),
            $this->createDataTableForDay('2026-04-19', $site, '2026-04-19 00:00:00'),
            $this->createDataTableForDay('2026-04-20', $site, '2026-04-20 12:00:00'),
        ];

        $forecastData = $this->invokePrivateMethod(
            $generator,
            'buildForecastData',
            [[
                'Visits' => [80.0, 100.0, 140.0, 60.0, 20.0, 0.0, 0.0, 30.0],
            ], $dataTables, [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ], [
                'Visits' => false,
            ]]
        );

        self::assertSame([[null, null, null, null, 47.9996, 58.3997, 74.7198, 59.9994]], $forecastData);
    }

    public function testBuildForecastDataDoesNotCarryForwardAcrossSuppressedForecastGap(): void
    {
        $generator = $this->createGenerator();
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-01', $site),
            $this->createDataTableForDay('2026-04-08', $site),
            $this->createDataTableForDay('2026-04-15', $site, '2026-04-15 22:48:00'),
            $this->createDataTableForDay('2026-04-16', $site, '2026-04-16 00:00:00'),
        ];

        $forecastData = $this->invokePrivateMethod(
            $generator,
            'buildForecastData',
            [[
                'Visits' => [50.0, 50.0, 90.0, 0.0],
            ], $dataTables, [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ], [
                'Visits' => false,
            ]]
        );

        self::assertSame([[null, null, null, 0.0]], $forecastData);
    }

    /**
     * @dataProvider getColumnValueRuleTestData
     * @param mixed $value
     */
    public function testHasColumnValueRule($value, bool $expected): void
    {
        $result = $this->invokePrivateMethod(
            $this->createGenerator(),
            'hasColumnValue',
            [$value]
        );

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public function getColumnValueRuleTestData(): iterable
    {
        yield 'false is missing' => [false, false];
        yield 'null is missing' => [null, false];
        yield 'empty string is missing' => ['', false];
        yield 'numeric zero counts as data' => [0, true];
        yield 'float zero counts as data' => [0.0, true];
        yield 'string zero counts as data' => ['0', true];
        yield 'positive integer counts as data' => [42, true];
        yield 'positive float counts as data' => [12.5, true];
        yield 'negative number counts as data' => [-3, true];
        yield 'non-empty string counts as data' => ['1.5', true];
    }

    private function createGenerator(): Evolution
    {
        $reflection = new ReflectionClass(Evolution::class);
        return $reflection->newInstanceWithoutConstructor();
    }

    private function createSiteMock(): Site
    {
        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTimezone'])
            ->getMock();

        $site->method('getTimezone')->willReturn('UTC');

        return $site;
    }

    private function createDataTableForDay(string $date, Site $site, ?string $archivedDate = null): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $site);

        if ($archivedDate !== null) {
            $dataTable->setMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME, $archivedDate);
        }

        return $dataTable;
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function invokePrivateMethod(Evolution $generator, string $methodName, array $arguments)
    {
        $reflection = new ReflectionClass(Evolution::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($generator, $arguments);
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function invokeProtectedMethod(Evolution $generator, string $methodName, array $arguments)
    {
        return $this->invokePrivateMethod($generator, $methodName, $arguments);
    }

    /**
     * @param mixed $value
     */
    private function setGeneratorProperty(Evolution $generator, string $propertyName, $value): void
    {
        $reflection = new ReflectionClass($generator);
        while ($reflection && !$reflection->hasProperty($propertyName)) {
            $reflection = $reflection->getParentClass();
        }

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($generator, $value);
    }
}
