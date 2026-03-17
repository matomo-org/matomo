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
