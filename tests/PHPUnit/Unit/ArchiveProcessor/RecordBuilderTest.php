<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Unit\ArchiveProcessor;

use PHPUnit\Framework\TestCase;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Record;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Row;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\Goals\RecordBuilders\GeneralGoalsRecords;
use Piwik\Segment;
use Piwik\Site;

class RecordBuilderTest extends TestCase
{
    /**
     * @var array
     */
    public $numericRecordsInserted = [];

    /**
     * @var array
     */
    public $blobRecordsInserted = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->numericRecordsInserted = [];
        $this->blobRecordsInserted = [];
    }

    protected function tearDown(): void
    {
        Manager::getInstance()->deleteAll();
        Site::clearCache();

        parent::tearDown();
    }

    public function testBuildFromLogsDoesNothingIfRecordBuilderNotEnabled()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function isEnabled(ArchiveProcessor $archiveProcessor): bool
            {
                return false;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor();
        $recordBuilder->buildFromLogs($mockArchiveProcessor);

        $this->assertEmpty($this->numericRecordsInserted);
        $this->assertEmpty($this->blobRecordsInserted);
    }

    public function testBuildFromLogsInsertsDataReturnedByAggregate()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor();
        $recordBuilder->buildFromLogs($mockArchiveProcessor);

        $expectedNumericRecords = ['TestPlugin_myMetric' => 50];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => 'the thing', 'nb_visits' => 40], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => 'another thing', 'nb_visits' => 50], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => 'a third thing', 'nb_visits' => 20], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildFromLogsIgnoresDataIfAssociatedRecordMetadataDoesNotExist()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor();
        $recordBuilder->buildFromLogs($mockArchiveProcessor);

        $this->assertEmpty($this->numericRecordsInserted);
        $this->assertEmpty($this->blobRecordsInserted);
    }

    public function testBuildFromLogsIgnoresDataIfRecordMetadataValueExistsButIsInvalid()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                // @phpstan-ignore-next-line intentionally returns invalid values to verify runtime filtering.
                return [
                    0,
                    'def',
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor();
        $recordBuilder->buildFromLogs($mockArchiveProcessor);

        $this->assertEmpty($this->numericRecordsInserted);
        $this->assertEmpty($this->blobRecordsInserted);
    }

    public function testBuildFromLogsUsesRecordSpecificLimitAndSortWhenSpecifiedInRecordMetadata()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport')
                        ->setMaxRowsInTable(2)
                        ->setColumnToSortByBeforeTruncation('nb_visits'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 30,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor();
        $recordBuilder->buildFromLogs($mockArchiveProcessor);

        $expectedNumericRecords = ['TestPlugin_myMetric' => 30];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    -1 => [Row::COLUMNS => ['label' => -1, 'nb_visits' => 60], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    0 => [Row::COLUMNS => ['label' => 'another thing', 'nb_visits' => 50], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildFromLogsUsesZeroRecordSpecificLimitInsteadOfDefaultLimit(): void
    {
        $recordBuilder = new class (2, null, 'nb_visits') extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport')
                        ->setMaxRowsInTable(0),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 30,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor();
        $recordBuilder->buildFromLogs($mockArchiveProcessor);

        $expectedNumericRecords = ['TestPlugin_myMetric' => 30];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => 'the thing', 'nb_visits' => 40], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => 'another thing', 'nb_visits' => 50], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => 'a third thing', 'nb_visits' => 20], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodDoesNothingIfRecordBuilderNotEnabled()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function isEnabled(ArchiveProcessor $archiveProcessor): bool
            {
                return false;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week');
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $this->assertEmpty($this->numericRecordsInserted);
        $this->assertEmpty($this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodAggregatesAllChildReportsIfNoRequestedReportsAreSpecified()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week');
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = [
            'TestPlugin_myMetric' => 9000,
            'TestPlugin_myOtherMetric' => 10500,
        ];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodAggregatesOnlyRequestedReportsIfRequestedReportsSpecifiedAndNoneAlreadyExist()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_myMetric', 'TestPlugin_myReport']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = ['TestPlugin_myMetric' => 9000];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodAggregatesOnlyRequestedReportsThatDoNotExistIfSomeRequestedReportsAlreadyExist()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor(
            'week',
            ['TestPlugin_myOtherMetric', 'TestPlugin_myMetric', 'TestPlugin_myReport', 'TestPlugin_myReport2'],
            ['TestPlugin_myMetric', 'TestPlugin_myReport']
        );
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = ['TestPlugin_myOtherMetric' => 9000];
        $expectedBlobRecords = [
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodUsesCustomBlobSerializationPropertiesIfSpecifiedInRecordMetadata()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport')
                        ->setColumnToSortByBeforeTruncation('nb_visits')
                        ->setMaxRowsInTable(2)
                        ->setBlobColumnAggregationOps(['nb_visits' => 'max']),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week');
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = [
            'TestPlugin_myMetric' => 9000,
            'TestPlugin_myOtherMetric' => 10500,
        ];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    0 => [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    -1 => [Row::COLUMNS => ['label' => '-1', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodBuildsHierarchyFromFlatBlobWhenFlatBlobIsRequested()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_hierarchy')
                        ->setBuiltFromFlatRecord('TestPlugin_flat', function (Row $flatRow): ?array {
                            $label = $flatRow->getColumn('label');
                            if (!is_string($label) || $label === '') {
                                return null;
                            }

                            return [$label];
                        }),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_flat'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function aggregateDataTableFromBlobs(
                ArchiveProcessor $archiveProcessor,
                string $recordName,
                ?array $columnsAggregationOperation,
                ?array $columnsToRenameAfterAggregation,
                ?array $periodsToInclude = null
            ): array {
                $table = new DataTable();
                if ($recordName === 'TestPlugin_flat') {
                    $table->addRowFromSimpleArray(['label' => '/flat-path', 'nb_visits' => 5]);
                    return [$table, true];
                }

                return [$table, false];
            }

            protected function aggregateRootDataTableFromBlobs(
                ArchiveProcessor $archiveProcessor,
                string $recordName,
                ?array $columnsAggregationOperation,
                ?array $columnsToRenameAfterAggregation
            ): array {
                $table = new DataTable();
                if ($recordName === 'TestPlugin_flat') {
                    $table->addRowFromSimpleArray(['label' => '/flat-path', 'nb_visits' => 5]);
                    return [$table, true, ['2020-03-04,2020-03-04' => true]];
                }

                return [$table, false, []];
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return ['2020-03-04,2020-03-04' => true];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_flat']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $this->assertArrayHasKey('TestPlugin_flat', $this->blobRecordsInserted);
        $this->assertArrayHasKey('TestPlugin_hierarchy', $this->blobRecordsInserted);

        $flatLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_flat');
        $hierarchyLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_hierarchy');

        $this->assertSame(['/flat-path'], $flatLabels);
        $this->assertSame(['/flat-path'], $hierarchyLabels);
    }

    public function testBuildForNonDayPeriodBuiltFromFlatReadsFlatBlobRowsOnlyOnce(): void
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => '/flat-path', 'nb_visits' => 5]);
        $serialized = $table->getSerialized();
        $rootBlob = reset($serialized);
        $counter = (object) ['flatQueryCount' => 0];

        $recordBuilder = new class ($counter, (string) $rootBlob) extends ArchiveProcessor\RecordBuilder {
            private $counter;
            private $flatRootBlob;

            public function __construct(object $counter, string $flatRootBlob)
            {
                parent::__construct();
                $this->counter = $counter;
                $this->flatRootBlob = $flatRootBlob;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_hierarchy')
                        ->setBuiltFromFlatRecord('TestPlugin_flat', function (Row $flatRow): ?array {
                            $label = $flatRow->getColumn('label');
                            if (!is_string($label) || $label === '') {
                                return null;
                            }

                            return [$label];
                        }),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_flat'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
            {
                if ($recordName !== 'TestPlugin_flat') {
                    return [];
                }

                $this->counter->flatQueryCount++;

                return [[
                    'name' => 'TestPlugin_flat',
                    'date1' => '2020-03-04',
                    'date2' => '2020-03-04',
                    'value' => $this->flatRootBlob,
                ]];
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return ['2020-03-04,2020-03-04' => true];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_hierarchy']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $this->assertSame(1, $counter->flatQueryCount);
        $this->assertArrayHasKey('TestPlugin_flat', $this->blobRecordsInserted);
        $this->assertArrayHasKey('TestPlugin_hierarchy', $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodCanFallbackToLegacyHierarchyWhenFlatBlobMissingForSomeSubperiods()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_hierarchy')
                        ->setBuiltFromFlatRecord(
                            'TestPlugin_flat',
                            function (Row $flatRow): ?array {
                                $label = $flatRow->getColumn('label');
                                if (!is_string($label) || $label === '') {
                                    return null;
                                }

                                return [$label];
                            },
                            function (DataTable $legacyHierarchy, DataTable $flatTable): void {
                                foreach ($legacyHierarchy->getRows() as $legacyRow) {
                                    $flatTable->addRowFromSimpleArray([
                                        'label' => (string) $legacyRow->getColumn('label'),
                                        'nb_visits' => $legacyRow->getColumn('nb_visits'),
                                    ]);
                                }
                            }
                        ),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_flat'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function aggregateDataTableFromBlobs(
                ArchiveProcessor $archiveProcessor,
                string $recordName,
                ?array $columnsAggregationOperation,
                ?array $columnsToRenameAfterAggregation,
                ?array $periodsToInclude = null
            ): array {
                $table = new DataTable();
                if ($recordName === 'TestPlugin_flat') {
                    $table->addRowFromSimpleArray(['label' => '/flat-path', 'nb_visits' => 5]);
                    return [$table, true];
                }

                if ($recordName === 'TestPlugin_hierarchy') {
                    $table->addRowFromSimpleArray(['label' => '/legacy-path', 'nb_visits' => 7]);
                    return [$table, true];
                }

                return [$table, false];
            }

            protected function aggregateRootDataTableFromBlobs(
                ArchiveProcessor $archiveProcessor,
                string $recordName,
                ?array $columnsAggregationOperation,
                ?array $columnsToRenameAfterAggregation
            ): array {
                $table = new DataTable();
                if ($recordName === 'TestPlugin_flat') {
                    $table->addRowFromSimpleArray(['label' => '/flat-path', 'nb_visits' => 5]);
                    return [$table, true, ['2020-03-04,2020-03-04' => true]];
                }

                return [$table, false, []];
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    '2020-03-04,2020-03-04' => true,
                    '2020-03-05,2020-03-05' => true,
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_hierarchy']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $this->assertArrayHasKey('TestPlugin_flat', $this->blobRecordsInserted);
        $this->assertArrayHasKey('TestPlugin_hierarchy', $this->blobRecordsInserted);

        $flatLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_flat');
        $hierarchyLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_hierarchy');

        sort($flatLabels);
        sort($hierarchyLabels);

        $this->assertSame(['/flat-path', '/legacy-path'], $flatLabels);
        $this->assertSame(['/flat-path', '/legacy-path'], $hierarchyLabels);
    }

    public function testBuildForNonDayPeriodConsumesFlatTableBeforePreInsertHook(): void
    {
        $hookState = (object) ['flatRowsAtHook' => null, 'hierarchyLabelsAtHook' => []];

        $recordBuilder = new class ($hookState) extends ArchiveProcessor\RecordBuilder {
            private $hookState;

            public function __construct(object $hookState)
            {
                parent::__construct();
                $this->hookState = $hookState;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_hierarchy')
                        ->setBuiltFromFlatRecord('TestPlugin_flat', function (Row $flatRow): ?array {
                            $label = $flatRow->getColumn('label');
                            if (!is_string($label) || $label === '') {
                                return null;
                            }

                            return [$label];
                        }),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_flat'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }

            protected function aggregateRootDataTableFromBlobs(
                ArchiveProcessor $archiveProcessor,
                string $recordName,
                ?array $columnsAggregationOperation,
                ?array $columnsToRenameAfterAggregation
            ): array {
                $table = new DataTable();
                if ($recordName === 'TestPlugin_flat') {
                    $table->addRowFromSimpleArray(['label' => '/flat-path-a', 'nb_visits' => 5]);
                    $table->addRowFromSimpleArray(['label' => '/flat-path-b', 'nb_visits' => 3]);
                    $table->addSummaryRow(new Row([Row::COLUMNS => ['label' => '-1', 'nb_visits' => 2]]));

                    return [$table, true, ['2020-03-04,2020-03-04' => true]];
                }

                return [$table, false, []];
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return ['2020-03-04,2020-03-04' => true];
            }

            protected function beforeInsertBuiltFromFlatHierarchyRecord(
                ArchiveProcessor $archiveProcessor,
                Record $hierarchicalRecord,
                DataTable $hierarchicalTable,
                DataTable $flatTable
            ): void {
                $this->hookState->flatRowsAtHook = $flatTable->getRowsCount();
                $this->hookState->hierarchyLabelsAtHook = $hierarchicalTable->getColumn('label');
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_flat']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $this->assertSame(0, $hookState->flatRowsAtHook);
        $this->assertSame(['/flat-path-a', '/flat-path-b', '-1'], $hookState->hierarchyLabelsAtHook);

        $this->assertSame(['/flat-path-a', '/flat-path-b', '-1'], $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_flat'));
        $this->assertSame(['/flat-path-a', '/flat-path-b', '-1'], $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_hierarchy'));
    }

    public function testBuildForNonDayPeriodCorrectlyAggregatesMetricsForMetricsThatAreRowCountsOfRecords()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport2', true),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week');
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = [
            'TestPlugin_myMetric' => 3,
            'TestPlugin_myOtherMetric' => 3,
        ];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodCorrectlyAggregatesMetricsForMetricsThatAreRowCountsOfRecordsWhenTheDependentRecordIsNotRequested()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport2'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_myMetric', 'TestPlugin_myOtherMetric']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = [
            'TestPlugin_myMetric' => 3,
            'TestPlugin_myOtherMetric' => 3,
        ];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodCorrectlyAggregatesMetricsForMetricsThatAreRowCountsOfRecordsWhenTheDependentRecordIsRequestedAndFound()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport'),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport2'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor(
            'week',
            ['TestPlugin_myMetric', 'TestPlugin_myOtherMetric', 'TestPlugin_myReport', 'TestPlugin_myReport2'],
            ['TestPlugin_myReport', 'TestPlugin_myReport2']
        );
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = [
            'TestPlugin_myMetric' => 3,
            'TestPlugin_myOtherMetric' => 3,
        ];
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodCorrectlyAggregatesMetricsForMetricsThatAreRecursiveRowCountsOfRecords()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport', true),
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myOtherMetric')
                        ->setIsCountOfBlobRecordRows('TestPlugin_myReport2', true),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport2'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 50,
                    'TestPlugin_myOtherMetric' => 100,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                    'TestPlugin_myReport2' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor(
            'week',
            ['TestPlugin_myMetric', 'TestPlugin_myOtherMetric'],
            null,
            true
        );
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $expectedNumericRecords = ['TestPlugin_myMetric' => 6, 'TestPlugin_myOtherMetric' => 6]; // TODO
        $expectedBlobRecords = [
            'TestPlugin_myReport' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => 1],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => 2],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => 3],
                ],
                // subtables
                [
                    [Row::COLUMNS => ['label' => '[subtable] the thing', 'nb_visits' => 15], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
                [
                    [Row::COLUMNS => ['label' => '[subtable] the thing', 'nb_visits' => 15], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
                [
                    [Row::COLUMNS => ['label' => '[subtable] the thing', 'nb_visits' => 15], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            'TestPlugin_myReport2' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => 1],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => 2],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => 3],
                ],
                // subtables
                [
                    [Row::COLUMNS => ['label' => '[subtable] the thing', 'nb_visits' => 15], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
                [
                    [Row::COLUMNS => ['label' => '[subtable] the thing', 'nb_visits' => 15], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
                [
                    [Row::COLUMNS => ['label' => '[subtable] the thing', 'nb_visits' => 15], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedNumericRecords, $this->numericRecordsInserted);
        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testGetQueryOriginHintUsesDerivedClassNameAsTheDefaultQueryOriginHint()
    {
        $goalsRecordBuilder = new GeneralGoalsRecords();
        $this->assertEquals('GeneralGoalsRecords', $goalsRecordBuilder->getQueryOriginHint());
    }

    public function testIsBuilderForAtLeastOneOfReturnsTrueIfTheRecordBuilderHasMetadataForAtLeastOneRequestedRecord()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 30,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $archiveProcessor = $this->getMockArchiveProcessor();
        $this->assertTrue($recordBuilder->isBuilderForAtLeastOneOf($archiveProcessor, ['TestPlugin_myMetric']));
        $this->assertTrue($recordBuilder->isBuilderForAtLeastOneOf($archiveProcessor, ['TestPlugin_myMetric', 'TestPlugin_myReport']));
        $this->assertTrue($recordBuilder->isBuilderForAtLeastOneOf($archiveProcessor, ['TestPlugin_myReport', 'AnotherPlugin_anotherReport']));
    }

    public function testIsBuilderForAtLeastOneOfReturnsFalseIfTheRecordBuilderDoesNotHaveMetadataForAnyRequestedReport()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_NUMERIC, 'TestPlugin_myMetric'),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_myReport'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    'TestPlugin_myMetric' => 30,
                    'TestPlugin_myReport' => RecordBuilderTest::makeTestDataTable(),
                ];
            }
        };

        $archiveProcessor = $this->getMockArchiveProcessor();
        $this->assertFalse($recordBuilder->isBuilderForAtLeastOneOf($archiveProcessor, ['AnotherPlugin_anotherReport']));
        $this->assertFalse($recordBuilder->isBuilderForAtLeastOneOf($archiveProcessor, ['AnotherPlugin_anotherReport2', 'AThirdPlugin_anotherReport3']));
    }

    public function getMockArchiveProcessor(
        string $period = 'day',
        ?array $requestedReports = null,
        ?array $foundRequestedReports = null,
        bool $addSubtablesToAggregatedTables = false
    ): ArchiveProcessor {
        Site::setSiteFromArray(1, ['idsite' => 1, 'ecommerce' => 0, 'sitesearch' => 0, 'exclude_unknown_urls' => 0, 'keep_url_fragment' => 0]);
        $params = new Parameters(new Site(1), PeriodFactory::build($period, '2020-03-04'), new Segment('', [1]));

        if (!empty($requestedReports)) {
            $params->setArchiveOnlyReport($requestedReports);
        }

        if (!empty($foundRequestedReports)) {
            $params->setFoundRequestedReports($foundRequestedReports);
        }

        $archiveWriter = new class () extends ArchiveWriter {
            public function __construct()
            {
                // disable original constructor
            }
        };

        $logAggregator = new class () extends LogAggregator {
            public function __construct()
            {
                // disable original constructor
            }
        };

        return new class ($this, $addSubtablesToAggregatedTables, $params, $archiveWriter, $logAggregator) extends ArchiveProcessor {
            /**
             * @var RecordBuilderTest
             */
            private $test;

            /**
             * @var bool
             */
            private $addSubtablesToAggregatedTables;

            public function __construct(
                RecordBuilderTest $test,
                bool $addSubtablesToAggregatedTables,
                Parameters $params,
                ArchiveWriter $archiveWriter,
                LogAggregator $logAggregator
            ) {
                parent::__construct($params, $archiveWriter, $logAggregator);

                $this->test = $test;
                $this->addSubtablesToAggregatedTables = $addSubtablesToAggregatedTables;
            }

            protected function aggregateDataTableRecord($name, $columnsAggregationOperation = null, $columnsToRenameAfterAggregation = null)
            {
                $dataTable = RecordBuilderTest::makeAggregatedTestDataTable();
                if ($this->addSubtablesToAggregatedTables) {
                    foreach ($dataTable->getRows() as $row) {
                        $row->setSubtable(RecordBuilderTest::makeAggregatedTestSubtable());
                    }
                }

                if (!empty($columnsAggregationOperation)) {
                    $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
                    $dataTable->filterSubtables(function (DataTable $subtable) use ($columnsAggregationOperation) {
                        $subtable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
                    });
                }

                return $dataTable;
            }

            protected function getAggregatedNumericMetrics($columns, $operationsToApply)
            {
                $metricValuesToUse = [9000, 10500, 15000, 12345, 1000];

                $metricValues = [];
                for ($i = 0; $i < count($columns); ++$i) {
                    $metricValueToUse = $metricValuesToUse[$i % count($metricValuesToUse)];
                    $metricValues[$columns[$i]] = $metricValueToUse;
                }
                return $metricValues;
            }

            public function insertNumericRecord($name, $value)
            {
                $this->test->numericRecordsInserted[$name] = $value;
            }

            public function insertBlobRecord($name, $values)
            {
                // make the serialized values more readable
                $values = array_map(function ($v) {
                    $deserialized = unserialize($v);

                    $asArray = json_encode($deserialized);
                    $asArray = json_decode($asArray, true);

                    return $asArray;
                }, $values);

                $this->test->blobRecordsInserted[$name] = $values;
            }
        };
    }

    private function getTopLevelLabelsOfInsertedBlobRecord(string $recordName): array
    {
        if (empty($this->blobRecordsInserted[$recordName][0])) {
            return [];
        }

        return array_values(array_map(function (array $row): string {
            return (string) ($row[Row::COLUMNS]['label'] ?? '');
        }, $this->blobRecordsInserted[$recordName][0]));
    }

    public static function makeTestDataTable(): DataTable
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => 'the thing', 'nb_visits' => 40]);
        $table->addRowFromSimpleArray(['label' => 'another thing', 'nb_visits' => 50]);
        $table->addRowFromSimpleArray(['label' => 'a third thing', 'nb_visits' => 20]);
        return $table;
    }

    public static function makeAggregatedTestDataTable(): DataTable
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => '[aggregated] the thing', 'nb_visits' => 140]);
        $table->addRowFromSimpleArray(['label' => '[aggregated] another thing', 'nb_visits' => 150]);
        $table->addRowFromSimpleArray(['label' => '[aggregated] a third thing', 'nb_visits' => 30]);
        return $table;
    }

    public static function makeAggregatedTestSubtable()
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => '[subtable] the thing', 'nb_visits' => 15]);
        return $table;
    }
}
