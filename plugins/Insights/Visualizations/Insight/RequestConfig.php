<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Insights\Visualizations\Insight;

use Piwik\ViewDataTable\RequestConfig as VisualizationRequestConfig;

class RequestConfig extends VisualizationRequestConfig
{
    public $min_impact_percent = 1;
    public $min_growth_percent = 20;
    public $compared_to_x_periods_ago = 1;
    public $order_by = 'absolute';
    public $filter_by = '';
    public $limit_increaser = '13';
    public $limit_decreaser = '12';

    public function __construct()
    {
        $this->disable_generic_filters = true;
        $this->disable_queued_filters  = true;

        $properties = array(
            'min_growth_percent',
            'min_impact_percent',
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
