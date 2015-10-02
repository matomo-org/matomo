<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\View;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    // By default plot up to the last 30 days of visitors on the map, for low traffic sites
    const REAL_TIME_WINDOW = 'last30';
    
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function visitorMap($fetch = false, $segmentOverride = false)
    {
        $this->checkUserCountryPluginEnabled();

        $idSite = Common::getRequestVar('idSite', 1, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $period = Common::getRequestVar('period');
        $date = Common::getRequestVar('date');
        $segment = $segmentOverride ? : Request::getRawSegmentFromRequest() ? : '';
        $token_auth = Piwik::getCurrentUserTokenAuth();

        $view = new View('@UserCountryMap/visitorMap');

        // request visits summary
        $request = new Request(
            'method=VisitsSummary.get&format=PHP'
            . '&idSite=' . $idSite
            . '&period=' . $period
            . '&date=' . $date
            . '&segment=' . $segment
            . '&token_auth=' . $token_auth
            . '&filter_limit=-1'
        );
        $config = array();
        $config['visitsSummary'] = unserialize($request->process());
        $config['countryDataUrl'] = $this->_report('UserCountry', 'getCountry',
            $idSite, $period, $date, $token_auth, false, $segment);
        $config['regionDataUrl'] = $this->_report('UserCountry', 'getRegion',
            $idSite, $period, $date, $token_auth, true, $segment);
        $config['cityDataUrl'] = $this->_report('UserCountry', 'getCity',
            $idSite, $period, $date, $token_auth, true, $segment);
        $config['countrySummaryUrl'] = $this->getApiRequestUrl('VisitsSummary', 'get',
            $idSite, $period, $date, $token_auth, true, $segment);
        $view->defaultMetric = 'nb_visits';

        // some translations
        $view->localeJSON = json_encode(array(
             'nb_visits'            => $this->translator->translate('General_NVisits'),
             'one_visit'            => $this->translator->translate('General_OneVisit'),
             'no_visit'             => $this->translator->translate('UserCountryMap_NoVisit'),
             'nb_actions'           => $this->translator->translate('VisitsSummary_NbActionsDescription'),
             'nb_actions_per_visit' => $this->translator->translate('VisitsSummary_NbActionsPerVisit'),
             'bounce_rate'          => $this->translator->translate('VisitsSummary_NbVisitsBounced'),
             'avg_time_on_site'     => $this->translator->translate('VisitsSummary_AverageVisitDuration'),
             'and_n_others'         => $this->translator->translate('UserCountryMap_AndNOthers'),
             'no_data'              => $this->translator->translate('CoreHome_ThereIsNoDataForThisReport'),
             'nb_uniq_visitors'     => $this->translator->translate('VisitsSummary_NbUniqueVisitors'),
             'nb_users'             => $this->translator->translate('VisitsSummary_NbUsers'),
        ));

        $view->reqParamsJSON = $this->getEnrichedRequest($params = array(
            'period'                      => $period,
            'idSite'                      => $idSite,
            'date'                        => $date,
            'segment'                     => $segment,
            'token_auth'                  => $token_auth,
            'enable_filter_excludelowpop' => 1,
            'filter_excludelowpop_value'  => -1
        ));

        $view->metrics = $config['metrics'] = $this->getMetrics($idSite, $period, $date, $token_auth);
        $config['svgBasePath'] = 'plugins/UserCountryMap/svg/';
        $config['mapCssPath'] = 'plugins/UserCountryMap/stylesheets/map.css';
        $view->config = json_encode($config);
        $view->noData = empty($config['visitsSummary']['nb_visits']);

        $countriesByIso = array();
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');
        $countries = array_keys($regionDataProvider->getCountryList());

        foreach ($countries AS $country) {
            $countriesByIso[strtoupper($country)] = Piwik::translate('Intl_Country_'.strtoupper($country));
        }

        $view->countriesByIso = $countriesByIso;

        $view->continents = array(
            'AF' => \Piwik\Plugins\UserCountry\continentTranslate('afr'),
            'AS' => \Piwik\Plugins\UserCountry\continentTranslate('asi'),
            'EU' => \Piwik\Plugins\UserCountry\continentTranslate('eur'),
            'NA' => \Piwik\Plugins\UserCountry\continentTranslate('amn'),
            'OC' => \Piwik\Plugins\UserCountry\continentTranslate('oce'),
            'SA' => \Piwik\Plugins\UserCountry\continentTranslate('ams')
        );

        return $view->render();
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
     * @param bool $fetch
     * @param bool|string $segmentOverride
     *
     * @return string
     */
    public function realtimeMap($standalone = false, $fetch = false, $segmentOverride = false)
    {
        $this->checkUserCountryPluginEnabled();

        $idSite = Common::getRequestVar('idSite', 1, 'int');
        Piwik::checkUserHasViewAccess($idSite);

        $token_auth = Piwik::getCurrentUserTokenAuth();
        $view = new View('@UserCountryMap/realtimeMap');

        $view->mapIsStandaloneNotWidget = $standalone;

        $view->metrics = $this->getMetrics($idSite, 'range', self::REAL_TIME_WINDOW, $token_auth);
        $view->defaultMetric = 'nb_visits';
        $liveRefreshAfterMs = (int)Config::getInstance()->General['live_widget_refresh_after_seconds'] * 1000;

        $goals = APIGoals::getInstance()->getGoals($idSite);
        $site = new Site($idSite);
        $hasGoals = !empty($goals) || $site->isEcommerceEnabled();

        // maximum number of visits to be displayed in the map
        $maxVisits = Common::getRequestVar('filter_limit', 100, 'int');

        // some translations
        $locale = array(
            'nb_actions'       => $this->translator->translate('VisitsSummary_NbActionsDescription'),
            'local_time'       => $this->translator->translate('VisitTime_ColumnLocalTime'),
            'from'             => $this->translator->translate('General_FromReferrer'),
            'seconds'          => $this->translator->translate('Intl_Seconds'),
            'seconds_ago'      => $this->translator->translate('UserCountryMap_SecondsAgo'),
            'minutes'          => $this->translator->translate('Intl_Minutes'),
            'minutes_ago'      => $this->translator->translate('UserCountryMap_MinutesAgo'),
            'hours'            => $this->translator->translate('Intl_Hours'),
            'hours_ago'        => $this->translator->translate('UserCountryMap_HoursAgo'),
            'days_ago'         => $this->translator->translate('UserCountryMap_DaysAgo'),
            'actions'          => $this->translator->translate('VisitsSummary_NbPageviewsDescription'),
            'searches'         => $this->translator->translate('UserCountryMap_Searches'),
            'goal_conversions' => $this->translator->translate('UserCountryMap_GoalConversions'),
        );

        $segment = $segmentOverride ? : Request::getRawSegmentFromRequest() ? : '';
        $params = array(
            'period'     => 'range',
            'idSite'     => $idSite,
            'segment'    => $segment,
            'token_auth' => $token_auth,
        );

        $realtimeWindow = Common::getRequestVar('realtimeWindow', self::REAL_TIME_WINDOW, 'string');
        if ($realtimeWindow != 'false') { // handle special value
            $params['date'] = $realtimeWindow;
        }

        $reqParams = $this->getEnrichedRequest($params, $encode = false);

        $view->config = array(
            'metrics'            => array(),
            'svgBasePath'        => $view->piwikUrl . 'plugins/UserCountryMap/svg/',
            'liveRefreshAfterMs' => $liveRefreshAfterMs,
            '_'                  => $locale,
            'reqParams'          => $reqParams,
            'siteHasGoals'       => $hasGoals,
            'maxVisits'          => $maxVisits,
            'changeVisitAlpha'   => Common::getRequestVar('changeVisitAlpha', true, 'int'),
            'removeOldVisits'    => Common::getRequestVar('removeOldVisits', true, 'int'),
            'showFooterMessage'  => Common::getRequestVar('showFooterMessage', true, 'int'),
            'showDateTime'       => Common::getRequestVar('showDateTime', true, 'int'),
            'doNotRefreshVisits' => Common::getRequestVar('doNotRefreshVisits', false, 'int'),
            'enableAnimation'    => Common::getRequestVar('enableAnimation', true, 'int'),
            'forceNowValue'      => Common::getRequestVar('forceNowValue', false, 'int')
        );

        return $view->render();
    }

    private function getEnrichedRequest($params, $encode = true)
    {
        $params['format'] = 'json';
        $params['showRawMetrics'] = 1;
        if (empty($params['segment'])) {
            $segment = \Piwik\API\Request::getRawSegmentFromRequest();
            if (!empty($segment)) {
                $params['segment'] = urldecode($segment);
            }
        }

        if ($encode) {
            $params = json_encode($params);
        }
        return $params;
    }

    private function checkUserCountryPluginEnabled()
    {
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('UserCountry')) {
            throw new Exception($this->translator->translate('General_Required', 'Plugin UserCountry'));
        }
    }

    private function getMetrics($idSite, $period, $date, $token_auth)
    {
        $request = new Request(
            'method=API.getMetadata&format=PHP'
            . '&apiModule=UserCountry&apiAction=getCountry'
            . '&idSite=' . $idSite
            . '&period=' . $period
            . '&date=' . $date
            . '&token_auth=' . $token_auth
            . '&filter_limit=-1'
        );
        $metaData = unserialize($request->process());

        $metrics = array();
        if (!empty($metaData[0]['metrics']) && is_array($metaData[0]['metrics'])) {
            foreach ($metaData[0]['metrics'] as $id => $val) {
                // todo: should use SettingsPiwik::isUniqueVisitorsEnabled ?
                if (Common::getRequestVar('period') == 'day' || $id != 'nb_uniq_visitors') {
                    $metrics[] = array($id, $val);
                }
            }
        }
        if (!empty($metaData[0]['processedMetrics']) && is_array($metaData[0]['processedMetrics'])) {
            foreach ($metaData[0]['processedMetrics'] as $id => $val) {
                $metrics[] = array($id, $val);
            }
        }
        return $metrics;
    }

    private function getApiRequestUrl($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false, $segmentOverride = false)
    {
        // use processed reports
        $url = "?module=" . $module
            . "&method=" . $module . "." . $action . "&format=JSON"
            . "&idSite=" . $idSite
            . "&period=" . $period
            . "&date=" . $date
            . "&token_auth=" . $token_auth
            . "&segment=" . ($segmentOverride ? : Request::getRawSegmentFromRequest())
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

    private function _report($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false, $segmentOverride = false)
    {
        return $this->getApiRequestUrl('API', 'getProcessedReport&apiModule=' . $module . '&apiAction=' . $action,
            $idSite, $period, $date, $token_auth, $filter_by_country, $segmentOverride);
    }
}
