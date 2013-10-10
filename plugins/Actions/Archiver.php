<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Actions
 */
namespace Piwik\Plugins\Actions;

use Piwik\Config;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Row\DataTableSummaryRow;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

/**
 * Class encapsulating logic to process Day/Period Archiving for the Actions reports
 *
 * @package Actions
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const DOWNLOADS_RECORD_NAME = 'Actions_downloads';
    const OUTLINKS_RECORD_NAME = 'Actions_outlink';
    const PAGE_TITLES_RECORD_NAME = 'Actions_actions';
    const SITE_SEARCH_RECORD_NAME = 'Actions_sitesearch';
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

    /* Metrics in use by the API Actions.get */
    public static $actionsAggregateMetrics = array(
        self::METRIC_PAGEVIEWS_RECORD_NAME      => 'nb_pageviews',
        self::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME => 'nb_uniq_pageviews',
        self::METRIC_DOWNLOADS_RECORD_NAME      => 'nb_downloads',
        self::METRIC_UNIQ_DOWNLOADS_RECORD_NAME => 'nb_uniq_downloads',
        self::METRIC_OUTLINKS_RECORD_NAME       => 'nb_outlinks',
        self::METRIC_UNIQ_OUTLINKS_RECORD_NAME  => 'nb_uniq_outlinks',
        self::METRIC_SEARCHES_RECORD_NAME       => 'nb_searches',
        self::METRIC_KEYWORDS_RECORD_NAME       => 'nb_keywords',
    );

    public static $actionTypes = array(
        Action::TYPE_ACTION_URL,
        Action::TYPE_OUTLINK,
        Action::TYPE_DOWNLOAD,
        Action::TYPE_ACTION_NAME,
        Action::TYPE_SITE_SEARCH,
    );
    static protected $invalidSummedColumnNameToRenamedNameFromPeriodArchive = array(
        Metrics::INDEX_NB_UNIQ_VISITORS            => Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
        Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS => Metrics::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS,
        Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS  => Metrics::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS,
    );
    static protected $invalidSummedColumnNameToDeleteFromDayArchive = array(
        Metrics::INDEX_NB_UNIQ_VISITORS,
        Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
        Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
    );
    private static $actionColumnAggregationOperations = array(
        Metrics::INDEX_PAGE_MAX_TIME_GENERATION => 'max',
        Metrics::INDEX_PAGE_MIN_TIME_GENERATION => 'min'
    );
    protected $actionsTablesByType = null;
    protected $isSiteSearchEnabled = false;

    function __construct($processor)
    {
        parent::__construct($processor);
        $this->isSiteSearchEnabled = $processor->getSite()->isSiteSearchEnabled();
    }

    /**
     * Archives Actions reports for a Day
     *
     * @return bool
     */
    public function archiveDay()
    {
        $rankingQueryLimit = self::getRankingQueryLimit();

        // FIXME: This is a quick fix for #3482. The actual cause of the bug is that
        // the site search & performance metrics additions to
        // ArchivingHelper::updateActionsTableWithRowQuery expect every
        // row to have 'type' data, but not all of the SQL queries that are run w/o
        // ranking query join on the log_action table and thus do not select the
        // log_action.type column.
        //
        // NOTES: Archiving logic can be generalized as follows:
        // 0) Do SQL query over log_link_visit_action & join on log_action to select
        //    some metrics (like visits, hits, etc.)
        // 1) For each row, cache the action row & metrics. (This is done by
        //    updateActionsTableWithRowQuery for result set rows that have
        //    name & type columns.)
        // 2) Do other SQL queries for metrics we can't put in the first query (like
        //    entry visits, exit vists, etc.) w/o joining log_action.
        // 3) For each row, find the cached row by idaction & add the new metrics to
        //    it. (This is done by updateActionsTableWithRowQuery for result set rows
        //    that DO NOT have name & type columns.)
        //
        // The site search & performance metrics additions expect a 'type' all the time
        // which breaks the original pre-rankingquery logic. Ranking query requires a
        // join, so the bug is only seen when ranking query is disabled.
        if ($rankingQueryLimit === 0) {
            $rankingQueryLimit = 100000;
        }

        ArchivingHelper::reloadConfig();

        $this->initActionsTables();
        $this->archiveDayActions($rankingQueryLimit);
        $this->archiveDayEntryActions($rankingQueryLimit);
        $this->archiveDayExitActions($rankingQueryLimit);
        $this->archiveDayActionsTime($rankingQueryLimit);

        $this->recordDayReports();

        return true;
    }

    /**
     * Returns the limit to use with RankingQuery for this plugin.
     *
     * @return int
     */
    private static function getRankingQueryLimit()
    {
        $configGeneral = Config::getInstance()->General;
        $configLimit = $configGeneral['archiving_ranking_query_row_limit'];
        return $configLimit == 0 ? 0 : max(
            $configLimit,
            $configGeneral['datatable_archiving_maximum_rows_actions'],
            $configGeneral['datatable_archiving_maximum_rows_subtable_actions']
        );
    }

    /*
     * Page URLs and Page names, general stats
     */

    /**
     * Initializes the DataTables created by the archiveDay function.
     */
    private function initActionsTables()
    {
        $this->actionsTablesByType = array();
        foreach (self::$actionTypes as $type) {
            $dataTable = new DataTable();
            $dataTable->setMaximumAllowedRows(ArchivingHelper::$maximumRowsInDataTableLevelZero);

            if ($type == Action::TYPE_ACTION_URL
                || $type == Action::TYPE_ACTION_NAME
            ) {
                // for page urls and page titles, performance metrics exist and have to be aggregated correctly
                $dataTable->setColumnAggregationOperations(self::$actionColumnAggregationOperations);
            }

            $this->actionsTablesByType[$type] = $dataTable;
        }
    }

    protected function archiveDayActions($rankingQueryLimit)
    {
        $select = "log_action.name,
				log_action.type,
				log_action.idaction,
				log_action.url_prefix,
				count(distinct log_link_visit_action.idvisit) as `" . Metrics::INDEX_NB_VISITS . "`,
				count(distinct log_link_visit_action.idvisitor) as `" . Metrics::INDEX_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Metrics::INDEX_PAGE_NB_HITS . "`,
				sum(
					case when " . Action::DB_COLUMN_TIME_GENERATION . " is null
						then 0
						else " . Action::DB_COLUMN_TIME_GENERATION . "
					end
				) / 1000 as `" . Metrics::INDEX_PAGE_SUM_TIME_GENERATION . "`,
				sum(
					case when " . Action::DB_COLUMN_TIME_GENERATION . " is null
						then 0
						else 1
					end
				) as `" . Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION . "`,
				min(" . Action::DB_COLUMN_TIME_GENERATION . ") / 1000
				    as `" . Metrics::INDEX_PAGE_MIN_TIME_GENERATION . "`,
				max(" . Action::DB_COLUMN_TIME_GENERATION . ") / 1000
                    as `" . Metrics::INDEX_PAGE_MAX_TIME_GENERATION . "`
				";

        $from = array(
            "log_link_visit_action",
            array(
                "table"  => "log_action",
                "joinOn" => "log_link_visit_action.%s = log_action.idaction"
            )
        );

        $where = "log_link_visit_action.server_time >= ?
				AND log_link_visit_action.server_time <= ?
				AND log_link_visit_action.idsite = ?
				AND log_link_visit_action.%s IS NOT NULL";

        $groupBy = "log_action.idaction";
        $orderBy = "`" . Metrics::INDEX_PAGE_NB_HITS . "` DESC, name ASC";

        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn(array('idaction', 'name'));
            $rankingQuery->addColumn(array('url_prefix', Metrics::INDEX_NB_UNIQ_VISITORS));
            $rankingQuery->addColumn(array(Metrics::INDEX_PAGE_NB_HITS, Metrics::INDEX_NB_VISITS), 'sum');
            if ($this->isSiteSearchEnabled()) {
                $rankingQuery->addColumn(Metrics::INDEX_SITE_SEARCH_HAS_NO_RESULT, 'min');
                $rankingQuery->addColumn(Metrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS, 'sum');
            }
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_SUM_TIME_GENERATION, 'sum');
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION, 'sum');
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_MIN_TIME_GENERATION, 'min');
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_MAX_TIME_GENERATION, 'max');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));
        }

        // Special Magic to get
        // 1) No result Keywords
        // 2) For each page view, count number of times the referrer page was a Site Search
        if ($this->isSiteSearchEnabled()) {
            $selectFlagNoResultKeywords = ",
				CASE WHEN (MAX(log_link_visit_action.custom_var_v" . Action::CVAR_INDEX_SEARCH_COUNT . ") = 0 AND log_link_visit_action.custom_var_k" . Action::CVAR_INDEX_SEARCH_COUNT . " = '" . Action::CVAR_KEY_SEARCH_COUNT . "') THEN 1 ELSE 0 END AS `" . Metrics::INDEX_SITE_SEARCH_HAS_NO_RESULT . "`";

            //we need an extra JOIN to know whether the referrer "idaction_name_ref" was a Site Search request
            $from[] = array(
                "table"      => "log_action",
                "tableAlias" => "log_action_name_ref",
                "joinOn"     => "log_link_visit_action.idaction_name_ref = log_action_name_ref.idaction"
            );

            $selectSiteSearchFollowingPages = ",
				SUM(CASE WHEN log_action_name_ref.type = " . Action::TYPE_SITE_SEARCH . " THEN 1 ELSE 0 END) AS `" . Metrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS . "`";

            $select .= $selectFlagNoResultKeywords
                . $selectSiteSearchFollowingPages;
        }

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "idaction_name", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "idaction_url", $rankingQuery);
    }

    protected function isSiteSearchEnabled()
    {
        return $this->isSiteSearchEnabled;
    }

    protected function archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, $sprintfField, $rankingQuery = false)
    {
        // idaction field needs to be set in select clause before calling getSelectQuery().
        // if a complex segmentation join is needed, the field needs to be propagated
        // to the outer select. therefore, $segment needs to know about it.
        $select = sprintf($select, $sprintfField);

        // get query with segmentation
        $query = $this->getLogAggregator()->generateQuery($select, $from, $where, $groupBy, $orderBy);

        // replace the rest of the %s
        $querySql = str_replace("%s", $sprintfField, $query['sql']);

        // apply ranking query
        if ($rankingQuery) {
            $querySql = $rankingQuery->generateQuery($querySql);
        }

        // get result
        $resultSet = $this->getLogAggregator()->getDb()->query($querySql, $query['bind']);
        $modified = ArchivingHelper::updateActionsTableWithRowQuery($resultSet, $sprintfField, $this->actionsTablesByType);
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
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS);
            $rankingQuery->addColumn(array(Metrics::INDEX_PAGE_ENTRY_NB_VISITS,
                                           Metrics::INDEX_PAGE_ENTRY_NB_ACTIONS,
                                           Metrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH,
                                           Metrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT), 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

            $extraSelects = 'log_action.type, log_action.name,';
            $from = array(
                "log_visit",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_visit.%s = log_action.idaction"
                )
            );
            $orderBy = "`" . Metrics::INDEX_PAGE_ENTRY_NB_ACTIONS . "` DESC, log_action.name ASC";
        } else {
            $extraSelects = false;
            $from = "log_visit";
            $orderBy = false;
        }

        $select = "log_visit.%s as idaction, $extraSelects
				count(distinct log_visit.idvisitor) as `" . Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Metrics::INDEX_PAGE_ENTRY_NB_VISITS . "`,
				sum(log_visit.visit_total_actions) as `" . Metrics::INDEX_PAGE_ENTRY_NB_ACTIONS . "`,
				sum(log_visit.visit_total_time) as `" . Metrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH . "`,
				sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `" . Metrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT . "`";

        $where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite = ?
		 		AND log_visit.%s > 0";

        $groupBy = "log_visit.%s, idaction";

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "visit_entry_idaction_url", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "visit_entry_idaction_name", $rankingQuery);
    }

    /**
     * Exit actions
     */
    public function archiveDayExitActions($rankingQueryLimit)
    {
        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS);
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_EXIT_NB_VISITS, 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

            $extraSelects = 'log_action.type, log_action.name,';
            $from = array(
                "log_visit",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_visit.%s = log_action.idaction"
                )
            );
            $orderBy = "`" . Metrics::INDEX_PAGE_EXIT_NB_VISITS . "` DESC, log_action.name ASC";
        } else {
            $extraSelects = false;
            $from = "log_visit";
            $orderBy = false;
        }

        $select = "log_visit.%s as idaction, $extraSelects
				count(distinct log_visit.idvisitor) as `" . Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Metrics::INDEX_PAGE_EXIT_NB_VISITS . "`";

        $where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
		 		AND log_visit.idsite = ?
		 		AND log_visit.%s > 0";

        $groupBy = "log_visit.%s, idaction";

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "visit_exit_idaction_url", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "visit_exit_idaction_name", $rankingQuery);
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
            $rankingQuery->setOthersLabel(DataTable::LABEL_SUMMARY_ROW);
            $rankingQuery->addLabelColumn('idaction');
            $rankingQuery->addColumn(Metrics::INDEX_PAGE_SUM_TIME_SPENT, 'sum');
            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

            $extraSelects = "log_action.type, log_action.name, count(*) as `" . Metrics::INDEX_PAGE_NB_HITS . "`,";
            $from = array(
                "log_link_visit_action",
                array(
                    "table"  => "log_action",
                    "joinOn" => "log_link_visit_action.%s = log_action.idaction"
                )
            );
            $orderBy = "`" . Metrics::INDEX_PAGE_NB_HITS . "` DESC, log_action.name ASC";
        } else {
            $extraSelects = false;
            $from = "log_link_visit_action";
            $orderBy = false;
        }

        $select = "log_link_visit_action.%s as idaction, $extraSelects
				sum(log_link_visit_action.time_spent_ref_action) as `" . Metrics::INDEX_PAGE_SUM_TIME_SPENT . "`";

        $where = "log_link_visit_action.server_time >= ?
				AND log_link_visit_action.server_time <= ?
		 		AND log_link_visit_action.idsite = ?
		 		AND log_link_visit_action.time_spent_ref_action > 0
		 		AND log_link_visit_action.%s > 0";

        $groupBy = "log_link_visit_action.%s, idaction";

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "idaction_url_ref", $rankingQuery);

        $this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy, "idaction_name_ref", $rankingQuery);
    }

    /**
     * Records in the DB the archived reports for Page views, Downloads, Outlinks, and Page titles
     */
    protected function recordDayReports()
    {
        ArchivingHelper::clearActionsCache();

        $this->recordPageUrlsReports();
        $this->recordDownloadsReports();
        $this->recordOutlinksReports();
        $this->recordPageTitlesReports();
        $this->recordSiteSearchReports();
    }

    protected function recordPageUrlsReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_ACTION_URL);
        $this->recordDataTable($dataTable, self::PAGE_URLS_RECORD_NAME);

        $records = array(
            self::METRIC_PAGEVIEWS_RECORD_NAME      => array_sum($dataTable->getColumn(Metrics::INDEX_PAGE_NB_HITS)),
            self::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME => array_sum($dataTable->getColumn(Metrics::INDEX_NB_VISITS)),
            self::METRIC_SUM_TIME_RECORD_NAME       => array_sum($dataTable->getColumn(Metrics::INDEX_PAGE_SUM_TIME_GENERATION)),
            self::METRIC_HITS_TIMED_RECORD_NAME     => array_sum($dataTable->getColumn(Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION))
        );
        $this->getProcessor()->insertNumericRecords($records);
    }

    /**
     * @param $typeId
     * @return DataTable
     */
    protected function getDataTable($typeId)
    {
        return $this->actionsTablesByType[$typeId];
    }

    protected function recordDataTable($dataTable, $recordName)
    {
        self::deleteInvalidSummedColumnsFromDataTable($dataTable);
        $s = $dataTable->getSerialized(ArchivingHelper::$maximumRowsInDataTableLevelZero, ArchivingHelper::$maximumRowsInSubDataTable, ArchivingHelper::$columnToSortByBeforeTruncation);
        $this->getProcessor()->insertBlobRecord($recordName, $s);
    }

    /**
     * For rows which have subtables (eg. directories with sub pages),
     * deletes columns which don't make sense when all values of sub pages are summed.
     *
     * @param $dataTable DataTable
     */
    static public function deleteInvalidSummedColumnsFromDataTable($dataTable)
    {
        foreach ($dataTable->getRows() as $id => $row) {
            if (($idSubtable = $row->getIdSubDataTable()) !== null
                || $id === DataTable::ID_SUMMARY_ROW
            ) {
                if ($idSubtable !== null) {
                    $subtable = Manager::getInstance()->getTable($idSubtable);
                    self::deleteInvalidSummedColumnsFromDataTable($subtable);
                }

                if ($row instanceof DataTableSummaryRow) {
                    $row->recalculate();
                }

                foreach (self::$invalidSummedColumnNameToDeleteFromDayArchive as $name) {
                    $row->deleteColumn($name);
                }
            }
        }

        // And this as well
        self::removeEmptyColumns($dataTable);
    }

    static protected function removeEmptyColumns($dataTable)
    {
        // Delete all columns that have a value of zero
        $dataTable->filter('ColumnDelete', array(
                                                $columnsToRemove = array(Metrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS),
                                                $columnsToKeep = array(),
                                                $deleteIfZeroOnly = true
                                           ));
    }

    protected function recordDownloadsReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_DOWNLOAD);
        $this->recordDataTable($dataTable, self::DOWNLOADS_RECORD_NAME);

        $this->getProcessor()->insertNumericRecord(self::METRIC_DOWNLOADS_RECORD_NAME, array_sum($dataTable->getColumn(Metrics::INDEX_PAGE_NB_HITS)));
        $this->getProcessor()->insertNumericRecord(self::METRIC_UNIQ_DOWNLOADS_RECORD_NAME, array_sum($dataTable->getColumn(Metrics::INDEX_NB_VISITS)));
    }

    protected function recordOutlinksReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_OUTLINK);
        $this->recordDataTable($dataTable, self::OUTLINKS_RECORD_NAME);

        $this->getProcessor()->insertNumericRecord(self::METRIC_OUTLINKS_RECORD_NAME, array_sum($dataTable->getColumn(Metrics::INDEX_PAGE_NB_HITS)));
        $this->getProcessor()->insertNumericRecord(self::METRIC_UNIQ_OUTLINKS_RECORD_NAME, array_sum($dataTable->getColumn(Metrics::INDEX_NB_VISITS)));
    }

    protected function recordPageTitlesReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_ACTION_NAME);
        $this->recordDataTable($dataTable, self::PAGE_TITLES_RECORD_NAME);
    }

    protected function recordSiteSearchReports()
    {
        $dataTable = $this->getDataTable(Action::TYPE_SITE_SEARCH);
        $this->deleteUnusedColumnsFromKeywordsDataTable($dataTable);
        $this->recordDataTable($dataTable, self::SITE_SEARCH_RECORD_NAME);

        $this->getProcessor()->insertNumericRecord(self::METRIC_SEARCHES_RECORD_NAME, array_sum($dataTable->getColumn(Metrics::INDEX_NB_VISITS)));
        $this->getProcessor()->insertNumericRecord(self::METRIC_KEYWORDS_RECORD_NAME, $dataTable->getRowsCount());
    }

    protected function deleteUnusedColumnsFromKeywordsDataTable($dataTable)
    {
        $columnsToDelete = array(
            Metrics::INDEX_NB_UNIQ_VISITORS,
            Metrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS,
            Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
            Metrics::INDEX_PAGE_ENTRY_NB_ACTIONS,
            Metrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH,
            Metrics::INDEX_PAGE_ENTRY_NB_VISITS,
            Metrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT,
            Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
        );
        $dataTable->deleteColumns($columnsToDelete);
    }

    public function archivePeriod()
    {
        ArchivingHelper::reloadConfig();
        $dataTableToSum = array(
            self::PAGE_TITLES_RECORD_NAME,
            self::PAGE_URLS_RECORD_NAME,
        );
        $this->getProcessor()->aggregateDataTableReports($dataTableToSum,
            ArchivingHelper::$maximumRowsInDataTableLevelZero,
            ArchivingHelper::$maximumRowsInSubDataTable,
            ArchivingHelper::$columnToSortByBeforeTruncation,
            self::$actionColumnAggregationOperations,
            self::$invalidSummedColumnNameToRenamedNameFromPeriodArchive
        );

        $dataTableToSum = array(
            self::DOWNLOADS_RECORD_NAME,
            self::OUTLINKS_RECORD_NAME,
            self::SITE_SEARCH_RECORD_NAME,
        );
        $aggregation = null;
        $nameToCount = $this->getProcessor()->aggregateDataTableReports($dataTableToSum,
            ArchivingHelper::$maximumRowsInDataTableLevelZero,
            ArchivingHelper::$maximumRowsInSubDataTable,
            ArchivingHelper::$columnToSortByBeforeTruncation,
            $aggregation,
            self::$invalidSummedColumnNameToRenamedNameFromPeriodArchive
        );

        $this->getProcessor()->aggregateNumericMetrics(array(
                                                            self::METRIC_PAGEVIEWS_RECORD_NAME,
                                                            self::METRIC_UNIQ_PAGEVIEWS_RECORD_NAME,
                                                            self::METRIC_DOWNLOADS_RECORD_NAME,
                                                            self::METRIC_UNIQ_DOWNLOADS_RECORD_NAME,
                                                            self::METRIC_OUTLINKS_RECORD_NAME,
                                                            self::METRIC_UNIQ_OUTLINKS_RECORD_NAME,
                                                            self::METRIC_SEARCHES_RECORD_NAME,
                                                            self::METRIC_SUM_TIME_RECORD_NAME,
                                                            self::METRIC_HITS_TIMED_RECORD_NAME,
                                                       ));

        // Unique Keywords can't be summed, instead we take the RowsCount() of the keyword table
        $this->getProcessor()->insertNumericRecord(self::METRIC_KEYWORDS_RECORD_NAME, $nameToCount[self::SITE_SEARCH_RECORD_NAME]['level0']);
    }
}
