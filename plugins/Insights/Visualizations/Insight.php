<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\Visualizations;

use Piwik\Common;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugin\Visualization;
use Piwik\Plugins\Insights\API;

/**
 * InsightsVisualization Visualization.
 *
 * @property Insight\RequestConfig $requestConfig
 */
class Insight extends Visualization
{
    public const ID = 'insightsVisualization';
    public const TEMPLATE_FILE     = '@Insights/insightVisualization.twig';
    public const FOOTER_ICON_TITLE = 'Insights';
    public const FOOTER_ICON       = 'icon-insights';

    public function beforeLoadDataTable()
    {
        // Insights compares the report with another date itself, so the API must not run it through the comparison
        // filter even when supportsComparison() is true
        $this->config->disable_comparison = true;

        if (!self::canDisplayViewDataTable($this)) {
            return;
        }

        if (!$this->requestConfig->filter_limit) {
            $this->requestConfig->filter_limit = 10;
        }

        $report = $this->requestConfig->apiMethodToRequestDataTable;
        $report = str_replace('.', '_', $report);

        if (!empty($this->requestConfig->request_parameters_to_modify['reportUniqueId'])) {
            $report = $this->requestConfig->request_parameters_to_modify['reportUniqueId'];
        }

        $this->requestConfig->apiMethodToRequestDataTable = 'Insights.getInsights';

        $this->requestConfig->request_parameters_to_modify = array(
            'reportUniqueId' => $report,
            'minImpactPercent' => $this->requestConfig->min_impact_percent,
            'minGrowthPercent' => $this->requestConfig->min_growth_percent,
            'comparedToXPeriods' => $this->getComparedToXPeriodsAgo(),
            'comparedToDate' => $this->getComparedDate() ?? '',
            'orderBy'  => $this->requestConfig->order_by,
            'filterBy' => $this->requestConfig->filter_by,
            'pivotBy' => false,
            'pivotByColumn' => false,
            'limitIncreaser' => $this->getLimitIncrease(),
            'limitDecreaser' => $this->getLimitDecrease(),
        );
    }

    /**
     * Insights handle every comparison themselves: they compare with the date selected in the date selector when they
     * can, and otherwise say which comparison they ignore, instead of the generic message of the visualizations that do
     * not support comparison.
     */
    public function supportsComparison()
    {
        return true;
    }

    /**
     * Returns the date selected in the date selector to compare the report with, or null when there is none Insights
     * can compare with: Insights compares with a single date of the same period type, and a date range with a range of
     * the same length.
     */
    private function getComparedDate(): ?string
    {
        $compareDates = array_values($this->requestConfig->compareDates);
        $comparePeriods = array_values($this->requestConfig->comparePeriods);

        if (count($compareDates) !== 1 || count($comparePeriods) !== 1) {
            return null;
        }

        $period = Common::getRequestVar('period', null, 'string');

        if ($comparePeriods[0] !== $period) {
            return null;
        }

        // ranges of different lengths would make most rows look like strong movers only because one is longer
        if ($period === 'range' && !$this->areRangesOfSameLength(Common::getRequestVar('date', null, 'string'), $compareDates[0])) {
            return null;
        }

        return $compareDates[0];
    }

    private function areRangesOfSameLength(string $date, string $comparedDate): bool
    {
        try {
            return (new Range('day', $date))->getNumberOfSubperiods()
                === (new Range('day', $comparedDate))->getNumberOfSubperiods();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getComparedToXPeriodsAgo()
    {
        $period = Common::getRequestVar('period', null, 'string');

        if ($period === 'month' && $this->requestConfig->compared_to_x_periods_ago > 1) {
            return 12;
        }

        if ($period !== 'day') {
            return 1;
        }

        return $this->requestConfig->compared_to_x_periods_ago;
    }

    private function getLimitIncrease()
    {
        $filterLimit   = $this->requestConfig->filter_limit;
        $limitIncrease = 0;

        if ($filterLimit == -1) {
            return -1;
        }

        if ($this->requestConfig->limit_increaser && !$this->requestConfig->limit_decreaser) {
            $limitIncrease = $filterLimit;
        } elseif ($this->requestConfig->limit_increaser && $this->requestConfig->limit_decreaser) {
            $limitIncrease = round($filterLimit / 2);
        }

        return $limitIncrease;
    }

    private function getLimitDecrease()
    {
        $filterLimit   = $this->requestConfig->filter_limit;

        if ($filterLimit == -1) {
            return -1;
        }

        $limitDecrease = $filterLimit - $this->getLimitIncrease();

        return abs($limitDecrease);
    }

    public static function getDefaultRequestConfig()
    {
        return new Insight\RequestConfig();
    }

    public function isThereDataToDisplay()
    {
        return true;
    }

    public function beforeRender()
    {
        $this->config->datatable_js_type = 'InsightsDataTable';
        $this->config->show_limit_control = true;
        $this->config->show_pivot_by_subtable = false;
        $this->config->show_pagination_control = false;
        $this->config->show_offset_information = false;
        $this->config->show_search = false;
        $this->config->show_export_as_rss_feed = false;

        if (!self::canDisplayViewDataTable($this)) {
            $this->assignTemplateVar('cannotDisplayReport', true);
            return;
        }

        $period = Common::getRequestVar('period', null, 'string');
        $this->assignTemplateVar('period', $period);
        $this->assignTemplateVar('isComparedToSelectedDate', null !== $this->getComparedDate());

        $this->addIgnoredComparisonMessages();
    }

    private function addIgnoredComparisonMessages(): void
    {
        $messages = [];

        if (!empty($this->requestConfig->compareSegments)) {
            $messages[] = Piwik::translate('Insights_ComparedSegmentsIgnored');
        }

        $isComparingDates = !empty($this->requestConfig->compareDates) || !empty($this->requestConfig->comparePeriods);
        $lastDate = $this->dataTable ? $this->dataTable->getMetadata('lastDate') : null;
        $lastPeriod = $this->dataTable ? $this->dataTable->getMetadata('period') : null;

        if ($isComparingDates && null === $this->getComparedDate() && $lastDate && $lastPeriod) {
            $comparedPeriod = Period\Factory::build($lastPeriod, $lastDate)->getLocalizedShortString();
            $messages[] = Piwik::translate('Insights_SelectedComparisonIgnored', Common::sanitizeInputValue($comparedPeriod));
        }

        if (empty($messages)) {
            return;
        }

        if (!empty($this->config->show_footer_message)) {
            array_unshift($messages, $this->config->show_footer_message);
        }

        $this->config->show_footer_message = implode('<br/>', $messages);
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        $period = Common::getRequestVar('period', null, 'string');
        $date   = Common::getRequestVar('date', null, 'string');

        $canGenerateInsights = API::getInstance()->canGenerateInsights($date, $period);

        if (!$canGenerateInsights) {
            return false;
        }

        if (
            $view->requestConfig->apiMethodToRequestDataTable
            && str_starts_with($view->requestConfig->apiMethodToRequestDataTable, 'DBStats')
        ) {
            return false;
        }

        return parent::canDisplayViewDataTable($view);
    }
}
