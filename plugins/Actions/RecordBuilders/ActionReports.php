<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\RecordBuilders;

use Piwik\API\Request;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\Cache;
use Piwik\Config\GeneralConfig;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugins\Actions\Archiver;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Actions\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;

class ActionReports extends ArchiveProcessor\RecordBuilder
{
    public function __construct()
    {
        ArchivingHelper::reloadConfig();

        parent::__construct(
            ArchivingHelper::$maximumRowsInDataTableLevelZero,
            ArchivingHelper::$maximumRowsInSubDataTable,
            ArchivingHelper::$columnToSortByBeforeTruncation,
            null,
            Metrics::$columnsToRenameAfterAggregation
        );
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::SITE_SEARCH_RECORD_NAME)
                ->setMaxRowsInTable(ArchivingHelper::$maximumRowsInDataTableSiteSearch),

            Record::make(Record::TYPE_BLOB, Archiver::PAGE_URLS_RECORD_NAME)
                ->setBlobColumnAggregationOps(Metrics::getColumnsAggregationOperation()),
            Record::make(Record::TYPE_BLOB, Archiver::PAGE_TITLES_RECORD_NAME)
                ->setBlobColumnAggregationOps(Metrics::getColumnsAggregationOperation()),

            Record::make(Record::TYPE_BLOB, Archiver::DOWNLOADS_RECORD_NAME),
            Record::make(Record::TYPE_BLOB, Archiver::OUTLINKS_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_SEARCHES_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_KEYWORDS_RECORD_NAME)
                ->setIsCountOfBlobRecordRows(Archiver::SITE_SEARCH_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_OUTLINKS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_UNIQ_OUTLINKS_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_PAGEVIEWS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_SUM_TIME_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_HITS_TIMED_RECORD_NAME),

            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_DOWNLOADS_RECORD_NAME),
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_UNIQ_DOWNLOADS_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        ArchivingHelper::reloadConfig();

        $tablesByType = $this->makeReportTables();

        $this->archiveDayActions(
            $archiveProcessor,
            $rankingQueryLimit,
            $tablesByType,
            array_diff(array_keys($tablesByType), [Action::TYPE_SITE_SEARCH]),
            true
        );

        if ($archiveProcessor->getParams()->getSite()->isSiteSearchEnabled()) {
            $rankingQueryLimitSiteSearch = max($rankingQueryLimit, ArchivingHelper::$maximumRowsInDataTableSiteSearch);
            $this->archiveDayActions($archiveProcessor, $rankingQueryLimitSiteSearch, $tablesByType, [Action::TYPE_SITE_SEARCH], false);
        }

        $this->archiveDayEntryActions($archiveProcessor->getLogAggregator(), $tablesByType, $rankingQueryLimit);
        $this->archiveDayExitActions($archiveProcessor->getLogAggregator(), $tablesByType, $rankingQueryLimit);
        $this->archiveDayActionsTime($archiveProcessor->getLogAggregator(), $tablesByType, $rankingQueryLimit);
        $this->archiveDayActionsGoals($archiveProcessor, $rankingQueryLimit);

        ArchivingHelper::clearActionsCache();

        $prefix = $archiveProcessor->getParams()->getSite()->getMainUrl();
        $prefix = rtrim($prefix, '/') . '/';
        ArchivingHelper::setFolderPathMetadata($tablesByType[Action::TYPE_PAGE_URL], $isUrl = true, $prefix);

        ArchivingHelper::setFolderPathMetadata($tablesByType[Action::TYPE_PAGE_TITLE], $isUrl = false);

        $dataTable = $tablesByType[Action::TYPE_SITE_SEARCH];
        $this->deleteUnusedColumnsFromKeywordsDataTable($dataTable);

        foreach ($tablesByType as $dataTable) {
            ArchivingHelper::deleteInvalidSummedColumnsFromDataTable($dataTable);
        }

        $nbSearches = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS));
        $nbKeywords = $dataTable->getRowsCount();

        $dataTable = $tablesByType[Action::TYPE_OUTLINK];
        $nbOutlinks = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS));
        $nbUniqOutlinks = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_NB_VISITS));

        $dataTable = $tablesByType[Action::TYPE_PAGE_URL];
        $nbPageviews = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS));
        $nbUniqPageviews = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_NB_VISITS));
        $nbSumTimeGeneration = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_GENERATION));
        $nbHitsWithTimeGeneration = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION));

        $dataTable = $tablesByType[Action::TYPE_DOWNLOAD];
        $nbDownloads = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS));
        $nbUniqDownloads = array_sum($dataTable->getColumn(PiwikMetrics::INDEX_NB_VISITS));

        return [
            // blob records
            Archiver::PAGE_URLS_RECORD_NAME => $tablesByType[Action::TYPE_PAGE_URL],
            Archiver::PAGE_TITLES_RECORD_NAME => $tablesByType[Action::TYPE_PAGE_TITLE],
            Archiver::DOWNLOADS_RECORD_NAME => $tablesByType[Action::TYPE_DOWNLOAD],
            Archiver::OUTLINKS_RECORD_NAME => $tablesByType[Action::TYPE_OUTLINK],
            Archiver::SITE_SEARCH_RECORD_NAME => $tablesByType[Action::TYPE_SITE_SEARCH],

            // numeric records
            Archiver::METRIC_SEARCHES_RECORD_NAME => $nbSearches,
            Archiver::METRIC_KEYWORDS_RECORD_NAME => $nbKeywords,

            Archiver::METRIC_OUTLINKS_RECORD_NAME => $nbOutlinks,
            Archiver::METRIC_UNIQ_OUTLINKS_RECORD_NAME => $nbUniqOutlinks,

            Archiver::METRIC_PAGEVIEWS_RECORD_NAME => $nbPageviews,
            Archiver::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME => $nbUniqPageviews,
            Archiver::METRIC_SUM_TIME_RECORD_NAME => $nbSumTimeGeneration,
            Archiver::METRIC_HITS_TIMED_RECORD_NAME => $nbHitsWithTimeGeneration,

            Archiver::METRIC_DOWNLOADS_RECORD_NAME => $nbDownloads,
            Archiver::METRIC_UNIQ_DOWNLOADS_RECORD_NAME => $nbUniqDownloads,
        ];
    }

    protected function deleteUnusedColumnsFromKeywordsDataTable(DataTable $dataTable): void
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

    private function makeReportTables(): array
    {
        $result = [];
        foreach (Metrics::$actionTypes as $type) {
            $dataTable = new DataTable();
            if ($type === Action::TYPE_SITE_SEARCH) {
                $maxRows = ArchivingHelper::$maximumRowsInDataTableSiteSearch;
            } else {
                $maxRows = ArchivingHelper::$maximumRowsInDataTableLevelZero;
            }
            $dataTable->setMaximumAllowedRows($maxRows);

            if (
                $type == Action::TYPE_PAGE_URL
                || $type == Action::TYPE_PAGE_TITLE
            ) {
                // for page urls and page titles, performance metrics exist and have to be aggregated correctly
                $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, Metrics::getColumnsAggregationOperation());
            }

            $result[$type] = $dataTable;
        }
        return $result;
    }

    protected function archiveDayActions(
        ArchiveProcessor $archiveProcessor,
        int $rankingQueryLimit,
        array $actionsTablesByType,
        $actionTypes,
        bool $includePageNotDefined
    ): void {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $metricsConfig = Metrics::getActionMetrics();

        $select = "log_action.name,
                log_action.type,
                log_action.idaction,
                log_action.url_prefix";

        $select = $this->addMetricsToSelect($select, $metricsConfig);

        $from = array(
            "log_link_visit_action",
            array(
                "table"  => "log_action",
                "joinOn" => "log_link_visit_action.%s = log_action.idaction"
            )
        );

        $where  = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.%s IS NOT NULL"
            . $this->getWhereClauseActionIsNotEvent();

        $actionTypesWhere = "log_action.type IN (" . implode(", ", $actionTypes) . ")";
        if ($includePageNotDefined) {
            $actionTypesWhere = "(" . $actionTypesWhere . " OR log_action.type IS NULL)";
        }
        $where .= " AND $actionTypesWhere";

        $groupBy = "log_link_visit_action.%s";
        $orderBy = "`" . PiwikMetrics::INDEX_PAGE_NB_HITS . "` DESC, name ASC";

        $siteSearchEnabled = $archiveProcessor->getParams()->getSite()->isSiteSearchEnabled();

        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(array('idaction', 'name'));
            $rankingQuery->addColumn('url_prefix');

            if ($siteSearchEnabled) {
                $rankingQuery->addColumn(PiwikMetrics::INDEX_SITE_SEARCH_HAS_NO_RESULT, 'min');
                $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS, 'sum');
            }

            $this->addMetricsToRankingQuery($rankingQuery, $metricsConfig);

            $rankingQuery->partitionResultIntoMultipleGroups('type', $actionTypes);
        }

        // Special Magic to get
        // 1) No result Keywords
        // 2) For each page view, count number of times the referrer page was a Site Search
        if ($siteSearchEnabled) {
            $this->updateQuerySelectFromForSiteSearch($select, $from);
        }

        $this->archiveDayQueryProcess($logAggregator, $actionsTablesByType, $select, $from, $where, $groupBy, $orderBy, "idaction_name", $rankingQuery, $metricsConfig);
        $this->archiveDayQueryProcess($logAggregator, $actionsTablesByType, $select, $from, $where, $groupBy, $orderBy, "idaction_url", $rankingQuery, $metricsConfig);
    }

    /**
     * Entry actions for Page URLs and Page names
     */
    protected function archiveDayEntryActions(LogAggregator $logAggregator, array $actionsTablesByType, int $rankingQueryLimit)
    {
        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS);
            $rankingQuery->addColumn(array(PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS,
                PiwikMetrics::INDEX_PAGE_ENTRY_NB_ACTIONS,
                PiwikMetrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH,
                PiwikMetrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT), 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($actionsTablesByType));

            $extraSelects = 'log_action.type, log_action.name,';
            $from = array(
                "log_visit",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_visit.%s = log_action.idaction"
                )
            );
            $orderBy = "`" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_ACTIONS . "` DESC, log_action.name ASC";
        } else {
            $extraSelects = false;
            $from = "log_visit";
            $orderBy = false;
        }

        $select = "log_visit.%s as idaction, $extraSelects
                count(distinct log_visit.idvisitor) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS . "`,
                count(*) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_VISITS . "`,
                sum(log_visit.visit_total_actions) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_NB_ACTIONS . "`,
                sum(log_visit.visit_total_time) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH . "`,
                sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `" . PiwikMetrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT . "`";

        $where  = $logAggregator->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= " AND log_visit.%s > 0";

        $groupBy = "log_visit.%s";

        $this->archiveDayQueryProcess(
            $logAggregator,
            $actionsTablesByType,
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            "visit_entry_idaction_url",
            $rankingQuery
        );

        $this->archiveDayQueryProcess(
            $logAggregator,
            $actionsTablesByType,
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            "visit_entry_idaction_name",
            $rankingQuery
        );
    }

    /**
     * Exit actions
     */
    protected function archiveDayExitActions(LogAggregator $logAggregator, array $actionsTablesByType, int $rankingQueryLimit)
    {
        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS);
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_EXIT_NB_VISITS, 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($actionsTablesByType));

            $extraSelects = 'log_action.type, log_action.name,';
            $from = array(
                "log_visit",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_visit.%s = log_action.idaction"
                )
            );
            $orderBy = "`" . PiwikMetrics::INDEX_PAGE_EXIT_NB_VISITS . "` DESC, log_action.name ASC";
        } else {
            $extraSelects = false;
            $from = "log_visit";
            $orderBy = false;
        }

        $select = "log_visit.%s as idaction, $extraSelects
                count(distinct log_visit.idvisitor) as `" . PiwikMetrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS . "`,
                count(*) as `" . PiwikMetrics::INDEX_PAGE_EXIT_NB_VISITS . "`";

        $where  = $logAggregator->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= " AND log_visit.%s > 0";

        $groupBy = "log_visit.%s";

        $this->archiveDayQueryProcess(
            $logAggregator,
            $actionsTablesByType,
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            "visit_exit_idaction_url",
            $rankingQuery
        );

        $this->archiveDayQueryProcess(
            $logAggregator,
            $actionsTablesByType,
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            "visit_exit_idaction_name",
            $rankingQuery
        );

        return array($rankingQuery, $extraSelects, $from, $orderBy, $select, $where, $groupBy);
    }

    /**
     * Time per action
     */
    protected function archiveDayActionsTime(LogAggregator $logAggregator, array $actionsTablesByType, int $rankingQueryLimit)
    {
        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_SPENT, 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($actionsTablesByType));

            $extraSelects = "log_action.type, log_action.name, count(*) as `" . PiwikMetrics::INDEX_PAGE_NB_HITS . "`,";
            $from = array(
                "log_link_visit_action",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_link_visit_action.%s = log_action.idaction"
                )
            );
            $orderBy = "`" . PiwikMetrics::INDEX_PAGE_NB_HITS . "` DESC, log_action.name ASC";
        } else {
            $extraSelects = false;
            $from = "log_link_visit_action";
            $orderBy = false;
        }

        $select = "log_link_visit_action.%s as idaction, $extraSelects
                sum(log_link_visit_action.time_spent_ref_action) as `" . PiwikMetrics::INDEX_PAGE_SUM_TIME_SPENT . "`";

        $where = $logAggregator->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.time_spent_ref_action > 0
                 AND log_link_visit_action.%s > 0"
            . $this->getWhereClauseActionIsNotEvent();

        $groupBy = "log_link_visit_action.%s";

        $this->archiveDayQueryProcess(
            $logAggregator,
            $actionsTablesByType,
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            "idaction_url_ref",
            $rankingQuery
        );

        $this->archiveDayQueryProcess(
            $logAggregator,
            $actionsTablesByType,
            $select,
            $from,
            $where,
            $groupBy,
            $orderBy,
            "idaction_name_ref",
            $rankingQuery
        );
    }

    protected function archiveDayQueryProcess(
        LogAggregator $logAggregator,
        array $actionsTablesByType,
        string $select,
        $from,
        string $where,
        string $groupBy,
        $orderBy,
        string $sprintfField,
        RankingQuery $rankingQuery = null,
        array $metricsConfig = array()
    ): void {
        $select = sprintf($select, $sprintfField);

        // get query with segmentation
        $query = $logAggregator->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // replace the rest of the %s
        $querySql = str_replace("%s", $sprintfField, $query['sql']);

        // apply ranking query
        if ($rankingQuery) {
            $querySql = $rankingQuery->generateRankingQuery($querySql);
        }

        // get result
        $resultSet = $logAggregator->getDb()->query($querySql, $query['bind']);
        ArchivingHelper::updateActionsTableWithRowQuery($resultSet, $sprintfField, $actionsTablesByType, $metricsConfig);
    }

    protected function updateQuerySelectFromForSiteSearch(string &$select, array &$from): void
    {
        $selectFlagNoResultKeywords = ",
                CASE WHEN (MAX(log_link_visit_action.search_count) = 0)
                THEN 1 ELSE 0 END
                    AS `" . PiwikMetrics::INDEX_SITE_SEARCH_HAS_NO_RESULT . "`";

        //we need an extra JOIN to know whether the referrer "idaction_name_ref" was a Site Search request
        $from[] = array(
            "table"      => "log_action",
            "tableAlias" => "log_action_name_ref",
            "joinOn"     => "log_link_visit_action.idaction_name_ref = log_action_name_ref.idaction"
        );

        $selectPageIsFollowingSiteSearch = ",
                SUM( CASE WHEN log_action_name_ref.type = " . Action::TYPE_SITE_SEARCH . "
                      THEN 1 ELSE 0 END)
                    AS `" . PiwikMetrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS . "`";

        $select .= $selectFlagNoResultKeywords
            . $selectPageIsFollowingSiteSearch;
    }

    private function addMetricsToRankingQuery(RankingQuery $rankingQuery, array $metricsConfig): void
    {
        foreach ($metricsConfig as $metric => $config) {
            if (!empty($config['aggregation'])) {
                $rankingQuery->addColumn($metric, $config['aggregation']);
            } else {
                $rankingQuery->addColumn($metric);
            }
        }
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

    protected function getWhereClauseActionIsNotEvent(): string
    {
        return " AND log_link_visit_action.idaction_event_category IS NULL";
    }

    /**
     * Add goals data for each combination of url / title and pageviews / entries
     *
     * @param int   $rankingQueryLimit
     *
     * @return void
     */
    protected function archiveDayActionsGoals(ArchiveProcessor $archiveProcessor, int $rankingQueryLimit): void
    {
        $site = $archiveProcessor->getParams()->getSite();

        if (
            !\Piwik\Common::isGoalPluginEnabled() ||
            GeneralConfig::getConfigValue('disable_archive_actions_goals', $site->getId())
        ) {
            return;
        }

        $goals = $this->getGoalsForSite($site->getId());

        // Add orders and abandoned cart codes if the site is enabled for ecommerce
        if ($site->isEcommerceEnabled()) {
            $goals[] = GoalManager::IDGOAL_CART;
            $goals[] = GoalManager::IDGOAL_ORDER;
        }

        foreach ($goals as $idGoal) {
            $this->archiveDayActionsGoalsPages($archiveProcessor->getLogAggregator(), true, $idGoal);
            $this->archiveDayActionsGoalsPages($archiveProcessor->getLogAggregator(), false, $idGoal);
        }

        $this->archiveDayActionsGoalsPagesEntry($archiveProcessor, $rankingQueryLimit, true);
        $this->archiveDayActionsGoalsPagesEntry($archiveProcessor, $rankingQueryLimit, false);
    }

    /**
     * Query goal page view data and update actions data table
     *
     * @param bool  $isUrl              If true then query goal data by url, else by name
     * @param int   $idGoal             Goal to archive
     *
     * @return int|null Count of records processed
     * @throws \Exception
     */
    protected function archiveDayActionsGoalsPages(LogAggregator $logAggregator, bool $isUrl, int $idGoal): ?int
    {
        $linkField = ($isUrl ? 'idaction_url' : 'idaction_name');
        $resultSet = $logAggregator->queryConversionsByPageView($linkField, $idGoal);
        if (!$resultSet) {
            return null;
        }
        return ArchivingHelper::updateActionsTableWithGoals($resultSet, true);
    }

    /**
     * Get a list of goal ids for a site
     *
     * @param string $idSite
     *
     * @return array
     */
    private function getGoalsForSite(string $idSite): array
    {
        $cache = Cache::getTransientCache();
        $key   = 'ActionArchives_allGoalIds_' . $idSite;

        if ($cache->contains($key)) {
            return $cache->fetch($key);
        }

        $siteGoals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);
        $goalIds = array_column($siteGoals, 'idgoal');

        $cache->save($key, $goalIds);
        return $goalIds;
    }

    /**
     * Query goal entry page data and update actions data table
     *
     * @param int   $rankingQueryLimit
     * @param bool  $isUrl              If true then query goal data by url, else by name
     *
     * @return int|null Count of records processed
     * @throws \Exception
     */
    protected function archiveDayActionsGoalsPagesEntry(ArchiveProcessor $archiveProcessor, int $rankingQueryLimit, bool $isUrl): ?int
    {
        if (GeneralConfig::getConfigValue('disable_archive_actions_goals', $archiveProcessor->getParams()->getSite()->getId())) {
            return null;
        }
        $linkField = ($isUrl ? 'visit_entry_idaction_url' : 'visit_entry_idaction_name');
        $resultSet = $archiveProcessor->getLogAggregator()->queryConversionsByEntryPageView($linkField, $rankingQueryLimit);
        if (!$resultSet) {
            return null;
        }

        return ArchivingHelper::updateActionsTableWithGoals($resultSet, false);
    }
}
