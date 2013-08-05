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
use Piwik\View;
use Piwik\JqplotDataGenerator;
use Piwik\DataTable;
use Piwik\DataTableVisualization;

/**
 * DataTable visualization that displays DataTable data in a JQPlot graph.
 * TODO: should merge all this logic w/ jqplotdatagenerator & 'Chart' visualizations.
 */
class JqplotGraph extends DataTableVisualization
{
    const DEFAULT_GRAPH_HEIGHT = 250;

    /**
     * TODO
     */
    public function __construct($view)
    {
        // Graphs require the full dataset, so no filters
        $this->request_parameters_to_modify['disable_generic_filters'] = true;
        
        // the queued filters will be manually applied later. This is to ensure that filtering using search
        // will be done on the table before the labels are enhanced (see ReplaceColumnNames)
        $this->request_parameters_to_modify['disable_queued_filters'] = true;

        $view->defaultPropertiesTo($this->getDefaultPropertyValues($view));

        if ($view->show_goals) {
            $goalMetrics = array('nb_conversions', 'revenue');
            $view->selectable_columns = array_merge($view->selectable_columns, $goalMetrics);

            $view->translations['nb_conversions'] = Piwik_Translate('Goals_ColumnConversions');
            $view->translations['revenue'] = Piwik_Translate('General_TotalRevenue');
        }

        $view->datatable_css_class = 'dataTableGraph'; // TODO: should be different css per visualization
    }

    /**
     * TODO
     */
    protected function getDefaultPropertyValues($view)
    {
        $result = array(
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'show_exclude_low_population' => false,
            'show_search' => false,
            'show_export_as_image_icon' => true,
            'display_percentage_in_tooltip' => true,
            'display_percentage_in_tooltip' => true,
            'y_axis_unit' => '',
            'show_all_ticks' => 0,
            'add_total_row' => 0,
            'allow_multi_select_series_picker' => true,
            'row_picker_mach_rows_by' => false,
            'row_picker_visible_rows' => array(),
            'selectable_columns' => array(),
            'graph_width' => '100%',
            'graph_height' => self::DEFAULT_GRAPH_HEIGHT . 'px'
        );

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

        // selectable columns
        $selectableColumns = array('nb_visits', 'nb_actions');
        if (Common::getRequestVar('period', false) == 'day') {
            $selectableColumns[] = 'nb_uniq_visitors';
        }
        $result['selectable_columns'] = $selectableColumns;

        return $result;
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
        $view = new View("@CoreHome/_dataTableViz_jqplotGraph.twig");
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

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory($properties['graph_type'], $properties);
    }
}