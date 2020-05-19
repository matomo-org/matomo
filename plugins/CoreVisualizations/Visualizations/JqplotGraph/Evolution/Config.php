<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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

    public function __construct()
    {
        parent::__construct();

        $this->show_all_views_icons = false;
        $this->show_table             = false;
        $this->show_table_all_columns = false;
        $this->hide_annotations_view  = false;
        $this->x_axis_step_size       = false;
        $this->show_line_graph        = true;

        $this->addPropertiesThatShouldBeAvailableClientSide(array('show_line_graph'));
        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array('show_line_graph'));

        $period = Common::getRequestVar('period');
        if ($period !== 'range') {
            $this->show_limit_control = true;
            $this->show_periods = true;
        }
    }

}
