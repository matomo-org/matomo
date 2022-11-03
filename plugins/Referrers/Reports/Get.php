<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Referrers\Reports;


use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\Date;
use Piwik\NumberFormatter;
use Piwik\Period;
use Piwik\Period\Factory;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\Referrers\Archiver;
use Piwik\Plugins\Referrers\Controller;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends Base
{
    const TOTAL_DIRECT_ENTRIES_METRIC_NAME = 'Referrers_directEntries';

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Referrers_ReferrersOverview');
        $this->documentation = Piwik::translate('Referrers_ReferrersOverviewDocumentation');
        $this->processedMetrics = [
            // none
        ];
        $this->metrics = [
            'Referrers_visitorsFromSearchEngines',
            'Referrers_visitorsFromSearchEngines_percent',
            'Referrers_visitorsFromSocialNetworks',
            'Referrers_visitorsFromSocialNetworks_percent',
            'Referrers_visitorsFromDirectEntry',
            'Referrers_visitorsFromDirectEntry_percent',
            'Referrers_visitorsFromWebsites',
            'Referrers_visitorsFromWebsites_percent',
            'Referrers_visitorsFromCampaigns',
            'Referrers_visitorsFromCampaigns_percent',
            Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME,
            Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME,
            Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME,
            Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME,
            Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME,
        ];
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        // empty
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)
            && $view instanceof Sparklines
        ) {
            $this->addSparklineColumns($view);
            $view->config->addTranslations($this->getSparklineTranslations());

            // add evolution values
            list($lastPeriodDate, $ignore) = Range::getLastDate();
            if ($lastPeriodDate !== false) {
                $date = Common::getRequestVar('date');

                /** @var DataTable $previousData */
                $previousData = Request::processRequest('Referrers.get', ['date' => $lastPeriodDate]);
                $previousDataRow = $previousData->getFirstRow();

                $view->config->compute_evolution = function ($columns) use ($date, $lastPeriodDate, $previousDataRow) {
                    $value = reset($columns);
                    $columnName = key($columns);

                    if (!in_array($columnName, $this->metrics)) {
                        return;
                    }

                    $pastValue = $previousDataRow ? $previousDataRow->getColumn($columnName) : 0;

                    $currentValueFormatted = NumberFormatter::getInstance()->format($value);
                    $pastValueFormatted    = NumberFormatter::getInstance()->format($pastValue);

                    return [
                        'currentValue' => $value,
                        'pastValue' => $pastValue,
                        'tooltip' => Piwik::translate('General_EvolutionSummaryGeneric', array(
                            Piwik::translate('General_NVisits', $currentValueFormatted),
                            $date,
                            Piwik::translate('General_NVisits', $pastValueFormatted),
                            $lastPeriodDate,
                            CalculateEvolutionFilter::calculate($value, $pastValue, $precision = 1)
                        )),
                    ];
                };
            }
        }
    }

    /**
     * Returns the pretty date representation
     *
     * @param $date string
     * @param $period string
     * @return string Pretty date
     */
    public static function getPrettyDate($date, $period)
    {
        return self::getCalendarPrettyDate(Factory::build($period, Date::factory($date)));
    }

    /**
     * Returns a prettified date string for use in period selector widget.
     *
     * @param Period $period The period to return a pretty string for.
     * @return string
     * @api
     */
    public static function getCalendarPrettyDate($period)
    {
        if ($period instanceof Month) {
            // show month name when period is for a month

            return $period->getLocalizedLongString();
        } else {
            return $period->getPrettyString();
        }
    }

    private function addSparklineColumns(Sparklines $view)
    {
        $directEntry = Controller::getTranslatedReferrerTypeLabel(Common::REFERRER_TYPE_DIRECT_ENTRY);
        $directEntry = urlencode($directEntry);

        $website = Controller::getTranslatedReferrerTypeLabel(Common::REFERRER_TYPE_WEBSITE);
        $website = urlencode($website);

        $searchEngine = Controller::getTranslatedReferrerTypeLabel(Common::REFERRER_TYPE_SEARCH_ENGINE);
        $searchEngine = urlencode($searchEngine);

        $campaigns = Controller::getTranslatedReferrerTypeLabel(Common::REFERRER_TYPE_CAMPAIGN);
        $campaigns = urlencode($campaigns);

        $socialNetworks = Controller::getTranslatedReferrerTypeLabel(Common::REFERRER_TYPE_SOCIAL_NETWORK);
        $socialNetworks = urlencode($socialNetworks);

        $total = Piwik::translate('General_Total');

        $view->config->addSparklineMetric(['Referrers_visitorsFromDirectEntry', 'Referrers_visitorsFromDirectEntry_percent'], 10, ['rows' => $directEntry . ',' . $total]);
        $view->config->addSparklineMetric(['Referrers_visitorsFromWebsites', 'Referrers_visitorsFromWebsites_percent'], 20, ['rows' => $website . ',' . $total]);
        $view->config->addSparklineMetric(['Referrers_visitorsFromSearchEngines', 'Referrers_visitorsFromSearchEngines_percent'], 30, ['rows' => $searchEngine . ',' . $total]);
        $view->config->addSparklineMetric(['Referrers_visitorsFromSocialNetworks', 'Referrers_visitorsFromSocialNetworks_percent'], 40, ['rows' => $socialNetworks . ',' . $total]);
        $view->config->addSparklineMetric(['Referrers_visitorsFromCampaigns', 'Referrers_visitorsFromCampaigns_percent'], 50, ['rows' => $campaigns . ',' . $total]);
        $view->config->addSparklineMetric([Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME], 50);
        $view->config->addSparklineMetric([Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME], 60);
        $view->config->addSparklineMetric([Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME], 70);
        $view->config->addSparklineMetric([Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME], 80);
        $view->config->addSparklineMetric([Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME], 90);
    }

    private function getSparklineTranslations()
    {
        $translations = [
            'Referrers_visitorsFromDirectEntry' => Piwik::translate('Referrers_TypeDirectEntries'),
            'Referrers_visitorsFromWebsites' => Piwik::translate('Referrers_TypeWebsites'),
            'Referrers_visitorsFromSearchEngines' => Piwik::translate('Referrers_TypeSearchEngines'),
            'Referrers_visitorsFromSocialNetworks' => Piwik::translate('Referrers_TypeSocialNetworks'),
            'Referrers_visitorsFromCampaigns' => Piwik::translate('Referrers_TypeCampaigns'),
        ];

        foreach ($translations as $name => $label) {
            $translations[$name . '_percent'] = Piwik::translate('Referrers_XPercentOfVisits');
        }

        $translations = array_merge($translations, [
            Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME => Piwik::translate('Referrers_DistinctSearchEngines'),
            Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME => Piwik::translate('Referrers_DistinctSocialNetworks'),
            Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME => Piwik::translate('Referrers_DistinctWebsites'),
            Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME => Piwik::translate('Referrers_DistinctKeywords'),
            Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME => Piwik::translate('Referrers_DistinctCampaigns'),
        ]);

        return $translations;
    }
}