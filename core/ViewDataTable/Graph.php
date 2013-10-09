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
namespace Piwik\ViewDataTable;


use Piwik\DataTable\Row;
use Piwik\Piwik;

/**
 * This is an abstract visualization that should be the base of any 'graph' visualization.
 * This class defines certain visualization properties that are specific to all graph types.
 * Derived visualizations can decide for themselves whether they should support individual
 * properties.
 */
abstract class Graph extends Visualization
{
    const ID = 'graph';

    /**
     * Whether the series picker should allow picking more than one series or not.
     *
     * Default value: true
     */
    const ALLOW_MULTI_SELECT_SERIES_PICKER = 'allow_multi_select_series_picker';

    /**
     * The maximum number of rows to render. All other rows will be aggregated in an 'Others' row.
     *
     * Default value: false (no limit)
     */
    const MAX_GRAPH_ELEMENTS = 'max_graph_elements';

    /**
     * Array property that contains the names of columns that can be selected in the Series Picker.
     *
     * Default value: false
     */
    const SELECTABLE_COLUMNS = 'selectable_columns';

    /**
     * Contains the column (if any) of the values used in the Row Picker.
     *
     * @see self::ROWS_TO_DISPLAY
     *
     * Default value: false
     */
    const ROW_PICKER_VALUE_COLUMN = 'row_picker_match_rows_by';

    /**
     * Contains the list of values identifying rows that should be displayed as separate series.
     * The values are of a specific column determined by the row_picker_match_rows_by column.
     *
     * @see self::ROW_PICKER_VALUE_COLUMN
     *
     * Default value: false
     */
    const ROWS_TO_DISPLAY = 'rows_to_display';

    /**
     * Contains the list of values available for the Row Picker. Currently set to be all visible
     * rows, if the row_picker_match_rows_by property is set.
     *
     * @see self::ROW_PICKER_VALUE_COLUMN
     */
    const SELECTABLE_ROWS = 'selectable_rows';

    /**
     * Controls whether all ticks & labels are shown on a graph's x-axis or just some.
     *
     * Default value: false
     */
    const SHOW_ALL_TICKS = 'show_all_ticks';

    /**
     * If true, a row with totals of each DataTable column is added.
     *
     * Default value: false
     */
    const ADD_TOTAL_ROW = 'add_total_row';

    /**
     * Controls whether the Series Picker is shown or not. The Series Picker allows users to
     * choose between displaying data of different columns.
     *
     * Default value: true
     */
    const SHOW_SERIES_PICKER = 'show_series_picker';

    /**
     * Controls whether the percentage of the total is displayed as a tooltip when hovering over
     * data points.
     *
     * NOTE: Sometimes this percentage is meaningless (when the total of the column values is
     * not the total number of elements in the set). In this case the tooltip should not be
     * displayed.
     *
     * Default value: true
     */
    const DISPLAY_PERCENTAGE_IN_TOOLTIP = 'display_percentage_in_tooltip';

    public static $clientSideProperties = array(
        'show_series_picker',
        'allow_multi_select_series_picker',
        'selectable_columns',
        'selectable_rows',
        'display_percentage_in_tooltip'
    );

    public static $clientSideParameters = array(
        'columns'
    );

    public static $overridableProperties = array(
        'show_all_ticks',
        'show_series_picker'
    );

    /**
     * Constructor.
     *
     * @param \Piwik\ViewDataTable $view
     * @param string $template
     */
    public function __construct($view, $template)
    {
        parent::__construct($template);

        if ($view->show_goals) {
            $view->translations['nb_conversions'] = Piwik::translate('Goals_ColumnConversions');
            $view->translations['revenue'] = Piwik::translate('General_TotalRevenue');
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
            'show_limit_control'       => false,
            'visualization_properties' => array(
                'graph' => array(
                    'add_total_row'                    => false,
                    'show_all_ticks'                   => false,
                    'allow_multi_select_series_picker' => true,
                    'max_graph_elements'               => false,
                    'selectable_columns'               => false,
                    'show_series_picker'               => true,
                    'display_percentage_in_tooltip'    => true,
                    'row_picker_match_rows_by'         => false,
                    'rows_to_display'                  => false,
                    'selectable_rows'                  => false
                )
            )
        );
    }

    /**
     * Defaults the selectable_columns property if it has not been set and then transforms
     * it into something the SeriesPicker JavaScript class can use.
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

            if ($view->show_goals) {
                $goalMetrics = array('nb_conversions', 'revenue');
                $selectableColumns = array_merge($selectableColumns, $goalMetrics);
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
     * Determines what rows are selectable and stores them in the selectable_rows property in
     * a format the SeriesPicker JavaScript class can use.
     */
    private function transformSelectableRows($view)
    {
        if ($view->visualization_properties->row_picker_match_rows_by === false) {
            return;
        }

        // collect all selectable rows
        $selectableRows = array();
        $view->filters[] = function ($dataTable, $view) use (&$selectableRows) {
            foreach ($dataTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');
                if ($rowLabel === false) {
                    continue;
                }

                // determine whether row is visible
                $isVisible = true;
                if ($view->visualization_properties->row_picker_match_rows_by == 'label') {
                    $isVisible = in_array($rowLabel, $view->visualization_properties->rows_to_display);
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