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
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

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

            $flatPeriodTable = $this->createFlatSerializedTable('/flat-a', ['/flat-a'], 4);
            $hierarchicalPeriodTable = $this->createHierarchicalSerializedTable('/legacy-b', 6, 2);

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
            $flatRowA = $flatResult->getRowFromLabel('/flat-a');
            $this->assertNotFalse($flatRowA);
            $this->assertSame(4, $flatRowA->getColumn('nb_hits'));

            $flatRowB = $flatResult->getRowFromLabel('/legacy-b');
            $this->assertNotFalse($flatRowB);
            $this->assertSame(6, $flatRowB->getColumn('nb_hits'));

            $flatSummary = $flatResult->getRowFromId(DataTable::ID_SUMMARY_ROW);
            $this->assertNotFalse($flatSummary);
            $this->assertSame(2, $flatSummary->getColumn('nb_hits'));

            $hierarchicalResult = DataTable::fromSerializedArray(
                $this->getRootBlobFromInsertedRecord($insertedBlobs[$hierarchicalRecordName], $hierarchicalRecordName)
            );
            $hierarchicalRowA = $hierarchicalResult->getRowFromLabel('/flat-a');
            $this->assertNotFalse($hierarchicalRowA);
            $this->assertSame(4, $hierarchicalRowA->getColumn('nb_hits'));

            $hierarchicalRowB = $hierarchicalResult->getRowFromLabel('/legacy-b');
            $this->assertNotFalse($hierarchicalRowB);
            $this->assertSame(6, $hierarchicalRowB->getColumn('nb_hits'));

            $hierarchicalSummary = $hierarchicalResult->getRowFromId(DataTable::ID_SUMMARY_ROW);
            $this->assertNotFalse($hierarchicalSummary);
            $this->assertSame(2, $hierarchicalSummary->getColumn('nb_hits'));
        });
    }

    public function testBuildForNonDayPeriodFlatFirstAggregatesMixedSourcesWhenOnlyFlatRecordIsRequested()
    {
        $this->withFlatLimit(50000, function () {
            $flatRecordName = Archiver::PAGE_URLS_FLAT_RECORD_NAME;
            $hierarchicalRecordName = Archiver::PAGE_URLS_RECORD_NAME;

            $flatPeriodTable = $this->createFlatSerializedTable('/flat-a', ['/flat-a'], 4);
            $hierarchicalPeriodTable = $this->createHierarchicalSerializedTable('/legacy-b', 6, 2);

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
                [Archiver::PAGE_URLS_FLAT_RECORD_NAME],
                [Archiver::PAGE_URLS_RECORD_NAME],
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

            $flatResult = DataTable::fromSerializedArray(
                $this->getRootBlobFromInsertedRecord($insertedBlobs[$flatRecordName], $flatRecordName)
            );
            $flatRowA = $flatResult->getRowFromLabel('/flat-a');
            $this->assertNotFalse($flatRowA);
            $this->assertSame(4, $flatRowA->getColumn('nb_hits'));

            $flatRowB = $flatResult->getRowFromLabel('/legacy-b');
            $this->assertNotFalse($flatRowB);
            $this->assertSame(6, $flatRowB->getColumn('nb_hits'));

            $flatSummary = $flatResult->getRowFromId(DataTable::ID_SUMMARY_ROW);
            $this->assertNotFalse($flatSummary);
            $this->assertSame(2, $flatSummary->getColumn('nb_hits'));
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

        $recordBuilder = new class extends ActionReports {
            public function buildHierarchyFromFlatForTest(DataTable $flat): DataTable
            {
                return $this->buildHierarchicalTableFromFlatTable(
                    $flat,
                    null,
                    function (Row $flatRow) {
                        $path = $flatRow->getMetadata(ArchivingHelper::ACTION_FLAT_PATH_METADATA_NAME);
                        return is_array($path) && !empty($path) ? $path : null;
                    }
                );
            }
        };

        $rebuilt = $recordBuilder->buildHierarchyFromFlatForTest($flat);
        $rebuiltSummary = $rebuilt->getRowFromId(DataTable::ID_SUMMARY_ROW);
        $this->assertNotFalse($rebuiltSummary);
        $this->assertSame(12, $rebuiltSummary->getColumn('nb_hits'));
    }

    public function testPageUrlFlatRowsDeduplicateUsingNormalizedUrl()
    {
        $table = new DataTable();

        $firstRow = $this->invokeGetFlatActionRow(
            'EXAMPLE.org/shared/path#',
            Action::TYPE_PAGE_URL,
            PageUrl::$urlPrefixMap['http://'],
            $table
        );
        $secondRow = $this->invokeGetFlatActionRow(
            'example.org/shared/path',
            Action::TYPE_PAGE_URL,
            PageUrl::$urlPrefixMap['http://'],
            $table
        );

        $this->assertSame($firstRow, $secondRow);
        $this->assertSame('/shared/path', $firstRow->getColumn('label'));
        $this->assertSame(1, $table->getRowsCount());
        $this->assertFalse($firstRow->getMetadata(ArchivingHelper::ACTION_FLAT_PATH_METADATA_NAME));
    }

    public function testPageUrlFlatRowsUseUrlPrefixToBuildCanonicalPathLabel()
    {
        $table = new DataTable();

        $row = $this->invokeGetFlatActionRow(
            'example.org/page',
            Action::TYPE_PAGE_URL,
            PageUrl::$urlPrefixMap['http://www.'],
            $table
        );

        $this->assertSame('/page', $row->getColumn('label'));
        $this->assertSame(1, $table->getRowsCount());
    }

    public function testPageUrlFlatRowsTreatStringActionTypeAsPageUrl()
    {
        $table = new DataTable();

        $row = $this->invokeGetFlatActionRow(
            'example.org/page',
            '1',
            PageUrl::$urlPrefixMap['http://www.'],
            $table
        );

        $this->assertSame('/page', $row->getColumn('label'));
        $this->assertSame(1, $table->getRowsCount());
    }

    public function testMergingFlatRowsUsesLastUrlMetadata()
    {
        $destination = new Row([
            Row::COLUMNS => ['label' => '/page', 'nb_hits' => 1],
        ]);
        $destination->setMetadata('url', 'http://example.org/first');

        $source = new Row([
            Row::COLUMNS => ['label' => '/page', 'nb_hits' => 1],
        ]);
        $source->setMetadata('url', 'http://example.org/last');

        $method = new \ReflectionMethod(ArchivingHelper::class, 'mergeRowIntoDestination');
        $method->setAccessible(true);
        $method->invoke(null, $source, $destination);

        $this->assertSame('http://example.org/last', $destination->getMetadata('url'));
    }

    public function testUnknownPageUrlFlatLabelRebuildsWithoutLeadingSlash()
    {
        $flatRow = new Row([
            Row::COLUMNS => [
                'label' => ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_URL),
                'nb_hits' => 1,
            ],
        ]);

        $recordBuilder = new ActionReports();
        $method = new \ReflectionMethod(ActionReports::class, 'flatRowToHierarchyPath');
        $method->setAccessible(true);
        $path = $method->invoke($recordBuilder, $flatRow, Action::TYPE_PAGE_URL);

        $this->assertSame([ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_URL)], $path);
    }

    public function testPageUrlFlatLabelRebuildPreservesTrailingIndexAtLevelLimit()
    {
        $this->withActionCategoryLevelLimit(10, function () {
            $flatRow = new Row([
                Row::COLUMNS => [
                    'label' => '/this/is/not/the/page/i/am/looking/for/index',
                    'nb_hits' => 1,
                ],
            ]);

            $recordBuilder = new ActionReports();
            $method = new \ReflectionMethod(ActionReports::class, 'flatRowToHierarchyPath');
            $method->setAccessible(true);
            $path = $method->invoke($recordBuilder, $flatRow, Action::TYPE_PAGE_URL);

            $this->assertSame(
                ['this', 'is', 'not', 'the', 'page', 'i', 'am', 'looking', 'for', '/index'],
                $path
            );
        });
    }

    public function testHierarchyBuiltFromCanonicalFlatUrlLabelMatchesFlatRowCount()
    {
        $flat = new DataTable();

        $firstRow = new Row([Row::COLUMNS => ['label' => '/shared/path', 'nb_hits' => 4]]);
        $flat->addRow($firstRow);

        $recordBuilder = new class extends ActionReports {
            public function buildUrlHierarchyFromFlatForTest(DataTable $flat): DataTable
            {
                return $this->buildHierarchicalTableFromFlatTable(
                    $flat,
                    null,
                    [$this, 'flatRowToUrlHierarchyPath']
                );
            }
        };

        $rebuilt = $recordBuilder->buildUrlHierarchyFromFlatForTest($flat);

        $this->assertSame(1, $flat->getRowsCount());
        $this->assertSame(1, $rebuilt->getRowsCount());

        $parentRow = $rebuilt->getRows()[0] ?? null;
        $this->assertInstanceOf(Row::class, $parentRow);
        $this->assertSame('shared', $parentRow->getColumn('label'));

        $leafTable = $parentRow->getSubtable();
        $this->assertInstanceOf(DataTable::class, $leafTable);

        $leafRow = $leafTable->getRows()[0] ?? null;
        $this->assertInstanceOf(Row::class, $leafRow);
        $this->assertSame('/path', $leafRow->getColumn('label'));
        $this->assertSame(4, $leafRow->getColumn('nb_hits'));
    }

    public function testPageTitleFlatLabelCanBeRebuiltIntoTheSameHierarchy()
    {
        $this->withTitleDelimiter(' / ', function () {
            $flat = new DataTable();
            $flat->addRow(new Row([Row::COLUMNS => ['label' => 'Parent / Child', 'nb_hits' => 3]]));

            $recordBuilder = new class extends ActionReports {
                public function buildTitleHierarchyFromFlatForTest(DataTable $flat): DataTable
                {
                    return $this->buildHierarchicalTableFromFlatTable(
                        $flat,
                        null,
                        [$this, 'flatRowToTitleHierarchyPath']
                    );
                }
            };

            $rebuilt = $recordBuilder->buildTitleHierarchyFromFlatForTest($flat);

            $parentRow = $rebuilt->getRows()[0] ?? null;
            $this->assertInstanceOf(Row::class, $parentRow);
            $this->assertSame('Parent', $parentRow->getColumn('label'));

            $leafTable = $parentRow->getSubtable();
            $this->assertInstanceOf(DataTable::class, $leafTable);

            $leafRow = $leafTable->getRows()[0] ?? null;
            $this->assertInstanceOf(Row::class, $leafRow);
            $this->assertSame(' Child', $leafRow->getColumn('label'));
            $this->assertSame(3, $leafRow->getColumn('nb_hits'));
        });
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

    private function withTitleDelimiter(string $titleDelimiter, callable $callback): void
    {
        $config = Config::getInstance();
        $hadPreviousDelimiter = array_key_exists('action_title_category_delimiter', $config->General);
        $previousDelimiter = $hadPreviousDelimiter ? $config->General['action_title_category_delimiter'] : null;

        $config->General['action_title_category_delimiter'] = $titleDelimiter;
        ArchivingHelper::reloadConfig();

        try {
            $callback();
        } finally {
            if ($hadPreviousDelimiter) {
                $config->General['action_title_category_delimiter'] = $previousDelimiter;
            } else {
                unset($config->General['action_title_category_delimiter']);
            }
            ArchivingHelper::reloadConfig();
        }
    }

    private function withActionCategoryLevelLimit(int $levelLimit, callable $callback): void
    {
        $config = Config::getInstance();
        $hadPreviousLimit = array_key_exists('action_category_level_limit', $config->General);
        $previousLimit = $hadPreviousLimit ? $config->General['action_category_level_limit'] : null;

        $config->General['action_category_level_limit'] = $levelLimit;
        ArchivingHelper::reloadConfig();

        try {
            $callback();
        } finally {
            if ($hadPreviousLimit) {
                $config->General['action_category_level_limit'] = $previousLimit;
            } else {
                unset($config->General['action_category_level_limit']);
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

    private function invokeGetFlatActionRow(string $actionName, int $actionType, int $urlPrefix, DataTable $table): Row
    {
        $reflection = new \ReflectionMethod(ArchivingHelper::class, 'getFlatActionRow');
        $reflection->setAccessible(true);

        return $reflection->invoke(null, $actionName, $actionType, $urlPrefix, $table);
    }
}
