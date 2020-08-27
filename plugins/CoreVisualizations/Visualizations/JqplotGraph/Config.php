<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

use Piwik\Plugins\CoreVisualizations\Visualizations\Graph\Config as GraphConfig;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class Config extends GraphConfig
{
    /**
     * The name of the JavaScript class to use as this graph's external series toggle. The class
     * must be a subclass of JQPlotExternalSeriesToggle.
     *
     * @see self::EXTERNAL_SERIES_TOGGLE_SHOW_ALL
     *
     * Default value: false
     */
    public $external_series_toggle = false;

    /**
     * Whether the graph should show all loaded series upon initial display.
     *
     * @see self::EXTERNAL_SERIES_TOGGLE
     *
     * Default value: false
     */
    public $external_series_toggle_show_all = false;

    /**
     * The number of x-axis ticks for each x-axis label.
     *
     * Default: 2
     */
    public $x_axis_step_size = 2;

    public function __construct()
    {
        parent::__construct();

        $this->show_exclude_low_population = false;
        $this->show_offset_information     = false;
        $this->show_pagination_control     = false;
        $this->show_exclude_low_population = false;
        $this->show_search                 = false;
        $this->show_export_as_image_icon   = true;
        $this->y_axis_unit                 = '';

        $this->addPropertiesThatShouldBeAvailableClientSide(array(
            'external_series_toggle',
            'external_series_toggle_show_all'
        ));

        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array('x_axis_step_size'));
    }

}
