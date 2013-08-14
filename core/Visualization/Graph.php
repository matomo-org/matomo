<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Visualization;

use Piwik\Common;
use Piwik\DataTableVisualization;

/**
 * This is an abstract visualization that should be the base of any 'graph' visualization.
 * This class defines certain visualization properties that are specific to all graph types.
 * Derived visualizations can decide for themselves whether they should support individual
 * properties.
 */
abstract class Graph extends DataTableVisualization
{
    const ID = 'graph';

    /**
     * Whether the series picker should allow picking more than one series or not.
     */
    const ALLOW_MULTI_SELECT_SERIES_PICKER = 'allow_multi_select_series_picker';

    /**
     * The maximum number of rows to renderh. All other rows will be aggregated in an 'Others' row.
     */
    const MAX_GRAPH_ELEMENTS = 'max_graph_elements';

    /**
     * Array property that contains the names of columns that can be selected in the Series Picker.
     */
    const SELECTABLE_COLUMNS = 'selectable_columns';

    /**
     * Controls whether all ticks & labels are shown on a graph's x-axis or just some.
     */
    const SHOW_ALL_TICKS = 'show_all_ticks';

    /**
     * If true, a row with totals of each DataTable column is added.
     */
    const ADD_TOTAL_ROW = 'add_total_row';

    /**
     * Controls whether the Series Picker is shown or not. The Series Picker allows users to
     * choose between displaying data of different columns.
     */
    const SHOW_SERIES_PICKER = 'show_series_picker';

    /**
     * Controls whether the percentage of the total is displayed as a tooltip when hovering over
     * data points.
     * 
     * NOTE: Sometimes this percentage is meaningless (when the total of the column values is
     * not the total number of elements in the set). In this case the tooltip should not be
     * displayed.
     */
    const DISPLAY_PERCENTAGE_IN_TOOLTIP = 'display_percentage_in_tooltip';

    /**
     * Constructor.
     * 
     * @param \Piwik\ViewDataTable $view
     */
    public function __construct($view)
    {
        if ($view->show_goals) {
            $goalMetrics = array('nb_conversions', 'revenue');
            $view->visualization_properties->selectable_columns = array_merge(
                $view->visualization_properties->selectable_columns, $goalMetrics);

            $view->translations['nb_conversions'] = Piwik_Translate('Goals_ColumnConversions');
            $view->translations['revenue'] = Piwik_Translate('General_TotalRevenue');
        }
    }

    public static function getDefaultPropertyValues()
    {
        // selectable columns
        $selectableColumns = array('nb_visits', 'nb_actions');
        if (Common::getRequestVar('period', false) == 'day') { // TODO: should depend on columns datatable has.
            $selectableColumns[] = 'nb_uniq_visitors';
        }

        return array(
            'visualization_properties' => array(
                'graph' => array(
                    'add_total_row' => 0,
                    'show_all_ticks' => false,
                    'allow_multi_select_series_picker' => true,
                    'max_graph_elements' => false,
                    'selectable_columns' => $selectableColumns,
                    'show_series_picker' => true,
                    'display_percentage_in_tooltip' => true,
                )
            )
        );
    }
}