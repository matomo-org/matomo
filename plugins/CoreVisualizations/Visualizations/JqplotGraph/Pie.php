<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
    const FOOTER_ICON       = 'plugins/Morpheus/images/chart_pie.png';
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

        $this->config->show_all_ticks = true;
        $this->config->datatable_js_type = 'JqplotPieGraphDataTable';
    }

    public function afterAllFiltersAreApplied()
    {
        parent::afterAllFiltersAreApplied();

        $metricColumn = reset($this->config->columns_to_display);

        if ($metricColumn == 'label') {
            $metricColumn = next($this->config->columns_to_display);
        }

        $this->config->columns_to_display = array($metricColumn ? : 'nb_visits');
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('pie', $properties);
    }
}
