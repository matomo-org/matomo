<?php
/**
 * Piwik - free/libre analytics platform
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
use Piwik\DataTable\Filter\ColumnCallbackAddColumnPercentage;
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
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends Base
{
    const TOTAL_DIRECT_ENTRIES_METRIC_NAME = 'Referrers_directEntries';

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Referrers_ReferrersOverview');
        $this->documentation = '';
        $this->processedMetrics = [
            // none
        ];

        // TODO: make this a static var (or the similar var in API)
        $this->metrics = [
            'visitorsFromSearchEngines',
            'visitorsFromSearchEngines_percent',
            'visitorsFromSocialNetworks',
            'visitorsFromSocialNetworks_percent',
            'visitorsFromDirectEntry',
            'visitorsFromDirectEntry_percent',
            'visitorsFromWebsites',
            'visitorsFromWebsites_percent',
            'visitorsFromCampaigns',
            'visitorsFromCampaigns_percent',
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
                $period = Common::getRequestVar('period');

                /** @var DataTable $previousData */
                $previousData = Request::processRequest('Referrers.get', ['date' => $lastPeriodDate]);
                $previousDataRow = $previousData->getFirstRow();

                $columnsWithEvolution = ['visitorsFromDirectEntry', 'visitorsFromSearchEngines', 'visitorsFromCampaigns', 'visitorsFromSocialNetworks',
                    Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME];
                $view->config->compute_evolution = function ($columns) use ($date, $lastPeriodDate, $previousDataRow, $columnsWithEvolution) {
                    $value = reset($columns);
                    $columnName = key($columns);

                    if (!in_array($columnName, $columnsWithEvolution)) {
                        return;
                    }

                    $pastValue = $previousDataRow->getColumn($columnName);

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
        $view->config->addSparklineMetric(['visitorsFromDirectEntry', 'visitorsFromDirectEntry_percent'], 10);
        $view->config->addPlaceholder(11);
        $view->config->addSparklineMetric(['visitorsFromSearchEngines', 'visitorsFromSearchEngines_percent'], 20);
        $view->config->addPlaceholder(21);
        $view->config->addSparklineMetric(['visitorsFromCampaigns', 'visitorsFromCampaigns_percent'], 30);
        $view->config->addPlaceholder(31);
        $view->config->addSparklineMetric(['visitorsFromSocialNetworks'], 40);
        $view->config->addPlaceholder(41);
        $view->config->addSparklineMetric([Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME], 50);
        $view->config->addPlaceholder(51);
    }

    private function getSparklineTranslations()
    {
        $translations = [
            'visitorsFromDirectEntry' => Piwik::translate('Referrers_TypeDirectEntries'),
            Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME => Piwik::translate('Referrers_DistinctKeywords'),
            'visitorsFromSearchEngines' => Piwik::translate('Referrers_TypeSearchEngines'),
            'visitorsFromSocialNetworks' => Piwik::translate('Referrers_TypeSocialNetworks'),
            'visitorsFromCampaigns' => Piwik::translate('Referrers_TypeCampaigns'),
        ];

        foreach ($translations as $name => $label) {
            $translations[$name . '_percent'] = Piwik::translate('Referrers_XPercentOfVisits');
        }

        return $translations;
    }
}