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
    const FOOTER_ICON       = 'icon-chart-bar';
    const FOOTER_ICON_TITLE = 'General_VBarGraph';
    
    public function beforeLoadDataTable()
    {
        parent::beforeLoadDataTable();

        $this->config->datatable_js_type = 'JqplotBarGraphDataTable';
    }

    public static function getDefaultConfig()
    {
        $config = new Config();
        $config->max_graph_elements = 6;

        return $config;
    }

    public function afterAllFiltersAreApplied()
    {
        parent::afterAllFiltersAreApplied();

        // Remove 'label' from columns_to_display and ensure there is at least one other column in the list
        $columnsToDisplay = $this->config->columns_to_display;

        if (! empty($columnsToDisplay) && $columnsToDisplay[0] == 'label') {
            array_shift($columns_to_display);
        }

        if (empty($columnsToDisplay)) {
            $columnsToDisplay = array('nb_visits');
        }

        $this->config->columns_to_display = $columnsToDisplay;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('bar', $properties);
    }
}
