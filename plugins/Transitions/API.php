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
        $archiveProcessing = new Piwik_ArchiveProcessing_Day();
        $archiveProcessing->setSite(new Piwik_Site($idSite));
        $archiveProcessing->setPeriod(Piwik_Period::advancedFactory($period, $date));
        $archiveProcessing->setSegment(new Piwik_Segment($segment, $idSite));
        $archiveProcessing->initForLiveUsage();

        // prepare the report
        $report = array(
            'date' => Piwik_Period_Day::advancedFactory($period, $date)->getLocalizedShortString()
        );

        // add data to the report
        $transitionsArchiving = new Piwik_Transitions;
        if ($returnNormalizedUrls) {
            $transitionsArchiving->returnNormalizedUrls();
        }

        $partsArray = explode(',', $parts);

        if ($parts == 'all' || in_array('internalReferrers', $partsArray)) {
            $this->addInternalReferrers($transitionsArchiving, $archiveProcessing, $report, $idaction,
                $actionType, $limitBeforeGrouping);
        }
        if ($parts == 'all' || in_array('followingActions', $partsArray)) {
            $includeLoops = $parts != 'all' && !in_array('internalReferrers', $partsArray);
            $this->addFollowingActions($transitionsArchiving, $archiveProcessing, $report, $idaction,
                $actionType, $limitBeforeGrouping, $includeLoops);
        }
        if ($parts == 'all' || in_array('externalReferrers', $partsArray)) {
            $this->addExternalReferrers($transitionsArchiving, $archiveProcessing, $report, $idaction,
                $actionType, $limitBeforeGrouping);
        }

        // derive the number of exits from the other metrics
        if ($parts == 'all') {
            $report['pageMetrics']['exits'] = $report['pageMetrics']['pageviews']
                - $transitionsArchiving->getTotalTransitionsToFollowingActions()
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
                $columnNames = array(Piwik_Archive::INDEX_NB_ACTIONS => 'referrals');
                if ($replaceLabel) {
                    $columnNames[Piwik_Archive::INDEX_NB_ACTIONS] = 'referrals';
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
     * @param Piwik_Transitions $transitionsArchiving
     * @param $archiveProcessing
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     * @throws Exception
     */
    private function addInternalReferrers($transitionsArchiving, $archiveProcessing, &$report,
                                          $idaction, $actionType, $limitBeforeGrouping)
    {

        $data = $transitionsArchiving->queryInternalReferrers(
            $idaction, $actionType, $archiveProcessing, $limitBeforeGrouping);

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
     * @param Piwik_Transitions $transitionsArchiving
     * @param $archiveProcessing
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     * @param boolean $includeLoops
     */
    private function addFollowingActions($transitionsArchiving, $archiveProcessing, &$report,
                                         $idaction, $actionType, $limitBeforeGrouping, $includeLoops = false)
    {

        $data = $transitionsArchiving->queryFollowingActions(
            $idaction, $actionType, $archiveProcessing, $limitBeforeGrouping, $includeLoops);

        foreach ($data as $tableName => $table) {
            $report[$tableName] = $table;
        }
    }

    /**
     * Add the external referrers to the report:
     * direct entries, websites, campaigns, search engines
     *
     * @param Piwik_Transitions $transitionsArchiving
     * @param $archiveProcessing
     * @param $report
     * @param $idaction
     * @param string $actionType
     * @param $limitBeforeGrouping
     */
    private function addExternalReferrers($transitionsArchiving, $archiveProcessing, &$report,
                                          $idaction, $actionType, $limitBeforeGrouping)
    {

        $data = $transitionsArchiving->queryExternalReferrers(
            $idaction, $actionType, $archiveProcessing, $limitBeforeGrouping);

        $report['pageMetrics']['entries'] = 0;
        $report['referrers'] = array();
        foreach ($data->getRows() as $row) {
            $referrerId = $row->getColumn('label');
            $visits = $row->getColumn(Piwik_Archive::INDEX_NB_VISITS);
            if ($visits) {
                // load details (i.e. subtables)
                $details = array();
                if ($idSubTable = $row->getIdSubDataTable()) {
                    $subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
                    foreach ($subTable->getRows() as $subRow) {
                        $details[] = array(
                            'label'     => $subRow->getColumn('label'),
                            'referrals' => $subRow->getColumn(Piwik_Archive::INDEX_NB_VISITS)
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