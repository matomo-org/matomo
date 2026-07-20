<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\API\Request;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Map;
use Piwik\Plugins\API\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group Plugins
 */
class ProcessedReportMetadataTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser(true);
        $this->idSite = Fixture::createWebsite('2015-01-01 00:00:00');

        $trackerDayOne = Fixture::getTracker($this->idSite, '2015-01-02 10:00:00');
        Fixture::checkResponse($trackerDayOne->doTrackPageView('/page-one'));
        Fixture::checkResponse($trackerDayOne->doTrackPageView('/page-two'));

        $trackerDayTwo = Fixture::getTracker($this->idSite, '2015-01-03 10:00:00');
        Fixture::checkResponse($trackerDayTwo->doTrackPageView('/page-one'));
        Fixture::checkResponse($trackerDayTwo->doTrackPageView('/page-three'));
    }

    public function testSinglePeriodProcessedReportCopiesAllTableMetadata(): void
    {
        $processed = $this->callProcessedReport('day', '2015-01-02');
        $reportData = $processed['reportData'] ?? null;
        $sourceData = $this->callRawReport('day', '2015-01-02');

        self::assertInstanceOf(DataTable::class, $reportData);
        self::assertInstanceOf(DataTable::class, $sourceData);
        $this->assertMetadataCopied($sourceData->getAllTableMetadata(), $reportData->getAllTableMetadata());

        // special example to prevent empty metadata being accepted
        $metadataValue = $reportData->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME);
        self::assertIsInt($metadataValue);
        self::assertGreaterThanOrEqual(0, $metadataValue);
    }

    public function testMultiPeriodProcessedReportCopiesAllInnerTableMetadata(): void
    {
        $processed = $this->callProcessedReport('day', '2015-01-02,2015-01-03');
        $reportData = $processed['reportData'] ?? null;
        $sourceData = $this->callRawReport('day', '2015-01-02,2015-01-03');

        self::assertInstanceOf(Map::class, $reportData);
        self::assertInstanceOf(Map::class, $sourceData);

        $processedTables = array_values($reportData->getDataTables());
        $sourceTables = array_values($sourceData->getDataTables());

        self::assertCount(count($sourceTables), $processedTables);

        foreach ($processedTables as $index => $table) {
            $sourceTable = $sourceTables[$index];
            self::assertInstanceOf(DataTable::class, $sourceTable);
            $this->assertMetadataCopied($sourceTable->getAllTableMetadata(), $table->getAllTableMetadata());

            // special example to prevent empty metadata being accepted
            $metadataValue = $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME);
            self::assertIsInt($metadataValue);
            self::assertGreaterThanOrEqual(0, $metadataValue);
        }
    }

    public function testGetMetadataKeepsUniqueVisitorsOnPeriodsWhereItIsProcessed(): void
    {
        // day + week/month enable unique visitors by default, so the labels must be advertised
        foreach (['day', 'week', 'month'] as $period) {
            $metrics = $this->getApiGetMetrics($period, '2015-01-02');
            self::assertArrayHasKey('nb_uniq_visitors', $metrics, "period=$period");
            self::assertArrayHasKey('nb_users', $metrics, "period=$period");
        }
    }

    public function testGetMetadataStripsUniqueVisitorsOnPeriodsWhereItIsNotProcessed(): void
    {
        // year/range disable unique visitors by default, so the metrics must not be advertised
        $year = $this->getApiGetMetrics('year', '2015-01-02');
        self::assertArrayNotHasKey('nb_uniq_visitors', $year);
        self::assertArrayNotHasKey('nb_users', $year);

        $range = $this->getApiGetMetrics('range', '2015-01-01,2015-01-03');
        self::assertArrayNotHasKey('nb_uniq_visitors', $range);
        self::assertArrayNotHasKey('nb_users', $range);
    }

    public function testGetMetadataAppliesSameUniqueVisitorsRuleToVisitsSummaryGetAsApiGet(): void
    {
        // VisitsSummary.get is a pure aggregate (no dimension), so it follows the same rule as
        // API.get: the metrics are advertised where they are processed (day + week/month by default)
        // and dropped where they are not (year off by default).
        foreach (['day', 'week', 'month'] as $period) {
            $metrics = $this->getMetricsFor('VisitsSummary', 'get', $period, '2015-01-02');
            self::assertArrayHasKey('nb_uniq_visitors', $metrics, "period=$period");
            self::assertArrayHasKey('nb_users', $metrics, "period=$period");
        }

        $year = $this->getMetricsFor('VisitsSummary', 'get', 'year', '2015-01-02');
        self::assertArrayNotHasKey('nb_uniq_visitors', $year);
        self::assertArrayNotHasKey('nb_users', $year);
    }

    public function testGetMetadataStripsUniqueVisitorsFromPerDimensionReportsOnNonDayPeriods(): void
    {
        // Per-dimension reports (here: UserCountry.getCountry) can't have unique visitors / users
        // summed across days, so those columns are deleted at aggregation time. They exist per row
        // on the day period, but must not be advertised on non-day periods (otherwise they would
        // show up as meaningless 0 values in exports, scheduled reports, Row Evolution, ...).
        $day = $this->getMetricsFor('UserCountry', 'getCountry', 'day', '2015-01-02');
        self::assertArrayHasKey('nb_uniq_visitors', $day);
        self::assertArrayHasKey('nb_users', $day);

        foreach (['week', 'month'] as $period) {
            $metrics = $this->getMetricsFor('UserCountry', 'getCountry', $period, '2015-01-02');
            self::assertArrayNotHasKey('nb_uniq_visitors', $metrics, "period=$period");
            self::assertArrayNotHasKey('nb_users', $metrics, "period=$period");
        }
    }

    public function testGetMetadataStripsUniqueVisitorsForMultiSiteNonDayRequests(): void
    {
        // unique visitors are not computed across sites on non-day periods unless
        // enable_processing_unique_visitors_multiple_sites is enabled (off by default)
        $idSite2 = Fixture::createWebsite('2015-01-01 00:00:00');
        Config::getInstance()->General['enable_processing_unique_visitors_multiple_sites'] = 0;

        $metrics = $this->getApiGetMetrics('week', '2015-01-02', $this->idSite . ',' . $idSite2);
        self::assertArrayNotHasKey('nb_uniq_visitors', $metrics);
        self::assertArrayNotHasKey('nb_users', $metrics);
    }

    /**
     * @param int|string|null $idSite
     * @return array<string, string>
     */
    private function getApiGetMetrics(string $period, string $date, $idSite = null): array
    {
        return $this->getMetricsFor('API', 'get', $period, $date, $idSite);
    }

    /**
     * @param int|string|null $idSite
     * @return array<string, string>
     */
    private function getMetricsFor(
        string $apiModule,
        string $apiAction,
        string $period,
        string $date,
        $idSite = null
    ): array {
        $metadata = API::getInstance()->getMetadata(
            $idSite ?? $this->idSite,
            $apiModule,
            $apiAction,
            [],
            false,
            $period,
            $date
        );

        self::assertIsArray($metadata);
        self::assertArrayHasKey(0, $metadata);
        self::assertIsArray($metadata[0]['metrics']);
        return $metadata[0]['metrics'];
    }

    /**
     * @return array<string, mixed>
     */
    private function callProcessedReport(string $period, string $date): array
    {
        $_GET['filter_limit'] = 1;
        $_GET['filter_offset'] = 0;

        $result = API::getInstance()->getProcessedReport(
            $this->idSite,
            $period,
            $date,
            'Actions',
            'getPageUrls',
            false,
            [],
            false,
            false,
            false,
            true
        );

        unset($_GET['filter_limit']);
        unset($_GET['filter_offset']);

        self::assertIsArray($result);
        return $result;
    }

    /**
     * @return DataTable|Map
     */
    private function callRawReport(string $period, string $date)
    {
        $result = Request::processRequest('Actions.getPageUrls', [
            'idSite' => $this->idSite,
            'period' => $period,
            'date' => $date,
            'filter_limit' => 1,
            'filter_offset' => 0,
        ]);

        self::assertTrue($result instanceof DataTable || $result instanceof Map);
        return $result;
    }

    /**
     * @param array<string, mixed> $sourceMetadata
     * @param array<string, mixed> $processedMetadata
     */
    private function assertMetadataCopied(array $sourceMetadata, array $processedMetadata): void
    {
        foreach ($sourceMetadata as $key => $sourceValue) {
            self::assertArrayHasKey($key, $processedMetadata);
            $this->assertMetadataValueCopied($sourceValue, $processedMetadata[$key]);
        }
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $processedValue
     */
    private function assertMetadataValueCopied($sourceValue, $processedValue): void
    {
        if (is_array($sourceValue)) {
            self::assertIsArray($processedValue);
            foreach ($sourceValue as $key => $value) {
                self::assertArrayHasKey($key, $processedValue);
                $this->assertMetadataValueCopied($value, $processedValue[$key]);
            }
            return;
        }

        if (is_object($sourceValue)) {
            self::assertIsObject($processedValue);
            self::assertSame(get_class($sourceValue), get_class($processedValue));
            return;
        }

        self::assertSame($sourceValue, $processedValue);
    }
}
