<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

use Piwik\ViewDataTable\Config as VisualizationConfig;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class Config extends VisualizationConfig
{

    /**
     * Whether the series picker should allow picking more than one series or not.
     *
     * Default value: true
     */
    public $allow_multi_select_series_picker = true;

    /**
     * The maximum number of rows to render. All other rows will be aggregated in an 'Others' row.
     *
     * Default value: false (no limit)
     */
    public $max_graph_elements = false;

    /**
     * Array property that contains the names of columns that can be selected in the Series Picker.
     *
     * Default value: false
     */
    public $selectable_columns = false;

    /**
     * Contains the column (if any) of the values used in the Row Picker.
     *
     * @see self::ROWS_TO_DISPLAY
     *
     * Default value: false
     */
    public $row_picker_match_rows_by = false;

    /**
     * Contains the list of values identifying rows that should be displayed as separate series.
     * The values are of a specific column determined by the row_picker_match_rows_by column.
     *
     * @see self::ROW_PICKER_VALUE_COLUMN
     *
     * Default value: false
     */
    public $rows_to_display = false;

    /**
     * Contains the list of values available for the Row Picker. Currently set to be all visible
     * rows, if the row_picker_match_rows_by property is set.
     *
     * @see self::ROW_PICKER_VALUE_COLUMN
     */
    public $selectable_rows = [];

    /**
     * Controls whether all ticks & labels are shown on a graph's x-axis or just some.
     *
     * Default value: false
     */
    public $show_all_ticks = false;

    /**
     * If true, a row with totals of each DataTable column is added.
     *
     * Default value: false
     */
    public $add_total_row = false;

    /**
     * Controls whether the Series Picker is shown or not. The Series Picker allows users to
     * choose between displaying data of different columns.
     *
     * Default value: true
     */
    public $show_series_picker = true;

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
    public $display_percentage_in_tooltip = true;

    public function __construct()
    {
        parent::__construct();

        $this->show_limit_control = false;

        $this->addPropertiesThatShouldBeAvailableClientSide(array(
            'show_series_picker',
            'allow_multi_select_series_picker',
            'selectable_columns',
            'selectable_rows',
            'display_percentage_in_tooltip'
        ));

        $this->addPropertiesThatCanBeOverwrittenByQueryParams(array(
            'show_all_ticks',
            'show_series_picker'
        ));
    }

}
