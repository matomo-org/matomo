<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Insights\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
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
    const ID = 'insightsVisualization';
    const TEMPLATE_FILE     = '@Insights/insightVisualization.twig';
    const FOOTER_ICON_TITLE = 'Insights';
    const FOOTER_ICON       = 'plugins/Insights/images/idea.png';

    public function beforeLoadDataTable()
    {
        if (!self::canDisplayViewDataTable($this)) {
            return;
        }

        if (!$this->requestConfig->filter_limit) {
            $this->requestConfig->filter_limit = 10;
        }

        $report = $this->requestConfig->apiMethodToRequestDataTable;
        $report = str_replace('.', '_', $report);

        $this->requestConfig->apiMethodToRequestDataTable = 'Insights.getInsights';

        $this->requestConfig->request_parameters_to_modify = array(
            'reportUniqueId' => $report,
            'minImpactPercent' => $this->requestConfig->min_impact_percent,
            'minGrowthPercent' => $this->requestConfig->min_growth_percent,
            'comparedToXPeriods' => $this->requestConfig->compared_to_x_periods_ago,
            'orderBy'  => $this->requestConfig->order_by,
            'filterBy' => $this->requestConfig->filter_by,
            'limitIncreaser' => $this->getLimitIncrease(),
            'limitDecreaser' => $this->getLimitDecrease(),
        );
    }

    private function getLimitIncrease()
    {
        $filterLimit   = $this->requestConfig->filter_limit;
        $limitIncrease = 0;

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
        $this->config->show_pagination_control = false;
        $this->config->show_offset_information = false;
        $this->config->show_search = false;

        if (!self::canDisplayViewDataTable($this)) {
            $this->assignTemplateVar('cannotDisplayReport', true);
            return;
        }

        $period = Common::getRequestVar('period', null, 'string');
        $this->assignTemplateVar('period', $period);
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        $period = Common::getRequestVar('period', null, 'string');
        $date   = Common::getRequestVar('date', null, 'string');

        $canGenerateInsights = API::getInstance()->canGenerateInsights($date, $period);

        if (!$canGenerateInsights) {
            return false;
        }

        if ($view->requestConfig->apiMethodToRequestDataTable
            && 0 === strpos($view->requestConfig->apiMethodToRequestDataTable, 'DBStats')) {
            return false;
        }

        return parent::canDisplayViewDataTable($view);
    }
}
