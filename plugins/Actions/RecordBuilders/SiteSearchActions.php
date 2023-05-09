<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\DataTable;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugins\Actions\Archiver;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Actions\Metrics;
use Piwik\Tracker\Action;

class SiteSearchActions extends Base
{

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor)
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::SITE_SEARCH_RECORD_NAME)
                ->setMaxRowsInTable(ArchivingHelper::$maximumRowsInDataTableSiteSearch)
                ->setMaxRowsInSubtable(ArchivingHelper::$maximumRowsInSubDataTable)
                ->setColumnToSortByBeforeTruncation(ArchivingHelper::$columnToSortByBeforeTruncation)
                ->setColumnToRenameAfterAggregation(Metrics::$columnsToRenameAfterAggregation),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_SEARCHES_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_KEYWORDS_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::SITE_SEARCH_RECORD_NAME),
        ];

        // TODO: handle $countRowsRecursive in RecordBuilder
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor)
    {
        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        ArchivingHelper::reloadConfig(); // TODO: why is this even?

        $tablesByType = [Action::TYPE_SITE_SEARCH => new DataTable()];

        $rankingQueryLimit = max($rankingQueryLimit, ArchivingHelper::$maximumRowsInDataTableSiteSearch);
        $this->archiveDayActions($archiveProcessor, $rankingQueryLimit, $tablesByType, false);

        $dataTable = $tablesByType[Action::TYPE_SITE_SEARCH];
        $this->deleteUnusedColumnsFromKeywordsDataTable($dataTable);

        $nbSearches = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS));
        $nbKeywords = $dataTable->getRowsCount();

        return [
            Archiver::SITE_SEARCH_RECORD_NAME => $dataTable,
            Archiver::METRIC_SEARCHES_RECORD_NAME => $nbSearches,
            Archiver::METRIC_KEYWORDS_RECORD_NAME => $nbKeywords,
        ];
    }

    protected function deleteUnusedColumnsFromKeywordsDataTable(DataTable $dataTable)
    {
        $columnsToDelete = array(
            PiwikMetrics::INDEX_NB_UNIQ_VISITORS,
            PiwikMetrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS,
            PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
            PiwikMetrics::INDEX_PAGE_ENTRY_NB_ACTIONS,
            PiwikMetrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH,
            PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS,
            PiwikMetrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT,
            PiwikMetrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
        );
        $dataTable->deleteColumns($columnsToDelete);
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor)
    {
        return $archiveProcessor->getParams()->getSite()->isSiteSearchEnabled();
    }
}