<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountryMap
 */

/**
 *
 * @package Piwik_UserCountryMap
 */
class Piwik_UserCountryMap_Controller extends Piwik_Controller
{

    // By default plot up to the last 30 days of visitors on the map, for low traffic sites
    const REAL_TIME_WINDOW = 'last30';

    public function visitorMap()
    {
        $this->checkUserCountryPluginEnabled();

        $idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $period = Piwik_Common::getRequestVar('period');
        $date = Piwik_Common::getRequestVar('date');
        $token_auth = Piwik::getCurrentUserTokenAuth();

        $view = Piwik_View::factory('visitor-map');

        // request visits summary
        $request = new Piwik_API_Request(
            'method=VisitsSummary.get&format=PHP'
                . '&idSite=' . $idSite
                . '&period=' . $period
                . '&date=' . $date
                . '&token_auth=' . $token_auth
                . '&filter_limit=-1'
        );
        $config = array();
        $config['visitsSummary'] = unserialize($request->process());
        $config['countryDataUrl'] = $this->_report('UserCountry', 'getCountry',
            $idSite, $period, $date, $token_auth);
        $config['regionDataUrl'] = $this->_report('UserCountry', 'getRegion',
            $idSite, $period, $date, $token_auth, true);
        $config['cityDataUrl'] = $this->_report('UserCountry', 'getCity',
            $idSite, $period, $date, $token_auth, true);
        $config['countrySummaryUrl'] = $this->getApiRequestUrl('VisitsSummary', 'get',
            $idSite, $period, $date, $token_auth, true);
        $view->defaultMetric = 'nb_visits';

        // some translations
        $view->localeJSON = Piwik_Common::json_encode(array(
                                                           'nb_visits'            => Piwik_Translate('VisitsSummary_NbVisits'),
                                                           'one_visit'            => Piwik_Translate('General_OneVisit'),
                                                           'no_visit'             => Piwik_Translate('UserCountryMap_NoVisit'),
                                                           'nb_actions'           => Piwik_Translate('VisitsSummary_NbActionsDescription'),
                                                           'nb_actions_per_visit' => Piwik_Translate('VisitsSummary_NbActionsPerVisit'),
                                                           'bounce_rate'          => Piwik_Translate('VisitsSummary_NbVisitsBounced'),
                                                           'avg_time_on_site'     => Piwik_Translate('VisitsSummary_AverageVisitDuration'),
                                                           'and_n_others'         => Piwik_Translate('UserCountryMap_AndNOthers'),
                                                           'no_data'              => Piwik_Translate('CoreHome_ThereIsNoDataForThisReport')
                                                      ));

        $view->reqParamsJSON = $this->getEnrichedRequest($params = array(
            'period'                      => $period,
            'idSite'                      => $idSite,
            'date'                        => $date,
            'token_auth'                  => $token_auth,
            'enable_filter_excludelowpop' => 1,
            'filter_excludelowpop_value'  => -1
        ));

        $view->metrics = $config['metrics'] = $this->getMetrics($idSite, $period, $date, $token_auth);
        $config['svgBasePath'] = 'plugins/UserCountryMap/svg/';
        $config['mapCssPath'] = 'plugins/UserCountryMap/css/map.css';
        $view->config = Piwik_Common::json_encode($config);
        $view->noData = empty($config['visitsSummary']['nb_visits']);

        echo $view->render();
    }

    /**
     * Used to build the report Visitor > Real time map
     */
    public function realtimeWorldMap()
    {
        return $this->realtimeMap($standalone = true);
    }

    /**
     * @param bool $standalone When set to true, the Top controls will be hidden to provide better full screen view
     */
    public function realtimeMap($standalone = false)
    {
        $this->checkUserCountryPluginEnabled();

        $idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $token_auth = Piwik::getCurrentUserTokenAuth();
        $view = Piwik_View::factory('realtime-map');

        $view->mapIsStandaloneNotWidget = $standalone;

        $view->metrics = $this->getMetrics($idSite, 'range', self::REAL_TIME_WINDOW, $token_auth);
        $view->defaultMetric = 'nb_visits';
        $view->liveRefreshAfterMs = (int)Piwik_Config::getInstance()->General['live_widget_refresh_after_seconds'] * 1000;

        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $site = new Piwik_Site($idSite);
        $view->hasGoals = !empty($goals) || $site->isEcommerceEnabled() ? 'true' : 'false';

        // maximum number of visits to be displayed in the map
        $view->maxVisits = Piwik_Common::getRequestVar('format_limit', 100, 'int');

        // some translations
        $view->localeJSON = json_encode(array(
                                             'nb_actions'       => Piwik_Translate('VisitsSummary_NbActionsDescription'),
                                             'local_time'       => Piwik_Translate('VisitTime_ColumnLocalTime'),
                                             'from'             => Piwik_Translate('General_FromReferrer'),
                                             'seconds'          => Piwik_Translate('UserCountryMap_Seconds'),
                                             'seconds_ago'      => Piwik_Translate('UserCountryMap_SecondsAgo'),
                                             'minutes'          => Piwik_Translate('UserCountryMap_Minutes'),
                                             'minutes_ago'      => Piwik_Translate('UserCountryMap_MinutesAgo'),
                                             'hours'            => Piwik_Translate('UserCountryMap_Hours'),
                                             'hours_ago'        => Piwik_Translate('UserCountryMap_HoursAgo'),
                                             'days_ago'         => Piwik_Translate('UserCountryMap_DaysAgo'),
                                             'actions'          => Piwik_Translate('VisitsSummary_NbPageviewsDescription'),
                                             'searches'         => Piwik_Translate('UserCountryMap_Searches'),
                                             'goal_conversions' => Piwik_Translate('UserCountryMap_GoalConversions'),
                                        ));

        $view->reqParamsJSON = $this->getEnrichedRequest(array(
                                                'period'         => 'range',
                                                'idSite'         => $idSite,
                                                'date'           => self::REAL_TIME_WINDOW,
                                                'token_auth'     => $token_auth,
                                           ));

        echo $view->render();
    }

    private function getEnrichedRequest($params)
    {
        $params['format'] = 'json';
        $params['showRawMetrics'] = 1;
        $segment = Piwik_ViewDataTable::getRawSegmentFromRequest();
        if(!empty($segment)) {
            $params['segment'] = $segment;
        }

        return Piwik_Common::json_encode($params);
    }


    private function checkUserCountryPluginEnabled()
    {
        if (!Piwik_PluginsManager::getInstance()->isPluginActivated('UserCountry')) {
            throw new Exception(Piwik_Translate('General_Required', 'Plugin UserCountry'));
        }
    }

    private function getMetrics($idSite, $period, $date, $token_auth)
    {
        $request = new Piwik_API_Request(
            'method=API.getMetadata&format=PHP'
                . '&apiModule=UserCountry&apiAction=getCountry'
                . '&idSite=' . $idSite
                . '&period=' . $period
                . '&date=' . $date
                . '&token_auth=' . $token_auth
                . '&filter_limit=-1'
        );
        $metaData = $request->process();

        $metrics = array();
        foreach ($metaData[0]['metrics'] as $id => $val) {
            if (Piwik_Common::getRequestVar('period') == 'day' || $id != 'nb_uniq_visitors') {
                $metrics[] = array($id, $val);
            }
        }
        foreach ($metaData[0]['processedMetrics'] as $id => $val) {
            $metrics[] = array($id, $val);
        }
        return $metrics;
    }

    private function getApiRequestUrl($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false)
    {
        // use processed reports
        $url = "?module=" . $module
            . "&method=" . $module . "." . $action . "&format=JSON"
            . "&idSite=" . $idSite
            . "&period=" . $period
            . "&date=" . $date
            . "&token_auth=" . $token_auth
            . "&segment=" . Piwik_ViewDataTable::getRawSegmentFromRequest()
            . "&enable_filter_excludelowpop=1"
            . "&showRawMetrics=1";

        if ($filter_by_country) {
            $url .= "&filter_column=country"
                . "&filter_sort_column=nb_visits"
                . "&filter_limit=-1"
                . "&filter_pattern=";
        } else {
            $url .= "&filter_limit=-1";
        }
        return $url;
    }

    private function _report($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false)
    {
        return $this->getApiRequestUrl('API', 'getProcessedReport&apiModule=' . $module . '&apiAction=' . $action, $idSite, $period, $date, $token_auth, $filter_by_country);
    }

}
