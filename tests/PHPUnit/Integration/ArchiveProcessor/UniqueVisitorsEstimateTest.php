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
use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReportsPurger;
use Piwik\SettingsPiwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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

    public static function beforeTableDataCached(): void
    {
        Fixture::createSuperUser();
        Fixture::createWebsite('2024-01-01', 0, 'Example', 'https://example.com');
        self::trackVisits();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->setGeneralConfig('enable_processing_unique_visitors_estimated', 1);
    }

    public function testNoEstimateWhenSettingIsOff()
    {
        $this->setGeneralConfig('enable_processing_unique_visitors_estimated', 0);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertArrayNotHasKey('nb_uniq_visitors_estimated', $row);
        $this->assertNull($this->getStoredSketch('month', '2024-03-01'));
    }

    /**
     * @dataProvider getPeriods
     */
    public function testEstimateIsStoredForEveryPeriod(string $period, string $date, int $firstDay, int $lastDay)
    {
        $row = $this->getVisitsSummary($period, $date);

        $this->assertEqualsWithDelta($this->countUniqueVisitors($firstDay, $lastDay), $row['nb_uniq_visitors_estimated'], 2);
        $this->assertLessThanOrEqual($row['nb_visits'], $row['nb_uniq_visitors_estimated']);
    }

    public function getPeriods(): array
    {
        return [
            'day' => ['day', '2024-03-04', 4, 4],
            'week' => ['week', '2024-03-04', 4, 10],
            'month' => ['month', '2024-03-01', 1, 10],
            'year' => ['year', '2024-03-01', 1, 10],
            'range' => ['range', '2024-03-02,2024-03-08', 2, 8],
        ];
    }

    public function testRangeStoresNoSketch()
    {
        $row = $this->getVisitsSummary('range', '2024-03-02,2024-03-08');

        $this->assertGreaterThan(0, $row['nb_uniq_visitors_estimated']);
        $this->assertNull($this->getStoredSketch('range', '2024-03-02,2024-03-08'));
        $this->assertNotNull($this->getStoredSketch('week', '2024-03-04'));
    }

    /**
     * @dataProvider getPrecisionSettings
     */
    public function testInvalidPrecisionFallsBackToTheDefault($setting, int $expected)
    {
        $this->setGeneralConfig('unique_visitors_estimated_precision', $setting);

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

    public function testLongerPeriodsAreEstimatedWithoutTheVisitLogs()
    {
        // archive the days, then remove the visits they were built from
        $this->getVisitsSummary('day', '2024-03-01,2024-03-31');
        Db::query('DELETE FROM ' . Common::prefixTable('log_visit'));

        $month = $this->getVisitsSummary('month', '2024-03-01');
        $range = $this->getVisitsSummary('range', '2024-03-02,2024-03-08');

        $this->assertEqualsWithDelta($this->countUniqueVisitors(1, 10), $month['nb_uniq_visitors_estimated'], 2);
        $this->assertEqualsWithDelta($this->countUniqueVisitors(2, 8), $range['nb_uniq_visitors_estimated'], 2);
    }

    public function testNoEstimateWhenADayWasArchivedBeforeTheSettingWasOn()
    {
        $this->setGeneralConfig('enable_processing_unique_visitors_estimated', 0);
        $this->getVisitsSummary('day', '2024-03-01');
        $this->setGeneralConfig('enable_processing_unique_visitors_estimated', 1);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertArrayNotHasKey('nb_uniq_visitors_estimated', $row);
        $this->assertSame(self::VISITS_PER_DAY * (self::LAST_DAY - self::FIRST_DAY + 1), (int) $row['nb_visits']);
    }

    /**
     * @dataProvider getGraphs
     */
    public function testGraphShowsAGapForDaysWithoutAnEstimate(string $plugin, string $suffix)
    {
        Request::processRequest("$plugin.get", ['idSite' => 1, 'period' => 'day', 'date' => '2024-03-02,2024-03-03']);
        $this->setGeneralConfig('enable_processing_unique_visitors_estimated', 0);
        Request::processRequest("$plugin.get", ['idSite' => 1, 'period' => 'day', 'date' => '2024-03-01']);
        $this->setGeneralConfig('enable_processing_unique_visitors_estimated', 1);
        // the report's metrics were cached while the setting was off
        Cache::getTransientCache()->flushAll();

        // read the stored archives only: browser archiving would add the missing estimate to a segment's archive
        Rules::$archivingDisabledByTests = true;
        $this->setGeneralConfig('browser_archiving_disabled_enforce', 1);

        $columns = ["nb_visits$suffix", "nb_uniq_visitors_estimated$suffix"];
        [$get, $request] = [$_GET, $_REQUEST];
        $_GET = $_REQUEST = [
            'idSite' => 1,
            'period' => 'day',
            'date' => '2024-03-01,2024-03-03',
            'columns' => implode(',', $columns),
            'viewDataTable' => 'graphEvolution',
            'forceView' => 1,
        ];
        try {
            $html = FrontController::getInstance()->fetchDispatch($plugin, 'getEvolutionGraph', [false, $columns]);
        } finally {
            [$_GET, $_REQUEST] = [$get, $request];
            Rules::$archivingDisabledByTests = false;
            $this->setGeneralConfig('browser_archiving_disabled_enforce', 0);
        }

        $this->assertSame(1, preg_match('/data-data="([^"]*)"/', $html, $matches));
        [$visits, $estimates] = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['data'];

        // the day archived before the setting was on has visits but no estimate
        $this->assertGreaterThan(0, $visits[0]);
        $this->assertNull($estimates[0]);
        $this->assertGreaterThan(0, $estimates[1]);
        $this->assertGreaterThan(0, $estimates[2]);
    }

    public function getGraphs(): array
    {
        return [
            'all visitors' => ['VisitsSummary', ''],
            'new visitors' => ['VisitFrequency', '_new'],
            'human visitors' => ['AIAgents', '_human'],
        ];
    }

    public function testEstimateIsStoredForSegments()
    {
        $segmented = $this->getVisitsSummary('month', '2024-03-01', 'visitCount>=1');

        $this->assertEqualsWithDelta($this->countUniqueVisitors(1, 10), $segmented['nb_uniq_visitors_estimated'], 2);
    }

    /**
     * @dataProvider getSplitColumns
     */
    public function testEstimateIsSplitByVisitorType(string $method, string $suffix)
    {
        $result = Request::processRequest($method, ['idSite' => 1, 'period' => 'month', 'date' => '2024-03-01']);
        $row = $result->getFirstRow()->getColumns();

        $this->assertGreaterThan(0, $row["nb_uniq_visitors$suffix"]);
        $this->assertEqualsWithDelta($row["nb_uniq_visitors$suffix"], $row["nb_uniq_visitors_estimated$suffix"], 2);
    }

    public function getSplitColumns(): array
    {
        return [
            'new' => ['VisitFrequency.get', '_new'],
            'returning' => ['VisitFrequency.get', '_returning'],
            'human' => ['AIAgents.get', '_human'],
        ];
    }

    public function testSegmentJoiningActionsGivesTheSameEstimate()
    {
        // every visit viewed /page, so a segment on the action table matches every visit
        $segmented = $this->getVisitsSummary('month', '2024-03-01', 'pageUrl=@%2Fpage');
        $all = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertSame($all['nb_uniq_visitors_estimated'], $segmented['nb_uniq_visitors_estimated']);
    }

    public function testPeriodMixingPrecisionsIsEstimatedAtTheLowerOne()
    {
        $this->getVisitsSummary('day', '2024-03-01,2024-03-05');
        $this->setGeneralConfig('unique_visitors_estimated_precision', 12);

        $row = $this->getVisitsSummary('month', '2024-03-01');

        $this->assertEqualsWithDelta($this->countUniqueVisitors(1, 10), $row['nb_uniq_visitors_estimated'], 2);
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
        $this->assertSame($before['nb_uniq_visitors_estimated'], $this->getVisitsSummary('month', '2024-03-01')['nb_uniq_visitors_estimated']);
    }

    public function testNoEstimateForArchivesCoveringSeveralSites()
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

        foreach (['day', 'month'] as $period) {
            $row = $this->getVisitsSummary($period, '2024-03-01', '', $idRollUp);

            $this->assertGreaterThan(0, $row['nb_visits']);
            $this->assertArrayNotHasKey('nb_uniq_visitors_estimated', $row);
            $this->assertNull($this->getStoredSketch($period, '2024-03-01', $idRollUp));
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
                $tracker->setVisitorId(self::getVisitorId($day, $visit));
                $tracker->setUrl('https://example.com/page');
                Fixture::assertTrue($tracker->doTrackPageView('page'));
            }
        }

        Fixture::checkBulkTrackingResponse($tracker->doBulkTrack());
    }

    /**
     * Visitors overlap from day to day, so merged periods must not just add the days up.
     */
    private static function getVisitorId(int $day, int $visit): string
    {
        return substr(md5('visitor' . (($day * 7 + $visit) % self::VISITOR_POOL)), 0, 16);
    }

    private function countUniqueVisitors(int $firstDay, int $lastDay): int
    {
        $visitors = [];
        for ($day = $firstDay; $day <= $lastDay; $day++) {
            for ($visit = 0; $visit < self::VISITS_PER_DAY; $visit++) {
                $visitors[] = self::getVisitorId($day, $visit);
            }
        }

        return count(array_unique($visitors));
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

    private function getStoredSketch(string $period, string $date, int $idSite = 1): ?string
    {
        /** @var Archive $archive */
        $archive = Archive::build($idSite, $period, $date);
        foreach ($archive->querySingleBlob(PluginsArchiver::UNIQUE_VISITORS_SKETCH_RECORD) as $row) {
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
