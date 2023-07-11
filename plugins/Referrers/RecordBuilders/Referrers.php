<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Referrers\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Referrers\API;
use Piwik\Plugins\Referrers\Archiver;

class Referrers extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;

        // Reading pre 2.0 config file settings
        $this->maxRowsInTable = @Config::getInstance()->General['datatable_archiving_maximum_rows_referers'];
        $this->maxRowsInSubtable = @Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referers'];
        if (empty($this->maxRowsInTable)) {
            $this->maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_referrers'];
            $this->maxRowsInSubtable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referrers'];
        }
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::SEARCH_ENGINES_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::SOCIAL_NETWORKS_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::KEYWORDS_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::CAMPAIGNS_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::WEBSITES_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::REFERRER_TYPE_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::SEARCH_ENGINES_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::SOCIAL_NETWORKS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::KEYWORDS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::CAMPAIGNS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::WEBSITES_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_URLS_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::WEBSITES_RECORD_NAME, true)
                // for non day periods, set the count to the count of the recursive row count - the toplevel row count (which is just the domains)
                ->setMultiplePeriodTransform(function (float $value, array $counts) {
                    return $value - $counts['level0'];
                }),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $records = [];
        foreach ($this->getRecordNames() as $record) {
            $records[$record] = new DataTable();
        }

        $distinctUrls = [];

        $logAggregator = $archiveProcessor->getLogAggregator();

        $this->aggregateFromVisits($logAggregator, $records, $distinctUrls, ["referer_type", "referer_name", "referer_keyword", "referer_url"]);
        $this->aggregateFromConversions($logAggregator, $records, ["referer_type", "referer_name", "referer_keyword"]);

        $numericRecords = [
            Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME => count($records[Archiver::SEARCH_ENGINES_RECORD_NAME]->getRows()),
            Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME => count($records[Archiver::SOCIAL_NETWORKS_RECORD_NAME]->getRows()),
            Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME => count($records[Archiver::KEYWORDS_RECORD_NAME]->getRows()),
            Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME => count($records[Archiver::CAMPAIGNS_RECORD_NAME]->getRows()),
            Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME => count($records[Archiver::WEBSITES_RECORD_NAME]->getRows()),
            Archiver::METRIC_DISTINCT_URLS_RECORD_NAME => count($distinctUrls),
        ];

        $records = array_merge($records, $numericRecords);

        return $records;
    }

    protected function getRecordNames()
    {
        return [
            Archiver::REFERRER_TYPE_RECORD_NAME,
            Archiver::KEYWORDS_RECORD_NAME,
            Archiver::SEARCH_ENGINES_RECORD_NAME,
            Archiver::SOCIAL_NETWORKS_RECORD_NAME,
            Archiver::WEBSITES_RECORD_NAME,
            Archiver::CAMPAIGNS_RECORD_NAME,
        ];
    }

    private function aggregateFromVisits(LogAggregator $logAggregator, array $reports, array &$distinctUrls, array $fields): void
    {
        $query = $logAggregator->queryVisitsByDimension($fields);
        while ($row = $query->fetch()) {
            $this->makeReferrerTypeNonEmpty($row);
            $this->aggregateVisitRow($row, $reports, $distinctUrls);
        }
    }

    protected function makeReferrerTypeNonEmpty(&$row): void
    {
        if (empty($row['referer_type'])) {
            $row['referer_type'] = Common::REFERRER_TYPE_DIRECT_ENTRY;
        }
    }

    /**
     * @param DataTable[] $reports
     */
    protected function aggregateVisitRow($row, array $reports, array &$distinctUrls): void
    {
        $columns = [
            Metrics::INDEX_NB_UNIQ_VISITORS => (int)$row[Metrics::INDEX_NB_UNIQ_VISITORS],
            Metrics::INDEX_NB_VISITS => (int)$row[Metrics::INDEX_NB_VISITS],
            Metrics::INDEX_NB_ACTIONS => (int)$row[Metrics::INDEX_NB_ACTIONS],
            Metrics::INDEX_NB_USERS => (int)$row[Metrics::INDEX_NB_USERS],
            Metrics::INDEX_MAX_ACTIONS => (int)$row[Metrics::INDEX_MAX_ACTIONS],
            Metrics::INDEX_SUM_VISIT_LENGTH => (int)$row[Metrics::INDEX_SUM_VISIT_LENGTH],
            Metrics::INDEX_BOUNCE_COUNT => (int)$row[Metrics::INDEX_BOUNCE_COUNT],
            Metrics::INDEX_NB_VISITS_CONVERTED => (int)$row[Metrics::INDEX_NB_VISITS_CONVERTED],
        ];

        switch ($row['referer_type']) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                if (empty($row['referer_keyword'])) {
                    $row['referer_keyword'] = API::LABEL_KEYWORD_NOT_DEFINED;
                }
                $searchEnginesArray = $reports[Archiver::SEARCH_ENGINES_RECORD_NAME];
                $topLevelRow = $searchEnginesArray->sumRowWithLabel($row['referer_name'], $columns);
                $topLevelRow->sumRowWithLabelToSubtable($row['referer_keyword'], $columns);

                $keywordsDataArray = $reports[Archiver::KEYWORDS_RECORD_NAME];
                $topLevelRow = $keywordsDataArray->sumRowWithLabel($row['referer_keyword'], $columns);
                $topLevelRow->sumRowWithLabelToSubtable($row['referer_name'], $columns);
                break;

            case Common::REFERRER_TYPE_SOCIAL_NETWORK:
                $topLevelRow = $reports[Archiver::SOCIAL_NETWORKS_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                $topLevelRow->sumRowWithLabelToSubtable($row['referer_url'], $columns);
                break;

            case Common::REFERRER_TYPE_WEBSITE:
                $topLevelRow = $reports[Archiver::WEBSITES_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                $topLevelRow->sumRowWithLabelToSubtable($row['referer_url'], $columns);

                $urlHash = substr(md5($row['referer_url']), 0, 10);
                if (!isset($distinctUrls[$urlHash])) {
                    $distinctUrls[$urlHash] = true;
                }
                break;

            case Common::REFERRER_TYPE_CAMPAIGN:
                $topLevelRow = $reports[Archiver::CAMPAIGNS_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                if (!empty($row['referer_keyword'])) {
                    $topLevelRow->sumRowWithLabelToSubtable($row['referer_keyword'], $columns);
                }
                break;

            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                // direct entry are aggregated below in $this->metricsByType array
                break;

            default:
                throw new \Exception("Non expected referer_type = " . $row['referer_type']);
        }
        $reports[Archiver::REFERRER_TYPE_RECORD_NAME]->sumRowWithLabel($row['referer_type'], $columns);
    }

    /**
     * @param DataTable[] $reports
     */
    protected function aggregateFromConversions(LogAggregator $logAggregator, array $reports, array $dimensions): void
    {
        $query = $logAggregator->queryConversionsByDimension($dimensions);
        while ($row = $query->fetch()) {
            $this->makeReferrerTypeNonEmpty($row);

            $idGoal = $row['idgoal'];
            $columns = [
                Metrics::INDEX_GOALS => [
                    $idGoal => Metrics::makeGoalColumnsRow($idGoal, $row),
                ],
            ];

            $skipAggregateByType = $this->aggregateConversionRow($row, $reports, $columns);
            if (!$skipAggregateByType) {
                $reports[Archiver::REFERRER_TYPE_RECORD_NAME]->sumRowWithLabel($row['referer_type'], $columns);
            }
        }

        foreach ($reports as $dataTable) {
            $dataTable->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);
        }
    }

    /**
     * @param DataTable[] $reports
     */
    protected function aggregateConversionRow(array $row, array $reports, array $columns): bool
    {
        $skipAggregateByType = false;
        switch ($row['referer_type']) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                if (empty($row['referer_keyword'])) {
                    $row['referer_keyword'] = API::LABEL_KEYWORD_NOT_DEFINED;
                }

                $reports[Archiver::SEARCH_ENGINES_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                $reports[Archiver::KEYWORDS_RECORD_NAME]->sumRowWithLabel($row['referer_keyword'], $columns);
                break;

            case Common::REFERRER_TYPE_SOCIAL_NETWORK:
                $reports[Archiver::SOCIAL_NETWORKS_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                break;

            case Common::REFERRER_TYPE_WEBSITE:
                $reports[Archiver::WEBSITES_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                break;

            case Common::REFERRER_TYPE_CAMPAIGN:
                $topLevelRow = $reports[Archiver::CAMPAIGNS_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
                if (!empty($row['referer_keyword'])) {
                    $topLevelRow->sumRowWithLabelToSubtable($row['referer_keyword'], $columns);
                }
                break;

            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                // Direct entry, no sub dimension
                break;

            default:
                // The referer type is user submitted for goal conversions, we ignore any malformed value
                // Continue to the next while iteration
                $skipAggregateByType = true;
                break;
        }
        return $skipAggregateByType;
    }
}
