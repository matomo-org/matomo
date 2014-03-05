<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Insights\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Period\Range;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugin\Visualization;
use Piwik\Plugins\Insights\Model;

/**
 * InsightsVisualization Visualization.
 *
 * @property Insight\Config        $config
 * @property Insight\RequestConfig $requestConfig
 */
class Insight extends Visualization
{
    const ID = 'insightsVisualization';
    const TEMPLATE_FILE     = '@Insights/insightVisualization.twig';
    const FOOTER_ICON_TITLE = 'Insights';
    const FOOTER_ICON       = 'plugins/Insights/images/idea.png';

    public function beforeLoadDataTable()
    {
        $report = $this->requestConfig->apiMethodToRequestDataTable;
        $report = str_replace('.', '_', $report);

        if (!$this->requestConfig->filter_limit) {
            $this->requestConfig->filter_limit = 10;
        }

        $limit = $this->requestConfig->filter_limit;
        $limitDecrease = 0;
        $limitIncrease = 0;

        if ($this->requestConfig->limit_increaser && !$this->requestConfig->limit_decreaser) {
            $limitIncrease = $limit;
        } elseif (!$this->requestConfig->limit_increaser && $this->requestConfig->limit_decreaser) {
            $limitDecrease = $limit;
        } elseif ($this->requestConfig->limit_increaser && $this->requestConfig->limit_decreaser) {
            $limitIncrease = round($limit / 2);
            $limitDecrease = $limit - $limitIncrease;
        }

        $this->requestConfig->apiMethodToRequestDataTable = 'Insights.getInsights';
        $this->requestConfig->request_parameters_to_modify = array(
            'reportUniqueId' => $report,
            'minImpactPercent' => $this->requestConfig->min_impact_percent,
            'minGrowthPercent' => $this->requestConfig->min_growth_percent,
            'comparedToXPeriods' => $this->requestConfig->compared_to_x_periods_ago,
            'orderBy' => $this->requestConfig->order_by,
            'filterBy' => $this->requestConfig->filter_by,
            'limitIncreaser' => $limitIncrease,
            'limitDecreaser' => $limitDecrease,
        );
    }

    public static function getDefaultConfig()
    {
        return new Insight\Config();
    }

    public static function getDefaultRequestConfig()
    {
        return new Insight\RequestConfig();
    }

    public function isThereDataToDisplay()
    {
        return true;
    }

    public function afterAllFiltersAreApplied()
    {
        $this->assignTemplateVar('period', Common::getRequestVar('period', null, 'string'));
    }

    public function beforeRender()
    {
        $this->config->datatable_js_type  = 'InsightsDataTable';
        $this->config->show_limit_control = true;
        $this->config->show_pagination_control = false;
        $this->config->show_offset_information = false;
        $this->config->show_search = false;
        $this->config->title = 'Insights of ' . $this->config->title;
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        $period = Common::getRequestVar('period', null, 'string');
        $date   = Common::getRequestVar('date', null, 'string');

        try {
            $model    = new Model();
            $lastDate = $model->getLastDate($date, $period, 1);
        } catch (\Exception $e) {
            return false;
        }

        if (empty($lastDate)) {
            return false;
        }

        return parent::canDisplayViewDataTable($view);
    }
}
