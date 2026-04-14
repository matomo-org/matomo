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
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Translation\Translator;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 * @method static \Piwik\Plugins\UserCountryMap\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns configuration data needed to render the visitor map widget.
     *
     * @param string|false $segment
     * @return array
     * @hide
     */
    public function getVisitorMapConfig(int $idSite, string $period, string $date, $segment = false): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('UserCountry')) {
            throw new Exception($this->translator->translate('General_Required', 'Plugin UserCountry'));
        }

        $tokenAuth = Piwik::getCurrentUserTokenAuth();

        if (empty($segment)) {
            $segment = '';
        }

        // request visits summary
        $visitsSummary = Request::processRequest('VisitsSummary.get', [
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'segment' => $segment,
            'filter_limit' => -1,
        ]);

        // processRequest returns a DataTable for this call, convert to array
        if ($visitsSummary instanceof \Piwik\DataTable) {
            $visitsSummary = $visitsSummary->getFirstRow();
            $visitsSummary = $visitsSummary ? $visitsSummary->getColumns() : [];
        }

        // metrics metadata
        $metrics = $this->getMetrics($idSite, $period, $date);

        // default metric
        $defaultMetric = array_key_exists('nb_uniq_visitors', $visitsSummary) ? 'nb_uniq_visitors' : 'nb_visits';

        // translations for the JS locale object
        $locale = $this->buildLocale();

        // request params for JS-side API calls
        $reqParams = [
            'period' => $period,
            'idSite' => $idSite,
            'date' => $date,
            'segment' => $segment,
            'token_auth' => $tokenAuth,
            'format' => 'json',
            'showRawMetrics' => 1,
            'enable_filter_excludelowpop' => 1,
            'filter_excludelowpop_value' => -1,
        ];

        // country names
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');
        $countries = array_keys($regionDataProvider->getCountryList());
        $countryNames = [];
        foreach ($countries as $country) {
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

        return [
            'visitsSummary' => $visitsSummary,
            'metrics' => $metrics,
            'defaultMetric' => $defaultMetric,
            'svgBasePath' => 'plugins/UserCountryMap/svg/',
            'mapCssPath' => 'plugins/UserCountryMap/stylesheets/map.css',
            'reqParams' => $reqParams,
            '_' => $locale,
            'countryNames' => $countryNames,
            'continents' => $continents,
        ];
    }

    private function buildLocale(): array
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

    private function getMetrics(int $idSite, string $period, string $date): array
    {
        $metaData = Request::processRequest('API.getMetadata', [
            'apiModule' => 'UserCountry',
            'apiAction' => 'getCountry',
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'filter_limit' => -1,
        ]);

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
