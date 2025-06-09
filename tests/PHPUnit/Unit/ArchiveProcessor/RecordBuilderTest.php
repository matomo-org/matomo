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
