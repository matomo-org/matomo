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
use Piwik\Visualization\Graph;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

/**
 * DataTable visualization that displays DataTable data in a JQPlot graph.
 * TODO: should merge all this logic w/ jqplotdatagenerator & 'Chart' visualizations.
 */
class JqplotGraph extends Graph
{
    const ID = 'jqplot_graph';

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
        $result = parent::getDefaultPropertyValues();
        return array_merge_recursive($result, array(
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
                    'external_series_toggle' => false,
                    'external_series_toggle_show_all' => false,
                )
            )
        ));
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

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Bar.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Pie.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/JqplotGraph/Evolution.php';