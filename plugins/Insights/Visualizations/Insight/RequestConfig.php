<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Insights\Visualizations\Insight;

use Piwik\Plugins\Insights\InsightReport;
use Piwik\Plugins\Insights\Visualizations\Insight;
use Piwik\ViewDataTable\RequestConfig as VisualizationRequestConfig;

class RequestConfig extends VisualizationRequestConfig
{
    public $min_impact_percent = '0.1';
    public $min_growth_percent = 1;
    public $compared_to_x_periods_ago = 1;
    public $order_by = InsightReport::ORDER_BY_ABSOLUTE;
    public $filter_by = '';
    public $limit_increaser = '5';
    public $limit_decreaser = '5';

    public function __construct()
    {
        $this->disable_generic_filters = true;

        $properties = array(
            'min_growth_percent',
            'order_by',
            'compared_to_x_periods_ago',
            'filter_by',
            'limit_increaser',
            'limit_decreaser',
            'filter_limit'
        );

        $this->addPropertiesThatShouldBeAvailableClientSide($properties);
        $this->addPropertiesThatCanBeOverwrittenByQueryParams($properties);
    }

}
