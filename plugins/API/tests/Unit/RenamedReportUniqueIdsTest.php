<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\Plugins\API\ProcessedReport;

/**
 * @group API
 * @group RenamedReportUniqueIdsTest
 */
class RenamedReportUniqueIdsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getRenamedReportUniqueIdCases
     */
    public function testGetRenamedReportUniqueIdResolvesRetiredIds(string $uniqueId, string $expected): void
    {
        self::assertSame($expected, ProcessedReport::getRenamedReportUniqueId($uniqueId));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function getRenamedReportUniqueIdCases(): iterable
    {
        yield 'goals overview' => ['Goals_get_idGoal--0', 'Goals_get'];
        yield 'visits until conversion overview' => [
            'Goals_getVisitsUntilConversion_idGoal--0',
            'Goals_getVisitsUntilConversion',
        ];
        yield 'days to conversion overview' => [
            'Goals_getDaysToConversion_idGoal--0',
            'Goals_getDaysToConversion',
        ];
        yield 'unrelated report' => ['VisitsSummary_get', 'VisitsSummary_get'];
        yield 'surviving all goals report' => ['Goals_get', 'Goals_get'];
        yield 'ecommerce order report' => ['Goals_get_idGoal--ecommerceOrder', 'Goals_get_idGoal--ecommerceOrder'];
        yield 'real goal one' => ['Goals_get_idGoal--1', 'Goals_get_idGoal--1'];
        yield 'empty id' => ['', ''];
    }

    public function testGetRenamedReportUniqueIdsResolvesEachEntry(): void
    {
        self::assertSame(
            ['VisitsSummary_get', 'Goals_get', 'Goals_getDaysToConversion'],
            ProcessedReport::getRenamedReportUniqueIds([
                'VisitsSummary_get',
                'Goals_get_idGoal--0',
                'Goals_getDaysToConversion_idGoal--0',
            ])
        );
    }

    public function testGetRenamedReportUniqueIdsCollapsesAnIdThatIsSelectedTwice(): void
    {
        self::assertSame(
            ['Goals_get'],
            ProcessedReport::getRenamedReportUniqueIds(['Goals_get', 'Goals_get_idGoal--0'])
        );
    }

    public function testGetRenamedReportUniqueIdsKeepsTheFirstSeenPosition(): void
    {
        self::assertSame(
            ['Goals_get', 'VisitsSummary_get', 'Actions_getPageUrls'],
            ProcessedReport::getRenamedReportUniqueIds([
                'Goals_get_idGoal--0',
                'VisitsSummary_get',
                'Goals_get',
                'Actions_getPageUrls',
                'VisitsSummary_get',
            ])
        );
    }

    public function testGetRenamedReportUniqueIdsLeavesNonStringEntriesInPlace(): void
    {
        self::assertSame(
            [null, 'Goals_get', 5, ['nested'], false],
            ProcessedReport::getRenamedReportUniqueIds([
                null,
                'Goals_get_idGoal--0',
                5,
                ['nested'],
                false,
            ])
        );
    }

    public function testGetRenamedReportUniqueIdsReturnsAnEmptyListForAnEmptySelection(): void
    {
        self::assertSame([], ProcessedReport::getRenamedReportUniqueIds([]));
    }

    public function testGetRenamedReportUniqueIdsReindexesTheSurvivingEntries(): void
    {
        self::assertSame(
            [0, 1],
            array_keys(ProcessedReport::getRenamedReportUniqueIds([
                'Goals_get_idGoal--0',
                'Goals_get',
                'VisitsSummary_get',
            ]))
        );
    }
}
