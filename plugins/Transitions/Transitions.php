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
class Piwik_Transitions extends Piwik_Plugin
{

    private $limitBeforeGrouping = 5;
    private $totalTransitionsToFollowingActions = 0;

    private $returnNormalizedUrls = false;

    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Transitions_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles'
        );
    }

    public function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();
        $cssFiles[] = 'plugins/Transitions/templates/transitions.css';
    }

    public function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = 'plugins/Transitions/templates/transitions.js';
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
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @param $limitBeforeGrouping
     * @return Piwik_DataTable
     */
    public function queryExternalReferrers($idaction, $actionType, $archiveProcessing,
                                           $limitBeforeGrouping = false)
    {
        $rankingQuery = new Piwik_RankingQuery($limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping);

        // we generate a single column that contains the interesting data for each referrer.
        // the reason we cannot group by referer_* becomes clear when we look at search engine keywords.
        // referer_url contains the url from the search engine, referer_keyword the keyword we want to
        // group by. when we group by both, we don't get a single column for the keyword but instead
        // one column per keyword + search engine url. this way, we could not get the top keywords using
        // the ranking query.
        $dimension = 'referrer_data';
        $rankingQuery->addLabelColumn('referrer_data');
        $select = '
			CASE referer_type
				WHEN ' . Piwik_Common::REFERER_TYPE_DIRECT_ENTRY . ' THEN \'\'
				WHEN ' . Piwik_Common::REFERER_TYPE_SEARCH_ENGINE . ' THEN referer_keyword
				WHEN ' . Piwik_Common::REFERER_TYPE_WEBSITE . ' THEN referer_url
				WHEN ' . Piwik_Common::REFERER_TYPE_CAMPAIGN . ' THEN CONCAT(referer_name, \' \', referer_keyword)
			END AS referrer_data,
			referer_type';

        // get one limited group per referrer type
        $rankingQuery->partitionResultIntoMultipleGroups('referer_type', array(
                                                                              Piwik_Common::REFERER_TYPE_DIRECT_ENTRY,
                                                                              Piwik_Common::REFERER_TYPE_SEARCH_ENGINE,
                                                                              Piwik_Common::REFERER_TYPE_WEBSITE,
                                                                              Piwik_Common::REFERER_TYPE_CAMPAIGN
                                                                         ));

        $orderBy = '`' . Piwik_Archive::INDEX_NB_VISITS . '` DESC';

        $type = $this->getColumnTypeSuffix($actionType);
        $where = 'visit_entry_idaction_' . $type . ' = ' . intval($idaction);

        $metrics = array(Piwik_Archive::INDEX_NB_VISITS);
        $data = $archiveProcessing->queryVisitsByDimension($dimension, $where, $metrics, $orderBy,
            $rankingQuery, $select, $selectGeneratesLabelColumn = true);

        $referrerData = array();
        $referrerSubData = array();

        foreach ($data as $referrerType => &$subData) {
            $referrerData[$referrerType] = array(Piwik_Archive::INDEX_NB_VISITS => 0);
            if ($referrerType != Piwik_Common::REFERER_TYPE_DIRECT_ENTRY) {
                $referrerSubData[$referrerType] = array();
            }

            foreach ($subData as &$row) {
                if ($referrerType == Piwik_Common::REFERER_TYPE_SEARCH_ENGINE && empty($row['referrer_data'])) {
                    $row['referrer_data'] = Piwik_Referers::LABEL_KEYWORD_NOT_DEFINED;
                }

                $referrerData[$referrerType][Piwik_Archive::INDEX_NB_VISITS] += $row[Piwik_Archive::INDEX_NB_VISITS];

                $label = $row['referrer_data'];
                if ($label) {
                    $referrerSubData[$referrerType][$label] = array(
                        Piwik_Archive::INDEX_NB_VISITS => $row[Piwik_Archive::INDEX_NB_VISITS]
                    );
                }
            }
        }

        return $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($referrerSubData, $referrerData);
    }

    /**
     * Get information about internal referrers (previous pages & loops, i.e. page refreshes)
     *
     * @param $idaction
     * @param $actionType
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @param $limitBeforeGrouping
     * @return array(previousPages:Piwik_DataTable, loops:integer)
     */
    public function queryInternalReferrers($idaction, $actionType, $archiveProcessing,
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

        $addSelect = '
			log_action.name, log_action.url_prefix,
			CASE WHEN log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction) . ' THEN 1 ELSE 0 END AS is_self,
			CASE
				WHEN log_action.type = ' . $mainActionType . ' THEN 1
				WHEN log_action.type = ' . Piwik_Tracker_Action::TYPE_SITE_SEARCH . ' THEN 2
				ELSE 0 
			END AS action_partition';

        $where = '
			log_link_visit_action.idaction_' . $type . ' = ' . intval($idaction);

        if ($dimension == 'idaction_url_ref') {
            // site search referrers are logged with url_ref=NULL
            // when we find one, we have to join on name_ref
            $dimension = 'IF( idaction_url_ref IS NULL, idaction_name_ref, idaction_url_ref )';
            $joinLogActionOn = $dimension;
        } else {
            $joinLogActionOn = $dimension;
            $dimension = array($dimension);
        }

        $orderBy = '`' . Piwik_Archive::INDEX_NB_ACTIONS . '` DESC';

        $metrics = array(Piwik_Archive::INDEX_NB_ACTIONS);
        $data = $archiveProcessing->queryActionsByDimension($dimension, $where, $metrics, $orderBy,
            $rankingQuery, $joinLogActionOn, $addSelect);

        $loops = 0;
        $nbPageviews = 0;
        $previousPagesDataTable = new Piwik_DataTable;
        if (isset($data['result'][1])) {
            foreach ($data['result'][1] as &$page) {
                $nbActions = intval($page[Piwik_Archive::INDEX_NB_ACTIONS]);
                $previousPagesDataTable->addRow(new Piwik_DataTable_Row(array(
                                                                             Piwik_DataTable_Row::COLUMNS => array(
                                                                                 'label'                         => $this->getPageLabel($page, $isTitle),
                                                                                 Piwik_Archive::INDEX_NB_ACTIONS => $nbActions
                                                                             )
                                                                        )));
                $nbPageviews += $nbActions;
            }
        }

        $previousSearchesDataTable = new Piwik_DataTable;
        if (isset($data['result'][2])) {
            foreach ($data['result'][2] as &$search) {
                $nbActions = intval($search[Piwik_Archive::INDEX_NB_ACTIONS]);
                $previousSearchesDataTable->addRow(new Piwik_DataTable_Row(array(
                                                                                Piwik_DataTable_Row::COLUMNS => array(
                                                                                    'label'                         => $search['name'],
                                                                                    Piwik_Archive::INDEX_NB_ACTIONS => $nbActions
                                                                                )
                                                                           )));
                $nbPageviews += $nbActions;
            }
        }

        if (isset($data['result'][0])) {
            foreach ($data['result'][0] as &$referrer) {
                $nbPageviews += intval($referrer[Piwik_Archive::INDEX_NB_ACTIONS]);
            }
        }

        if (count($data['excludedFromLimit'])) {
            $loops += intval($data['excludedFromLimit'][0][Piwik_Archive::INDEX_NB_ACTIONS]);
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

    /**
     * Get information about the following actions (following pages, site searches, outlinks, downloads)
     *
     * @param $idaction
     * @param $actionType
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @param $limitBeforeGrouping
     * @param $includeLoops
     * @return array(followingPages:Piwik_DataTable, outlinks:Piwik_DataTable, downloads:Piwik_DataTable)
     */
    public function queryFollowingActions($idaction, $actionType, Piwik_ArchiveProcessing_Day $archiveProcessing,
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
            $addSelect = 'log_action.name, log_action.url_prefix, log_action.type';
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
            $addSelect = '
				CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.name
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Piwik_Tracker_Action::TYPE_ACTION_URL . ' THEN log_action2.name
					' /* following download or outlink: use url */ . '
					ELSE log_action1.name
				END AS name,
				CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.type
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Piwik_Tracker_Action::TYPE_ACTION_URL . ' THEN log_action2.type
					' /* following download or outlink: use url */ . '
					ELSE log_action1.type
				END AS type,
				NULL AS url_prefix
			';
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

        $orderBy = '`' . Piwik_Archive::INDEX_NB_ACTIONS . '` DESC';

        $metrics = array(Piwik_Archive::INDEX_NB_ACTIONS);
        $data = $archiveProcessing->queryActionsByDimension($dimension, $where, $metrics, $orderBy,
            $rankingQuery, $joinLogActionColumn, $addSelect);

        $this->totalTransitionsToFollowingActions = 0;
        $dataTables = array();
        foreach ($types as $type => $recordName) {
            $dataTable = new Piwik_DataTable;
            if (isset($data[$type])) {
                foreach ($data[$type] as &$record) {
                    $actions = intval($record[Piwik_Archive::INDEX_NB_ACTIONS]);
                    $dataTable->addRow(new Piwik_DataTable_Row(array(
                                                                    Piwik_DataTable_Row::COLUMNS => array(
                                                                        'label'                         => $this->getPageLabel($record, $isTitle),
                                                                        Piwik_Archive::INDEX_NB_ACTIONS => $actions
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
     * Get the sum of all transitions to following actions (pages, outlinks, downloads).
     * Only works if queryFollowingActions() has been used directly before.
     */
    public function getTotalTransitionsToFollowingActions()
    {
        return $this->totalTransitionsToFollowingActions;
    }

    private function getColumnTypeSuffix($actionType)
    {
        if ($actionType == 'title') {
            return 'name';
        }
        return 'url';
    }

}