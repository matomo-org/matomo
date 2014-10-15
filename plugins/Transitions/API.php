<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Transitions;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\Actions\Actions;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\RankingQuery;
use Piwik\Segment;
use Piwik\SegmentExpression;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\TableLogAction;

/**
 * @method static \Piwik\Plugins\Transitions\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getTransitionsForPageTitle($pageTitle, $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false)
    {
        return $this->getTransitionsForAction($pageTitle, 'title', $idSite, $period, $date, $segment, $limitBeforeGrouping);
    }

    public function getTransitionsForPageUrl($pageUrl, $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false)
    {
        return $this->getTransitionsForAction($pageUrl, 'url', $idSite, $period, $date, $segment, $limitBeforeGrouping);
    }

    /**
     * General method to get transitions for an action
     *
     * @param $actionName
     * @param $actionType "url"|"title"
     * @param $idSite
     * @param $period
     * @param $date
     * @param bool $segment
     * @param bool $limitBeforeGrouping
     * @param string $parts
     * @return array
     * @throws Exception
     */
    public function getTransitionsForAction($actionName, $actionType, $idSite, $period, $date,
                                            $segment = false, $limitBeforeGrouping = false, $parts = 'all')
    {
        Piwik::checkUserHasViewAccess($idSite);

        // get idaction of the requested action
        $idaction = $this->deriveIdAction($actionName, $actionType);
        if ($idaction < 0) {
            throw new Exception('NoDataForAction');
        }

        // prepare log aggregator
        $segment = new Segment($segment, $idSite);
        $site = new Site($idSite);
        $period = Period\Factory::build($period, $date);
        $params = new ArchiveProcessor\Parameters($site, $period, $segment);
        $logAggregator = new LogAggregator($params);

        // prepare the report
        $report = array(
            'date' => Period\Factory::build($period->getLabel(), $date)->getLocalizedShortString()
        );

        $partsArray = explode(',', $parts);
        if ($parts == 'all' || in_array('internalReferrers', $partsArray)) {
            $this->addInternalReferrers($logAggregator, $report, $idaction, $actionType, $limitBeforeGrouping);
        }
        if ($parts == 'all' || in_array('followingActions', $partsArray)) {
            $includeLoops = $parts != 'all' && !in_array('internalReferrers', $partsArray);
            $this->addFollowingActions($logAggregator, $report, $idaction, $actionType, $limitBeforeGrouping, $includeLoops);
        }
        if ($parts == 'all' || in_array('externalReferrers', $partsArray)) {
            $this->addExternalReferrers($logAggregator, $report, $idaction, $actionType, $limitBeforeGrouping);
        }

        // derive the number of exits from the other metrics
        if ($parts == 'all') {
            $report['pageMetrics']['exits'] = $report['pageMetrics']['pageviews']
                - $this->getTotalTransitionsToFollowingActions()
                - $report['pageMetrics']['loops'];
        }

        // replace column names in the data tables
        $reportNames = array(
            'previousPages'         => true,
            'previousSiteSearches'  => false,
            'followingPages'        => true,
            'followingSiteSearches' => false,
            'outlinks'              => true,
            'downloads'             => true
        );
        foreach ($reportNames as $reportName => $replaceLabel) {
            if (isset($report[$reportName])) {
                $columnNames = array(Metrics::INDEX_NB_ACTIONS => 'referrals');
                if ($replaceLabel) {
                    $columnNames[Metrics::INDEX_NB_ACTIONS] = 'referrals';
                }
                $report[$reportName]->filter('ReplaceColumnNames', array($columnNames));
            }
        }

        return $report;
    }

    /**
     * Derive the action ID from the request action name and type.
     */
    private function deriveIdAction($actionName, $actionType)
    {
        switch ($actionType) {
            case 'url':
                $originalActionName = $actionName;
                $actionName = Common::unsanitizeInputValue($actionName);
                $id = TableLogAction::getIdActionFromSegment($actionName, 'idaction_url', SegmentExpression::MATCH_EQUAL, 'pageUrl');

                if ($id < 0) {
                    // an example where this is needed is urls containing < or >
                    $actionName = $originalActionName;
                    $id = TableLogAction::getIdActionFromSegment($actionName, 'idaction_url', SegmentExpression::MATCH_EQUAL, 'pageUrl');
                }

                return $id;

            case 'title':
                $id = TableLogAction::getIdActionFromSegment($actionName, 'idaction_name', SegmentExpression::MATCH_EQUAL, 'pageTitle');

                if ($id < 0) {
                    $unknown = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_TITLE);
                    if (trim($actionName) == trim($unknown)) {
                        $id = TableLogAction::getIdActionFromSegment('', 'idaction_name', SegmentExpression::MATCH_EQUAL, 'pageTitle');
                    }
                }

                return $id;

            default:
                throw new Exception('Unknown action type');
        }
    }

    /**
     * Add the internal referrers to the report:
     * previous pages and previous site searches
     *
     * @param LogAggregator $logAggregator
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     * @throws Exception
     */
    private function addInternalReferrers($logAggregator, &$report, $idaction, $actionType, $limitBeforeGrouping)
    {
        $data = $this->queryInternalReferrers($idaction, $actionType, $logAggregator, $limitBeforeGrouping);

        if ($data['pageviews'] == 0) {
            throw new Exception('NoDataForAction');
        }

        $report['previousPages'] = & $data['previousPages'];
        $report['previousSiteSearches'] = & $data['previousSiteSearches'];
        $report['pageMetrics']['loops'] = $data['loops'];
        $report['pageMetrics']['pageviews'] = $data['pageviews'];
    }

    /**
     * Add the following actions to the report:
     * following pages, downloads, outlinks
     *
     * @param LogAggregator $logAggregator
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     * @param boolean $includeLoops
     */
    private function addFollowingActions($logAggregator, &$report, $idaction, $actionType, $limitBeforeGrouping, $includeLoops = false)
    {
        $data = $this->queryFollowingActions(
            $idaction, $actionType, $logAggregator, $limitBeforeGrouping, $includeLoops);

        foreach ($data as $tableName => $table) {
            $report[$tableName] = $table;
        }
    }

    /**
     * Get information about the following actions (following pages, site searches, outlinks, downloads)
     *
     * @param $idaction
     * @param $actionType
     * @param LogAggregator $logAggregator
     * @param $limitBeforeGrouping
     * @param $includeLoops
     * @return array(followingPages:DataTable, outlinks:DataTable, downloads:DataTable)
     */
    protected function queryFollowingActions($idaction, $actionType, LogAggregator $logAggregator,
                                          $limitBeforeGrouping = false, $includeLoops = false)
    {
        $types = array();

        if ($actionType != 'title') {
            // specific setup for page urls
            $types[Action::TYPE_PAGE_URL] = 'followingPages';
            $dimension = 'if ( idaction_url IS NULL, idaction_name, idaction_url )';
            // site search referrers are logged with url=NULL
            // when we find one, we have to join on name
            $joinLogActionColumn = $dimension;
            $selects = array('log_action.name', 'log_action.url_prefix', 'log_action.type');
        } else {
            // specific setup for page titles:
            $types[Action::TYPE_PAGE_TITLE] = 'followingPages';
            // join log_action on name and url and pick depending on url type
            // the table joined on url is log_action1
            $joinLogActionColumn = array('idaction_url', 'idaction_name');
            $dimension = '
				CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.idaction
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Action::TYPE_PAGE_URL . ' THEN log_action2.idaction
					' /* following download or outlink: use url */ . '
					ELSE log_action1.idaction
				END
			';
            $selects = array(
                'CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.name
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Action::TYPE_PAGE_URL . ' THEN log_action2.name
					' /* following download or outlink: use url */ . '
					ELSE log_action1.name
				END AS `name`',
                'CASE
                    ' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.type
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Action::TYPE_PAGE_URL . ' THEN log_action2.type
					' /* following download or outlink: use url */ . '
					ELSE log_action1.type
				END AS `type`',
                'NULL AS `url_prefix`'
            );
        }

        // these types are available for both titles and urls
        $types[Action::TYPE_SITE_SEARCH] = 'followingSiteSearches';
        $types[Action::TYPE_OUTLINK] = 'outlinks';
        $types[Action::TYPE_DOWNLOAD] = 'downloads';

        $rankingQuery = new RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
        $rankingQuery->addLabelColumn(array('name', 'url_prefix'));
        $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($types));

        $type = $this->getColumnTypeSuffix($actionType);
        $where = 'log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction);
        if (!$includeLoops) {
            $where .= ' AND (log_link_visit_action.idaction_' . $type . ' IS NULL OR '
                . 'log_link_visit_action.idaction_' . $type . ' != ' . intval($idaction) . ')';
        }

        $metrics = array(Metrics::INDEX_NB_ACTIONS);
        $data = $logAggregator->queryActionsByDimension(array($dimension), $where, $selects, $metrics, $rankingQuery, $joinLogActionColumn);

        $dataTables = $this->makeDataTablesFollowingActions($types, $data);

        return $dataTables;
    }

    /**
     * Get information about external referrers (i.e. search engines, websites & campaigns)
     *
     * @param $idaction
     * @param $actionType
     * @param Logaggregator $logAggregator
     * @param $limitBeforeGrouping
     * @return DataTable
     */
    protected function queryExternalReferrers($idaction, $actionType, $logAggregator, $limitBeforeGrouping = false)
    {
        $rankingQuery = new RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);

        // we generate a single column that contains the interesting data for each referrer.
        // the reason we cannot group by referer_* becomes clear when we look at search engine keywords.
        // referer_url contains the url from the search engine, referer_keyword the keyword we want to
        // group by. when we group by both, we don't get a single column for the keyword but instead
        // one column per keyword + search engine url. this way, we could not get the top keywords using
        // the ranking query.
        $dimensions = array('referrer_data', 'referer_type');
        $rankingQuery->addLabelColumn('referrer_data');
        $selects = array(
            'CASE log_visit.referer_type
				WHEN ' . Common::REFERRER_TYPE_DIRECT_ENTRY . ' THEN \'\'
				WHEN ' . Common::REFERRER_TYPE_SEARCH_ENGINE . ' THEN log_visit.referer_keyword
				WHEN ' . Common::REFERRER_TYPE_WEBSITE . ' THEN log_visit.referer_url
				WHEN ' . Common::REFERRER_TYPE_CAMPAIGN . ' THEN CONCAT(log_visit.referer_name, \' \', log_visit.referer_keyword)
			END AS `referrer_data`');

        // get one limited group per referrer type
        $rankingQuery->partitionResultIntoMultipleGroups('referer_type', array(
                                                                              Common::REFERRER_TYPE_DIRECT_ENTRY,
                                                                              Common::REFERRER_TYPE_SEARCH_ENGINE,
                                                                              Common::REFERRER_TYPE_WEBSITE,
                                                                              Common::REFERRER_TYPE_CAMPAIGN
                                                                         ));

        $type = $this->getColumnTypeSuffix($actionType);
        $where = 'visit_entry_idaction_' . $type . ' = ' . intval($idaction);

        $metrics = array(Metrics::INDEX_NB_VISITS);
        $data = $logAggregator->queryVisitsByDimension($dimensions, $where, $selects, $metrics, $rankingQuery);

        $referrerData = array();
        $referrerSubData = array();

        foreach ($data as $referrerType => &$subData) {
            $referrerData[$referrerType] = array(Metrics::INDEX_NB_VISITS => 0);
            if ($referrerType != Common::REFERRER_TYPE_DIRECT_ENTRY) {
                $referrerSubData[$referrerType] = array();
            }

            foreach ($subData as &$row) {
                if ($referrerType == Common::REFERRER_TYPE_SEARCH_ENGINE && empty($row['referrer_data'])) {
                    $row['referrer_data'] = \Piwik\Plugins\Referrers\API::LABEL_KEYWORD_NOT_DEFINED;
                }

                $referrerData[$referrerType][Metrics::INDEX_NB_VISITS] += $row[Metrics::INDEX_NB_VISITS];

                $label = $row['referrer_data'];
                if ($label) {
                    $referrerSubData[$referrerType][$label] = array(
                        Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS]
                    );
                }
            }
        }

        $array = new DataArray($referrerData, $referrerSubData);
        return $array->asDataTable();
    }

    /**
     * Get information about internal referrers (previous pages & loops, i.e. page refreshes)
     *
     * @param $idaction
     * @param $actionType
     * @param LogAggregator $logAggregator
     * @param $limitBeforeGrouping
     * @return array(previousPages:DataTable, loops:integer)
     */
    protected function queryInternalReferrers($idaction, $actionType, $logAggregator, $limitBeforeGrouping = false)
    {
        $keyIsOther = 0;
        $keyIsPageUrlAction = 1;
        $keyIsSiteSearchAction = 2;

        $rankingQuery = new RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
        $rankingQuery->addLabelColumn(array('name', 'url_prefix'));
        $rankingQuery->setColumnToMarkExcludedRows('is_self');
        $rankingQuery->partitionResultIntoMultipleGroups('action_partition', array($keyIsOther, $keyIsPageUrlAction, $keyIsSiteSearchAction));

        $type = $this->getColumnTypeSuffix($actionType);
        $mainActionType = Action::TYPE_PAGE_URL;
        $dimension = 'idaction_url_ref';

        if ($actionType == 'title') {
            $mainActionType = Action::TYPE_PAGE_TITLE;
            $dimension = 'idaction_name_ref';
        }

        $selects = array(
            'log_action.name',
            'log_action.url_prefix',
            'CASE WHEN log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction) . ' THEN 1 ELSE 0 END AS `is_self`',
            'CASE
                WHEN log_action.type = ' . $mainActionType . ' THEN ' . $keyIsPageUrlAction . '
                        WHEN log_action.type = ' . Action::TYPE_SITE_SEARCH . ' THEN ' . $keyIsSiteSearchAction .'
                        ELSE ' . $keyIsOther . '
                    END AS `action_partition`'
        );

        $where = ' log_link_visit_action.idaction_' . $type . ' = ' . intval($idaction);

        if ($dimension == 'idaction_url_ref') {
            // site search referrers are logged with url_ref=NULL
            // when we find one, we have to join on name_ref
            $dimension = 'if ( idaction_url_ref IS NULL, idaction_name_ref, idaction_url_ref )';
            $joinLogActionOn = $dimension;
        } else {
            $joinLogActionOn = $dimension;
        }
        $metrics = array(Metrics::INDEX_NB_ACTIONS);
        $data = $logAggregator->queryActionsByDimension(array($dimension), $where, $selects, $metrics, $rankingQuery, $joinLogActionOn);

        $loops = 0;
        $nbPageviews = 0;
        $previousPagesDataTable = new DataTable;
        if (isset($data['result'][$keyIsPageUrlAction])) {
            foreach ($data['result'][$keyIsPageUrlAction] as &$page) {
                $nbActions = intval($page[Metrics::INDEX_NB_ACTIONS]);
                $previousPagesDataTable->addRow(new Row(array(
                                                             Row::COLUMNS => array(
                                                                 'label'                   => $this->getPageLabel($page, Action::TYPE_PAGE_URL),
                                                                 Metrics::INDEX_NB_ACTIONS => $nbActions
                                                             )
                                                        )));
                $nbPageviews += $nbActions;
            }
        }

        $previousSearchesDataTable = new DataTable;
        if (isset($data['result'][$keyIsSiteSearchAction])) {
            foreach ($data['result'][$keyIsSiteSearchAction] as &$search) {
                $nbActions = intval($search[Metrics::INDEX_NB_ACTIONS]);
                $previousSearchesDataTable->addRow(new Row(array(
                                                                Row::COLUMNS => array(
                                                                    'label'                   => $search['name'],
                                                                    Metrics::INDEX_NB_ACTIONS => $nbActions
                                                                )
                                                           )));
                $nbPageviews += $nbActions;
            }
        }

        if (isset($data['result'][0])) {
            foreach ($data['result'][0] as &$referrer) {
                $nbPageviews += intval($referrer[Metrics::INDEX_NB_ACTIONS]);
            }
        }

        if (count($data['excludedFromLimit'])) {
            $loops += intval($data['excludedFromLimit'][0][Metrics::INDEX_NB_ACTIONS]);
            $nbPageviews += $loops;
        }

        return array(
            'pageviews'            => $nbPageviews,
            'previousPages'        => $previousPagesDataTable,
            'previousSiteSearches' => $previousSearchesDataTable,
            'loops'                => $loops
        );
    }

    private function getPageLabel(&$pageRecord, $type)
    {
        if ($type == Action::TYPE_PAGE_TITLE) {
            $label = $pageRecord['name'];
            if (empty($label)) {
                $label = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_TITLE);
            }
            return $label;
        }

        if ($type == Action::TYPE_OUTLINK || $type == Action::TYPE_DOWNLOAD) {
            return PageUrl::reconstructNormalizedUrl($pageRecord['name'], $pageRecord['url_prefix']);
        }

        return $pageRecord['name'];
    }

    private function getColumnTypeSuffix($actionType)
    {
        if ($actionType == 'title') {
            return 'name';
        }
        return 'url';
    }

    private $limitBeforeGrouping = 5;
    private $totalTransitionsToFollowingActions = 0;

    /**
     * Get the sum of all transitions to following actions (pages, outlinks, downloads).
     * Only works if queryFollowingActions() has been used directly before.
     */
    protected function getTotalTransitionsToFollowingActions()
    {
        return $this->totalTransitionsToFollowingActions;
    }

    /**
     * Add the external referrers to the report:
     * direct entries, websites, campaigns, search engines
     *
     * @param LogAggregator $logAggregator
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     */
    private function addExternalReferrers($logAggregator, &$report, $idaction, $actionType, $limitBeforeGrouping)
    {
        $data = $this->queryExternalReferrers(
            $idaction, $actionType, $logAggregator, $limitBeforeGrouping);

        $report['pageMetrics']['entries'] = 0;
        $report['referrers'] = array();
        foreach ($data->getRows() as $row) {
            $referrerId = $row->getColumn('label');
            $visits = $row->getColumn(Metrics::INDEX_NB_VISITS);
            if ($visits) {
                // load details (i.e. subtables)
                $details = array();
                if ($idSubTable = $row->getIdSubDataTable()) {
                    $subTable = Manager::getInstance()->getTable($idSubTable);
                    foreach ($subTable->getRows() as $subRow) {
                        $details[] = array(
                            'label'     => $subRow->getColumn('label'),
                            'referrals' => $subRow->getColumn(Metrics::INDEX_NB_VISITS)
                        );
                    }
                }
                $report['referrers'][] = array(
                    'label'     => $this->getReferrerLabel($referrerId),
                    'shortName' => \Piwik\Plugins\Referrers\getReferrerTypeFromShortName($referrerId),
                    'visits'    => $visits,
                    'details'   => $details
                );
                $report['pageMetrics']['entries'] += $visits;
            }
        }

        // if there's no data for referrers, ResponseBuilder::handleMultiDimensionalArray
        // does not detect the multi dimensional array and the data is rendered differently, which
        // causes an exception.
        if (count($report['referrers']) == 0) {
            $report['referrers'][] = array(
                'label'     => $this->getReferrerLabel(Common::REFERRER_TYPE_DIRECT_ENTRY),
                'shortName' => \Piwik\Plugins\Referrers\getReferrerTypeLabel(Common::REFERRER_TYPE_DIRECT_ENTRY),
                'visits'    => 0
            );
        }
    }

    private function getReferrerLabel($referrerId)
    {
        switch ($referrerId) {
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                return Controller::getTranslation('directEntries');
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                return Controller::getTranslation('fromSearchEngines');
            case Common::REFERRER_TYPE_WEBSITE:
                return Controller::getTranslation('fromWebsites');
            case Common::REFERRER_TYPE_CAMPAIGN:
                return Controller::getTranslation('fromCampaigns');
            default:
                return Piwik::translate('General_Others');
        }
    }

    public function getTranslations()
    {
        $controller = new Controller();
        return $controller->getTranslations();
    }

    protected function makeDataTablesFollowingActions($types, $data)
    {
        $this->totalTransitionsToFollowingActions = 0;
        $dataTables = array();
        foreach ($types as $type => $recordName) {
            $dataTable = new DataTable;
            if (isset($data[$type])) {
                foreach ($data[$type] as &$record) {
                    $actions = intval($record[Metrics::INDEX_NB_ACTIONS]);
                    $dataTable->addRow(new Row(array(
                                                    Row::COLUMNS => array(
                                                        'label'                   => $this->getPageLabel($record, $type),
                                                        Metrics::INDEX_NB_ACTIONS => $actions
                                                    )
                                               )));
                    $this->totalTransitionsToFollowingActions += $actions;
                }
            }
            $dataTables[$recordName] = $dataTable;
        }
        return $dataTables;
    }
}
