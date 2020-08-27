<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

/**
 * Visualization that renders HTML for a Pie graph using jqPlot.
 */
class Pie extends JqplotGraph
{
    const ID = 'graphPie';
    const FOOTER_ICON       = 'icon-chart-pie';
    const FOOTER_ICON_TITLE = 'General_Piechart';

    public static function getDefaultConfig()
    {
        $config = new Config();
        $config->max_graph_elements = 6;
        $config->allow_multi_select_series_picker = false;

        return $config;
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->checkRequestIsNotForMultiplePeriods();

        $this->config->show_all_ticks = true;
        $this->config->datatable_js_type = 'JqplotPieGraphDataTable';
    }

    protected function ensureValidColumnsToDisplay()
    {
        parent::ensureValidColumnsToDisplay();

        $columnsToDisplay = $this->config->columns_to_display;

        // Ensure only one column_to_display - it is a pie graph after all!
        $metricColumn = reset($columnsToDisplay);

        // Set to a sensible default if no suitable value was found
        $this->config->columns_to_display = array($metricColumn ? : 'nb_visits');
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('pie', $properties, $this);
    }
}
