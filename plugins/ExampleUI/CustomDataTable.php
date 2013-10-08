<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleUI
 */

namespace Piwik\Plugins\ExampleUI;

use Piwik\View;
use Piwik\ViewDataTable;

class CustomDataTable
{
    public function render($value, $label, $apiAction, $controllerAction)
    {
        $view = ViewDataTable::factory('table', $apiAction, $controllerAction);

        $view->translations['value'] = $value;
        $view->translations['label'] = $label;
        $view->filter_sort_column = 'label';
        $view->filter_sort_order = 'asc';
        $view->filter_limit = 24;
        $view->y_axis_unit = '°C'; // useful if the user requests the bar graph
        $view->show_exclude_low_population = false;
        $view->show_table_all_columns = false;
        $view->visualization_properties->setForVisualization(
            'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\HtmlTable',
            'disable_row_evolution',
            true
        );
        $view->visualization_properties->setForVisualization(
            'Piwik\\Plugins\\CoreVisualizations\\Visualizations\\JqplotGraph',
            'max_graph_elements',
            24
        );

        return $view->render();
    }

}