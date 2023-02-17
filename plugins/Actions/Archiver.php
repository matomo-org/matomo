<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\API\Request;
use Piwik\Cache;
use Piwik\Config\GeneralConfig;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics as PiwikMetrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;

/**
 * Class encapsulating logic to process Day/Period Archiving for the Actions reports
 *
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const DOWNLOADS_RECORD_NAME = 'Actions_downloads';
    const OUTLINKS_RECORD_NAME = 'Actions_outlink';
    const PAGE_TITLES_RECORD_NAME = 'Actions_actions';
    const SITE_SEARCH_RECORD_NAME = 'Actions_sitesearch';
    const SITE_SEARCH_CATEGORY_RECORD_NAME = 'Actions_SiteSearchCategories';
    const PAGE_URLS_RECORD_NAME = 'Actions_actions_url';

    const METRIC_PAGEVIEWS_RECORD_NAME = 'Actions_nb_pageviews';
    const METRIC_UNIQ_PAGEVIEWS_RECORD_NAME = 'Actions_nb_uniq_pageviews';
    const METRIC_SUM_TIME_RECORD_NAME = 'Actions_sum_time_generation';
    const METRIC_HITS_TIMED_RECORD_NAME = 'Actions_nb_hits_with_time_generation';
    const METRIC_DOWNLOADS_RECORD_NAME = 'Actions_nb_downloads';
    const METRIC_UNIQ_DOWNLOADS_RECORD_NAME = 'Actions_nb_uniq_downloads';
    const METRIC_OUTLINKS_RECORD_NAME = 'Actions_nb_outlinks';
    const METRIC_UNIQ_OUTLINKS_RECORD_NAME = 'Actions_nb_uniq_outlinks';
    const METRIC_SEARCHES_RECORD_NAME = 'Actions_nb_searches';
    const METRIC_KEYWORDS_RECORD_NAME = 'Actions_nb_keywords';

    protected $actionsTablesByType = null;
    protected $isSiteSearchEnabled = false;

    function __construct($processor)
    {
        parent::__construct($processor);
        $this->isSiteSearchEnabled = $processor->getParams()->getSite()->isSiteSearchEnabled();
    }

    /**
     * Archives Actions reports for a Day
     *
     * @return bool
     */
    public function aggregateDayReport()
    {
        $rankingQueryLimit = ArchivingHelper::getRankingQueryLimit();
        ArchivingHelper::reloadConfig();

        $this->initActionsTables();

        $this->archiveDayPageActions($rankingQueryLimit);
        $this->archiveDaySiteSearchActions($rankingQueryLimit);
        $this->archiveDaySearchCategoryActions();
        $this->archiveDayEntryActions($rankingQueryLimit);
        $this->archiveDayExitActions($rankingQueryLimit);
        $this->archiveDayActionsTime($rankingQueryLimit);
        $this->archiveDayActionsGoals($rankingQueryLimit);

        $this->insertDayReports();

        return true;
    }

    /**
     * @return array
     */
    protected function getMetricNames()
    {
        return array(
            self::METRIC_PAGEVIEWS_RECORD_NAME,
            self::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME,
            self::METRIC_DOWNLOADS_RECORD_NAME,
            self::METRIC_UNIQ_DOWNLOADS_RECORD_NAME,
            self::METRIC_OUTLINKS_RECORD_NAME,
            self::METRIC_UNIQ_OUTLINKS_RECORD_NAME,
            self::METRIC_SEARCHES_RECORD_NAME,
            self::METRIC_SUM_TIME_RECORD_NAME,
            self::METRIC_HITS_TIMED_RECORD_NAME,
        );
    }

    /**
     * @return string
     */
    public static function getWhereClauseActionIsNotEvent()
    {
        return " AND log_link_visit_action.idaction_event_category IS NULL";
    }

    /**
     * @param $select
     * @param $from
     */
    protected function updateQuerySelectFromForSiteSearch(&$select, &$from)
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

    /**
     * Initializes the DataTables created by the archiveDay function.
     */
    private function initActionsTables()
    {
        $this->actionsTablesByType = array();
        foreach (Metrics::$actionTypes as $type) {
            $dataTable = new DataTable();
            if ($type === Action::TYPE_SITE_SEARCH) {
                $maxRows = ArchivingHelper::$maximumRowsInDataTableSiteSearch;
            } else {
                $maxRows = ArchivingHelper::$maximumRowsInDataTableLevelZero;
            }
            $dataTable->setMaximumAllowedRows($maxRows);

            if ($type == Action::TYPE_PAGE_URL
                || $type == Action::TYPE_PAGE_TITLE
            ) {
                // for page urls and page titles, performance metrics exist and have to be aggregated correctly
                $dataTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, Metrics::$columnsAggregationOperation);
            }

            $this->actionsTablesByType[$type] = $dataTable;
        }
    }

    protected function archiveDayPageActions($rankingQueryLimit)
    {
        $typesToQuery = $this->actionsTablesByType;
        unset($typesToQuery[Action::TYPE_SITE_SEARCH]);
        $this->archiveDayActions($rankingQueryLimit, array_keys($typesToQuery), true);
    }

    protected function archiveDaySiteSearchActions($rankingQueryLimit)
    {
        if ($this->isSiteSearchEnabled()) {
            $rankingQueryLimit = max($rankingQueryLimit, ArchivingHelper::$maximumRowsInDataTableSiteSearch);
            $this->archiveDayActions($rankingQueryLimit, array(Action::TYPE_SITE_SEARCH), false);
        }
    }

    protected function archiveDaySearchCategoryActions()
    {
        $where = "%s.search_cat != '' AND %s.search_cat IS NOT NULL";
        $dimensions = array('search_cat');
        $query = $this->getLogAggregator()->queryActionsByDimension(
            $dimensions, 
            $where
        );

        $dataArray = new DataArray();
        while ($row = $query->fetch()) {
            $dataArray->sumMetricsActions($row['search_cat'], $row);
        }

        $dataTable = $dataArray->asDataTable();
        $report = $dataTable->getSerialized(ArchivingHelper::$maximumRowsInDataTableSiteSearch);
        $this->getProcessor()->insertBlobRecord(self::SITE_SEARCH_CATEGORY_RECORD_NAME, $report);
    }

    protected function archiveDayActions($rankingQueryLimit, array $actionTypes, $includePageNotDefined)
    {
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

        $where  = $this->getLogAggregator()->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.%s IS NOT NULL"
            . $this->getWhereClauseActionIsNotEvent();

        $actionTypesWhere = "log_action.type IN (" . implode(", ", $actionTypes) . ")";
        if ($includePageNotDefined) {
            $actionTypesWhere = "(" . $actionTypesWhere . " OR log_action.type IS NULL)";
        }
        $where .= " AND $actionTypesWhere";

        $groupBy = "log_link_visit_action.%s";
        $orderBy = "`" . PiwikMetrics::INDEX_PAGE_NB_HITS . "` DESC, name ASC";

        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn(array('idaction', 'name'));
            $rankingQuery->addColumn('url_prefix');

            if ($this->isSiteSearchEnabled()) {
                $rankingQuery->addColumn(PiwikMetrics::INDEX_SITE_SEARCH_HAS_NO_RESULT, 'min');
                $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS, 'sum');
            }

            $this->addMetricsToRankingQuery($rankingQuery, $metricsConfig);

            $rankingQuery->partitionResultIntoMultipleGroups('type', $actionTypes);
        }

        // Special Magic to get
        // 1) No result Keywords
        // 2) For each page view, count number of times the referrer page was a Site Search
        if ($this->isSiteSearchEnabled()) {
            $this->updateQuerySelectFromForSiteSearch($select, $from);
        }

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "idaction_name", $rankingQuery, $metricsConfig);
        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "idaction_url", $rankingQuery, $metricsConfig);
    }

    private function addMetricsToSelect($select, $metricsConfig)
    {
        if (!empty($metricsConfig)) {
            foreach ($metricsConfig as $metric => $config) {
                $select .= ', ' . $config['query'] . " as `" . $metric . "`";
            }
        }

        return $select;
    }

    private function addMetricsToRankingQuery(RankingQuery $rankingQuery, $metricsConfig)
    {
        foreach ($metricsConfig as $metric => $config) {
            if (!empty($config['aggregation'])) {
                $rankingQuery->addColumn($metric, $config['aggregation']);
            } else {
                $rankingQuery->addColumn($metric);
            }
        }
    }

    protected function isSiteSearchEnabled()
    {
        return $this->isSiteSearchEnabled;
    }

    protected function archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, $sprintfField, RankingQuery $rankingQuery = null, $metricsConfig = array())
    {
        $select = sprintf($select, $sprintfField);

        // get query with segmentation
        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // replace the rest of the %s
        $querySql = str_replace("%s", $sprintfField, $query['sql']);

        // apply ranking query
        if ($rankingQuery) {
            $querySql = $rankingQuery->generateRankingQuery($querySql);
        }

        // get result
        $resultSet = $this->getLogAggregator()->getDb()->query($querySql, $query['bind']);
        $modified = ArchivingHelper::updateActionsTableWithRowQuery($resultSet, $sprintfField, $this->actionsTablesByType, $metricsConfig);
        return $modified;
    }

    /**
     * Entry actions for Page URLs and Page names
     */
    protected function archiveDayEntryActions($rankingQueryLimit)
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
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

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

        $where  = $this->getLogAggregator()->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= " AND log_visit.%s > 0";

        $groupBy = "log_visit.%s";

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "visit_entry_idaction_url", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "visit_entry_idaction_name", $rankingQuery);
    }

    /**
     * Exit actions
     */
    protected function archiveDayExitActions($rankingQueryLimit)
    {
        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS);
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_EXIT_NB_VISITS, 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

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

        $where  = $this->getLogAggregator()->getWhereStatement('log_visit', 'visit_last_action_time');
        $where .= " AND log_visit.%s > 0";

        $groupBy = "log_visit.%s";

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "visit_exit_idaction_url", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "visit_exit_idaction_name", $rankingQuery);
        return array($rankingQuery, $extraSelects, $from, $orderBy, $select, $where, $groupBy);
    }

    /**
     * Time per action
     */
    protected function archiveDayActionsTime($rankingQueryLimit)
    {
        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_SPENT, 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

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

        $where = $this->getLogAggregator()->getWhereStatement('log_link_visit_action', 'server_time');
        $where .= " AND log_link_visit_action.time_spent_ref_action > 0
                 AND log_link_visit_action.%s > 0"
            . $this->getWhereClauseActionIsNotEvent();

        $groupBy = "log_link_visit_action.%s";

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "idaction_url_ref", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $groupBy, $orderBy, "idaction_name_ref", $rankingQuery);
    }

    /**
     * Records in the DB the archived reports for Page views, Downloads, Outlinks, and Page titles
     */
    protected function insertDayReports()
    {
        ArchivingHelper::clearActionsCache();

        $this->insertPageUrlsReports();
        $this->insertDownloadsReports();
        $this->insertOutlinksReports();
        $this->insertPageTitlesReports();
        $this->insertSiteSearchReports();
    }

    protected function insertPageUrlsReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_PAGE_URL);

        $prefix = $this->getProcessor()->getParams()->getSite()->getMainUrl();
        $prefix = rtrim($prefix, '/') . '/';
        ArchivingHelper::setFolderPathMetadata($dataTable, $isUrl = true, $prefix);

        $this->insertTable($dataTable, self::PAGE_URLS_RECORD_NAME);

        $records = array(
            self::METRIC_PAGEVIEWS_RECORD_NAME      => array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS)),
            self::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME => array_sum($dataTable->getColumn(PiwikMetrics::INDEX_NB_VISITS)),
            self::METRIC_SUM_TIME_RECORD_NAME       => array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_GENERATION)),
            self::METRIC_HITS_TIMED_RECORD_NAME     => array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION))
        );
        $this->getProcessor()->insertNumericRecords($records);
    }

    /**
     * Add goals data for each combination of url / title and pageviews / entries
     *
     * @param int   $rankingQueryLimit
     *
     * @return void
     */
    protected function archiveDayActionsGoals(int $rankingQueryLimit): void
    {

        $site = $this->getProcessor()->getParams()->getSite();

        if (!\Piwik\Common::isGoalPluginEnabled() ||
            GeneralConfig::getConfigValue('disable_archive_actions_goals', $site->getId())) {
            return;
        }

        $goals = $this->getGoalsForSite($site->getId());

        // Add orders and abandoned cart codes if the site is enabled for ecommerce
        if ($site->isEcommerceEnabled()) {
            $goals[] = GoalManager::IDGOAL_CART;
            $goals[] = GoalManager::IDGOAL_ORDER;
        }

        foreach ($goals as $idGoal) {
            $this->archiveDayActionsGoalsPages(true, $idGoal);
            $this->archiveDayActionsGoalsPages(false, $idGoal);
        }

        $this->archiveDayActionsGoalsPagesEntry($rankingQueryLimit, true);
        $this->archiveDayActionsGoalsPagesEntry($rankingQueryLimit, false);
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
    protected function archiveDayActionsGoalsPages(bool $isUrl, int $idGoal): ?int
    {
        $linkField = ($isUrl ? 'idaction_url' : 'idaction_name');
        $resultSet = $this->getLogAggregator()->queryConversionsByPageView($linkField, $idGoal);
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
    private function getGoalsForSite(string $idSite) : array
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
    protected function archiveDayActionsGoalsPagesEntry(int $rankingQueryLimit, bool $isUrl): ?int
    {
        if (GeneralConfig::getConfigValue('disable_archive_actions_goals', $this->getProcessor()->getParams()->getSite()->getId())) {
            return null;
        }
        $linkField = ($isUrl ? 'visit_entry_idaction_url' : 'visit_entry_idaction_name');
        $resultSet = $this->getLogAggregator()->queryConversionsByEntryPageView($linkField, $rankingQueryLimit);
        if (!$resultSet) {
            return null;
        }

        return ArchivingHelper::updateActionsTableWithGoals($resultSet, false);
    }

    /**
     * @param $typeId
     * @return DataTable
     */
    protected function getDataTable($typeId)
    {
        return $this->actionsTablesByType[$typeId];
    }

    protected function insertTable(DataTable $dataTable, $recordName)
    {
        ArchivingHelper::deleteInvalidSummedColumnsFromDataTable($dataTable);
        if ($recordName === Archiver::SITE_SEARCH_RECORD_NAME) {
            $maxRows = ArchivingHelper::$maximumRowsInDataTableSiteSearch;
        } else {
            $maxRows = ArchivingHelper::$maximumRowsInDataTableLevelZero;
        }
        $report = $dataTable->getSerialized($maxRows, ArchivingHelper::$maximumRowsInSubDataTable, ArchivingHelper::$columnToSortByBeforeTruncation);
        $this->getProcessor()->insertBlobRecord($recordName, $report);
    }

    protected function insertDownloadsReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_DOWNLOAD);
        $this->insertTable($dataTable, self::DOWNLOADS_RECORD_NAME);

        $this->getProcessor()->insertNumericRecord(self::METRIC_DOWNLOADS_RECORD_NAME, array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS)));
        $this->getProcessor()->insertNumericRecord(self::METRIC_UNIQ_DOWNLOADS_RECORD_NAME, array_sum($dataTable->getColumn(PiwikMetrics::INDEX_NB_VISITS)));
    }

    protected function insertOutlinksReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_OUTLINK);
        $this->insertTable($dataTable, self::OUTLINKS_RECORD_NAME);

        $this->getProcessor()->insertNumericRecord(self::METRIC_OUTLINKS_RECORD_NAME, array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS)));
        $this->getProcessor()->insertNumericRecord(self::METRIC_UNIQ_OUTLINKS_RECORD_NAME, array_sum($dataTable->getColumn(PiwikMetrics::INDEX_NB_VISITS)));
    }

    protected function insertPageTitlesReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_PAGE_TITLE);
        ArchivingHelper::setFolderPathMetadata($dataTable, $isUrl = false);
        $this->insertTable($dataTable, self::PAGE_TITLES_RECORD_NAME);
    }

    protected function insertSiteSearchReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_SITE_SEARCH);
        $this->deleteUnusedColumnsFromKeywordsDataTable($dataTable);
        $this->insertTable($dataTable, self::SITE_SEARCH_RECORD_NAME);

        $this->getProcessor()->insertNumericRecord(self::METRIC_SEARCHES_RECORD_NAME, array_sum($dataTable->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS)));
        $this->getProcessor()->insertNumericRecord(self::METRIC_KEYWORDS_RECORD_NAME, $dataTable->getRowsCount());
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

    public function aggregateMultipleReports()
    {
        ArchivingHelper::reloadConfig();
        $dataTableToSum = [
            self::PAGE_TITLES_RECORD_NAME,
            self::PAGE_URLS_RECORD_NAME,
        ];
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableToSum,
            ArchivingHelper::$maximumRowsInDataTableLevelZero,
            ArchivingHelper::$maximumRowsInSubDataTable,
            ArchivingHelper::$columnToSortByBeforeTruncation,
            Metrics::$columnsAggregationOperation,
            Metrics::$columnsToRenameAfterAggregation,
            $countRowsRecursive = []
        );

        $aggregation = null;
        $dataTableToSum = [
            self::DOWNLOADS_RECORD_NAME,
            self::OUTLINKS_RECORD_NAME,
        ];
        $this->getProcessor()->aggregateDataTableRecords(
            $dataTableToSum,
            ArchivingHelper::$maximumRowsInDataTableLevelZero,
            ArchivingHelper::$maximumRowsInSubDataTable,
            ArchivingHelper::$columnToSortByBeforeTruncation,
            $aggregation,
            Metrics::$columnsToRenameAfterAggregation,
            $countRowsRecursive = []
        );

        $dataTableToSum = [
            self::SITE_SEARCH_RECORD_NAME,
            self::SITE_SEARCH_CATEGORY_RECORD_NAME,
        ];
        $nameToCount    = $this->getProcessor()->aggregateDataTableRecords(
            $dataTableToSum,
            ArchivingHelper::$maximumRowsInDataTableSiteSearch,
            ArchivingHelper::$maximumRowsInSubDataTable,
            ArchivingHelper::$columnToSortByBeforeTruncation,
            $aggregation,
            Metrics::$columnsToRenameAfterAggregation,
            $countRowsRecursive = []
        );

        $this->getProcessor()->aggregateNumericMetrics($this->getMetricNames());

        // Unique Keywords can't be summed, instead we take the RowsCount() of the keyword table
        $this->getProcessor()->insertNumericRecord(
            self::METRIC_KEYWORDS_RECORD_NAME,
            $nameToCount[self::SITE_SEARCH_RECORD_NAME]['level0']
        );
    }
}
