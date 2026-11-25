<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

use Piwik\Plugins\CoreVisualizations\Visualizations\Graph\Config as GraphConfig;

/**
 * Display properties of the jqPlot graph visualizations.
 *
 * Set them via `$view->config` in Report::configureView().
 *
 * @api
 */
class Config extends GraphConfig
{
    /**
     * The name of the JavaScript class to use as this graph's external series toggle. The class
     * must be a subclass of JQPlotExternalSeriesToggle.
     *
     * @see $external_series_toggle_show_all
     *
     * Default value: false
     *
     * @var string|false
     * @internal
     */
    public $external_series_toggle = false;

    /**
     * Whether the graph should show all loaded series upon initial display.
     *
     * @see $external_series_toggle
     *
     * Default value: false
     *
     * @var bool
     * @internal
     */
    public $external_series_toggle_show_all = false;

    /**
     * The number of x-axis ticks for each x-axis label.
     *
     * Default: 2
     *
     * @var int|false
     */
    public $x_axis_step_size = 2;

    /**
     * @internal
     */
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
            'external_series_toggle_show_all',
        ));

        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array('x_axis_step_size'));
    }
}
