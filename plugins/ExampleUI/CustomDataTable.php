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

        $view->config->translations['value'] = $value;
        $view->config->translations['label'] = $label;
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order = 'asc';
        $view->requestConfig->filter_limit = 24;
        $view->config->y_axis_unit = '°C'; // useful if the user requests the bar graph
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns = false;
        $view->config->disable_row_evolution = true;
        $view->config->max_graph_elements = 24;

        return $view->render();
    }

}