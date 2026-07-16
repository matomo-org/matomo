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

    public function testBuildForNonDayPeriodAppliesAggregatedRecordTransformAndLeavesOtherRecordsUntouched()
    {
        $received = (object) ['recordName' => null, 'hasArchiveProcessor' => false];

        $recordBuilder = new class ($received) extends ArchiveProcessor\RecordBuilder {
            private $received;

            public function __construct(object $received)
            {
                parent::__construct();
                $this->received = $received;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                $received = $this->received;

                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_scored')
                        ->setAggregatedRecordTransform(
                            function (DataTable $table, ArchiveProcessor $ap, Record $record) use ($received): void {
                                // Record the contextual args to prove they were passed through.
                                $received->recordName = $record->getName();
                                $received->hasArchiveProcessor = $ap instanceof ArchiveProcessor;

                                // Recompute a column from the aggregated (summed) values.
                                foreach ($table->getRows() as $row) {
                                    $row->setColumn('score', 1000 - (int) $row->getColumn('nb_visits'));
                                }
                            }
                        ),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_plain'),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week');
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        // The callback received the full contextual signature.
        $this->assertSame('TestPlugin_scored', $received->recordName);
        $this->assertTrue($received->hasArchiveProcessor);

        $expectedBlobRecords = [
            // The transform ran on the aggregated table (nb_visits are the summed values) and the
            // recomputed 'score' column was stored.
            'TestPlugin_scored' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140, 'score' => 860], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150, 'score' => 850], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30, 'score' => 970], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
            // A record without a transform is unaffected.
            'TestPlugin_plain' => [
                [
                    [Row::COLUMNS => ['label' => '[aggregated] the thing', 'nb_visits' => 140], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] another thing', 'nb_visits' => 150], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                    [Row::COLUMNS => ['label' => '[aggregated] a third thing', 'nb_visits' => 30], Row::METADATA => [], Row::DATATABLE_ASSOCIATED => null],
                ],
            ],
        ];

        $this->assertEquals($expectedBlobRecords, $this->blobRecordsInserted);
    }

    public function testBuildForNonDayPeriodTruncatesUsingColumnRecomputedByAggregatedRecordTransform()
    {
        $recordBuilder = new class () extends ArchiveProcessor\RecordBuilder {
            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_scored')
                        ->setColumnToSortByBeforeTruncation('score')
                        ->setMaxRowsInTable(2)
                        ->setAggregatedRecordTransform(function (DataTable $table): void {
                            // Inverse of nb_visits, so the score order differs from the physical nb_visits order.
                            foreach ($table->getRows() as $row) {
                                $row->setColumn('score', 1000 - (int) $row->getColumn('nb_visits'));
                            }
                        }),
                ];
            }

            protected function aggregate(ArchiveProcessor $archiveProcessor): array
            {
                return [];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week');
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        // "a third thing" has the highest score (970) but the lowest nb_visits (30); it survives
        // truncation, proving the sort-before-truncation used the transform-computed 'score' column.
        $labels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_scored');
        $this->assertSame('[aggregated] a third thing', $labels[0]);
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

    public function testBuildForNonDayPeriodAppliesAggregatedRecordTransformOnBothFlatAndHierarchyRecords()
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
                        })
                        // Recomputed on the rebuilt hierarchy table, after it is built.
                        ->setAggregatedRecordTransform(function (DataTable $table): void {
                            foreach ($table->getRows() as $row) {
                                $row->setColumn('hier_score', 20);
                            }
                        }),
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_flat')
                        // Recomputed on the aggregated flat table, before it is stored.
                        ->setAggregatedRecordTransform(function (DataTable $table): void {
                            foreach ($table->getRows() as $row) {
                                $row->setColumn('flat_score', 10);
                            }
                        }),
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

        // The flat record's transform ran on the aggregated flat table.
        $flatScores = $this->getFlatInsertedBlobMetric('TestPlugin_flat', 'flat_score');
        ksort($flatScores);
        $this->assertSame(['/flat-path-a' => 10, '/flat-path-b' => 10], $flatScores);

        // The hierarchy record's transform ran on the rebuilt hierarchy table.
        $hierScores = $this->getFlatInsertedBlobMetric('TestPlugin_hierarchy', 'hier_score');
        ksort($hierScores);
        $this->assertSame(['/flat-path-a' => 20, '/flat-path-b' => 20], $hierScores);
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

            protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
            {
                if ($recordName !== 'TestPlugin_hierarchy') {
                    return [];
                }

                $legacyTable = new DataTable();
                $legacyTable->addRowFromSimpleArray(['label' => '/legacy-path', 'nb_visits' => 7]);

                yield [
                    'date1' => '2020-03-05',
                    'date2' => '2020-03-05',
                    'name' => 'TestPlugin_hierarchy',
                    'value' => $legacyTable->getSerialized()[0],
                ];
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

    public function testBuildForNonDayPeriodReducesLegacyFallbackPerPeriod(): void
    {
        $state = (object) ['reducerCalls' => []];

        $recordBuilder = new class ($state) extends ArchiveProcessor\RecordBuilder {
            private $state;

            public function __construct(object $state)
            {
                parent::__construct();
                $this->state = $state;
            }

            public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    Record::make(Record::TYPE_BLOB, 'TestPlugin_hierarchy')
                        ->setBuiltFromFlatRecord(
                            'TestPlugin_flat',
                            function (Row $flatRow): ?array {
                                $label = $flatRow->getColumn('label');
                                return is_string($label) && $label !== '' ? [$label] : null;
                            },
                            function (DataTable $legacyHierarchy, DataTable $flatTable) {
                                $this->state->reducerCalls[] = $legacyHierarchy->getColumn('label');

                                foreach ($legacyHierarchy->getRowsWithoutSummaryRow() as $row) {
                                    $flatTable->addRow(clone $row);
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

            protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
            {
                if ($recordName !== 'TestPlugin_hierarchy') {
                    return [];
                }

                $legacyTableA = new DataTable();
                $legacyTableA->addRowFromSimpleArray(['label' => '/legacy-path-a', 'nb_visits' => 2]);

                $legacyTableB = new DataTable();
                $legacyTableB->addRowFromSimpleArray(['label' => '/legacy-path-b', 'nb_visits' => 3]);

                yield [
                    'date1' => '2020-03-05',
                    'date2' => '2020-03-05',
                    'name' => 'TestPlugin_hierarchy',
                    'value' => $legacyTableA->getSerialized()[0],
                ];

                yield [
                    'date1' => '2020-03-06',
                    'date2' => '2020-03-06',
                    'name' => 'TestPlugin_hierarchy',
                    'value' => $legacyTableB->getSerialized()[0],
                ];
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    '2020-03-04,2020-03-04' => true,
                    '2020-03-05,2020-03-05' => true,
                    '2020-03-06,2020-03-06' => true,
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_hierarchy']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        $this->assertSame([['/legacy-path-a'], ['/legacy-path-b']], $state->reducerCalls);

        $flatLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_flat');
        sort($flatLabels);
        $this->assertSame(['/flat-path', '/legacy-path-a', '/legacy-path-b'], $flatLabels);

        $hierarchyLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_hierarchy');
        sort($hierarchyLabels);
        $this->assertSame(['/flat-path', '/legacy-path-a', '/legacy-path-b'], $hierarchyLabels);
    }

    public function testBuildForNonDayPeriodPassesDeepLegacyHierarchyWithSubtablesToReducer(): void
    {
        $state = (object) ['receivedHierarchies' => []];

        $recordBuilder = new class ($state) extends ArchiveProcessor\RecordBuilder {
            private $state;

            public function __construct(object $state)
            {
                parent::__construct();
                $this->state = $state;
            }

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
                                return explode('/', ltrim($label, '/'));
                            },
                            function (DataTable $legacyHierarchy, DataTable $flatTable) {
                                $this->state->receivedHierarchies[] = RecordBuilderTest::describeHierarchy($legacyHierarchy);
                                RecordBuilderTest::flattenHierarchyIntoFlatTable($legacyHierarchy, $flatTable);
                            }
                        ),
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
                // No flat record for any period, so the legacy fallback must cover both days.
                return [new DataTable(), false, []];
            }

            protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
            {
                if ($recordName !== 'TestPlugin_hierarchy') {
                    return [];
                }

                $deepTable = RecordBuilderTest::makeDeepHierarchyTable([
                    '/products' => [
                        'shoes' => 4,
                        'shirts' => 1,
                    ],
                    '/about' => 9,
                ]);

                foreach (RecordBuilderTest::serializeToArchiveRows('TestPlugin_hierarchy', '2020-03-04', $deepTable) as $row) {
                    yield $row;
                }
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return ['2020-03-04,2020-03-04' => true];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_hierarchy']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        // Reducer must have been called exactly once (single period) and must have received the parent
        // rows with subtables attached, not a flattened table.
        $this->assertCount(1, $state->receivedHierarchies);
        $this->assertSame(
            [
                '/products' => ['shoes' => 4, 'shirts' => 1],
                '/about' => 9,
            ],
            $state->receivedHierarchies[0]
        );

        $flatLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_flat');
        sort($flatLabels);
        $this->assertSame(['/about', '/products/shirts', '/products/shoes'], $flatLabels);

        // The hierarchy blob must be rebuilt from the resulting flat table. Top-level labels come
        // from the first segment returned by the flatRowToHierarchyPath callback (leading "/" stripped).
        $hierarchyLabels = $this->getTopLevelLabelsOfInsertedBlobRecord('TestPlugin_hierarchy');
        sort($hierarchyLabels);
        $this->assertSame(['about', 'products'], $hierarchyLabels);
    }

    public function testBuildForNonDayPeriodMergesOverlappingDeepLegacyHierarchyPathsAcrossPeriods(): void
    {
        $state = (object) ['reducerCallCount' => 0];

        $recordBuilder = new class ($state) extends ArchiveProcessor\RecordBuilder {
            private $state;

            public function __construct(object $state)
            {
                parent::__construct();
                $this->state = $state;
            }

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
                                return explode('/', ltrim($label, '/'));
                            },
                            function (DataTable $legacyHierarchy, DataTable $flatTable) {
                                $this->state->reducerCallCount++;
                                RecordBuilderTest::flattenHierarchyIntoFlatTable($legacyHierarchy, $flatTable);
                            }
                        ),
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
                // No flat blobs for either period -> both periods fall through to the legacy deep hierarchy.
                return [new DataTable(), false, []];
            }

            protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
            {
                if ($recordName !== 'TestPlugin_hierarchy') {
                    return [];
                }

                // Period A: /products/shoes=4, /products/shirts=1, /about=9
                $periodA = RecordBuilderTest::makeDeepHierarchyTable([
                    '/products' => [
                        'shoes' => 4,
                        'shirts' => 1,
                    ],
                    '/about' => 9,
                ]);

                // Period B: /products/shoes=2 (overlap), /products/hats=6, /about=3 (overlap)
                $periodB = RecordBuilderTest::makeDeepHierarchyTable([
                    '/products' => [
                        'shoes' => 2,
                        'hats' => 6,
                    ],
                    '/about' => 3,
                ]);

                foreach (RecordBuilderTest::serializeToArchiveRows('TestPlugin_hierarchy', '2020-03-05', $periodA) as $row) {
                    yield $row;
                }
                foreach (RecordBuilderTest::serializeToArchiveRows('TestPlugin_hierarchy', '2020-03-06', $periodB) as $row) {
                    yield $row;
                }
            }

            protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
            {
                return [
                    '2020-03-05,2020-03-05' => true,
                    '2020-03-06,2020-03-06' => true,
                ];
            }
        };

        $mockArchiveProcessor = $this->getMockArchiveProcessor('week', ['TestPlugin_hierarchy']);
        $recordBuilder->buildForNonDayPeriod($mockArchiveProcessor);

        // One reducer call per period with legacy data.
        $this->assertSame(2, $state->reducerCallCount);

        $flatByLabel = $this->getFlatInsertedBlobMetric('TestPlugin_flat', 'nb_visits');
        ksort($flatByLabel);

        // Overlapping leaves must be summed across periods; non-overlapping leaves must appear once.
        $this->assertSame(
            [
                '/about' => 12,                // 9 + 3
                '/products/hats' => 6,         // period B only
                '/products/shirts' => 1,       // period A only
                '/products/shoes' => 6,        // 4 + 2
            ],
            $flatByLabel
        );
    }

    public function testBuildForNonDayPeriodMixesFlatRecordWithDeepLegacyHierarchyFallback(): void
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
                                return explode('/', ltrim($label, '/'));
                            },
                            function (DataTable $legacyHierarchy, DataTable $flatTable) {
                                RecordBuilderTest::flattenHierarchyIntoFlatTable($legacyHierarchy, $flatTable);
                            }
                        ),
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
                    // Period 03-04 already has a flat record with paths overlapping the legacy hierarchy.
                    $table->addRowFromSimpleArray(['label' => '/products/shoes', 'nb_visits' => 10]);
                    $table->addRowFromSimpleArray(['label' => '/contact', 'nb_visits' => 2]);
                    return [$table, true, ['2020-03-04,2020-03-04' => true]];
                }
                return [$table, false, []];
            }

            protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
            {
                if ($recordName !== 'TestPlugin_hierarchy') {
                    return [];
                }

                // Only period 03-05 falls back to legacy deep hierarchy.
                $legacyTable = RecordBuilderTest::makeDeepHierarchyTable([
                    '/products' => [
                        'shoes' => 4,
                        'shirts' => 1,
                    ],
                    '/about' => 9,
                ]);

                foreach (RecordBuilderTest::serializeToArchiveRows('TestPlugin_hierarchy', '2020-03-05', $legacyTable) as $row) {
                    yield $row;
                }
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

        $flatByLabel = $this->getFlatInsertedBlobMetric('TestPlugin_flat', 'nb_visits');
        ksort($flatByLabel);

        $this->assertSame(
            [
                '/about' => 9,                 // from legacy hierarchy (period 03-05)
                '/contact' => 2,               // from flat record (period 03-04)
                '/products/shirts' => 1,       // from legacy hierarchy (period 03-05)
                '/products/shoes' => 14,       // 10 (flat) + 4 (legacy) merged by label
            ],
            $flatByLabel
        );
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

    /**
     * Builds a two-level DataTable from a nested spec.
     * Leaf entries are `label => nb_visits`; nested entries are `label => [childLabel => nb_visits, ...]`.
     */
    public static function makeDeepHierarchyTable(array $spec): DataTable
    {
        $table = new DataTable();
        foreach ($spec as $label => $children) {
            if (is_array($children)) {
                $row = new Row([Row::COLUMNS => ['label' => $label]]);
                $subtable = new DataTable();
                foreach ($children as $childLabel => $nbVisits) {
                    $subtable->addRowFromSimpleArray(['label' => $childLabel, 'nb_visits' => $nbVisits]);
                }
                $row->setSubtable($subtable);
                $table->addRow($row);
            } else {
                $table->addRowFromSimpleArray(['label' => $label, 'nb_visits' => $children]);
            }
        }
        return $table;
    }

    /**
     * Serializes a DataTable (including subtables) into the archive-row shape expected by
     * `querySingleBlobRows()` — root blob named $recordName (serialized key 0 per the DataTable
     * spec), subtable blobs named $recordName_<id>. Root is yielded first so consumers can rely
     * on parent-before-child ordering, mirroring how ArchiveSelector orders rows.
     *
     * @return iterable<array{name: string, date1: string, date2: string, value: string}>
     */
    public static function serializeToArchiveRows(string $recordName, string $date, DataTable $table): iterable
    {
        $serialized = $table->getSerialized();
        ksort($serialized);
        foreach ($serialized as $key => $value) {
            yield [
                'name' => $key === 0 ? $recordName : $recordName . '_' . $key,
                'date1' => $date,
                'date2' => $date,
                'value' => $value,
            ];
        }
    }

    /**
     * Serializes a DataTable hierarchy into a plain nested array (label => nb_visits or label => [child => nb_visits]).
     * Used by tests to compare a hierarchy structure without depending on internal row IDs.
     */
    public static function describeHierarchy(DataTable $table): array
    {
        $result = [];
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $label = $row->getColumn('label');
            $subtable = $row->getSubtable();
            if ($subtable instanceof DataTable) {
                $children = [];
                foreach ($subtable->getRowsWithoutSummaryRow() as $childRow) {
                    $children[(string) $childRow->getColumn('label')] = $childRow->getColumn('nb_visits');
                }
                $result[(string) $label] = $children;
            } else {
                $result[(string) $label] = $row->getColumn('nb_visits');
            }
        }
        return $result;
    }

    /**
     * Walks a (possibly deep) hierarchy DataTable and merges every leaf into the flat table by its
     * fully-qualified "/parent/child" path label, summing nb_visits when the same label is seen
     * again. Mirrors the essential semantics used by the production Actions legacy-hierarchy reducer.
     */
    public static function flattenHierarchyIntoFlatTable(DataTable $hierarchy, DataTable $flatTable): void
    {
        $walker = function (DataTable $source, array $path) use (&$walker, $flatTable): void {
            foreach ($source->getRowsWithoutSummaryRow() as $row) {
                $label = (string) $row->getColumn('label');
                $subtable = $row->getSubtable();
                if ($subtable instanceof DataTable) {
                    $walker($subtable, array_merge($path, [$label]));
                    continue;
                }

                $segments = array_merge($path, [$label]);
                $flatLabel = '/' . implode('/', array_map(static function (string $segment): string {
                    return ltrim($segment, '/');
                }, $segments));

                $nbVisits = (int) $row->getColumn('nb_visits');
                $existing = $flatTable->getRowFromLabel($flatLabel);
                if ($existing === false) {
                    $flatTable->addRow(new Row([Row::COLUMNS => [
                        'label' => $flatLabel,
                        'nb_visits' => $nbVisits,
                    ]]));
                } else {
                    $existing->setColumn(
                        'nb_visits',
                        (int) $existing->getColumn('nb_visits') + $nbVisits
                    );
                }
            }
        };

        $walker($hierarchy, []);
    }

    /**
     * @return array<string, int>
     */
    private function getFlatInsertedBlobMetric(string $recordName, string $metric): array
    {
        if (empty($this->blobRecordsInserted[$recordName][0])) {
            return [];
        }

        $result = [];
        foreach ($this->blobRecordsInserted[$recordName][0] as $rowId => $row) {
            if ($rowId === DataTable::ID_SUMMARY_ROW) {
                continue;
            }
            $label = (string) ($row[Row::COLUMNS]['label'] ?? '');
            $result[$label] = (int) ($row[Row::COLUMNS][$metric] ?? 0);
        }
        return $result;
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
