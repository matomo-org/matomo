<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\API\Request;
use Piwik\Plugins\Goals\tests\Fixtures\StarredGoals;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Starred goals in the API output: the `starred` field of the goals, and report metadata that lists
 * the reports of starred goals first.
 *
 * @group Goals
 * @group Plugins
 * @group StarredGoalsTest
 */
class StarredGoalsTest extends SystemTestCase
{
    /**
     * @var StarredGoals
     */
    public static $fixture = null;

    /**
     * @dataProvider getApiForTesting
     * @param string|string[] $api
     * @param array<string, mixed> $params
     */
    public function testApi($api, array $params): void
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>}>
     */
    public function getApiForTesting(): iterable
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        yield 'goals of the site with starred goals' => ['Goals.getGoals', [
            'idSite' => $idSite,
            'date' => $dateTime,
        ]];

        yield 'goals of the site without starred goals' => ['Goals.getGoals', [
            'idSite' => self::$fixture->idSiteWithoutStarredGoals,
            'date' => $dateTime,
            'testSuffix' => '_siteWithoutStarredGoals',
        ]];

        yield 'goals of both sites' => ['Goals.getGoals', [
            'idSite' => 'all',
            'date' => $dateTime,
            'testSuffix' => '_allSites',
        ]];

        yield 'a starred goal' => ['Goals.getGoal', [
            'idSite' => $idSite,
            'date' => $dateTime,
            'otherRequestParameters' => ['idGoal' => 3],
            'testSuffix' => '_starred',
        ]];

        yield 'a goal that is not starred' => ['Goals.getGoal', [
            'idSite' => $idSite,
            'date' => $dateTime,
            'otherRequestParameters' => ['idGoal' => 1],
            'testSuffix' => '_notStarred',
        ]];

        // the conversions of a goal do not depend on it being starred
        yield 'conversions of all goals' => ['Goals.get', [
            'idSite' => $idSite,
            'date' => $dateTime,
            'periods' => ['day'],
            'otherRequestParameters' => ['showAllGoalSpecificMetrics' => 1],
        ]];
    }

    public function testReportMetadataListsTheReportsOfStarredGoalsFirst(): void
    {
        // starred: Demo request (5), Goal 10 (3). Then by name: apple pay (7), contact form (2), Download (6),
        // Goal 2 (4), Newsletter (1)
        $expectedOrder = [5, 3, 7, 2, 6, 4, 1];

        foreach (['get', 'getDaysToConversion', 'getVisitsUntilConversion'] as $action) {
            $reports = $this->getGoalReports(self::$fixture->idSite, $action);

            self::assertSame($expectedOrder, array_keys($reports), $action);

            $starred = array_keys(array_filter($reports, function (array $report): bool {
                return true === ($report['isStarred'] ?? null);
            }));
            self::assertSame([5, 3], $starred, $action);

            // the order values follow the order of the list
            $orders = array_column($reports, 'order');
            $sortedOrders = $orders;
            sort($sortedOrders);
            self::assertSame($sortedOrders, $orders, $action);
        }
    }

    public function testReportMetadataOfASiteWithoutStarredGoalsIsOrderedByGoalName(): void
    {
        // Alpha (3), Beta (2), Newsletter (1)
        $reports = $this->getGoalReports(self::$fixture->idSiteWithoutStarredGoals, 'get');

        self::assertSame([3, 2, 1], array_keys($reports));
        foreach ($reports as $report) {
            // API.getReportMetadata removes attributes that are false
            self::assertArrayNotHasKey('isStarred', $report);
        }
    }

    /**
     * @return array<int, array<string, mixed>> report metadata by goal ID, in the order of the reports
     */
    private function getGoalReports(int $idSite, string $action): array
    {
        $reports = Request::processRequest('API.getReportMetadata', [
            'idSites' => $idSite,
            'period' => 'day',
            'date' => self::$fixture->dateTime,
            'filter_limit' => '-1',
        ]);

        $goalReports = [];
        foreach ($reports as $report) {
            $idGoal = $report['parameters']['idGoal'] ?? null;
            if ('Goals' === $report['module'] && $action === $report['action'] && is_numeric($idGoal)) {
                $goalReports[(int) $idGoal] = $report;
            }
        }

        return $goalReports;
    }

    public static function getOutputPrefix()
    {
        return 'starredGoals';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

StarredGoalsTest::$fixture = new StarredGoals();
