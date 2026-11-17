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
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Metrics;
use Piwik\Plugins\DevicesDetection\Archiver;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group UniqueVisitorsEstimate
 */
class RowUniqueVisitorsEstimateTest extends IntegrationTestCase
{
    private const SKETCHES_RECORD = Archiver::DEVICE_TYPE_RECORD_NAME . '_uniq_visitors_sketches';
    private const VISITS_PER_DAY = 24;
    private const VISITOR_POOL = 60;
    private const USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    ];

    public static function beforeTableDataCached(): void
    {
        Fixture::createSuperUser();
        Fixture::createWebsite('2024-01-01', 0, 'Example', 'https://example.com');
        self::trackVisits();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->setEstimatedPeriods(['week', 'month', 'year']);
    }

    /**
     * @dataProvider getEstimatedReports
     */
    public function testEstimatedWeekGivesEachRowItsUniqueVisitors(string $recordName, string $column)
    {
        $rows = $this->getRows($recordName, 'week', '2024-03-04');

        $this->assertGreaterThan(1, count($rows));
        foreach ($rows as $label => $columns) {
            $exact = $this->countExact($column, $label, 4, 10);
            $this->assertEqualsWithDelta($exact, $columns[Metrics::INDEX_NB_UNIQ_VISITORS], 1, "$recordName $label");
            $this->assertLessThanOrEqual($columns[Metrics::INDEX_NB_VISITS], $columns[Metrics::INDEX_NB_UNIQ_VISITORS]);
            $this->assertArrayHasKey(Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS, $columns);
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getEstimatedReports(): array
    {
        return [
            'device types' => [Archiver::DEVICE_TYPE_RECORD_NAME, Archiver::DEVICE_TYPE_FIELD],
            'browsers' => [Archiver::BROWSER_RECORD_NAME, Archiver::BROWSER_FIELD],
            'operating systems' => [Archiver::OS_RECORD_NAME, Archiver::OS_FIELD],
        ];
    }

    public function testSegmentedWeekEstimatesTheRowsFromTheSegmentsOwnSketches()
    {
        $rows = $this->getRows(Archiver::DEVICE_TYPE_RECORD_NAME, 'week', '2024-03-04', 'browserCode==MF');

        $this->assertCount(2, $rows); // Mobile Safari on iPhone and iPad
        foreach ($rows as $deviceType => $columns) {
            $exact = $this->countExact(Archiver::DEVICE_TYPE_FIELD, $deviceType, 4, 10, "config_browser_name = 'MF'");
            $this->assertEqualsWithDelta($exact, $columns[Metrics::INDEX_NB_UNIQ_VISITORS], 1, "device type $deviceType");
        }
        $this->assertNotNull($this->getStoredSketches('day', '2024-03-04', 'browserCode==MF'));
    }

    public function testEstimatedYearIsBuiltFromTheMonthsSketches()
    {
        $rows = $this->getDeviceTypeRows('year', '2024-03-01');

        foreach ($rows as $deviceType => $columns) {
            $this->assertEqualsWithDelta($this->countExact(Archiver::DEVICE_TYPE_FIELD, $deviceType, 1, 10), $columns[Metrics::INDEX_NB_UNIQ_VISITORS], 1);
        }
        $this->assertNotNull($this->getStoredSketches('month', '2024-03-01'));
    }

    public function testNoEstimatedPeriodStoresNoSketchesAndKeepsTheDailySums()
    {
        $this->setEstimatedPeriods([]);

        $rows = $this->getDeviceTypeRows('week', '2024-03-04');

        $this->assertNull($this->getStoredSketches('day', '2024-03-04'));
        foreach ($rows as $columns) {
            $this->assertArrayNotHasKey(Metrics::INDEX_NB_UNIQ_VISITORS, $columns);
            $this->assertArrayHasKey(Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS, $columns);
        }
    }

    public function testDayArchivedWithoutSketchesLeavesTheRowsWithoutUniqueVisitors()
    {
        $this->setEstimatedPeriods([]);
        $this->getDeviceTypeRows('day', '2024-03-04');
        $this->setEstimatedPeriods(['week', 'month', 'year']);

        $rows = $this->getDeviceTypeRows('week', '2024-03-04');

        $this->assertNotEmpty($rows);
        foreach ($rows as $columns) {
            $this->assertArrayNotHasKey(Metrics::INDEX_NB_UNIQ_VISITORS, $columns);
        }
    }

    public function testApiReturnsTheRowEstimates()
    {
        $table = Request::processRequest('DevicesDetection.getType', [
            'idSite' => 1,
            'period' => 'week',
            'date' => '2024-03-04',
            'filter_limit' => -1,
        ]);

        $estimates = array_filter($table->getColumn('nb_uniq_visitors'));
        $this->assertCount(count(self::USER_AGENTS), $estimates);
    }

    /**
     * @return array<string, array<int|string, mixed>> the record's columns by device type ID
     */
    private function getDeviceTypeRows(string $period, string $date): array
    {
        return $this->getRows(Archiver::DEVICE_TYPE_RECORD_NAME, $period, $date);
    }

    /**
     * @return array<string, array<int|string, mixed>> the record's columns by label
     */
    private function getRows(string $recordName, string $period, string $date, string $segment = ''): array
    {
        $table = Archive::build(1, $period, $date, $segment)->getDataTable($recordName);
        $this->assertInstanceOf(DataTable::class, $table);

        $rows = [];
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $rows[(string) $row->getColumn('label')] = $row->getColumns();
        }
        return $rows;
    }

    private function getStoredSketches(string $period, string $date, string $segment = ''): ?string
    {
        /** @var Archive $archive */
        $archive = Archive::build(1, $period, $date, $segment);
        foreach ($archive->querySingleBlob(self::SKETCHES_RECORD) as $row) {
            return $row['value'];
        }
        return null;
    }

    private function countExact(string $column, string $value, int $firstDay, int $lastDay, string $andWhere = '1 = 1'): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(DISTINCT idvisitor) FROM ' . Common::prefixTable('log_visit')
            . " WHERE idsite = 1 AND $column = ? AND $andWhere AND visit_last_action_time BETWEEN ? AND ?",
            [$value, sprintf('2024-03-%02d 00:00:00', $firstDay), sprintf('2024-03-%02d 23:59:59', $lastDay)]
        );
    }

    /**
     * @param string[] $periods
     */
    private function setEstimatedPeriods(array $periods): void
    {
        $general = Config::getInstance()->General;
        foreach (['week', 'month', 'year', 'range'] as $period) {
            $general["enable_processing_estimated_unique_visitors_$period"] = (int) in_array($period, $periods, true);
        }
        Config::getInstance()->General = $general;
    }

    private static function trackVisits(): void
    {
        $tracker = Fixture::getTracker(1, '2024-03-01 00:00:00');
        $tracker->enableBulkTracking();

        for ($day = 1; $day <= 10; $day++) {
            for ($visit = 0; $visit < self::VISITS_PER_DAY; $visit++) {
                $tracker->setForceVisitDateTime(sprintf('2024-03-%02d %02d:00:00', $day, $visit));
                $tracker->setForceNewVisit();
                // visitors come back on other days and other devices, so a row's total is not the sum of its days
                $tracker->setVisitorId(substr(md5('visitor' . (($day * 7 + $visit) % self::VISITOR_POOL)), 0, 16));
                $tracker->setUserAgent(self::USER_AGENTS[($day + $visit) % count(self::USER_AGENTS)]);
                $tracker->setUrl('https://example.com/page');
                Fixture::assertTrue($tracker->doTrackPageView('page'));
            }
        }

        Fixture::checkBulkTrackingResponse($tracker->doBulkTrack());
    }
}
