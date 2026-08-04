<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;

use Piwik\Common;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Config as JqplotGraphConfig;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class Config extends JqplotGraphConfig
{
    /**
     * Whether to show a line graph or a bar graph.
     *
     * Default value: true
     */
    public $show_line_graph = true;

    /**
     * Whether forecast values should be rendered for incomplete periods. The forecast is
     * always active; whether anything is actually drawn still depends on feasibility (line
     * chart, not {@see $disable_forecast}, and at least one incomplete period that yields a
     * renderable value). Forced off in bar mode by the visualization's beforeLoadDataTable()
     * since the BarRenderer has nowhere to draw forecast points.
     *
     * Default value: true
     */
    public $show_forecast = true;

    /**
     * Hard gate that suppresses the forecast feature regardless of {@see $show_forecast}.
     * Skips the precompute path so callers that fan out into label-filtered inner API calls
     * (e.g. row evolution popovers) do not pay for the sub-period blob fetches the forecast
     * builder consumes.
     *
     * Default value: false
     */
    public $disable_forecast = false;

    public function __construct()
    {
        parent::__construct();

        $this->show_all_views_icons = false;
        $this->show_table             = false;
        $this->show_table_all_columns = false;
        $this->hide_annotations_view  = false;
        $this->x_axis_step_size       = false;
        $this->show_line_graph        = true;
        $this->show_forecast          = true;
        $this->disable_forecast       = false;

        $this->addPropertiesThatShouldBeAvailableClientSide(['show_line_graph']);
        $this->addPropertiesThatCanBeOverwrittenByQueryParams(['show_line_graph']);

        $period = Common::getRequestVar('period');
        if ($period !== 'range') {
            $this->show_limit_control = true;
            $this->show_periods = true;
        }
    }
}
