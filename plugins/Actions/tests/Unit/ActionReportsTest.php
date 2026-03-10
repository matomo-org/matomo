<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Unit;

use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Plugins\Actions\Archiver;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Actions\RecordBuilders\ActionReports;

/**
 * @group Actions
 * @group Plugins
 */
class ActionReportsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRecordMetadataDoesNotIncludeFlatRecordsWhenFlatLimitIsZero()
    {
        $this->withFlatLimit(0, function () {
            $recordBuilder = new ActionReports();
            $records = $recordBuilder->getRecordMetadata($this->createMock(ArchiveProcessor::class));
            $recordNames = array_map(function ($record) {
                return $record->getName();
            }, $records);

            $this->assertNotContains(Archiver::PAGE_URLS_FLAT_RECORD_NAME, $recordNames);
            $this->assertNotContains(Archiver::PAGE_TITLES_FLAT_RECORD_NAME, $recordNames);
        });
    }

    public function testBuildForNonDayPeriodUsesLegacyPathWhenFlatArchivingDisabled()
    {
        $this->withFlatLimit(0, function () {
            $params = $this->createParams(
                [Archiver::PAGE_URLS_RECORD_NAME],
                [],
                ['2026-01-01']
            );
            $archiveProcessor = $this->getMockBuilder(ArchiveProcessor::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getParams', 'aggregateDataTableRecords', 'aggregateNumericMetrics', 'insertNumericRecords'])
                ->getMock();
            $archiveProcessor->method('getParams')->willReturn($params);
            $archiveProcessor->expects($this->never())->method('aggregateNumericMetrics');
            $archiveProcessor->expects($this->never())->method('insertNumericRecords');

            $aggregatedRecordNames = [];
            $archiveProcessor->expects($this->once())->method('aggregateDataTableRecords')
                ->willReturnCallback(function (...$args) use (&$aggregatedRecordNames) {
                    $aggregatedRecordNames[] = $args[0];
                    return [];
                });

            $recordBuilder = new ActionReports();
            $recordBuilder->buildForNonDayPeriod($archiveProcessor);

            $this->assertSame([Archiver::PAGE_URLS_RECORD_NAME], $aggregatedRecordNames);
        });
    }

    public function testBuildForNonDayPeriodFlatFirstAggregatesMixedFlatAndHierarchicalSources()
    {
        $this->withFlatLimit(50000, function () {
            $flatRecordName = Archiver::PAGE_URLS_FLAT_RECORD_NAME;
            $hierarchicalRecordName = Archiver::PAGE_URLS_RECORD_NAME;

            $flatPeriodTable = $this->createFlatSerializedTable('flat-a', ['flat-a'], 4);
            $hierarchicalPeriodTable = $this->createHierarchicalSerializedTable('legacy-b', 6, 2);

            $rowsByRecordName = [
                $flatRecordName => [[
                    'date1' => '2026-01-01',
                    'date2' => '2026-01-01',
                    'name' => $flatRecordName,
                    'value' => $flatPeriodTable,
                ]],
                $hierarchicalRecordName => [[
                    'date1' => '2026-01-02',
                    'date2' => '2026-01-02',
                    'name' => $hierarchicalRecordName,
                    'value' => $hierarchicalPeriodTable,
                ]],
            ];

            $recordBuilder = new class ($rowsByRecordName) extends ActionReports {
                private $rowsByRecordName;

                public function __construct(array $rowsByRecordName)
                {
                    $this->rowsByRecordName = $rowsByRecordName;
                    parent::__construct();
                }

                protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
                {
                    return $this->rowsByRecordName[$recordName] ?? [];
                }
            };

            $params = $this->createParams(
                [Archiver::PAGE_URLS_RECORD_NAME],
                [],
                ['2026-01-01', '2026-01-02']
            );
            $archiveProcessor = $this->getMockBuilder(ArchiveProcessor::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getParams', 'insertBlobRecord', 'aggregateDataTableRecords', 'aggregateNumericMetrics', 'insertNumericRecords'])
                ->getMock();
            $archiveProcessor->method('getParams')->willReturn($params);
            $archiveProcessor->expects($this->never())->method('aggregateDataTableRecords');
            $archiveProcessor->expects($this->never())->method('aggregateNumericMetrics');
            $archiveProcessor->expects($this->never())->method('insertNumericRecords');

            $insertedBlobs = [];
            $archiveProcessor->method('insertBlobRecord')->willReturnCallback(function ($recordName, $blobValue) use (&$insertedBlobs) {
                $insertedBlobs[$recordName] = $blobValue;
            });

            $recordBuilder->buildForNonDayPeriod($archiveProcessor);

            $this->assertArrayHasKey($flatRecordName, $insertedBlobs);
            $this->assertArrayHasKey($hierarchicalRecordName, $insertedBlobs);
            $this->assertArrayNotHasKey(Archiver::PAGE_TITLES_RECORD_NAME, $insertedBlobs);

            $flatResult = DataTable::fromSerializedArray(
                $this->getRootBlobFromInsertedRecord($insertedBlobs[$flatRecordName], $flatRecordName)
            );
            $flatRowA = $flatResult->getRowFromLabel('flat-a');
            $this->assertNotFalse($flatRowA);
            $this->assertSame(4, $flatRowA->getColumn('nb_hits'));

            $flatRowB = $flatResult->getRowFromLabel('legacy-b');
            $this->assertNotFalse($flatRowB);
            $this->assertSame(6, $flatRowB->getColumn('nb_hits'));

            $flatSummary = $flatResult->getRowFromId(DataTable::ID_SUMMARY_ROW);
            $this->assertNotFalse($flatSummary);
            $this->assertSame(2, $flatSummary->getColumn('nb_hits'));

            $hierarchicalResult = DataTable::fromSerializedArray(
                $this->getRootBlobFromInsertedRecord($insertedBlobs[$hierarchicalRecordName], $hierarchicalRecordName)
            );
            $hierarchicalRowA = $hierarchicalResult->getRowFromLabel('flat-a');
            $this->assertNotFalse($hierarchicalRowA);
            $this->assertSame(4, $hierarchicalRowA->getColumn('nb_hits'));

            $hierarchicalRowB = $hierarchicalResult->getRowFromLabel('legacy-b');
            $this->assertNotFalse($hierarchicalRowB);
            $this->assertSame(6, $hierarchicalRowB->getColumn('nb_hits'));

            $hierarchicalSummary = $hierarchicalResult->getRowFromId(DataTable::ID_SUMMARY_ROW);
            $this->assertNotFalse($hierarchicalSummary);
            $this->assertSame(2, $hierarchicalSummary->getColumn('nb_hits'));
        });
    }

    public function testMergeHierarchicalActionsTableIntoFlatTableMovesNestedOthersToGlobalOthers()
    {
        $hierarchical = new DataTable();
        $rootA = $hierarchical->addRow(new Row([Row::COLUMNS => ['label' => 'a', 'nb_hits' => 8]]));
        $rootB = $hierarchical->addRow(new Row([Row::COLUMNS => ['label' => 'b', 'nb_hits' => 2]]));
        $hierarchical->addSummaryRow(new Row([Row::COLUMNS => ['label' => DataTable::LABEL_SUMMARY_ROW, 'nb_hits' => 7]]));

        $subtableA = new DataTable();
        $subtableA->addRow(new Row([Row::COLUMNS => ['label' => '/x', 'nb_hits' => 3]]));
        $subtableA->addSummaryRow(new Row([Row::COLUMNS => ['label' => DataTable::LABEL_SUMMARY_ROW, 'nb_hits' => 5]]));
        $rootA->setSubtable($subtableA);

        $flat = new DataTable();
        ArchivingHelper::mergeHierarchicalActionsTableIntoFlatTable($hierarchical, $flat);

        $flatSummary = $flat->getRowFromId(DataTable::ID_SUMMARY_ROW);
        $this->assertNotFalse($flatSummary);
        $this->assertSame(12, $flatSummary->getColumn('nb_hits'));

        $flatRowA = $flat->getRowFromLabel(json_encode(['a', '/x']));
        $this->assertNotFalse($flatRowA);
        $this->assertSame(3, $flatRowA->getColumn('nb_hits'));

        $flatRowB = $flat->getRowFromLabel(json_encode(['b']));
        $this->assertNotFalse($flatRowB);
        $this->assertSame(2, $flatRowB->getColumn('nb_hits'));

        $rebuilt = ArchivingHelper::buildHierarchicalActionsTableFromFlatTable($flat);
        $rebuiltSummary = $rebuilt->getRowFromId(DataTable::ID_SUMMARY_ROW);
        $this->assertNotFalse($rebuiltSummary);
        $this->assertSame(12, $rebuiltSummary->getColumn('nb_hits'));
    }

    private function withFlatLimit(int $flatLimit, callable $callback): void
    {
        $config = Config::getInstance();
        $hadPreviousFlatLimit = array_key_exists('datatable_archiving_maximum_rows_actions_flat', $config->General);
        $previousFlatLimit = $hadPreviousFlatLimit ? $config->General['datatable_archiving_maximum_rows_actions_flat'] : null;

        $config->General['datatable_archiving_maximum_rows_actions_flat'] = $flatLimit;
        ArchivingHelper::reloadConfig();

        try {
            $callback();
        } finally {
            if ($hadPreviousFlatLimit) {
                $config->General['datatable_archiving_maximum_rows_actions_flat'] = $previousFlatLimit;
            } else {
                unset($config->General['datatable_archiving_maximum_rows_actions_flat']);
            }
            ArchivingHelper::reloadConfig();
        }
    }

    private function createParams(array $requestedReports, array $foundRequestedReports, array $dates): object
    {
        $subperiods = array_map(function ($date) {
            return new class ($date) {
                private $date;

                public function __construct(string $date)
                {
                    $this->date = $date;
                }

                public function getDateStart(): Date
                {
                    return Date::factory($this->date);
                }

                public function getDateEnd(): Date
                {
                    return Date::factory($this->date);
                }
            };
        }, $dates);

        $period = new class ($subperiods) {
            private $subperiods;

            public function __construct(array $subperiods)
            {
                $this->subperiods = $subperiods;
            }

            public function getSubperiods(): array
            {
                return $this->subperiods;
            }
        };

        $site = new class {
            public function getId(): int
            {
                return 1;
            }

            public function getMainUrl(): string
            {
                return 'https://example.test/';
            }
        };

        return new class ($requestedReports, $foundRequestedReports, $period, $site) {
            private $requestedReports;
            private $foundRequestedReports;
            private $period;
            private $site;

            public function __construct(array $requestedReports, array $foundRequestedReports, object $period, object $site)
            {
                $this->requestedReports = $requestedReports;
                $this->foundRequestedReports = $foundRequestedReports;
                $this->period = $period;
                $this->site = $site;
            }

            public function getArchiveOnlyReportAsArray(): array
            {
                return $this->requestedReports;
            }

            public function getFoundRequestedReports(): array
            {
                return $this->foundRequestedReports;
            }

            public function getPeriod(): object
            {
                return $this->period;
            }

            public function getSite(): object
            {
                return $this->site;
            }

            public function getSegment()
            {
                return null;
            }
        };
    }

    private function createFlatSerializedTable(string $label, array $actionPath, int $nbHits): string
    {
        $flat = new DataTable();
        $flatRow = new Row([Row::COLUMNS => ['label' => $label, 'nb_hits' => $nbHits]]);
        $flatRow->setMetadata(ArchivingHelper::ACTION_FLAT_PATH_METADATA_NAME, $actionPath);
        $flat->addRow($flatRow);

        return $this->getRootSerializedBlob($flat);
    }

    private function createHierarchicalSerializedTable(string $label, int $nbHits, int $summaryHits): string
    {
        $table = new DataTable();
        $table->addRow(new Row([Row::COLUMNS => ['label' => $label, 'nb_hits' => $nbHits]]));
        $table->addSummaryRow(new Row([Row::COLUMNS => ['label' => DataTable::LABEL_SUMMARY_ROW, 'nb_hits' => $summaryHits]]));

        return $this->getRootSerializedBlob($table);
    }

    private function getRootSerializedBlob(DataTable $table): string
    {
        $serialized = $table->getSerialized(null, null, null);
        if (!is_array($serialized)) {
            return $serialized;
        }

        return (string) reset($serialized);
    }

    private function getRootBlobFromInsertedRecord($blobValue, string $recordName): string
    {
        if (!is_array($blobValue)) {
            return $blobValue;
        }

        if (!empty($blobValue[$recordName])) {
            return $blobValue[$recordName];
        }

        return (string) reset($blobValue);
    }
}
