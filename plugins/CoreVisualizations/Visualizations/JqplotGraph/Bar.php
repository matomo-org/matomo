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
 * Visualization that renders HTML for a Bar graph using jqPlot.
 */
class Bar extends JqplotGraph
{
    const ID = 'graphVerticalBar';
    const FOOTER_ICON       = 'plugins/Morpheus/images/chart_bar.png';
    const FOOTER_ICON_TITLE = 'General_VBarGraph';

    public function beforeRender()
    {
        parent::beforeRender();

        $this->config->datatable_js_type = 'JqplotBarGraphDataTable';
    }

    public static function getDefaultConfig()
    {
        $config = new Config();
        $config->max_graph_elements = 6;

        return $config;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('bar', $properties);
    }
}
