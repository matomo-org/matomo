<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
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

    public function __construct($view)
    {
        parent::__construct($view);
        $view->visualization_properties->show_all_ticks = true;
        $view->datatable_js_type = 'JqplotPieGraphDataTable';

        // make sure only one non-label column is displayed
        $view->after_data_loaded_functions[] = function ($dataTable) use ($view) {
            $metricColumn = reset($view->columns_to_display);
            if ($metricColumn == 'label') {
                $metricColumn = next($view->columns_to_display);
            }
            $view->columns_to_display = array($metricColumn ?: 'nb_visits');
        };
    }

    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        $result['visualization_properties']['graph']['max_graph_elements'] = 6;
        $result['visualization_properties']['graph']['allow_multi_select_series_picker'] = false;
        return $result;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('pie', $properties);
    }
}