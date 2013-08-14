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
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\View;
use Piwik\DataTable;
use Piwik\DataTableVisualization;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Bar.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Pie.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Evolution.php';

/**
 * DataTable visualization that displays DataTable data in a JQPlot graph.
 * TODO: should merge all this logic w/ jqplotdatagenerator & 'Chart' visualizations.
 */
class JqplotGraph extends DataTableVisualization
{
    const ID = 'jqplot_graph';

    /**
     * Whether the series picker should allow picking more than one series or not.
     */
    const ALLOW_MULTI_SELECT_SERIES_PICKER = 'allow_multi_select_series_picker';

    /**
     * The maximum number of elements to render when rendering a jqPlot graph. All other elements
     * will be aggregated in an 'Others' element.
     */
    const MAX_GRAPH_ELEMENTS = 'max_graph_elements';

    /**
     * The name of the JavaScript class to use as this graph's external series toggle. The class
     * must be a subclass of JQPlotExternalSeriesToggle.
     * 
     * @see self::EXTERNAL_SERIES_TOGGLE_SHOW_ALL
     */
    const EXTERNAL_SERIES_TOGGLE = 'external_series_toggle';

    /**
     * Whether the graph should show all loaded series upon initial display.
     * 
     * @see self::EXTERNAL_SERIES_TOGGLE
     */
    const EXTERNAL_SERIES_TOGGLE_SHOW_ALL = 'external_series_toggle_show_all';

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
     * Controls whether the percentage of the total is displayed as a tooltip in Jqplot graphs.
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
        // Graphs require the full dataset, so no filters
        $this->request_parameters_to_modify['disable_generic_filters'] = true;
        
        // the queued filters will be manually applied later. This is to ensure that filtering using search
        // will be done on the table before the labels are enhanced (see ReplaceColumnNames)
        $this->request_parameters_to_modify['disable_queued_filters'] = true;

        if ($view->show_goals) {
            $goalMetrics = array('nb_conversions', 'revenue');
            $view->visualization_properties->selectable_columns = array_merge(
                $view->visualization_properties->selectable_columns, $goalMetrics);

            $view->translations['nb_conversions'] = Piwik_Translate('Goals_ColumnConversions');
            $view->translations['revenue'] = Piwik_Translate('General_TotalRevenue');
        }

        // do not sort if sorted column was initially "label" or eg. it would make "Visits by Server time" not pretty
        if ($view->filter_sort_column != 'label') {
            $columns = $view->columns_to_display;

            $firstColumn = reset($columns);
            if ($firstColumn == 'label') {
                $firstColumn = next($columns);
            }

            $result['filter_sort_column'] = $firstColumn;
            $result['filter_sort_order'] = 'desc';
        }
    }

    /**
     * Returns an array mapping property names with default values for this visualization.
     * 
     * @return array
     */
    public static function getDefaultPropertyValues()
    {
        // selectable columns
        $selectableColumns = array('nb_visits', 'nb_actions');
        if (Common::getRequestVar('period', false) == 'day') { // TODO: should depend on columns datatable has.
            $selectableColumns[] = 'nb_uniq_visitors';
        }

        return array(
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'show_exclude_low_population' => false,
            'show_search' => false,
            'show_export_as_image_icon' => true,
            'y_axis_unit' => '',
            'row_picker_match_rows_by' => false,
            'row_picker_visible_rows' => array(),
            'visualization_properties' => array(
                'jqplot_graph' => array(
                    'add_total_row' => 0,
                    'show_all_ticks' => false,
                    'allow_multi_select_series_picker' => true,
                    'max_graph_elements' => false,
                    'selectable_columns' => $selectableColumns,
                    'external_series_toggle' => false,
                    'external_series_toggle_show_all' => false,
                    'show_series_picker' => true,
                    'display_percentage_in_tooltip' => true,
                )
            )
        );
    }
    
    /**
     * Renders this visualization.
     *
     * @param DataTable $dataTable
     * @param array $properties View Properties.
     * @return string
     */
    public function render($dataTable, $properties)
    {
        $view = new View("@CoreVisualizations/_dataTableViz_jqplotGraph.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        $view->data = $this->getGraphData($dataTable, $properties);
        return $view->render();
    }

    /**
     * Generats JQPlot graph data for a DataTable.
     */
    private function getGraphData($dataTable, $properties)
    {
        $properties = array_merge($properties, $properties['request_parameters_to_modify']);
        $dataGenerator = $this->makeDataGenerator($properties);

        $jsonData = $dataGenerator->generate($dataTable);
        return str_replace(array("\r", "\n"), '', $jsonData);
    }

    /**
     * Returns a JqplotDataGenerator for the given graph_type in $properties
     * @param array $properties
     * @return JqplotDataGenerator
     */
    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory($properties['graph_type'], $properties);
    }
}