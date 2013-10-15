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
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Piwik;
use Piwik\Plugin\Visualization;
use Piwik\Visualization\Config;
use Piwik\Visualization\Request;

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

    public static $clientSideConfigProperties = array(
        'show_series_picker',
        'allow_multi_select_series_picker',
        'selectable_columns',
        'selectable_rows',
        'display_percentage_in_tooltip'
    );

    public static $clientSideRequestParameters = array(
        'columns'
    );

    public static $overridableProperties = array(
        'show_all_ticks',
        'show_series_picker'
    );

    public $selectableRows = array();

    public function configureVisualization()
    {
        if ($this->config->show_goals) {
            $this->config->translations['nb_conversions'] = Piwik::translate('Goals_ColumnConversions');
            $this->config->translations['revenue'] = Piwik::translate('General_TotalRevenue');
        }
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
    public function afterAllFilteresAreApplied()
    {
        $this->config->visualization_properties->selectable_rows = array_values($this->selectableRows);

        $selectableColumns = $this->config->visualization_properties->selectable_columns;

        // set default selectable columns, if none specified
        if ($selectableColumns === false) {
            $selectableColumns = array('nb_visits', 'nb_actions');

            if (in_array('nb_uniq_visitors', $this->dataTable->getColumns())) {
                $selectableColumns[] = 'nb_uniq_visitors';
            }
        }

        if ($this->config->show_goals) {
            $goalMetrics = array('nb_conversions', 'revenue');
            $selectableColumns = array_merge($selectableColumns, $goalMetrics);
        }

        $transformed = array();
        foreach ($selectableColumns as $column) {
            $transformed[] = array(
                'column'      => $column,
                'translation' => @$this->config->translations[$column],
                'displayed'   => in_array($column, $this->config->columns_to_display)
            );
        }
        $this->config->visualization_properties->selectable_columns = $transformed;
    }

    /**
     * Determines what rows are selectable and stores them in the selectable_rows property in
     * a format the SeriesPicker JavaScript class can use.
     */
    public function beforeLoadDataTable()
    {
        // TODO: this should not be required here. filter_limit should not be a view property, instead HtmlTable should use 'limit' or something,
        //       and manually set request_parameters_to_modify['filter_limit'] based on that. (same for filter_offset).
        $this->config->request_parameters_to_modify['filter_limit'] = false;

        if ($this->config->visualization_properties->max_graph_elements) {
            $this->requestConfig->request_parameters_to_modify['filter_truncate'] = $this->config->visualization_properties->max_graph_elements - 1;
        }

        if ($this->config->visualization_properties->row_picker_match_rows_by === false) {
            return;
        }
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        // collect all selectable rows
        $self = $this;
        $properties = $this->config;

        $this->dataTable->filter(function ($dataTable) use ($self, $properties) {
            foreach ($dataTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');
                if ($rowLabel === false) {
                    continue;
                }

                // determine whether row is visible
                $isVisible = true;
                if ($properties->visualization_properties->row_picker_match_rows_by == 'label') {
                    $isVisible = in_array($rowLabel, $properties->visualization_properties->rows_to_display);
                }

                // build config
                if (!isset($self->selectableRows[$rowLabel])) {
                    $self->selectableRows[$rowLabel] = array(
                        'label'     => $rowLabel,
                        'matcher'   => $rowLabel,
                        'displayed' => $isVisible
                    );
                }
            }
        });
    }
}