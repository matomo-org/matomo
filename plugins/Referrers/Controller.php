<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\DataTable\Map;
use Piwik\Metrics;
use Piwik\NumberFormatter;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\ViewDataTable;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function getSparklines()
    {
        $metrics = $this->getReferrersVisitorsByType();
        $distinctMetrics = $this->getDistinctReferrersMetrics();

        $numberFormatter = NumberFormatter::getInstance();

        $totalVisits = array_sum($metrics);
        foreach ($metrics as $name => $value) {

            // calculate percent of total, if there were any visits
            if ($value != 0 && $totalVisits != 0) {
                $percentName = $name . 'Percent';
                $metrics[$percentName] = round(($value / $totalVisits) * 100, 0);
            }
        }

        // calculate evolution for visit metrics & distinct metrics
        list($lastPeriodDate, $ignore) = Range::getLastDate();
        if ($lastPeriodDate !== false) {
            $date = Common::getRequestVar('date');
            $period = Common::getRequestVar('period');

            $prettyDate = self::getPrettyDate($date, $period);
            $prettyLastPeriodDate = self::getPrettyDate($lastPeriodDate, $period);

            // visit metrics
            $previousValues = $this->getReferrersVisitorsByType($lastPeriodDate);
            $metrics = $this->addEvolutionPropertiesToView($prettyDate, $metrics, $prettyLastPeriodDate, $previousValues);

            // distinct metrics
            $previousValues = $this->getDistinctReferrersMetrics($lastPeriodDate);
            $distinctMetrics = $this->addEvolutionPropertiesToView($prettyDate, $distinctMetrics, $prettyLastPeriodDate, $previousValues);
        }

        /** @var Sparklines $view */
        $view = ViewDataTable\Factory::build(Sparklines::ID, $api = '', $controller = '', $force = true, $loadUserParams = false);

        // DIRECT ENTRY
        $metrics['visitorsFromDirectEntry'] = $numberFormatter->formatNumber($metrics['visitorsFromDirectEntry']);
        $values = array($metrics['visitorsFromDirectEntry']);
        $descriptions = array(Piwik::translate('Referrers_TypeDirectEntries'));

        if (!empty($metrics['visitorsFromDirectEntryPercent'])) {
            $metrics['visitorsFromDirectEntryPercent'] = $numberFormatter->formatPercent($metrics['visitorsFromDirectEntryPercent'], $precision = 1);
            $values[] = $metrics['visitorsFromDirectEntryPercent'];
            $descriptions[] = Piwik::translate('Referrers_XPercentOfVisits');
        }

        $directEntryParams = $this->getReferrerSparklineParams(Common::REFERRER_TYPE_DIRECT_ENTRY);

        $view->config->addSparkline($directEntryParams, $values, $descriptions, @$metrics['visitorsFromDirectEntryEvolution']);


        // WEBSITES
        $metrics['visitorsFromWebsites'] = $numberFormatter->formatNumber($metrics['visitorsFromWebsites']);
        $values = array($metrics['visitorsFromWebsites']);
        $descriptions = array(Piwik::translate('Referrers_TypeWebsites'));

        if (!empty($metrics['visitorsFromWebsitesPercent'])) {
            $metrics['visitorsFromWebsitesPercent'] = $numberFormatter->formatPercent($metrics['visitorsFromWebsitesPercent'], $precision = 1);
            $values[] = $metrics['visitorsFromWebsitesPercent'];
            $descriptions[] = Piwik::translate('Referrers_XPercentOfVisits');
        }

        $searchEngineParams = $this->getReferrerSparklineParams(Common::REFERRER_TYPE_WEBSITE);

        $view->config->addSparkline($searchEngineParams, $values, $descriptions, @$metrics['visitorsFromWebsitesEvolution']);


        // SEARCH ENGINES
        $metrics['visitorsFromSearchEngines'] = $numberFormatter->formatNumber($metrics['visitorsFromSearchEngines']);
        $values = array($metrics['visitorsFromSearchEngines']);
        $descriptions = array(Piwik::translate('Referrers_TypeSearchEngines'));

        if (!empty($metrics['visitorsFromSearchEnginesPercent'])) {
            $metrics['visitorsFromSearchEnginesPercent'] = $numberFormatter->formatPercent($metrics['visitorsFromSearchEnginesPercent'], $precision = 1);
            $values[] = $metrics['visitorsFromSearchEnginesPercent'];
            $descriptions[] = Piwik::translate('Referrers_XPercentOfVisits');
        }
        $searchEngineParams = $this->getReferrerSparklineParams(Common::REFERRER_TYPE_SEARCH_ENGINE);

        $view->config->addSparkline($searchEngineParams, $values, $descriptions, @$metrics['visitorsFromSearchEnginesEvolution']);

        // SOCIAL NETWORKS
        $metrics['visitorsFromSocialNetworks'] = $numberFormatter->formatNumber($metrics['visitorsFromSocialNetworks']);
        $values = array($metrics['visitorsFromSocialNetworks']);
        $descriptions = array(Piwik::translate('Referrers_TypeSocialNetworks'));

        if (!empty($metrics['visitorsFromSocialNetworksPercent'])) {
            $metrics['visitorsFromSocialNetworksPercent'] = $numberFormatter->formatPercent($metrics['visitorsFromSocialNetworksPercent'], $precision = 1);
            $values[] = $metrics['visitorsFromSocialNetworksPercent'];
            $descriptions[] = Piwik::translate('Referrers_XPercentOfVisits');
        }
        $socialNetworkParams = $this->getReferrerSparklineParams(Common::REFERRER_TYPE_SOCIAL_NETWORK);

        $view->config->addSparkline($socialNetworkParams, $values, $descriptions, @$metrics['visitorsFromSocialNetworksEvolution']);


        // CAMPAIGNS
        $metrics['visitorsFromCampaigns'] = $numberFormatter->formatNumber($metrics['visitorsFromCampaigns']);
        $values = array($metrics['visitorsFromCampaigns']);
        $descriptions = array(Piwik::translate('Referrers_TypeCampaigns'));

        if (!empty($metrics['visitorsFromCampaignsPercent'])) {
            $metrics['visitorsFromCampaignsPercent'] = $numberFormatter->formatPercent($metrics['visitorsFromCampaignsPercent'], $precision = 1);
            $values[] = $metrics['visitorsFromCampaignsPercent'];
            $descriptions[] = Piwik::translate('Referrers_XPercentOfVisits');
        }

        $searchEngineParams = $this->getReferrerSparklineParams(Common::REFERRER_TYPE_CAMPAIGN);

        $view->config->addSparkline($searchEngineParams, $values, $descriptions, @$metrics['visitorsFromCampaignsEvolution']);


        // DISTINCT SEARCH ENGINES
        $sparklineParams = $this->getDistinctSparklineUrlParams('getLastDistinctSearchEnginesGraph');
        $value = $distinctMetrics['numberDistinctSearchEngines'];
        $value = $numberFormatter->formatNumber($value);
        $description = Piwik::translate('Referrers_DistinctSearchEngines');

        $view->config->addSparkline($sparklineParams, $value, $description, @$distinctMetrics['numberDistinctSearchEnginesEvolution']);


        // DISTINCT SOCIAL NETWORKS
        $sparklineParams = $this->getDistinctSparklineUrlParams('getLastDistinctSocialNetworksGraph');
        $value = $distinctMetrics['numberDistinctSocialNetworks'];
        $value = $numberFormatter->formatNumber($value);
        $description = Piwik::translate('Referrers_DistinctSocialNetworks');

        $view->config->addSparkline($sparklineParams, $value, $description, @$distinctMetrics['numberDistinctSocialNetworksEvolution']);


        // DISTINCT WEBSITES
        $sparklineParams = $this->getDistinctSparklineUrlParams('getLastDistinctWebsitesGraph');

        $distinctMetrics['numberDistinctWebsites'] = $numberFormatter->formatNumber($distinctMetrics['numberDistinctWebsites']);
        $distinctMetrics['numberDistinctWebsitesUrls'] = $numberFormatter->formatNumber($distinctMetrics['numberDistinctWebsitesUrls']);

        $values = array($distinctMetrics['numberDistinctWebsites'], $distinctMetrics['numberDistinctWebsitesUrls']);
        $descriptions = array(Piwik::translate('Referrers_DistinctWebsites'), Piwik::translate('Referrers_UsingNDistinctUrls'));

        $view->config->addSparkline($sparklineParams, $values, $descriptions, @$distinctMetrics['numberDistinctWebsitesEvolution']);


        // DISTINCT KEYWORDS
        $sparklineParams = $this->getDistinctSparklineUrlParams('getLastDistinctKeywordsGraph');
        $value = $distinctMetrics['numberDistinctKeywords'];
        $value = $numberFormatter->formatNumber($value);
        $description = Piwik::translate('Referrers_DistinctKeywords');

        $view->config->addSparkline($sparklineParams, $value, $description, @$distinctMetrics['numberDistinctKeywordsEvolution']);


        // DISTINCT CAMPAIGNS
        $sparklineParams = $this->getDistinctSparklineUrlParams('getLastDistinctCampaignsGraph');
        $value = $distinctMetrics['numberDistinctCampaigns'];
        $value = $numberFormatter->formatNumber($value);
        $description = Piwik::translate('Referrers_DistinctCampaigns');

        $view->config->addSparkline($sparklineParams, $value, $description, @$distinctMetrics['numberDistinctCampaignsEvolution']);

        return $view->render();
    }

    private function getDistinctSparklineUrlParams($action)
    {
        return array('module' => $this->pluginName, 'action' => $action);
    }

    protected function getReferrersVisitorsByType($date = false)
    {
        if ($date === false) {
            $date = Common::getRequestVar('date', false);
        }

        // we disable the queued filters because here we want to get the visits coming from search engines
        // if the filters were applied we would have to look up for a label looking like "Search Engines"
        // which is not good when we have translations
        $dataTableReferrersType = Request::processRequest(
            "Referrers.getReferrerType", array('disable_queued_filters' => '1', 'date' => $date));

        $nameToColumnId = array(
            'visitorsFromSearchEngines'  => Common::REFERRER_TYPE_SEARCH_ENGINE,
            'visitorsFromSocialNetworks' => Common::REFERRER_TYPE_SOCIAL_NETWORK,
            'visitorsFromDirectEntry'    => Common::REFERRER_TYPE_DIRECT_ENTRY,
            'visitorsFromWebsites'       => Common::REFERRER_TYPE_WEBSITE,
            'visitorsFromCampaigns'      => Common::REFERRER_TYPE_CAMPAIGN,
        );
        $return = array();
        foreach ($nameToColumnId as $nameVar => $columnId) {
            $value = 0;
            $row = $dataTableReferrersType->getRowFromLabel($columnId);
            if ($row !== false) {
                $value = $row->getColumn(Metrics::INDEX_NB_VISITS);
            }
            $return[$nameVar] = $value;
        }
        return $return;
    }

    protected $referrerTypeToLabel = array(
        Common::REFERRER_TYPE_DIRECT_ENTRY   => 'Referrers_DirectEntry',
        Common::REFERRER_TYPE_SEARCH_ENGINE  => 'Referrers_SearchEngines',
        Common::REFERRER_TYPE_SOCIAL_NETWORK => 'Referrers_Socials',
        Common::REFERRER_TYPE_WEBSITE        => 'Referrers_Websites',
        Common::REFERRER_TYPE_CAMPAIGN       => 'Referrers_Campaigns',
    );

    public function getEvolutionGraph($typeReferrer = false, array $columns = array(), array $defaultColumns = array())
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referrers.getReferrerType');

        $view->config->add_total_row = true;

        // configure displayed columns
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }
        if (false !== $columns) {
            $columns = !is_array($columns) ? array($columns) : $columns;
        }

        if (!empty($columns)) {
            $view->config->columns_to_display = $columns;
        } elseif (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        // configure selectable columns
        $period = Common::getRequestVar('period', false);

        if (SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            $selectable = array('nb_visits', 'nb_uniq_visitors', 'nb_users', 'nb_actions');
        } else {
            $selectable = array('nb_visits', 'nb_actions');
        }
        $view->config->selectable_columns = $selectable;

        // configure displayed rows
        $visibleRows = Common::getRequestVar('rows', false);
        if ($visibleRows !== false) {
            // this happens when the row picker has been used
            $visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
            $visibleRows = array_map('urldecode', $visibleRows);

            // typeReferrer is redundant if rows are defined, so make sure it's not used
            $view->config->custom_parameters['typeReferrer'] = false;
        } else {
            // use $typeReferrer as default
            if ($typeReferrer === false) {
                $typeReferrer = Common::getRequestVar('typeReferrer', Common::REFERRER_TYPE_DIRECT_ENTRY);
            }
            $label = self::getTranslatedReferrerTypeLabel($typeReferrer);
            $total = $this->translator->translate('General_Total');

            if (!empty($view->config->rows_to_display)) {
                $visibleRows = $view->config->rows_to_display;
            } else {
                $visibleRows = array($label, $total);
            }

            $view->requestConfig->request_parameters_to_modify['rows'] = $label . ',' . $total;
        }
        $view->config->row_picker_match_rows_by = 'label';
        $view->config->rows_to_display = $visibleRows;

        $view->config->documentation = $this->translator->translate('Referrers_EvolutionDocumentation') . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation') . '<br />'
            . $this->translator->translate('Referrers_EvolutionDocumentationMoreInfo', '&quot;'
                . $this->translator->translate('Referrers_ReferrerTypes') . '&quot;');

        return $this->renderView($view);
    }

    public function getLastDistinctSearchEnginesGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctSearchEngines");
        $view->config->translations['Referrers_distinctSearchEngines'] = ucfirst($this->translator->translate('Referrers_DistinctSearchEngines'));
        $view->config->columns_to_display = array('Referrers_distinctSearchEngines');
        return $this->renderView($view);
    }

    public function getLastDistinctSocialNetworksGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctSocialNetworks");
        $view->config->translations['Referrers_distinctSocialNetworks'] = ucfirst($this->translator->translate('Referrers_DistinctSocialNetworks'));
        $view->config->columns_to_display = array('Referrers_distinctSocialNetworks');
        return $this->renderView($view);
    }

    public function getLastDistinctKeywordsGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctKeywords");
        $view->config->translations['Referrers_distinctKeywords'] = ucfirst($this->translator->translate('Referrers_DistinctKeywords'));
        $view->config->columns_to_display = array('Referrers_distinctKeywords');
        return $this->renderView($view);
    }

    public function getLastDistinctWebsitesGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctWebsites");
        $view->config->translations['Referrers_distinctWebsites'] = ucfirst($this->translator->translate('Referrers_DistinctWebsites'));
        $view->config->columns_to_display = array('Referrers_distinctWebsites');
        return $this->renderView($view);
    }

    public function getLastDistinctCampaignsGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctCampaigns");
        $view->config->translations['Referrers_distinctCampaigns'] = ucfirst($this->translator->translate('Referrers_DistinctCampaigns'));
        $view->config->columns_to_display = array('Referrers_distinctCampaigns');
        return $this->renderView($view);
    }

    /**
     * Returns the i18n-ized label for a referrer type.
     *
     * @param int $typeReferrer The referrer type. Referrer types are defined in Common class.
     * @return string The i18n-ized label.
     */
    public static function getTranslatedReferrerTypeLabel($typeReferrer)
    {
        $label = getReferrerTypeLabel($typeReferrer);
        return Piwik::translate($label);
    }

    /**
     * Returns the URL for the sparkline of visits with a specific referrer type.
     *
     * @param int $referrerType The referrer type. Referrer types are defined in Common class.
     * @return string The URL that can be used to get a sparkline image.
     */
    private function getReferrerSparklineParams($referrerType)
    {
        $totalRow = $this->translator->translate('General_Total');

        return array(
            'columns'      => array('nb_visits'),
            'rows'         => array(self::getTranslatedReferrerTypeLabel($referrerType), $totalRow),
            'typeReferrer' => $referrerType,
            'module'       => $this->pluginName,
            'action'       => 'getReferrerType'
        );
    }

    /**
     * Returns an array containing the number of distinct referrers for each
     * referrer type.
     *
     * @param bool|string $date The date to use when getting metrics. If false, the
     *                           date query param is used.
     * @return array The metrics.
     */
    private function getDistinctReferrersMetrics($date = false)
    {
        $propertyToAccessorMapping = array(
            'numberDistinctSearchEngines'  => 'getNumberOfDistinctSearchEngines',
            'numberDistinctSocialNetworks' => 'getNumberOfDistinctSocialNetworks',
            'numberDistinctKeywords'       => 'getNumberOfDistinctKeywords',
            'numberDistinctWebsites'       => 'getNumberOfDistinctWebsites',
            'numberDistinctWebsitesUrls'   => 'getNumberOfDistinctWebsitesUrls',
            'numberDistinctCampaigns'      => 'getNumberOfDistinctCampaigns',
        );

        $result = array();
        foreach ($propertyToAccessorMapping as $property => $method) {
            $result[$property] = $this->getNumericValue('Referrers.' . $method, $date);
        }
        return $result;
    }

    /**
     * Utility method that calculates evolution values for a set of current & past values
     * and sets properties on a View w/ HTML that displays the evolution percents.
     *
     * @param string $date The date of the current values.
     * @param array $currentValues Array mapping view property names w/ present values.
     * @param string $lastPeriodDate The date of the period in the past.
     * @param array $previousValues Array mapping view property names w/ past values. Keys
     *                              in this array should be the same as keys in $currentValues.
     * @return array Added current values
     */
    private function addEvolutionPropertiesToView($date, $currentValues, $lastPeriodDate, $previousValues)
    {
        foreach ($previousValues as $name => $pastValue) {
            $currentValue = $currentValues[$name];
            $evolutionName = $name . 'Evolution';

            $currentValueFormatted = NumberFormatter::getInstance()->format($currentValue);
            $pastValueFormatted    = NumberFormatter::getInstance()->format($pastValue);

            $currentValues[$evolutionName] = array(
                'currentValue' => $currentValue,
                'pastValue' => $pastValue,
                'tooltip' => Piwik::translate('General_EvolutionSummaryGeneric', array(
                    Piwik::translate('General_NVisits', $currentValueFormatted),
                    $date,
                    Piwik::translate('General_NVisits', $pastValueFormatted),
                    $lastPeriodDate,
                    CalculateEvolutionFilter::calculate($currentValue, $pastValue, $precision = 1)
                ))
            );
        }

        return $currentValues;
    }
}
