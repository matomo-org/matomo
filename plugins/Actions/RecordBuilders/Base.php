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
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\DataAccess\LogAggregator;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Actions\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

abstract class Base extends RecordBuilder
{
    protected function archiveDayActions(ArchiveProcessor $archiveProcessor, $rankingQueryLimit, &$actionsTablesByType, $includePageNotDefined)
    {
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

        $actionTypesWhere = "log_action.type IN (" . implode(", ", array_keys($actionsTablesByType)) . ")";
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

            $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($actionsTablesByType));
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

    protected function archiveDayQueryProcess(LogAggregator $logAggregator, &$actionsTablesByType, $select, $from, $where, $groupBy, $orderBy, $sprintfField, RankingQuery $rankingQuery = null, $metricsConfig = array())
    {
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
        $modified = ArchivingHelper::updateActionsTableWithRowQuery($resultSet, $sprintfField, $actionsTablesByType, $metricsConfig);
        return $modified;
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

    private function addMetricsToSelect($select, $metricsConfig)
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
}