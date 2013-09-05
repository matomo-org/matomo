<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package TreemapVisualization
 */

namespace Piwik\Plugins\TreemapVisualization;

use Piwik\Common;
use Piwik\View;
use Piwik\Period\Range;
use Piwik\DataTable\Map;
use Piwik\Visualization\Graph;

/**
 * DataTable visualization that displays DataTable data as a treemap (see
 * http://en.wikipedia.org/wiki/Treemapping).
 * 
 * Uses the JavaScript Infovis Toolkit (see philogb.github.io/jit/).
 */
class Treemap extends Graph
{
    const ID = 'infoviz-treemap';
    const FOOTER_ICON = 'plugins/TreemapVisualization/images/treemap-icon.png';
    const FOOTER_ICON_TITLE = 'Treemap';

    /**
     * Controls whether the treemap nodes should be colored based on the evolution percent of
     * individual metrics, or not. If false, the jqPlot pie graph's series colors are used to
     * randomly color different nodes.
     * 
     * Default Value: false
     */
    const SHOW_EVOLUTION_VALUES = 'show_evolution_values';

    /**
     * The amount of subtree levels to display initially. This property is forwarded to API
     * requests along w/ 'expanded=1'. API methods can use the 'depth' parameter to make sure
     * the least amount of data is queried and the least amount of memory is allocated.
     * 
     * Default Value: 1
     */
    const DEPTH = 'depth';

    public static $clientSideProperties = array('filter_offset', 'max_graph_elements', 'depth', 'show_evolution_values');

    /**
     * Constructor.
     * 
     * @param \Piwik\ViewDataTable $view
     */
    public function __construct($view)
    {
        parent::__construct($view);

        $view->datatable_js_type = 'TreemapDataTable';
        $view->request_parameters_to_modify['expanded'] = 1;
        $view->request_parameters_to_modify['depth'] = $view->visualization_properties->depth;
        $view->show_pagination_control = false;
        $view->show_offset_information = false;

        $self = $this;
        $view->filters[] = function ($dataTable, $view) use ($self) {
            $view->custom_parameters['columns'] = $self->getMetricToGraph($view->columns_to_display);
        };

        $this->handleShowEvolutionValues($view);
    }

    /**
     * Renders the treemap.
     * 
     * @param \Piwik\DataTable $dataTable
     * @param array $properties
     * 
     * @return string
     */
    public function render($dataTable, $properties)
    {
        $view = new View('@TreemapVisualization/_dataTableViz_treemap.twig');
        $view->graphData = $this->getGraphData($dataTable, $properties);
        $view->properties = $properties;
        return $view->render();
    }

    /**
     * Returns the default view property values for this visualization.
     * 
     * @return array
     */
    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        $result['visualization_properties']['graph']['max_graph_elements'] = 10;
        $result['visualization_properties']['graph']['allow_multi_select_series_picker'] = false;
        $result['visualization_properties']['infoviz-treemap']['show_evolution_values'] = true;
        $result['visualization_properties']['infoviz-treemap']['depth'] = 1;
        return $result;
    }

    /**
     * Checks if the data obtained by ViewDataTable has data or not. Since we get the last period
     * when calculating evolution, we need this hook to determine if there's data in the latest
     * table.
     * 
     * @param \Piwik\DataTable $dataTable
     * @return true
     */
    public function isThereDataToDisplay($dataTable, $view)
    {
        if ($dataTable instanceof Map) { // will be true if calculating evolution values
            $childTables = $dataTable->getArray();
            $dataTable = end($childTables);
        }

        return $dataTable->getRowsCount() != 0;
    }

    private function getGraphData($dataTable, $properties)
    {
        $metric = $this->getMetricToGraph($properties['columns_to_display']);
        $translation = empty($properties['translations'][$metric]) ? $metric : $properties['translations'][$metric];

        $generator = new TreemapDataGenerator($metric, $translation);
        $generator->setInitialRowOffset($properties['filter_offset'] ?: 0);
        if ($properties['visualization_properties']->show_evolution_values
            && Common::getRequestVar('period') != 'range'
        ) {
            $generator->showEvolutionValues();
        }

        $truncateAfter = Common::getRequestVar('truncateAfter', false, 'int');
        if ($truncateAfter > 0) {
            $generator->setTruncateAfter($truncateAfter);
        }

        return Common::json_encode($generator->generate($dataTable));
    }

    public function getMetricToGraph($columnsToDisplay)
    {
        $firstColumn = reset($columnsToDisplay);
        if ($firstColumn == 'label') {
            $firstColumn = next($columnsToDisplay);
        }
        return $firstColumn;
    }

    private function handleShowEvolutionValues($view)
    {
        // evolution values cannot be calculated if range period is used
        $period = Common::getRequestVar('period');
        if ($period == 'range') {
            return;
        }

        if ($view->visualization_properties->show_evolution_values) {
            $date = Common::getRequestVar('date');
            list($previousDate, $ignore) = Range::getLastDate($date, $period);

            $view->request_parameters_to_modify['date'] = $previousDate . ',' . $date;
        }
    }
}