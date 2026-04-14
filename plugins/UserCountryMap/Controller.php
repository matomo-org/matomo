<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountryMap;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\View;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class Controller extends \Piwik\Plugin\Controller
{
    // By default plot up to the last 3 days of visitors on the map, for low traffic sites
    public const REAL_TIME_WINDOW = 'last3';

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
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

        $this->checkSitePermission();
        Piwik::checkUserHasViewAccess($this->idSite);

        $token_auth = Piwik::getCurrentUserTokenAuth();
        $view = new View('@UserCountryMap/realtimeMap');

        $view->mapIsStandaloneNotWidget = !(bool) Common::getRequestVar('widget', $standalone, 'int');

        $view->metrics = $this->getMetrics($this->idSite, 'range', self::REAL_TIME_WINDOW, $token_auth);
        $view->defaultMetric = 'nb_visits';
        $liveRefreshAfterMs = (int)Config::getInstance()->General['live_widget_refresh_after_seconds'] * 1000;

        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $this->idSite, 'filter_limit' => '-1'], $default = []);
        $site = new Site($this->idSite);
        $hasGoals = !empty($goals) || $site->isEcommerceEnabled();

        // maximum number of visits to be displayed in the map
        $maxVisits = Common::getRequestVar('filter_limit', 100, 'int');

        // some translations
        $locale = [
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
            'actions'          => $this->translator->translate('Transitions_NumPageviews'),
            'searches'         => $this->translator->translate('UserCountryMap_Searches'),
            'goal_conversions' => $this->translator->translate('UserCountryMap_GoalConversions'),
        ];

        $segment = $segmentOverride ? : Request::getRawSegmentFromRequest() ? : '';
        $params = [
            'period'     => 'range',
            'idSite'     => $this->idSite,
            'segment'    => $segment,
            'token_auth' => $token_auth,
        ];

        $realtimeWindow = Common::getRequestVar('realtimeWindow', self::REAL_TIME_WINDOW, 'string');
        if ($realtimeWindow != 'false') { // handle special value
            $params['date'] = $realtimeWindow;
        }

        $reqParams = $this->getEnrichedRequest($params, $encode = false);

        $view->config = [
            'metrics'            => [],
            'svgBasePath'        => 'plugins/UserCountryMap/svg/',
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
            'forceNowValue'      => Common::getRequestVar('forceNowValue', false, 'int'),
        ];

        return $view->render();
    }

    /**
     * Returns the visitor map widget configuration as JSON.
     * Used by the client-rendered VisitorMapWidget Vue component.
     */
    public function getVisitorMapConfig()
    {
        $this->checkUserCountryPluginEnabled();
        $this->checkSitePermission();
        Piwik::checkUserHasViewAccess($this->idSite);

        $period = Common::getRequestVar('period');
        $date = Common::getRequestVar('date');
        $segment = Request::getRawSegmentFromRequest();
        if (empty($segment)) {
            $segment = '';
        }

        // visits summary
        $visitsSummary = json_decode((new Request([
            'method' => 'VisitsSummary.get',
            'format' => 'json',
            'idSite' => $this->idSite,
            'period' => $period,
            'date' => $date,
            'segment' => $segment,
            'filter_limit' => -1,
        ]))->process(), true);

        $defaultMetric = array_key_exists('nb_uniq_visitors', $visitsSummary ?? [])
            ? 'nb_uniq_visitors' : 'nb_visits';

        // locale translations
        $locale = $this->buildVisitorMapLocale();

        // request params for JS-side API calls (no token_auth — session cookie auth)
        $reqParams = $this->getEnrichedRequest([
            'period' => $period,
            'idSite' => $this->idSite,
            'date' => $date,
            'segment' => $segment,
            'enable_filter_excludelowpop' => 1,
            'filter_excludelowpop_value' => -1,
        ], false);

        // metrics metadata
        $metrics = $this->getMetrics($this->idSite, $period, $date, Piwik::getCurrentUserTokenAuth());

        // country names
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');
        $countryNames = [];
        foreach (array_keys($regionDataProvider->getCountryList()) as $country) {
            $countryNames[strtoupper($country)] = Piwik::translate('Intl_Country_' . strtoupper($country));
        }

        // continent names
        $continents = [
            'AF' => \Piwik\Plugins\UserCountry\continentTranslate('afr'),
            'AS' => \Piwik\Plugins\UserCountry\continentTranslate('asi'),
            'EU' => \Piwik\Plugins\UserCountry\continentTranslate('eur'),
            'NA' => \Piwik\Plugins\UserCountry\continentTranslate('amn'),
            'OC' => \Piwik\Plugins\UserCountry\continentTranslate('oce'),
            'SA' => \Piwik\Plugins\UserCountry\continentTranslate('ams'),
        ];

        return json_encode([
            'visitsSummary' => $visitsSummary,
            'metrics' => $metrics,
            'defaultMetric' => $defaultMetric,
            'svgBasePath' => 'plugins/UserCountryMap/svg/',
            'mapCssPath' => 'plugins/UserCountryMap/stylesheets/map.css',
            'reqParams' => $reqParams,
            '_' => $locale,
            'countryNames' => $countryNames,
            'continents' => $continents,
        ]);
    }

    private function buildVisitorMapLocale(): array
    {
        $noVisitTranslation = $this->translator->translate('UserCountryMap_NoVisit');

        $translations = [
            'nb_visits' => $this->translator->translate('General_NVisits'),
            'no_visit' => $noVisitTranslation,
            'nb_actions' => $this->translator->translate('VisitsSummary_NbActionsDescription'),
            'nb_actions_per_visit' => $this->translator->translate('VisitsSummary_NbActionsPerVisit'),
            'bounce_rate' => $this->translator->translate('VisitsSummary_NbVisitsBounced'),
            'avg_time_on_site' => $this->translator->translate('VisitsSummary_AverageVisitDuration'),
            'and_n_others' => $this->translator->translate('UserCountryMap_AndNOthers'),
            'nb_uniq_visitors' => $this->translator->translate('General_NUniqueVisitors'),
            'nb_users' => $this->translator->translate('VisitsSummary_NbUsers'),
        ];

        foreach ($translations as &$translation) {
            if (
                false === strpos($translation, '%s')
                && $translation !== $noVisitTranslation
            ) {
                $translation = '%s ' . $translation;
            }
        }

        $translations['one_visit'] = $this->translator->translate('General_OneVisit');
        $translations['no_data'] = $this->translator->translate('CoreHome_ThereIsNoDataForThisReport');

        return $translations;
    }

    /**
     * Returns the real-time map widget configuration as JSON.
     * Used by the client-rendered RealtimeMapWidget Vue component.
     */
    public function getRealtimeMapConfig()
    {
        $this->checkUserCountryPluginEnabled();
        $this->checkSitePermission();
        Piwik::checkUserHasViewAccess($this->idSite);

        $liveRefreshAfterMs = (int) Config::getInstance()->General['live_widget_refresh_after_seconds'] * 1000;

        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $this->idSite, 'filter_limit' => '-1'], $default = []);
        $site = new Site($this->idSite);
        $hasGoals = !empty($goals) || $site->isEcommerceEnabled();

        $maxVisits = Common::getRequestVar('filter_limit', 100, 'int');

        $locale = [
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
            'actions'          => $this->translator->translate('Transitions_NumPageviews'),
            'searches'         => $this->translator->translate('UserCountryMap_Searches'),
            'goal_conversions' => $this->translator->translate('UserCountryMap_GoalConversions'),
        ];

        $segment = Request::getRawSegmentFromRequest() ?: '';
        $params = [
            'period' => 'range',
            'idSite' => $this->idSite,
            'segment' => $segment,
        ];

        $realtimeWindow = Common::getRequestVar('realtimeWindow', self::REAL_TIME_WINDOW, 'string');
        if ($realtimeWindow != 'false') {
            $params['date'] = $realtimeWindow;
        }

        $reqParams = $this->getEnrichedRequest($params, false);

        return json_encode([
            'metrics'            => [],
            'svgBasePath'        => 'plugins/UserCountryMap/svg/',
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
            'forceNowValue'      => Common::getRequestVar('forceNowValue', false, 'int'),
        ]);
    }

    private function getEnrichedRequest($params, $encode = true)
    {
        $params['format'] = 'json';
        $params['showRawMetrics'] = 1;
        if (empty($params['segment'])) {
            $segment = Request::getRawSegmentFromRequest();
            if (!empty($segment)) {
                $params['segment'] = $segment;
            }
        }

        if (!empty($params['segment'])) {
            $params['segment'] = urldecode($params['segment']);
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

    private function getMetrics(
        $idSite,
        $period,
        $date,
        #[\SensitiveParameter]
        $token_auth
    ) {
        $request = new Request([
            'method' => 'API.getMetadata',
            'format' => 'json',
            'apiModule' => 'UserCountry',
            'apiAction' => 'getCountry',
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'token_auth' => $token_auth,
            'filter_limit' => -1,
        ]);
        $metaData = json_decode($request->process(), true);

        $metrics = [];
        if (!empty($metaData[0]['metrics']) && is_array($metaData[0]['metrics'])) {
            foreach ($metaData[0]['metrics'] as $id => $val) {
                $metrics[] = [$id, $val];
            }
        }
        if (!empty($metaData[0]['processedMetrics']) && is_array($metaData[0]['processedMetrics'])) {
            foreach ($metaData[0]['processedMetrics'] as $id => $val) {
                $metrics[] = [$id, $val];
            }
        }
        return $metrics;
    }
}
