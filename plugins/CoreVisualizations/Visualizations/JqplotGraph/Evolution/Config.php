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
     * Whether forecast values should be rendered for incomplete periods.
     *
     * Default value: false
     */
    public $show_forecast = false;

    public function __construct()
    {
        parent::__construct();

        $this->show_all_views_icons = false;
        $this->show_table             = false;
        $this->show_table_all_columns = false;
        $this->hide_annotations_view  = false;
        $this->x_axis_step_size       = false;
        $this->show_line_graph        = true;
        $this->show_forecast          = false;

        $this->addPropertiesThatShouldBeAvailableClientSide(['show_line_graph', 'show_forecast']);
        $this->addPropertiesThatCanBeOverwrittenByQueryParams(['show_line_graph', 'show_forecast']);

        $period = Common::getRequestVar('period');
        if ($period !== 'range') {
            $this->show_limit_control = true;
            $this->show_periods = true;
        }
    }
}
