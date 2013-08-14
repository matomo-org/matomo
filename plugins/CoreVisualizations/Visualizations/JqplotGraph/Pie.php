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

use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

/**
 * Visualization that renders HTML for a Pie graph using jqPlot.
 */
class Pie extends JqplotGraph
{
    const ID = 'graphPie';

    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        $result['visualization_properties']['jqplot_graph']['max_graph_elements'] = 6;
        $result['visualization_properties']['jqplot_graph']['allow_multi_select_series_picker'] = false;
        return $result;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('pie', $properties);
    }
}