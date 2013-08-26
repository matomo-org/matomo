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
use Piwik\DataTable\Row;
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
     * Contains the column (if any) of the values used in the Row Picker.
     * 
     * @see self::ROW_PICKER_VISIBLE_VALUES
     */
    const ROW_PICKER_VALUE_COLUMN = 'row_picker_match_rows_by';

    /**
     * Contains the list of values available for the Row Picker.
     * TODO: row_picker_visible_rows & selectable_rows are different, but it seems like they shouldn't
     *       be...
     * 
     * @see self::ROW_PICKER_VALUE_COLUMN
     */
    const ROW_PICKER_VISIBLE_VALUES = 'row_picker_visible_rows';

    /**
     * Contains the list of values available for the Row Picker. Currently set to be all visible
     * rows, if the row_picker_match_rows_by property is set.
     * 
     * @see self::ROW_PICKER_VALUE_COLUMN
     */
    const SELECTABLE_ROWS = 'selectable_rows';

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

    public static $clientSideProperties = array(
        'show_series_picker',
        'allow_multi_select_series_picker',
        'selectable_columns',
        'selectable_rows'
    );

    public static $clientSideParameters = array(
        'columns'
    );

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

        // TODO: this should not be required here. filter_limit should not be a view property, instead HtmlTable should use 'limit' or something,
        //       and manually set request_parameters_to_modify['filter_limit'] based on that. (same for filter_offset).
        $view->request_parameters_to_modify['filter_limit'] = false;

        if ($view->visualization_properties->max_graph_elements) {
            $view->request_parameters_to_modify['filter_truncate'] = $view->visualization_properties->max_graph_elements - 1;
        }

        $this->transformSelectableColumns($view);
        $this->transformSelectableRows($view);
    }

    public static function getDefaultPropertyValues()
    {
        return array(
            'visualization_properties' => array(
                'graph' => array(
                    'add_total_row' => 0,
                    'show_all_ticks' => false,
                    'allow_multi_select_series_picker' => true,
                    'max_graph_elements' => false,
                    'selectable_columns' => false,
                    'show_series_picker' => true,
                    'display_percentage_in_tooltip' => true,
                    'row_picker_match_rows_by' => false,
                    'row_picker_visible_rows' => array(),
                )
            )
        );
    }

    /**
     * TODO
     */
    private function transformSelectableColumns($view)
    {
        $view->after_data_loaded_functions[] = function ($dataTable, $view) {
            $selectableColumns = $view->visualization_properties->selectable_columns;

            // set default selectable columns, if none specified
            if ($selectableColumns === false) {
                $selectableColumns = array('nb_visits', 'nb_actions');

                if (in_array('nb_uniq_visitors', $dataTable->getColumns())) {
                    $selectableColumns[] = 'nb_uniq_visitors';
                }
            }

            $transformed = array();
            foreach ($selectableColumns as $column) {
                $transformed[] = array(
                    'column'      => $column,
                    'translation' => @$view->translations[$column],
                    'displayed'   => in_array($column, $view->columns_to_display)
                );
            }
            $view->visualization_properties->selectable_columns = $transformed;
        };
    }

    /**
     * TODO
     */
    private function transformSelectableRows($view)
    {
        if ($view->visualization_properties->row_picker_match_rows_by === false) {
            return;
        }

        // collect all selectable rows
        $selectableRows = array();
        $view->filters[] = function ($dataTable, $view) use (&$selectableRows) {
            if ($dataTable->getRowsCount() > 0) {
                $rows = $dataTable->getRows();
            } else {
                $rows = array(new Row());
            }

            foreach ($rows as $row) {
                $rowLabel = $row->getColumn('label');
                if ($rowLabel === false) {
                    continue;
                }

                // determine whether row is visible
                $isVisible = true;
                if ($view->visualization_properties->row_picker_match_rows_by == 'label') {
                    $isVisible = in_array($rowLabel, $view->visualization_properties->row_picker_visible_rows);
                }

                // build config
                if (!isset($selectableRows[$rowLabel])) {
                    $selectableRows[$rowLabel] = array(
                        'label'     => $rowLabel,
                        'matcher'   => $rowLabel,
                        'displayed' => $isVisible
                    );
                }
            }
        };

        // set selectable rows as a view property
        $view->after_data_loaded_functions[] = function ($dataTable, $view) use (&$selectableRows) {
            $view->visualization_properties->selectable_rows = array_values($selectableRows);
        };
    }
}