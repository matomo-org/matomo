<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\Actions\Metrics as ActionsMetrics;
use Piwik\Plugins\CustomDimensions\Archiver;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\RankingQuery;
use Piwik\Tracker;

class CustomDimension extends RecordBuilder
{
    /**
     * @var array
     */
    private $dimensionInfo;

    /**
     * @var int
     */
    private $rankingQueryLimit;

    public function __construct(array $dimensionInfo)
    {
        parent::__construct();

        $this->dimensionInfo = $dimensionInfo;

        $this->maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_custom_dimensions'];
        $this->maxRowsInSubtable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_custom_dimensions'];
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->rankingQueryLimit = $this->getRankingQueryLimit();
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $recordName = Archiver::buildRecordNameForCustomDimensionId($this->dimensionInfo['idcustomdimension']);
        return [
            Record::make(Record::TYPE_BLOB, $recordName),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $dimension = $this->dimensionInfo;
        if (!$dimension['active']) { // sanity check
            return [];
        }

        $logAggregator = $archiveProcessor->getLogAggregator();

        $report = new DataTable();
        $recordName = Archiver::buildRecordNameForCustomDimensionId($dimension['idcustomdimension']);

        $valueField = LogTable::buildCustomDimensionColumnName($dimension);
        $dimensions = [$valueField];

        if ($dimension['scope'] === CustomDimensions::SCOPE_VISIT) {
            $this->aggregateFromVisits($report, $logAggregator, $valueField, $dimensions, " log_visit.$valueField is not null");
            $this->aggregateFromConversions($report, $logAggregator, $valueField, $dimensions, " log_conversion.$valueField is not null");
        } elseif ($dimension['scope'] === CustomDimensions::SCOPE_ACTION) {
            $this->aggregateFromActions($report, $logAggregator, $valueField);
        }

        $report->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);

        return [
            $recordName => $report,
        ];
    }

    private function aggregateFromVisits(
        DataTable $report,
        LogAggregator $logAggregator,
        string $valueField,
        array $dimensions,
        string $where
    ): void {
        if ($this->rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($this->rankingQueryLimit);
            $rankingQuery->addLabelColumn($dimensions[0]);

            $query = $logAggregator->queryVisitsByDimension($dimensions, $where, [], false, $rankingQuery, false, -1,
                $rankingQueryGenerate = true);
        } else {
            $query = $logAggregator->queryVisitsByDimension($dimensions, $where);
        }

        $defaultColumns = [
            Metrics::INDEX_NB_UNIQ_VISITORS    => 0,
            Metrics::INDEX_NB_VISITS           => 0,
            Metrics::INDEX_NB_ACTIONS          => 0,
            Metrics::INDEX_NB_USERS            => 0,
            Metrics::INDEX_MAX_ACTIONS         => 0,
            Metrics::INDEX_SUM_VISIT_LENGTH    => 0,
            Metrics::INDEX_BOUNCE_COUNT        => 0,
            Metrics::INDEX_NB_VISITS_CONVERTED => 0,
        ];

        while ($row = $query->fetch()) {
            $customDimensionValue = $this->cleanCustomDimensionValue($row[$valueField]);
            unset($row[$valueField]);

            $columns = $defaultColumns;
            foreach ($row as $name => $columnValue) {
                $columns[$name] = $columnValue;
            }

            $report->sumRowWithLabel($customDimensionValue, $columns);
        }
    }

    private function aggregateFromConversions(
        DataTable $report,
        LogAggregator $logAggregator,
        string $valueField,
        array $dimensions,
        string $where
    ): void {
        if ($this->rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($this->rankingQueryLimit);
            $rankingQuery->addLabelColumn([$dimensions[0], 'idgoal']);

            $query = $logAggregator->queryConversionsByDimension($dimensions, $where, false, [], $rankingQuery, $rankingQueryGenerate = true);
        } else {
            $query = $logAggregator->queryConversionsByDimension($dimensions, $where);
        }

        while ($row = $query->fetch()) {
            $value = $this->cleanCustomDimensionValue($row[$valueField]);
            unset($row[$valueField]);

            if ($value === RankingQuery::LABEL_SUMMARY_ROW) {
                // skip summary row
                continue;
            }

            $idGoal = (int) $row['idgoal'];
            $columns = [
                Metrics::INDEX_GOALS => [
                    $idGoal => Metrics::makeGoalColumnsRow($idGoal, $row),
                ],
            ];

            $report->sumRowWithLabel($value, $columns);
        }
    }

    protected function aggregateFromActions(DataTable $report, LogAggregator $logAggregator, $valueField): void
    {
        $metricsConfig = ActionsMetrics::getActionMetrics();

        $resultSet = $this->queryCustomDimensionActions($metricsConfig, $logAggregator, $valueField);

        $metricIds = array_keys($metricsConfig);
        $metricIds[] = Metrics::INDEX_PAGE_SUM_TIME_SPENT;
        $metricIds[] = Metrics::INDEX_BOUNCE_COUNT;
        $metricIds[] = Metrics::INDEX_PAGE_EXIT_NB_VISITS;

        while ($row = $resultSet->fetch()) {
            if (!isset($row[Metrics::INDEX_NB_VISITS])) {
                return;
            }

            $label = $row[$valueField];
            $label = $this->cleanCustomDimensionValue($label);

            $columns = [];
            foreach ($metricIds as $id) {
                $columns[$id] = (float) ($row[$id] ?? 0);
            }

            $tableRow = $report->sumRowWithLabel($label, $columns);

            $url = $row['url'];
            if (empty($url)) {
                continue;
            }

            // make sure we always work with normalized URL no matter how the individual action stores it
            $normalized = Tracker\PageUrl::normalizeUrl($url);
            $url = $normalized['url'];

            if (empty($url)) {
                continue;
            }

            $tableRow->sumRowWithLabelToSubtable($url, $columns);
        }
    }

    public function queryCustomDimensionActions(array $metricsConfig, LogAggregator $logAggregator, $valueField, $additionalWhere = '')
    {
        $select = "log_link_visit_action.$valueField,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `" . Metrics::INDEX_PAGE_SUM_TIME_SPENT . "`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `" . Metrics::INDEX_BOUNCE_COUNT . "`,
                  sum(IF(log_visit.last_idlink_va = log_link_visit_action.idlink_va, 1, 0)) as `" . Metrics::INDEX_PAGE_EXIT_NB_VISITS . "`";

        $select = $this->addMetricsToSelect($select, $metricsConfig);

        $from = [
            "log_link_visit_action",
            [
                "table"  => "log_visit",
                "joinOn" => "log_visit.idvisit = log_link_visit_action.idvisit"
            ],
            [
                "table"  => "log_action",
                "joinOn" => "log_link_visit_action.idaction_url = log_action.idaction"
            ]
        ];

        $where  = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.$valueField is not null";

        if (!empty($additionalWhere)) {
            $where .= ' AND ' . $additionalWhere;
        }

        $groupBy = "log_link_visit_action.$valueField, url";
        $orderBy = "`" . Metrics::INDEX_PAGE_NB_HITS . "` DESC";

        // get query with segmentation
        $query     = $logAggregator->generateQuery($select, $from, $where, $groupBy, $orderBy);

        if ($this->rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($this->rankingQueryLimit);
            $rankingQuery->addLabelColumn([$valueField, 'url']);

            $sumMetrics = [
                Metrics::INDEX_PAGE_SUM_TIME_SPENT,
                Metrics::INDEX_BOUNCE_COUNT,
                Metrics::INDEX_PAGE_EXIT_NB_VISITS,
                // NOTE: INDEX_NB_UNIQ_VISITORS is summed in LogAggregator's queryActionsByDimension, so we do it here as well
                Metrics::INDEX_NB_UNIQ_VISITORS,
            ];
            $rankingQuery->addColumn($sumMetrics, 'sum');

            foreach ($metricsConfig as $column => $config) {
                if (empty($config['aggregation'])) {
                    continue;
                }
                $rankingQuery->addColumn($column, $config['aggregation']);
            }

            $query['sql'] = $rankingQuery->generateRankingQuery($query['sql']);
        }

        $db        = $logAggregator->getDb();
        $resultSet = $db->query($query['sql'], $query['bind']);

        return $resultSet;
    }

    private function getRankingQueryLimit(): int
    {
        $configGeneral = Config::getInstance()->General;
        $configLimit = max($configGeneral['archiving_ranking_query_row_limit'], 10 * $this->maxRowsInTable);
        $limit = $configLimit == 0 ? 0 : max(
            $configLimit,
            $this->maxRowsInTable
        );
        return $limit;
    }

    protected function cleanCustomDimensionValue(string $value): string
    {
        if (isset($value) && strlen($value)) {
            return $value;
        }

        return Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED;
    }

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
