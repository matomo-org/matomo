<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\API\Request;
use Piwik\Archive;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReportsPurger;
use Piwik\SettingsPiwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\EstimatedUniques;

/**
 * @group Core
 * @group UniqueVisitorsEstimate
 */
class UniqueVisitorsEstimateTest extends IntegrationTestCase
{
    private const FIRST_DAY = 1;
    private const LAST_DAY = 10;
    private const VISITS_PER_DAY = 20;
    private const VISITOR_POOL = 60;
    private const USER_POOL = 25;
    // the last day has no logged-in users
    private const LAST_DAY_WITH_USERS = 9;
    private const ESTIMATED_PERIODS = ['week', 'month', 'year', 'range'];
    // enable_processing_unique_visitors_... of a period that isn't estimated: its default in global.ini.php
    // (estimated periods are not counted in these tests unless a test says so)
    private const EXACT_PERIOD_DEFAULTS = ['week' => 1, 'month' => 1, 'year' => 0, 'range' => 0];

    public static function beforeTableDataCached(): void
    {
        Fixture::createSuperUser();
        Fixture::createWebsite('2024-01-01', 0, 'Example', 'https://example.com');
        self::trackVisits();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->setEstimatedPeriods(self::ESTIMATED_PERIODS);
    }

    public function testNoEstimatedPeriodKeepsTheExactCountsAndStoresNoSketch()
    {
        $this->setEstimatedPeriods([]);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertSame($this->countExact('idvisitor', 1, 10), (int) $row['nb_uniq_visitors']);
        $this->assertSame($this->countExact('user_id', 1, 10), (int) $row['nb_users']);
        $this->assertNull($this->getStoredSketch('day', '2024-03-01'));
        $this->assertNull($this->getStoredSketch('month', '2024-03-01'));
        $this->assertArrayNotHasKey('uniques_estimated', $row);
    }

    public function testDaysKeepTheExactCounts()
    {
        $row = $this->getVisitsSummary('day', '2024-03-04');

        $this->assertSame($this->countExact('idvisitor', 4, 4), (int) $row['nb_uniq_visitors']);
        $this->assertSame($this->countExact('user_id', 4, 4), (int) $row['nb_users']);
        $this->assertNotNull($this->getStoredSketch('day', '2024-03-04'));
        $this->assertNotNull($this->getStoredSketch('day', '2024-03-04', PluginsArchiver::USERS_SKETCH_RECORD));
        $this->assertArrayNotHasKey('uniques_estimated', $row);
    }

    /**
     * @dataProvider getLongerPeriods
     */
    public function testLongerPeriodsAreEstimatedWithoutTheVisitLogs(string $period, string $date, int $firstDay, int $lastDay)
    {
        $visitors = $this->countExact('idvisitor', $firstDay, $lastDay);
        $users = $this->countExact('user_id', $firstDay, $lastDay);

        // archive the days, then remove the visits they were built from
        $this->getVisitsSummary('day', '2024-03-01,2024-03-31');
        Db::query('DELETE FROM ' . Common::prefixTable('log_visit'));

        $row = $this->getVisitsSummary($period, $date);

        $this->assertEqualsWithDelta($visitors, $row['nb_uniq_visitors'], 2);
        $this->assertEqualsWithDelta($users, $row['nb_users'], 2);
        $this->assertLessThanOrEqual($row['nb_visits'], $row['nb_uniq_visitors']);
        $this->assertSame(1, $row['uniques_estimated']);
    }

    public function getLongerPeriods(): array
    {
        return [
            'week' => ['week', '2024-03-04', 4, 10],
            'month' => ['month', '2024-03-01', 1, 10],
            'year' => ['year', '2024-03-01', 1, 10],
            'range' => ['range', '2024-03-02,2024-03-08', 2, 8],
        ];
    }

    public function testRangeStoresNoSketch()
    {
        $row = $this->getVisitsSummary('range', '2024-03-02,2024-03-08');

        $this->assertGreaterThan(0, $row['nb_uniq_visitors']);
        $this->assertNull($this->getStoredSketch('range', '2024-03-02,2024-03-08'));
        $this->assertNull($this->getStoredSketch('range', '2024-03-02,2024-03-08', PluginsArchiver::USERS_SKETCH_RECORD));
        $this->assertNotNull($this->getStoredSketch('week', '2024-03-04'));
    }

    public function testUsersSketchIsOnlyStoredForDaysWithUsers()
    {
        $week = $this->getVisitsSummary('week', '2024-03-04');

        $this->assertNull($this->getStoredSketch('day', '2024-03-10', PluginsArchiver::USERS_SKETCH_RECORD));
        $this->assertNotNull($this->getStoredSketch('day', '2024-03-10'));
        // the day without a users sketch counts as having no users, not as missing data
        $this->assertEqualsWithDelta($this->countExact('user_id', 4, 10), $week['nb_users'], 2);
        $this->assertNotNull($this->getStoredSketch('week', '2024-03-04', PluginsArchiver::USERS_SKETCH_RECORD));
    }

    public function testPeriodWithoutUsersHasZeroUsersAndNoUsersSketch()
    {
        Db::query('UPDATE ' . Common::prefixTable('log_visit') . ' SET user_id = NULL');

        $week = $this->getVisitsSummary('week', '2024-03-04');

        $this->assertSame(0, (int) $week['nb_users']);
        $this->assertGreaterThan(0, $week['nb_uniq_visitors']);
        $this->assertNull($this->getStoredSketch('week', '2024-03-04', PluginsArchiver::USERS_SKETCH_RECORD));
    }

    public function testUsersDifferingInCaseOrAccentsAreCountedOnceAcrossDays()
    {
        // two users, written differently from visit to visit and day to day
        Db::query(
            'UPDATE ' . Common::prefixTable('log_visit') . ' SET user_id = ELT((DAY(visit_last_action_time) + HOUR(visit_last_action_time)) % 6 + 1, ?, ?, ?, ?, ?, ?)'
            . ' WHERE user_id IS NOT NULL',
            ['Alice', 'alice', 'ALICE', 'José', 'Jose', 'JOSÉ']
        );
        // the exact count follows the column's case and accent insensitive collation
        $this->assertSame(2, $this->countExact('user_id', 4, 10));

        $week = $this->getVisitsSummary('week', '2024-03-04');

        $this->assertSame(1, $week['uniques_estimated']);
        $this->assertSame(2, (int) $week['nb_users']);
    }

    /**
     * @dataProvider getPrecisionSettings
     */
    public function testInvalidPrecisionFallsBackToTheDefault($setting, int $expected)
    {
        $this->setGeneralConfig('unique_visitors_estimate_precision', $setting);

        $this->assertSame($expected, SettingsPiwik::getUniqueVisitorsEstimatePrecision());
    }

    public function getPrecisionSettings(): array
    {
        return [
            'valid' => [12, 12],
            'valid string' => ['16', 16],
            'lowest' => [4, 4],
            'highest' => [18, 18],
            'not a number' => ['high', 14],
            'too low' => [3, 14],
            'too high' => [19, 14],
            'empty' => ['', 14],
        ];
    }

    public function testDayArchivedBeforeAPeriodWasEstimatedLeavesTheUniquesOut()
    {
        $this->archiveFirstDayWithoutSketches();

        $row = $this->getVisitsSummary('month', '2024-03-01');

        // the month isn't set to be counted either
        $this->assertGreaterThan(0, $row['nb_visits']);
        $this->assertArrayNotHasKey('nb_uniq_visitors', $row);
        $this->assertArrayNotHasKey('nb_users', $row);
        $this->assertNull($this->getStoredSketch('month', '2024-03-01'));
        $this->assertSame(0, $row['uniques_estimated']);
    }

    public function testEachPeriodFollowsItsOwnSetting()
    {
        $this->setEstimatedPeriods(['month', 'year']);

        $week = $this->getVisitsSummary('week', '2024-03-04');
        $month = $this->getVisitsSummary('month', '2024-03-01');
        $year = $this->getVisitsSummary('year', '2024-03-01');
        $range = $this->getVisitsSummary('range', '2024-03-02,2024-03-08');

        $this->assertSame($this->countExact('idvisitor', 4, 10), (int) $week['nb_uniq_visitors']);
        $this->assertSame($this->countExact('user_id', 4, 10), (int) $week['nb_users']);
        $this->assertArrayNotHasKey('uniques_estimated', $week);
        $this->assertSame(1, $month['uniques_estimated']);
        $this->assertSame(1, $year['uniques_estimated']);
        // ranges are off, as by default
        $this->assertArrayNotHasKey('nb_uniq_visitors', $range);
        $this->assertArrayNotHasKey('uniques_estimated', $range);
    }

    public function testEstimatedYearIsBuiltFromMonthsCountedExactly()
    {
        $this->setEstimatedPeriods(['year']);

        $month = $this->getVisitsSummary('month', '2024-03-01');
        $year = $this->getVisitsSummary('year', '2024-03-01');

        $this->assertSame($this->countExact('idvisitor', 1, 10), (int) $month['nb_uniq_visitors']);
        $this->assertArrayNotHasKey('uniques_estimated', $month);
        // the month is counted, but stores the sketch the year needs
        $this->assertNotNull($this->getStoredSketch('month', '2024-03-01'));
        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10), $year['nb_uniq_visitors'], 2);
        $this->assertSame(1, $year['uniques_estimated']);
    }

    public function testPeriodThatIsAlsoCountedFallsBackToTheExactCountWithoutSketches()
    {
        $this->setEstimatedPeriods(self::ESTIMATED_PERIODS, ['month']);
        $this->archiveFirstDayWithoutSketches(['month']);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertSame($this->countExact('idvisitor', 1, 10), (int) $row['nb_uniq_visitors']);
        $this->assertSame($this->countExact('user_id', 1, 10), (int) $row['nb_users']);
        $this->assertSame(0, $row['uniques_estimated']);
    }

    public function testPeriodThatIsAlsoCountedIsEstimatedWhenItHasSketches()
    {
        $this->setEstimatedPeriods(['month'], ['month']);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        // other reports that count unique visitors themselves still follow the exact setting
        $this->assertTrue(SettingsPiwik::isUniqueVisitorsEnabled('month'));
        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10), $row['nb_uniq_visitors'], 2);
        $this->assertSame(1, $row['uniques_estimated']);
    }

    /**
     * @dataProvider getStoredSketchPeriods
     */
    public function testSketchesAreOnlyStoredWhereALongerEstimatedPeriodReadsThem(array $estimatedPeriods, array $periodsWithSketch)
    {
        $this->setEstimatedPeriods($estimatedPeriods);

        $this->getVisitsSummary('year', '2024-03-01');
        $this->getVisitsSummary('range', '2024-03-02,2024-03-08');

        $stored = [];
        foreach (['day' => '2024-03-05', 'week' => '2024-03-04', 'month' => '2024-03-01', 'year' => '2024-03-01'] as $period => $date) {
            if ($this->getStoredSketch($period, $date) !== null) {
                $stored[] = $period;
            }
        }

        $this->assertSame($periodsWithSketch, $stored);
    }

    public function getStoredSketchPeriods(): array
    {
        return [
            'weeks' => [['week'], ['day']],
            'months' => [['month'], ['day', 'week']],
            'years' => [['year'], ['day', 'week', 'month']],
            'ranges' => [['range'], ['day', 'week', 'month', 'year']],
            'weeks and months' => [['week', 'month'], ['day', 'week']],
        ];
    }

    /**
     * @dataProvider getPeriodSettings
     * @param mixed $exactSetting
     * @param mixed $estimateSetting
     */
    public function testSettingsSelectOffCountedOrEstimated(string $period, $exactSetting, $estimateSetting, bool $counted, bool $estimated)
    {
        $this->setGeneralConfig("enable_processing_unique_visitors_$period", $exactSetting);
        $this->setGeneralConfig("enable_processing_estimated_unique_visitors_$period", $estimateSetting);

        $this->assertSame($counted, SettingsPiwik::isUniqueVisitorsEnabled($period));
        $this->assertSame($estimated, SettingsPiwik::isUniqueVisitorsEstimated($period));
        $this->assertSame($counted || $estimated, SettingsPiwik::isUniqueVisitorsTotalAvailable($period));
    }

    public function getPeriodSettings(): array
    {
        return [
            'off' => ['month', 0, 0, false, false],
            'counted' => ['month', 1, 0, true, false],
            'estimated' => ['month', 0, 1, false, true],
            'counted and estimated' => ['month', 1, 1, true, true],
            'estimated, set as a string' => ['year', 0, '1', false, true],
            'the old value 2 is not an estimate' => ['month', 2, 0, false, false],
            'only 1 estimates' => ['month', 0, 2, false, false],
            'days are counted, never estimated' => ['day', 1, 1, true, false],
        ];
    }

    public function testNoSketchIsStoredWhenOnlyDaysAreSetToBeEstimated()
    {
        $this->setEstimatedPeriods([]);
        $this->setGeneralConfig('enable_processing_estimated_unique_visitors_day', 1);

        $this->assertFalse(SettingsPiwik::isUniqueVisitorsEstimateEnabled());
        $this->assertFalse(SettingsPiwik::isUniqueVisitorsSketchNeeded('day'));
    }

    public function testRangeWithoutAnEstimateHidesUniquesAndIsNotArchivedAgain()
    {
        $this->archiveFirstDayWithoutSketches();

        $countRangeArchives = function () {
            return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_numeric_2024_03') . " WHERE period = 5 AND name LIKE 'done%'");
        };

        $this->getVisitsSummary('range', '2024-03-01,2024-03-03');
        $archives = $countRangeArchives();
        Cache::getTransientCache()->flushAll();
        $row = $this->getVisitsSummary('range', '2024-03-01,2024-03-03');

        $this->assertSame($archives, $countRangeArchives());
        $this->assertGreaterThan(0, $row['nb_visits']);
        // range uniques are off, so the exact path stores 0, which is hidden rather than shown as a count
        $this->assertArrayNotHasKey('nb_uniq_visitors', $row);
        $this->assertArrayNotHasKey('nb_users', $row);
        $this->assertSame(0, $row['uniques_estimated']);
    }

    public function testDamagedSketchLeavesTheUniquesOut()
    {
        $this->getVisitsSummary('day', '2024-03-04,2024-03-10');
        Db::query(
            'UPDATE ' . Common::prefixTable('archive_blob_2024_03') . ' SET value = ? WHERE name = ? AND date1 = ? AND period = 1',
            [gzcompress('damaged'), PluginsArchiver::UNIQUE_VISITORS_SKETCH_RECORD, '2024-03-05']
        );

        $week = $this->getVisitsSummary('week', '2024-03-04');

        $this->assertSame(self::VISITS_PER_DAY * 7, (int) $week['nb_visits']);
        $this->assertArrayNotHasKey('nb_uniq_visitors', $week);
        $this->assertNull($this->getStoredSketch('week', '2024-03-04'));
        $this->assertSame(0, $week['uniques_estimated']);
    }

    public function testSegmentsAreEstimated()
    {
        $segmented = $this->getVisitsSummary('month', '2024-03-01', 'visitCount>=1');

        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10), $segmented['nb_uniq_visitors'], 2);
        $this->assertEqualsWithDelta($this->countExact('user_id', 1, 10), $segmented['nb_users'], 2);
    }

    public function testSegmentJoiningActionsGivesTheSameEstimate()
    {
        // every visit viewed /page, so a segment on the action table matches every visit
        $segmented = $this->getVisitsSummary('month', '2024-03-01', 'pageUrl=@%2Fpage');
        $all = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertSame($all['nb_uniq_visitors'], $segmented['nb_uniq_visitors']);
        $this->assertSame($all['nb_users'], $segmented['nb_users']);
    }

    /**
     * @dataProvider getSplitColumns
     */
    public function testEstimateIsSplitByVisitorType(string $method, string $suffix, string $where)
    {
        $result = Request::processRequest($method, ['idSite' => 1, 'period' => 'month', 'date' => '2024-03-01']);
        $row = $result->getFirstRow()->getColumns();

        $this->assertGreaterThan(0, $row["nb_uniq_visitors$suffix"]);
        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10, $where), $row["nb_uniq_visitors$suffix"], 2);
        $this->assertEqualsWithDelta($this->countExact('user_id', 1, 10, $where), $row["nb_users$suffix"], 2);
        $this->assertSame(1, $row["uniques_estimated$suffix"]);
    }

    /**
     * @dataProvider getUniquesColumnsBySuffix
     */
    public function testEstimateFlagIsReturnedWhenRequestedByNameOnly(string $method, string $suffix)
    {
        $this->assertArrayNotHasKey("uniques_estimated$suffix", $this->getMonthColumns($method, "nb_uniq_visitors$suffix"));
        $this->assertSame(1, $this->getMonthColumns($method, "nb_uniq_visitors$suffix,uniques_estimated$suffix")["uniques_estimated$suffix"]);
        $this->assertArrayNotHasKey("uniques_estimated$suffix", $this->getMonthColumns($method, "nb_visits$suffix"));
    }

    public function getUniquesColumnsBySuffix(): array
    {
        return [
            'all visitors' => ['VisitsSummary.get', ''],
            'new visitors' => ['VisitFrequency.get', '_new'],
            'human visitors' => ['AIAgents.get', '_human'],
        ];
    }

    public function testEstimateFlagRequestedByNameFromApiGet()
    {
        $this->assertSame(['uniques_estimated' => 1], $this->getMonthColumns('API.get', 'uniques_estimated'));
        $withVisits = $this->getMonthColumns('API.get', 'nb_visits,uniques_estimated');
        $this->assertEqualsCanonicalizing(['nb_visits', 'uniques_estimated'], array_keys($withVisits));
        $this->assertSame(1, $withVisits['uniques_estimated']);
        $this->assertSame(['uniques_estimated_new' => 1], $this->getMonthColumns('API.get', 'uniques_estimated_new'));
    }

    public function testUsersAreKeptWhenRequestedWithoutUniqueVisitors()
    {
        $this->assertArrayHasKey('nb_users', $this->getMonthColumns('VisitsSummary.get', 'nb_visits,nb_users'));
        $this->assertArrayHasKey('nb_users', $this->getMonthColumns('API.get', 'nb_visits,nb_users'));
        $this->assertArrayHasKey('nb_users_returning', $this->getMonthColumns('VisitFrequency.get', 'nb_visits_returning,nb_users_returning'));
    }

    public function testSingleUniquesMetricsAreEstimatedOnYears()
    {
        $visitors = Request::processRequest('VisitsSummary.getUniqueVisitors', ['idSite' => 1, 'period' => 'year', 'date' => '2024-03-01']);
        $users = Request::processRequest('VisitsSummary.getUsers', ['idSite' => 1, 'period' => 'year', 'date' => '2024-03-01']);

        $this->assertSame(['nb_uniq_visitors'], array_keys($visitors->getFirstRow()->getColumns()));
        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10), $visitors->getFirstRow()->getColumn('nb_uniq_visitors'), 2);
        $this->assertSame(['nb_users'], array_keys($users->getFirstRow()->getColumns()));
        $this->assertEqualsWithDelta($this->countExact('user_id', 1, 10), $users->getFirstRow()->getColumn('nb_users'), 2);
    }

    public function testYearWithoutAnEstimateHasNoUniquesAnywhere()
    {
        $this->archiveFirstDayWithoutSketches();

        $this->assertSame([], $this->getYearColumns('VisitsSummary.getUniqueVisitors'));
        $this->assertSame([], $this->getYearColumns('VisitsSummary.getUsers'));
        // the flag fetched with the uniques must not be left as the only column, where it reads as 0 visitors
        $this->assertSame([], $this->getYearColumns('VisitsSummary.get', 'nb_uniq_visitors'));
        $this->assertSame([], $this->getYearColumns('VisitsSummary.get', 'nb_uniq_visitors,nb_users'));
        $this->assertStringNotContainsString('VisitsSummary_NbUniqueVisitorsEstimated', $this->getSparklines('year', '2024-03-01'));
    }

    public function testSparklinesShowTheEstimateOnYears()
    {
        $this->assertStringContainsString('VisitsSummary_NbUniqueVisitorsEstimated', $this->getSparklines('year', '2024-03-01'));
    }

    public function testSparklinesTitleTheUsersCardAsEstimatedOnYears()
    {
        $this->assertSame('VisitsSummary_ColumnNbUsersEstimated', $this->getSparklineTitles('year', '2024-03-01')['nb_users']);
    }

    public function testSparklinesKeepTheUsersCardTitleWhenThePeriodWasCountedExactly()
    {
        $this->archiveFirstDayWithoutSketches(['month']);

        $this->assertSame('General_ColumnNbUsers', $this->getSparklineTitles('month', '2024-03-01')['nb_users']);
    }

    public function testSparklinesLabelACountedMonthComparedWithAnEstimatedYear()
    {
        // the month is counted, so only the year it's compared with is estimated
        $this->setEstimatedPeriods(['year']);
        $compare = ['comparePeriods' => ['year'], 'compareDates' => ['2024-03-01']];

        $compared = $this->getSparklines('month', '2024-03-01', $compare);
        $this->assertStringContainsString('VisitsSummary_ColumnNbUsersEstimated', $compared);
        $this->assertStringContainsString('VisitsSummary_NbUniqueVisitorsEstimated', $compared);
        $this->assertStringNotContainsString('Estimated', $this->getSparklines('month', '2024-03-01'));
    }

    public function testSparklinesShowNoUsersEvolutionWithoutAPreviousEstimate()
    {
        // the previous range, 2024-03-01 to 03, can't be estimated
        $this->archiveFirstDayWithoutSketches();

        $evolutions = $this->getSparklineEvolutions('range', '2024-03-04,2024-03-06');

        $this->assertTrue($evolutions['nb_visits']);
        $this->assertFalse($evolutions['nb_users']);
    }

    /**
     * @dataProvider getComparedYears
     */
    public function testSparklinesShowTheUniquesOfAComparedYear(bool $hasEstimate, array $expected)
    {
        if (!$hasEstimate) {
            $this->archiveFirstDayWithoutSketches();
        }

        // the month is counted, so only the year depends on the sketches
        $this->setEstimatedPeriods(['year']);

        $users = [];
        foreach ($this->getSparklineMetrics('month', '2024-03-01', ['comparePeriods' => ['year'], 'compareDates' => ['2024-03-01']]) as [, $metric]) {
            // the card is titled as estimated when the year was
            if (in_array($metric['title'], ['General_ColumnNbUsers', 'VisitsSummary_ColumnNbUsersEstimated'])) {
                $users[] = $metric['value'];
            }
        }

        // the month, then the year it's compared with
        $this->assertSame($expected, $users);
    }

    public function getComparedYears(): array
    {
        return [
            'estimated' => [true, [25, 25]],
            'not estimated' => [false, [25, '-']],
        ];
    }

    public function testSparklinesKeepTheEvolutionOfMetricsLeftOutOfTheRequestedColumns()
    {
        // the previous period is fetched with the same columns, so it has no pageviews to compare with
        $evolutions = $this->getSparklineEvolutions('day', '2024-03-02', ['columns' => 'nb_visits,nb_actions']);

        $this->assertTrue($evolutions['nb_visits']);
        $this->assertTrue($evolutions['nb_pageviews']);
        $this->assertTrue($evolutions['avg_time_on_site']);
    }

    public function getSplitColumns(): array
    {
        return [
            'new' => ['VisitFrequency.get', '_new', 'visitor_returning = 0'],
            'returning' => ['VisitFrequency.get', '_returning', 'visitor_returning > 0'],
            'human' => ['AIAgents.get', '_human', '1 = 1'],
        ];
    }

    public function testPeriodMixingPrecisionsIsEstimatedAtTheLowerOne()
    {
        $this->getVisitsSummary('day', '2024-03-01,2024-03-05');
        $this->setGeneralConfig('unique_visitors_estimate_precision', 12);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10), $row['nb_uniq_visitors'], 2);
        $this->assertSame(12, ord($this->getStoredSketch('month', '2024-03-01')[0]));
    }

    public function testEstimateSurvivesPurgingOldReportsWhenBasicMetricsAreKept()
    {
        $before = $this->getVisitsSummary('month', '2024-03-01');

        ReportsPurger::make([
            'delete_reports_older_than' => 1,
            'delete_reports_keep_basic_metrics' => 1,
            'delete_reports_keep_day_reports' => 0,
            'delete_reports_keep_week_reports' => 0,
            'delete_reports_keep_month_reports' => 0,
            'delete_reports_keep_year_reports' => 0,
            'delete_reports_keep_range_reports' => 0,
            'delete_reports_keep_segment_reports' => 0,
            'delete_logs_max_rows_per_query' => 100000,
        ], PrivacyManager::getAllMetricsToKeep())->purgeData();

        // the sketch is a report blob, so it goes, but the estimate is a basic metric and stays
        $this->assertNull($this->getStoredSketch('month', '2024-03-01'));
        $after = $this->getVisitsSummary('month', '2024-03-01');
        $this->assertSame($before['nb_uniq_visitors'], $after['nb_uniq_visitors']);
        $this->assertSame($before['nb_users'], $after['nb_users']);
        $this->assertSame(1, $after['uniques_estimated']);
    }

    public function testArchivesCoveringSeveralSitesAreNotEstimated()
    {
        $idOtherSite = Fixture::createWebsite('2024-01-01', 0, 'Other', 'https://other.example.com');
        $idRollUp = Fixture::createWebsite('2024-01-01', 0, 'Roll-Up', 'https://rollup.example.com');
        Piwik::addAction('ArchiveProcessor.Parameters.getIdSites', function (&$idSites) use ($idRollUp, $idOtherSite) {
            if ($idSites === [$idRollUp]) {
                $idSites = [1, $idOtherSite];
            }
        });
        // like roll-ups, the site has no visits of its own, so it must not be skipped as empty
        Piwik::addAction('CronArchive.getIdSitesNotUsingTracker', function (&$idSites) use ($idRollUp) {
            $idSites[] = $idRollUp;
        });

        $row = $this->getVisitsSummary('month', '2024-03-01', '', $idRollUp);

        $this->assertSame(self::VISITS_PER_DAY * 10, (int) $row['nb_visits']);
        // as with the setting off, unique visitors across several sites are not processed
        $this->assertArrayNotHasKey('nb_uniq_visitors', $row);
        $this->assertSame(0, $row['uniques_estimated']);
        $this->assertNull($this->getStoredSketch('day', '2024-03-01', PluginsArchiver::UNIQUE_VISITORS_SKETCH_RECORD, $idRollUp));
        $this->assertNull($this->getStoredSketch('month', '2024-03-01', PluginsArchiver::UNIQUE_VISITORS_SKETCH_RECORD, $idRollUp));
    }

    /**
     * @dataProvider getLabelledReports
     */
    public function testEstimatedColumnsAreLabelledOnLongerPeriodsOnly(string $module, string $suffix)
    {
        $labels = function (?string $period) use ($module, $suffix) {
            $params = ['idSite' => 1, 'date' => '2024-03-01'];
            if ($period !== null) {
                $params['period'] = $period;
            }
            $metadata = Request::processRequest('API.getReportMetadata', $params);
            foreach ($metadata as $report) {
                if ($report['module'] === $module && $report['action'] === 'get') {
                    return [$report['metrics']["nb_uniq_visitors$suffix"], $report['metrics']["nb_users$suffix"]];
                }
            }

            return [];
        };

        // tests don't load translations, so the labels are the translation keys
        foreach (array_merge($labels('day'), $labels(null)) as $label) {
            $this->assertStringNotContainsString('Estimated', $label);
        }

        foreach ($labels('month') as $label) {
            $this->assertStringContainsString('Estimated', $label);
        }
    }

    public function getLabelledReports(): array
    {
        return [
            'all visitors' => ['VisitsSummary', ''],
            'new visitors' => ['VisitFrequency', '_new'],
            'human visitors' => ['AIAgents', '_human'],
            'main metrics, all visitors' => ['API', ''],
            'main metrics, returning visitors' => ['API', '_returning'],
            'main metrics, AI agents' => ['API', '_ai_agent'],
        ];
    }

    public function testMainMetricsKeepTheEstimateOnYears()
    {
        $report = Request::processRequest('API.getProcessedReport', [
            'idSite' => 1,
            'period' => 'year',
            'date' => '2024-03-01',
            'apiModule' => 'API',
            'apiAction' => 'get',
            'format_metrics' => 0,
        ]);
        $row = $report['reportData']->getFirstRow()->getColumns();

        $this->assertSame('VisitsSummary_ColumnNbUniqVisitorsEstimated', $report['metadata']['metrics']['nb_uniq_visitors']);
        $this->assertSame('VisitFrequency_ColumnUniqueReturningVisitorsEstimated', $report['metadata']['metrics']['nb_uniq_visitors_returning']);
        $this->assertEqualsWithDelta($this->countExact('idvisitor', 1, 10), $row['nb_uniq_visitors'], 2);
        $this->assertEqualsWithDelta($this->countExact('user_id', 1, 10), $row['nb_users'], 2);
    }

    /**
     * @dataProvider getEvolutionGraphLabels
     */
    public function testEvolutionGraphLabelsTheEstimate(string $module, string $column, string $period, string $date, string $label)
    {
        $html = $this->dispatch($module, 'getEvolutionGraph', [
            'idSite' => 1,
            'period' => $period,
            'date' => $date,
            'columns' => $column,
            'viewDataTable' => 'graphEvolution',
            'forceView' => 1,
        ], $module === 'VisitsSummary' ? [false, [$column]] : []);

        // the entry in the series picker
        $this->assertStringContainsString('"column":"' . $column . '","translation":"' . $label . '"', html_entity_decode($html, ENT_QUOTES));
    }

    public function getEvolutionGraphLabels(): array
    {
        return [
            'months, estimated' => ['VisitsSummary', 'nb_uniq_visitors', 'month', '2024-03-01', 'VisitsSummary_ColumnNbUniqVisitorsEstimated'],
            // a range's graph plots its days, which are counted exactly
            'range, plotting days' => ['VisitsSummary', 'nb_uniq_visitors', 'range', '2024-03-01,2024-03-10', 'General_ColumnNbUniqVisitors'],
            'new visitors, estimated' => ['VisitFrequency', 'nb_uniq_visitors_new', 'month', '2024-03-01', 'VisitFrequency_ColumnUniqueNewVisitorsEstimated'],
            'human visitors, estimated' => ['AIAgents', 'nb_uniq_visitors_human', 'month', '2024-03-01', 'AIAgents_ColumnHumanUniqueVisitorsEstimated'],
        ];
    }

    /**
     * @dataProvider getEvolutionGraphSeries
     */
    public function testEvolutionGraphPlotsTheUniquesOfWebsiteTotals(string $module, string $action, string $period, string $date, bool $estimate, array $expected)
    {
        $this->setEstimatedPeriods($estimate ? self::ESTIMATED_PERIODS : []);

        $this->assertSame($expected, $this->getPlottedSeries($module, $action, $period, $date, 'nb_visits,nb_uniq_visitors'));
    }

    public function getEvolutionGraphSeries(): array
    {
        return [
            'year, estimated' => ['VisitsSummary', 'getEvolutionGraph', 'year', '2024-03-01', true, ['General_ColumnNbVisits', 'VisitsSummary_ColumnNbUniqVisitorsEstimated']],
            // a range's graph plots its days, which are counted exactly
            'range, plotting days' => ['VisitsSummary', 'getEvolutionGraph', 'range', '2024-03-01,2024-03-10', true, ['General_ColumnNbVisits', 'General_ColumnNbUniqVisitors']],
            'year, setting off' => ['VisitsSummary', 'getEvolutionGraph', 'year', '2024-03-01', false, ['General_ColumnNbVisits']],
            // report rows aren't estimated
            'year, report rows' => ['Referrers', 'getReferrerType', 'year', '2024-03-01', true, ['Referrers_DirectEntry (General_ColumnNbVisits)']],
        ];
    }

    public function testEvolutionGraphLeavesAGapForAYearWithoutAnEstimate()
    {
        $this->archiveFirstDayWithoutSketches();

        $data = $this->getGraphData('VisitsSummary', 'getEvolutionGraph', 'year', '2024-03-01', 'nb_visits,nb_uniq_visitors', ['evolution_year_last_n' => 3]);

        // years without visits are a real 0, the year with visits but no estimate is a gap
        $this->assertSame([[0, 0, 200], [0, 0, null]], $data['data']);
        $this->assertTrue($data['params']['series'][1]['breakOnNull']);
    }

    /**
     * @dataProvider getSiteTotalReports
     */
    public function testProcessedReportLeavesOutTheUniquesOfAYearWithoutAnEstimate(string $module): void
    {
        $this->archiveFirstDayWithoutSketches();

        $report = Request::processRequest('API.getProcessedReport', [
            'idSite' => 1, 'period' => 'year', 'date' => '2024-03-01',
            'apiModule' => $module, 'apiAction' => 'get', 'format_metrics' => 0,
        ]);

        $row = $report['reportData']->getFirstRow()->getColumns();
        $this->assertGreaterThan(0, $row['nb_visits']);
        foreach (['nb_uniq_visitors', 'nb_users'] as $column) {
            $this->assertArrayNotHasKey($column, $row);
            $this->assertArrayNotHasKey($column, $report['columns']);
            $this->assertArrayNotHasKey($column, $report['metadata']['metrics']);
        }
    }

    public function getSiteTotalReports(): array
    {
        return ['visits summary' => ['VisitsSummary'], 'main metrics' => ['API']];
    }

    public function testProcessedReportLeavesOutTheUniquesOfOnlyThePeriodsWithoutAnEstimate(): void
    {
        $this->archiveFirstDayWithoutSketches();

        $report = Request::processRequest('API.getProcessedReport', [
            'idSite' => 1, 'period' => 'week', 'date' => '2024-02-26,2024-03-10',
            'apiModule' => 'VisitsSummary', 'apiAction' => 'get', 'format_metrics' => 0,
        ]);

        [$weekWithoutEstimate, $estimatedWeek] = array_values($report['reportData']->getDataTables());
        $this->assertArrayHasKey('nb_uniq_visitors', $report['columns']);
        $this->assertArrayNotHasKey('nb_uniq_visitors', $weekWithoutEstimate->getFirstRow()->getColumns());
        $this->assertGreaterThan(0, $estimatedWeek->getFirstRow()->getColumn('nb_uniq_visitors'));
    }

    public function testUniquesAreLeftOutOnlyFromPeriodsWithVisits(): void
    {
        $this->assertTrue(EstimatedUniques::isLeftOut(['nb_visits' => 5], 'nb_uniq_visitors'));
        $this->assertTrue(EstimatedUniques::isLeftOut(['nb_visits_returning' => 5], 'nb_users_returning'));
        $this->assertFalse(EstimatedUniques::isLeftOut(['nb_visits' => 0], 'nb_uniq_visitors'));
        $this->assertFalse(EstimatedUniques::isLeftOut(['nb_visits' => 5, 'nb_uniq_visitors' => 3], 'nb_uniq_visitors'));
        $this->assertFalse(EstimatedUniques::isLeftOut(['nb_visits' => 5], 'nb_actions'));
        $this->assertFalse(EstimatedUniques::isLeftOut(['nb_visits_percent_of_total' => 5], 'nb_users_percent_of_total'));

        $this->setEstimatedPeriods([]);
        $this->assertFalse(EstimatedUniques::isLeftOut(['nb_visits' => 5], 'nb_uniq_visitors'));
    }

    /**
     * @dataProvider getSplitUniquesColumns
     */
    public function testSplitEvolutionGraphsLeaveAGapForAYearWithoutAnEstimate(string $module, string $columns)
    {
        // a day of each visitor type's segment without sketches, as if archived before the setting was on
        Request::processRequest($module . '.get', ['idSite' => 1, 'period' => 'day', 'date' => '2024-03-01']);
        Db::query(
            'DELETE FROM ' . Common::prefixTable('archive_blob_2024_03') . ' WHERE name IN (?, ?) AND date1 = ? AND period = 1',
            [PluginsArchiver::UNIQUE_VISITORS_SKETCH_RECORD, PluginsArchiver::USERS_SKETCH_RECORD, '2024-03-01']
        );
        // browser archiving of a segment re-applies the fixture's invalidations, which would rebuild the sketches
        StaticContainer::get(ArchiveInvalidator::class)->forgetRememberedArchivedReportsToInvalidateForSite(1);

        $data = $this->getGraphData($module, 'getEvolutionGraph', 'year', '2024-03-01', $columns, ['evolution_year_last_n' => 3]);

        $this->assertSame([0, 0, null], $data['data'][1]);
        $this->assertTrue($data['params']['series'][1]['breakOnNull']);
    }

    public function getSplitUniquesColumns(): array
    {
        return [
            'new visitors' => ['VisitFrequency', 'nb_visits_new,nb_uniq_visitors_new'],
            'human users' => ['AIAgents', 'nb_visits_human,nb_users_human'],
        ];
    }

    private function getPlottedSeries(string $module, string $action, string $period, string $date, string $columns): array
    {
        $data = $this->getGraphData($module, $action, $period, $date, $columns);

        return array_column($data['params']['series'], 'label');
    }

    private function getGraphData(string $module, string $action, string $period, string $date, string $columns, array $extraParams = []): array
    {
        $html = $this->dispatch($module, $action, $extraParams + [
            'idSite' => 1,
            'period' => $period,
            'date' => $date,
            'columns' => $columns,
            'viewDataTable' => 'graphEvolution',
            'forceView' => 1,
        ]);

        $this->assertSame(1, preg_match('/data-data="([^"]*)"/', $html, $matches), 'graph data not found');

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);
    }

    private function getSparklines(string $period, string $date, array $extraParams = []): string
    {
        $html = $this->dispatch('VisitsSummary', 'getSparklines', ['idSite' => 1, 'period' => $period, 'date' => $date] + $extraParams);

        return html_entity_decode($html, ENT_QUOTES);
    }

    /**
     * @return array<string, bool> whether each sparkline metric shows an evolution
     */
    private function getSparklineEvolutions(string $period, string $date, array $extraParams = []): array
    {
        $evolutions = [];
        foreach ($this->getSparklineMetrics($period, $date, $extraParams) as [$sparkline, $metric]) {
            $evolutions[$metric['column']] = isset($sparkline['evolution']);
        }

        return $evolutions;
    }

    /**
     * @return array<string, string> the card title of each sparkline metric
     */
    private function getSparklineTitles(string $period, string $date): array
    {
        $titles = [];
        foreach ($this->getSparklineMetrics($period, $date) as [, $metric]) {
            $titles[$metric['column']] = $metric['title'];
        }

        return $titles;
    }

    /**
     * @return array<int, array{0: array, 1: array}> each sparkline with each of its metrics, in the order shown
     */
    private function getSparklineMetrics(string $period, string $date, array $extraParams = []): array
    {
        $html = $this->getSparklines($period, $date, $extraParams);
        $this->assertSame(1, preg_match('/ sparklines="([\[{].*?[\]}])"\s/', $html, $matches), 'sparklines not found in: ' . substr($html, 0, 1500));
        $result = [];
        foreach (json_decode($matches[1], true) as $sparklines) {
            foreach ($sparklines as $sparkline) {
                foreach ($sparkline['metrics'] as $metrics) {
                    $result[] = [$sparkline, $metrics[0]];
                }
            }
        }

        return $result;
    }

    /**
     * Dispatches a controller action with `$params` as the request, and returns its output.
     */
    private function dispatch(string $module, string $action, array $params, array $parameters = []): string
    {
        [$get, $request] = [$_GET, $_REQUEST];
        $_GET = $_REQUEST = $params;
        try {
            return FrontController::getInstance()->fetchDispatch($module, $action, $parameters);
        } finally {
            [$_GET, $_REQUEST] = [$get, $request];
        }
    }

    private function getMonthColumns(string $method, string $columns): array
    {
        $result = Request::processRequest($method, ['idSite' => 1, 'period' => 'month', 'date' => '2024-03-01', 'columns' => $columns]);

        return $result->getFirstRow() ? $result->getFirstRow()->getColumns() : [];
    }

    private function getYearColumns(string $method, string $columns = ''): array
    {
        $params = ['idSite' => 1, 'period' => 'year', 'date' => '2024-03-01'];
        if ($columns !== '') {
            $params['columns'] = $columns;
        }
        $result = Request::processRequest($method, $params);

        return $result->getFirstRow() ? $result->getFirstRow()->getColumns() : [];
    }

    /**
     * Archives the first day as if before any period was estimated, so it has no sketches.
     *
     * @param string[] $alsoCounted
     */
    private function archiveFirstDayWithoutSketches(array $alsoCounted = []): void
    {
        $this->setEstimatedPeriods([]);
        $this->getVisitsSummary('day', '2024-03-01');
        $this->setEstimatedPeriods(self::ESTIMATED_PERIODS, $alsoCounted);
    }

    /**
     * Estimates `$periods` and no other period. Their exact count is off unless they are in
     * `$alsoCounted`; the other periods keep their default.
     *
     * @param string[] $periods
     * @param string[] $alsoCounted
     */
    private function setEstimatedPeriods(array $periods, array $alsoCounted = []): void
    {
        foreach (['week', 'month', 'year', 'range'] as $period) {
            $this->setGeneralConfig("enable_processing_estimated_unique_visitors_$period", (int) in_array($period, $periods, true));
        }

        foreach (self::EXACT_PERIOD_DEFAULTS as $period => $default) {
            $counted = in_array($period, $periods, true) ? (int) in_array($period, $alsoCounted, true) : $default;
            $this->setGeneralConfig("enable_processing_unique_visitors_$period", $counted);
        }
    }

    private static function trackVisits(): void
    {
        $tracker = Fixture::getTracker(1, '2024-03-01 00:00:00');
        $tracker->enableBulkTracking();

        for ($day = self::FIRST_DAY; $day <= self::LAST_DAY; $day++) {
            for ($visit = 0; $visit < self::VISITS_PER_DAY; $visit++) {
                $tracker->setForceVisitDateTime(sprintf('2024-03-%02d %02d:00:00', $day, $visit));
                $tracker->setForceNewVisit();
                $tracker->setVisitorId(substr(md5('visitor' . (($day * 7 + $visit) % self::VISITOR_POOL)), 0, 16));
                // visitors and users overlap from day to day, so merged periods must not just add the days up
                $tracker->setUserId($day <= self::LAST_DAY_WITH_USERS && $visit % 4 === 0 ? 'user' . (($day * 3 + $visit) % self::USER_POOL) : null);
                $tracker->setUrl('https://example.com/page');
                Fixture::assertTrue($tracker->doTrackPageView('page'));
            }
        }

        Fixture::checkBulkTrackingResponse($tracker->doBulkTrack());
    }

    /**
     * Counts what the exact query counts, from the tracked visits.
     */
    private function countExact(string $column, int $firstDay, int $lastDay, string $where = '1 = 1'): int
    {
        return (int) Db::fetchOne(
            "SELECT COUNT(DISTINCT $column) FROM " . Common::prefixTable('log_visit')
            . " WHERE idsite = 1 AND visit_last_action_time BETWEEN ? AND ? AND $where",
            [sprintf('2024-03-%02d 00:00:00', $firstDay), sprintf('2024-03-%02d 23:59:59', $lastDay)]
        );
    }

    private function getVisitsSummary(string $period, string $date, string $segment = '', int $idSite = 1): array
    {
        $result = Request::processRequest('VisitsSummary.get', [
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'segment' => $segment,
        ]);

        // a list of days is only requested to archive them
        if (!$result instanceof DataTable) {
            return [];
        }

        return $result->getFirstRow()->getColumns();
    }

    private function getStoredSketch(
        string $period,
        string $date,
        string $recordName = PluginsArchiver::UNIQUE_VISITORS_SKETCH_RECORD,
        int $idSite = 1
    ): ?string {
        /** @var Archive $archive */
        $archive = Archive::build($idSite, $period, $date);
        foreach ($archive->querySingleBlob($recordName) as $row) {
            return $row['value'];
        }

        return null;
    }

    private function setGeneralConfig(string $name, int|string $value): void
    {
        $general = Config::getInstance()->General;
        $general[$name] = $value;
        Config::getInstance()->General = $general;
    }
}
