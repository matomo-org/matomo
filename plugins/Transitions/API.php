<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Transitions
 */

/**
 * @package Piwik_Transitions
 */
class Piwik_Transitions_API
{

    static private $instance = null;

    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

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
     * @param bool $returnNormalizedUrls
     * @return array
     * @throws Exception
     */
    public function getTransitionsForAction($actionName, $actionType, $idSite, $period, $date,
                                            $segment = false, $limitBeforeGrouping = false, $parts = 'all', $returnNormalizedUrls = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        // get idaction of the requested action
        $idaction = $this->deriveIdAction($actionName, $actionType);
        if ($idaction < 0) {
            throw new Exception('NoDataForAction');
        }

        // prepare archive processing that can be used by the archiving code
        $segment = new Piwik_Segment($segment, $idSite);
        $site = new Piwik_Site($idSite);
        $period = Piwik_Period::advancedFactory($period, $date);
        $archiveProcessor = new Piwik_ArchiveProcessor_Day($period, $site, $segment);
        $logAggregator = $archiveProcessor->getLogAggregator();
        // prepare the report
        $report = array(
            'date' => Piwik_Period_Day::advancedFactory($period->getLabel(), $date)->getLocalizedShortString()
        );

        // add data to the report
        $transitionsArchiving = new Piwik_Transitions;
        if ($returnNormalizedUrls) {
            $transitionsArchiving->returnNormalizedUrls();
        }

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
                $columnNames = array(Piwik_Metrics::INDEX_NB_ACTIONS => 'referrals');
                if ($replaceLabel) {
                    $columnNames[Piwik_Metrics::INDEX_NB_ACTIONS] = 'referrals';
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
        $actionsPlugin = new Piwik_Actions;
        switch ($actionType) {
            case 'url':
                $originalActionName = $actionName;
                $actionName = Piwik_Common::unsanitizeInputValue($actionName);
                $id = $actionsPlugin->getIdActionFromSegment($actionName, 'idaction_url', Piwik_SegmentExpression::MATCH_EQUAL, 'pageUrl');

                if ($id < 0) {
                    // an example where this is needed is urls containing < or >
                    $actionName = $originalActionName;
                    $id = $actionsPlugin->getIdActionFromSegment($actionName, 'idaction_url', Piwik_SegmentExpression::MATCH_EQUAL, 'pageUrl');
                }

                return $id;

            case 'title':
                $id = $actionsPlugin->getIdActionFromSegment($actionName, 'idaction_name', Piwik_SegmentExpression::MATCH_EQUAL, 'pageTitle');

                if ($id < 0) {
                    $unknown = Piwik_Actions_ArchivingHelper::getUnknownActionName(
                        Piwik_Tracker_Action::TYPE_ACTION_NAME);

                    if (trim($actionName) == trim($unknown)) {
                        $id = $actionsPlugin->getIdActionFromSegment('', 'idaction_name', Piwik_SegmentExpression::MATCH_EQUAL, 'pageTitle');
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
     * @param Piwik_DataAccess_LogAggregator $logAggregator
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
     * @param Piwik_DataAccess_LogAggregator $logAggregator
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
     * @param Piwik_DataAccess_LogAggregator  $logAggregator
     * @param $limitBeforeGrouping
     * @param $includeLoops
     * @return array(followingPages:Piwik_DataTable, outlinks:Piwik_DataTable, downloads:Piwik_DataTable)
     */
    public function queryFollowingActions($idaction, $actionType, Piwik_DataAccess_LogAggregator $logAggregator,
                                          $limitBeforeGrouping = false, $includeLoops = false)
    {
        $types = array();

        $isTitle = ($actionType == 'title');
        if (!$isTitle) {
            // specific setup for page urls
            $types[Piwik_Tracker_Action::TYPE_ACTION_URL] = 'followingPages';
            $dimension = 'IF( idaction_url IS NULL, idaction_name, idaction_url )';
            // site search referrers are logged with url=NULL
            // when we find one, we have to join on name
            $joinLogActionColumn = $dimension;
            $selects = array('log_action.name', 'log_action.url_prefix', 'log_action.type');
        } else {
            // specific setup for page titles:
            $types[Piwik_Tracker_Action::TYPE_ACTION_NAME] = 'followingPages';
            // join log_action on name and url and pick depending on url type
            // the table joined on url is log_action1
            $joinLogActionColumn = array('idaction_url', 'idaction_name');
            $dimension = '
				CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.idaction
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Piwik_Tracker_Action::TYPE_ACTION_URL . ' THEN log_action2.idaction
					' /* following download or outlink: use url */ . '
					ELSE log_action1.idaction
				END
			';
            $selects = array(
                'CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.name
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Piwik_Tracker_Action::TYPE_ACTION_URL . ' THEN log_action2.name
					' /* following download or outlink: use url */ . '
					ELSE log_action1.name
				END AS `name`',
                'CASE
                    ' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.type
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Piwik_Tracker_Action::TYPE_ACTION_URL . ' THEN log_action2.type
					' /* following download or outlink: use url */ . '
					ELSE log_action1.type
				END AS `type`',
                'NULL AS `url_prefix`'
            );
        }

        // these types are available for both titles and urls
        $types[Piwik_Tracker_Action::TYPE_SITE_SEARCH] = 'followingSiteSearches';
        $types[Piwik_Tracker_Action::TYPE_OUTLINK] = 'outlinks';
        $types[Piwik_Tracker_Action::TYPE_DOWNLOAD] = 'downloads';

        $rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
        $rankingQuery->addLabelColumn(array('name', 'url_prefix'));
        $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($types));

        $type = $this->getColumnTypeSuffix($actionType);
        $where = 'log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction);
        if (!$includeLoops) {
            $where .= ' AND (log_link_visit_action.idaction_' . $type . ' IS NULL OR '
                . 'log_link_visit_action.idaction_' . $type . ' != ' . intval($idaction) . ')';
        }

        $metrics = array(Piwik_Metrics::INDEX_NB_ACTIONS);
        $data = $logAggregator->queryActionsByDimension(array($dimension), $where, $selects, $metrics, $rankingQuery, $joinLogActionColumn);

        $this->totalTransitionsToFollowingActions = 0;
        $dataTables = array();
        foreach ($types as $type => $recordName) {
            $dataTable = new Piwik_DataTable;
            if (isset($data[$type])) {
                foreach ($data[$type] as &$record) {
                    $actions = intval($record[Piwik_Metrics::INDEX_NB_ACTIONS]);
                    $dataTable->addRow(new Piwik_DataTable_Row(array(
                                                                    Piwik_DataTable_Row::COLUMNS => array(
                                                                        'label'                         => $this->getPageLabel($record, $isTitle),
                                                                        Piwik_Metrics::INDEX_NB_ACTIONS => $actions
                                                                    )
                                                               )));
                    $this->totalTransitionsToFollowingActions += $actions;
                }
            }
            $dataTables[$recordName] = $dataTable;
        }

        return $dataTables;
    }


    /**
     * After calling this method, the query*()-Methods will return urls in their
     * normalized form (without the prefix reconstructed)
     */
    public function returnNormalizedUrls()
    {
        $this->returnNormalizedUrls = true;
    }

    /**
     * Get information about external referrers (i.e. search engines, websites & campaigns)
     *
     * @param $idaction
     * @param $actionType
     * @param Piwik_ArchiveProcessor_Day $logAggregator
     * @param $limitBeforeGrouping
     * @return Piwik_DataTable
     */
    public function queryExternalReferrers($idaction, $actionType, $logAggregator,
                                           $limitBeforeGrouping = false)
    {
        $rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);

        // we generate a single column that contains the interesting data for each referrer.
        // the reason we cannot group by referer_* becomes clear when we look at search engine keywords.
        // referer_url contains the url from the search engine, referer_keyword the keyword we want to
        // group by. when we group by both, we don't get a single column for the keyword but instead
        // one column per keyword + search engine url. this way, we could not get the top keywords using
        // the ranking query.
        $dimensions = array('referrer_data', 'referer_type');
        $rankingQuery->addLabelColumn('referrer_data');
        $selects = array(
            'CASE referer_type
				WHEN ' . Piwik_Common::REFERER_TYPE_DIRECT_ENTRY . ' THEN \'\'
				WHEN ' . Piwik_Common::REFERER_TYPE_SEARCH_ENGINE . ' THEN referer_keyword
				WHEN ' . Piwik_Common::REFERER_TYPE_WEBSITE . ' THEN referer_url
				WHEN ' . Piwik_Common::REFERER_TYPE_CAMPAIGN . ' THEN CONCAT(referer_name, \' \', referer_keyword)
			END AS `referrer_data`');

        // get one limited group per referrer type
        $rankingQuery->partitionResultIntoMultipleGroups('referer_type', array(
                                                                              Piwik_Common::REFERER_TYPE_DIRECT_ENTRY,
                                                                              Piwik_Common::REFERER_TYPE_SEARCH_ENGINE,
                                                                              Piwik_Common::REFERER_TYPE_WEBSITE,
                                                                              Piwik_Common::REFERER_TYPE_CAMPAIGN
                                                                         ));

        $type = $this->getColumnTypeSuffix($actionType);
        $where = 'visit_entry_idaction_' . $type . ' = ' . intval($idaction);

        $metrics = array(Piwik_Metrics::INDEX_NB_VISITS);
        $data = $logAggregator->queryVisitsByDimension($dimensions, $where, $selects, $metrics, $rankingQuery);

        $referrerData = array();
        $referrerSubData = array();

        foreach ($data as $referrerType => &$subData) {
            $referrerData[$referrerType] = array(Piwik_Metrics::INDEX_NB_VISITS => 0);
            if ($referrerType != Piwik_Common::REFERER_TYPE_DIRECT_ENTRY) {
                $referrerSubData[$referrerType] = array();
            }

            foreach ($subData as &$row) {
                if ($referrerType == Piwik_Common::REFERER_TYPE_SEARCH_ENGINE && empty($row['referrer_data'])) {
                    $row['referrer_data'] = Piwik_Referers_API::LABEL_KEYWORD_NOT_DEFINED;
                }

                $referrerData[$referrerType][Piwik_Metrics::INDEX_NB_VISITS] += $row[Piwik_Metrics::INDEX_NB_VISITS];

                $label = $row['referrer_data'];
                if ($label) {
                    $referrerSubData[$referrerType][$label] = array(
                        Piwik_Metrics::INDEX_NB_VISITS => $row[Piwik_Metrics::INDEX_NB_VISITS]
                    );
                }
            }
        }

        //FIXMEA refactor after integration tests written
        $array = new Piwik_DataArray($referrerData, $referrerSubData);
        return Piwik_ArchiveProcessor_Day::getDataTableFromDataArray($array);
    }

    /**
     * Get information about internal referrers (previous pages & loops, i.e. page refreshes)
     *
     * @param $idaction
     * @param $actionType
     * @param Piwik_ArchiveProcessor_Day $logAggregator
     * @param $limitBeforeGrouping
     * @return array(previousPages:Piwik_DataTable, loops:integer)
     */
    protected function queryInternalReferrers($idaction, $actionType, $logAggregator,
                                           $limitBeforeGrouping = false)
    {
        $rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);
        $rankingQuery->addLabelColumn(array('name', 'url_prefix'));
        $rankingQuery->setColumnToMarkExcludedRows('is_self');
        $rankingQuery->partitionResultIntoMultipleGroups('action_partition', array(0, 1, 2));

        $type = $this->getColumnTypeSuffix($actionType);
        $mainActionType = Piwik_Tracker_Action::TYPE_ACTION_URL;
        $dimension = 'idaction_url_ref';
        $isTitle = $actionType == 'title';

        if ($isTitle) {
            $mainActionType = Piwik_Tracker_Action::TYPE_ACTION_NAME;
            $dimension = 'idaction_name_ref';
        }

        $selects = array(
            'log_action.name',
            'log_action.url_prefix',
            'CASE WHEN log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction) . ' THEN 1 ELSE 0 END AS `is_self`',
            'CASE
                WHEN log_action.type = ' . $mainActionType . ' THEN 1
                        WHEN log_action.type = ' . Piwik_Tracker_Action::TYPE_SITE_SEARCH . ' THEN 2
                        ELSE 0
                    END AS `action_partition`'
        );

        $where = '
			log_link_visit_action.idaction_' . $type . ' = ' . intval($idaction);

        if ($dimension == 'idaction_url_ref') {
            // site search referrers are logged with url_ref=NULL
            // when we find one, we have to join on name_ref
            $dimension = 'IF( idaction_url_ref IS NULL, idaction_name_ref, idaction_url_ref )';
            $joinLogActionOn = $dimension;
        } else {
            $joinLogActionOn = $dimension;
        }
        $metrics = array(Piwik_Metrics::INDEX_NB_ACTIONS);
        $data = $logAggregator->queryActionsByDimension(array($dimension), $where, $selects, $metrics, $rankingQuery, $joinLogActionOn);

        $loops = 0;
        $nbPageviews = 0;
        $previousPagesDataTable = new Piwik_DataTable;
        if (isset($data['result'][1])) {
            foreach ($data['result'][1] as &$page) {
                $nbActions = intval($page[Piwik_Metrics::INDEX_NB_ACTIONS]);
                $previousPagesDataTable->addRow(new Piwik_DataTable_Row(array(
                                                                             Piwik_DataTable_Row::COLUMNS => array(
                                                                                 'label'                         => $this->getPageLabel($page, $isTitle),
                                                                                 Piwik_Metrics::INDEX_NB_ACTIONS => $nbActions
                                                                             )
                                                                        )));
                $nbPageviews += $nbActions;
            }
        }

        $previousSearchesDataTable = new Piwik_DataTable;
        if (isset($data['result'][2])) {
            foreach ($data['result'][2] as &$search) {
                $nbActions = intval($search[Piwik_Metrics::INDEX_NB_ACTIONS]);
                $previousSearchesDataTable->addRow(new Piwik_DataTable_Row(array(
                                                                                Piwik_DataTable_Row::COLUMNS => array(
                                                                                    'label'                         => $search['name'],
                                                                                    Piwik_Metrics::INDEX_NB_ACTIONS => $nbActions
                                                                                )
                                                                           )));
                $nbPageviews += $nbActions;
            }
        }

        if (isset($data['result'][0])) {
            foreach ($data['result'][0] as &$referrer) {
                $nbPageviews += intval($referrer[Piwik_Metrics::INDEX_NB_ACTIONS]);
            }
        }

        if (count($data['excludedFromLimit'])) {
            $loops += intval($data['excludedFromLimit'][0][Piwik_Metrics::INDEX_NB_ACTIONS]);
            $nbPageviews += $loops;
        }

        return array(
            'pageviews'            => $nbPageviews,
            'previousPages'        => $previousPagesDataTable,
            'previousSiteSearches' => $previousSearchesDataTable,
            'loops'                => $loops
        );
    }

    private function getPageLabel(&$pageRecord, $isTitle)
    {
        if ($isTitle) {
            $label = $pageRecord['name'];
            if (empty($label)) {
                $label = Piwik_Actions_ArchivingHelper::getUnknownActionName(
                    Piwik_Tracker_Action::TYPE_ACTION_NAME);
            }
            return $label;
        } else if ($this->returnNormalizedUrls) {
            return $pageRecord['name'];
        } else {
            return Piwik_Tracker_Action::reconstructNormalizedUrl(
                $pageRecord['name'], $pageRecord['url_prefix']);
        }
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

    private $returnNormalizedUrls = false;


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
     * @param Piwik_DataAccess_LogAggregator  $logAggregator
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     */
    private function addExternalReferrers($logAggregator, &$report,
                                          $idaction, $actionType, $limitBeforeGrouping)
    {

        $data = $this->queryExternalReferrers(
            $idaction, $actionType, $logAggregator, $limitBeforeGrouping);

        $report['pageMetrics']['entries'] = 0;
        $report['referrers'] = array();
        foreach ($data->getRows() as $row) {
            $referrerId = $row->getColumn('label');
            $visits = $row->getColumn(Piwik_Metrics::INDEX_NB_VISITS);
            if ($visits) {
                // load details (i.e. subtables)
                $details = array();
                if ($idSubTable = $row->getIdSubDataTable()) {
                    $subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
                    foreach ($subTable->getRows() as $subRow) {
                        $details[] = array(
                            'label'     => $subRow->getColumn('label'),
                            'referrals' => $subRow->getColumn(Piwik_Metrics::INDEX_NB_VISITS)
                        );
                    }
                }
                $report['referrers'][] = array(
                    'label'     => $this->getReferrerLabel($referrerId),
                    'shortName' => Piwik_getRefererTypeFromShortName($referrerId),
                    'visits'    => $visits,
                    'details'   => $details
                );
                $report['pageMetrics']['entries'] += $visits;
            }
        }

        // if there's no data for referrers, Piwik_API_ResponseBuilder::handleMultiDimensionalArray
        // does not detect the multi dimensional array and the data is rendered differently, which
        // causes an exception.
        if (count($report['referrers']) == 0) {
            $report['referrers'][] = array(
                'label'     => $this->getReferrerLabel(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY),
                'shortName' => Piwik_getRefererTypeLabel(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY),
                'visits'    => 0
            );
        }
    }

    private function getReferrerLabel($referrerId)
    {
        switch ($referrerId) {
            case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                return Piwik_Transitions_Controller::getTranslation('directEntries');
            case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                return Piwik_Transitions_Controller::getTranslation('fromSearchEngines');
            case Piwik_Common::REFERER_TYPE_WEBSITE:
                return Piwik_Transitions_Controller::getTranslation('fromWebsites');
            case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                return Piwik_Transitions_Controller::getTranslation('fromCampaigns');
            default:
                return Piwik_Translate('General_Others');
        }
    }

    public function getTranslations()
    {
        $controller = new Piwik_Transitions_Controller();
        return $controller->getTranslations();
    }

}