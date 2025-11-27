<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Referrers\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Referrers\Archiver;
use Piwik\RankingQuery;
use Piwik\Tracker\PageUrl;

class AIReferrers extends RecordBuilder
{
    private $rankingQueryLimit;

    public function __construct()
    {
        parent::__construct();

        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;

        // Reading pre 2.0 config file settings
        $this->maxRowsInTable    = @Config::getInstance()->General['datatable_archiving_maximum_rows_referers'];
        $this->maxRowsInSubtable = @Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referers'];
        if (empty($this->maxRowsInTable)) {
            $this->maxRowsInTable    = Config::getInstance()->General['datatable_archiving_maximum_rows_referrers'];
            $this->maxRowsInSubtable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referrers'];
        }
        $this->rankingQueryLimit = $this->getRankingQueryLimit();
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DISTINCT_AI_ASSISTANT_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $records = [];
        foreach ($this->getRecordNames() as $record) {
            $records[$record] = new DataTable();
        }

        $logAggregator = $archiveProcessor->getLogAggregator();

        $this->aggregateFromVisits($logAggregator, $records[Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME], 'visit_entry_idaction_url');
        $this->aggregateFromVisits($logAggregator, $records[Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME], 'visit_entry_idaction_name');

        $this->aggregateFromConversions($logAggregator, $records, ["referer_name"]);

        $records[Archiver::METRIC_DISTINCT_AI_ASSISTANT_RECORD_NAME] = count($records[Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME]->getRows());

        return $records;
    }

    /**
     * @return string[]
     */
    protected function getRecordNames(): array
    {
        return [
            Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME,
            Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME,
        ];
    }

    protected function aggregateFromVisits(LogAggregator $logAggregator, DataTable $report, string $field): void
    {
        $resultSet = $this->queryAIReferrerEntryPages($logAggregator, $field);

        $actionRows = [];

        while ($row = $resultSet->fetch()) {
            if (!isset($row[Metrics::INDEX_NB_VISITS])) {
                return;
            }

            $label = $row['referer_name'];

            $pageUrlOrTitle = $row['action_name'];

            if (is_null($label)) {
                continue;
            }

            if (!is_null($pageUrlOrTitle)) {
                $actionRows[] = $row;
                continue;
            }

            $report->sumRowWithLabel($label, $this->makeVisitRow($row));
        }

        foreach ($actionRows as $row) {
            if (!isset($row[Metrics::INDEX_NB_VISITS])) {
                return;
            }

            $label          = $row['referer_name'];
            $pageUrlOrTitle = $row['action_name'];

            $tableRow = $report->getRowFromLabel($label);

            if (empty($tableRow)) {
                continue;
            }

            if ($field === 'visit_entry_idaction_url') {
                // make sure we always work with normalized URL no matter how the individual action stores it
                $normalized     = PageUrl::normalizeUrl($pageUrlOrTitle);
                $pageUrlOrTitle = $normalized['url'];
            }

            $tableRow->sumRowWithLabelToSubtable($pageUrlOrTitle, $this->makeVisitRow($row));
        }
    }


    /**
     * @param array<string|int, scalar> $row
     * @return array<int, float>
     */
    protected function makeVisitRow(array $row): array
    {
        $metricIds = [
            Metrics::INDEX_NB_UNIQ_VISITORS,
            Metrics::INDEX_NB_VISITS,
            Metrics::INDEX_NB_ACTIONS,
            Metrics::INDEX_NB_USERS,
            Metrics::INDEX_MAX_ACTIONS,
            Metrics::INDEX_SUM_VISIT_LENGTH,
            Metrics::INDEX_BOUNCE_COUNT,
            Metrics::INDEX_NB_VISITS_CONVERTED,
        ];

        $columns = [];
        foreach ($metricIds as $id) {
            $columns[$id] = (float)($row[$id] ?? 0);
        }

        return $columns;
    }

    /**
     * @param DataTable[] $reports
     */
    protected function aggregateFromConversions(LogAggregator $logAggregator, array $reports, array $dimensions): void
    {
        $where = '%s.referer_type = ' . Common::REFERRER_TYPE_AI_ASSISTANT;
        $query = $logAggregator->queryConversionsByDimension($dimensions, $where);

        while ($row = $query->fetch()) {
            $idGoal  = (int)$row['idgoal'];
            $columns = [
                Metrics::INDEX_GOALS => [
                    $idGoal => Metrics::makeGoalColumnsRow($idGoal, $row),
                ],
            ];

            $this->aggregateConversionRow($row, $reports, $columns);
        }

        foreach ($reports as $dataTable) {
            $dataTable->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);
        }
    }

    /**
     * @param DataTable[] $reports
     */
    protected function aggregateConversionRow(array $row, array $reports, array $columns): void
    {
        $reports[Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
        $reports[Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME]->sumRowWithLabel($row['referer_name'], $columns);
    }

    protected function queryAIReferrerEntryPages(LogAggregator $logAggregator, string $actionIdField)
    {
        $metricsConfig = $logAggregator->getVisitsMetricFields();
        $select        = "log_visit.referer_name, COALESCE(log_action.name, '') as action_name";

        $select = $this->addMetricsToSelect($select, $metricsConfig);

        $from = [
            "log_visit",
            [
                "table"  => "log_action",
                "joinOn" => "log_visit.$actionIdField = log_action.idaction",
            ],
        ];

        $where = $logAggregator->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= ' AND log_visit.referer_type = ' . Common::REFERRER_TYPE_AI_ASSISTANT;

        $groupBy = "log_visit.referer_name, action_name";
        $orderBy = "`" . Metrics::INDEX_NB_VISITS . "` DESC";

        // get query with segmentation
        $query = $logAggregator->generateQuery(
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            0,
            0,
            true
        );

        if ($this->rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($this->rankingQueryLimit);
            $rankingQuery->addLabelColumn(['referer_name', 'action_name']);

            $rankingQuery->addColumn(array_keys($metricsConfig), 'sum');

            foreach ($metricsConfig as $column => $config) {
                if (empty($config['aggregation'])) {
                    continue;
                }
                $rankingQuery->addColumn($column, $config['aggregation']);
            }


            $query['sql'] = $rankingQuery->generateRankingQuery($query['sql'], true);
        }

        $db = $logAggregator->getDb();
        return $db->query($query['sql'], $query['bind']);
    }

    private function getRankingQueryLimit(): int
    {
        $configGeneral = Config::getInstance()->General;
        $configLimit   = max($configGeneral['archiving_ranking_query_row_limit'], 10 * $this->maxRowsInTable);
        return $configLimit == 0 ? 0 : max($configLimit, $this->maxRowsInTable);
    }

    /**
     * @param array<int, array{aggregation: string, query: string}> $metricsConfig
     */
    private function addMetricsToSelect(string $select, array $metricsConfig): string
    {
        if (!empty($metricsConfig)) {
            foreach ($metricsConfig as $metric => $config) {
                $select .= ', ' . $config['query'] . " as `" . $metric . "`";
            }
        }

        return $select;
    }
}
