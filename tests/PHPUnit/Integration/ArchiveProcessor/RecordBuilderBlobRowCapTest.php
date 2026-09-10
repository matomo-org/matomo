<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Record;
use Piwik\Config;
use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The flat record is the one an operator can give a very large row limit
 * (datatable_archiving_maximum_rows_actions_flat), so it is the record the MEDIUMBLOB cap exists
 * for. It is serialized on its own path, separate from RecordBuilder::insertBlobRecord(), and this
 * covers that the cap is applied there too.
 *
 * @group ArchiveBlobRowCap
 * @group Core
 */
class RecordBuilderBlobRowCapTest extends IntegrationTestCase
{
    private const FLAT_RECORD = 'TestPlugin_myReportFlat';
    private const HIERARCHICAL_RECORD = 'TestPlugin_myReport';
    private const CONFIGURED_MAX_ROWS = 500000;

    /**
     * @var mixed
     */
    private $originalDatabaseConfig;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalDatabaseConfig = Config::getInstance()->database;
        ArchiveBlobColumnType::clearCache();
    }

    public function tearDown(): void
    {
        Config::getInstance()->database = $this->originalDatabaseConfig;
        ArchiveBlobColumnType::clearCache();

        parent::tearDown();
    }

    public function testFlatRecordRowLimitIsCappedOnAMediumBlobTable(): void
    {
        $this->setFlag(true);
        $this->cacheTargetTableAsMediumBlob(true);

        self::assertSame(100000, $this->getRowLimitUsedForFlatRecord());
    }

    public function testFlatRecordRowLimitIsNotCappedOnALongBlobTable(): void
    {
        $this->setFlag(true);
        $this->cacheTargetTableAsMediumBlob(false);

        self::assertSame(self::CONFIGURED_MAX_ROWS, $this->getRowLimitUsedForFlatRecord());
    }

    public function testFlatRecordRowLimitIsNotCappedWhenFlagIsUnset(): void
    {
        $this->setFlag(false);
        $this->cacheTargetTableAsMediumBlob(true);

        self::assertSame(self::CONFIGURED_MAX_ROWS, $this->getRowLimitUsedForFlatRecord());
    }

    /**
     * Runs the non-day flat aggregation and returns the row limit the flat table was serialized
     * with.
     */
    private function getRowLimitUsedForFlatRecord(): ?int
    {
        // Records the row limit getSerialized() was called with.
        $flatTable = new class () extends DataTable {
            /**
             * @var int|null
             */
            public $maxRowsUsed;

            public function getSerialized(
                $maximumRowsInDataTable = self::MAXIMUM_DEPTH_LEVEL_ALLOWED,
                $maximumRowsInSubDataTable = null,
                $columnToSortByBeforeTruncation = null,
                &$aSerializedDataTable = []
            ) {
                $this->maxRowsUsed = $maximumRowsInDataTable;

                return [0 => serialize([])];
            }
        };

        $recordBuilder = new class ($flatTable) extends ArchiveProcessor\RecordBuilder {
            /** @var DataTable */
            private $flatTable;

            public function __construct(DataTable $flatTable)
            {
                parent::__construct();
                $this->flatTable = $flatTable;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function aggregateRootDataTableFromBlobs(
                ArchiveProcessor $archiveProcessor,
                string $recordName,
                ?array $columnAggregationOps,
                ?array $columnToRenameAfterAggregation
            ): array {
                return [$this->flatTable, true, []];
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function buildHierarchicalTableFromFlatTableAndConsumeRows(
                DataTable $flatTable,
                ?array $columnAggregationOps,
                callable $flatToHierarchyPathCallback,
                ?array $defaultRowColumns = null
            ): DataTable {
                return new DataTable();
            }

            public function runFlatAggregation(
                ArchiveProcessor $archiveProcessor,
                Record $hierarchicalRecord,
                array $recordsByName
            ): void {
                $processed = [];
                $this->aggregateBuiltFromFlatRecordForNonDay(
                    $archiveProcessor,
                    $hierarchicalRecord,
                    $recordsByName,
                    null,
                    null,
                    null,
                    $processed
                );
            }
        };

        $records = $this->makeRecords();
        $recordBuilder->runFlatAggregation(
            $this->makeArchiveProcessor(),
            $records[self::HIERARCHICAL_RECORD],
            $records
        );

        return $flatTable->maxRowsUsed;
    }

    /**
     * @return array<string, Record>
     */
    private function makeRecords(): array
    {
        $flat = Record::make(Record::TYPE_BLOB, self::FLAT_RECORD)
            ->setMaxRowsInTable(self::CONFIGURED_MAX_ROWS);

        $hierarchical = Record::make(Record::TYPE_BLOB, self::HIERARCHICAL_RECORD)
            ->setBuiltFromFlatRecord(self::FLAT_RECORD, function () {
                return [];
            })
            ->setMaxRowsInTable(0)
            ->setMaxRowsInSubtable(0);

        return [self::FLAT_RECORD => $flat, self::HIERARCHICAL_RECORD => $hierarchical];
    }

    private function makeArchiveProcessor(): ArchiveProcessor
    {
        Site::setSiteFromArray(1, [
            'idsite' => 1,
            'ecommerce' => 0,
            'sitesearch' => 0,
            'exclude_unknown_urls' => 0,
            'keep_url_fragment' => 0,
        ]);
        $params = new Parameters(new Site(1), PeriodFactory::build('month', self::getTestDate()), new Segment('', [1]));

        $archiveWriter = new class () extends ArchiveWriter {
            public function __construct()
            {
                // disable original constructor
            }

            public function insertBlobRecord($name, $values)
            {
                // no-op: this test only cares about the row limit used to serialize
            }
        };

        $logAggregator = new class () extends LogAggregator {
            public function __construct()
            {
                // disable original constructor
            }
        };

        return new ArchiveProcessor($params, $archiveWriter, $logAggregator);
    }

    private function setFlag(bool $isSet): void
    {
        $config = Config::getInstance();
        $database = $config->database;
        if ($isSet) {
            $database[ArchiveBlobColumnType::CONFIG_KEY] = '1';
        } else {
            unset($database[ArchiveBlobColumnType::CONFIG_KEY]);
        }
        $config->database = $database;
    }

    /**
     * Seeds the column-type cache for the archive_blob table this period writes to, so the test
     * does not have to alter a shared fixture table's schema.
     */
    private function cacheTargetTableAsMediumBlob(bool $isMediumBlob): void
    {
        $table = ArchiveTableCreator::getBlobTable(Date::factory(self::getTestDate()), true);

        $cache = new \ReflectionProperty(ArchiveBlobColumnType::class, 'cache');
        $cache->setAccessible(true);
        $cache->setValue(null, [$table => $isMediumBlob]);
    }

    private static function getTestDate(): string
    {
        return '2020-03-04';
    }
}
