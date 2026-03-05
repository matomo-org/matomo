<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Unit;

use Piwik\ArchiveProcessor;
use Piwik\DataTable;
use Piwik\DataTable\Row;
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
        $previousFlatLimit = ArchivingHelper::$maximumRowsInDataTableFlat;
        try {
            ArchivingHelper::$maximumRowsInDataTableFlat = 0;

            $recordBuilder = new ActionReports();
            $records = $recordBuilder->getRecordMetadata($this->createMock(ArchiveProcessor::class));
            $recordNames = array_map(function ($record) {
                return $record->getName();
            }, $records);

            $this->assertNotContains(Archiver::PAGE_URLS_FLAT_RECORD_NAME, $recordNames);
            $this->assertNotContains(Archiver::PAGE_TITLES_FLAT_RECORD_NAME, $recordNames);
        } finally {
            ArchivingHelper::$maximumRowsInDataTableFlat = $previousFlatLimit;
        }
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
}
