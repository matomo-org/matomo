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
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\DataTable\Map;
use Piwik\ViewDataTable\Graph;

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

    public static $clientSideProperties = array(
        'filter_offset',
        'max_graph_elements',
        'show_evolution_values',
        'subtable_controller_action'
    );

    /**
     * Constructor.
     * 
     * @param \Piwik\ViewDataTable $view
     */
    public function __construct($view)
    {
        // we determine the elements count dynamically based on available width/height
        $view->visualization_properties->max_graph_elements = false;

        parent::__construct($view, '@TreemapVisualization/_dataTableViz_treemap.twig');

        $view->datatable_js_type = 'TreemapDataTable';
        $view->show_pagination_control = false;
        $view->show_offset_information = false;
        $view->show_flatten_table = false;

        $metric = $this->getMetricToGraph($view->columns_to_display);
        $view->filters[] = function ($dataTable, $view) use ($metric) {
            $view->custom_parameters['columns'] = $metric;
        };

        $this->createTreemapDataGenerator($view);
        $this->handleShowEvolutionValues($view);
    }

    private function createTreemapDataGenerator($view)
    {
        $metric = $this->getMetricToGraph($view->columns_to_display);
        $translation = empty($view->translations[$metric]) ? $metric : $view->translations[$metric];

        $this->generator = new TreemapDataGenerator($metric, $translation);
        $this->generator->setInitialRowOffset($view->filter_offset ?: 0);

        $availableWidth = Common::getRequestVar('availableWidth', false);
        $availableHeight = Common::getRequestVar('availableHeight', false);
        $this->generator->setAvailableDimensions($availableWidth, $availableHeight);
    }

    /**
     * Returns the default view property values for this visualization.
     * 
     * @return array
     */
    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        $result['visualization_properties']['graph']['allow_multi_select_series_picker'] = false;
        $result['visualization_properties']['infoviz-treemap']['show_evolution_values'] = true;
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
        return $this->getCurrentData($dataTable)->getRowsCount() != 0;
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

            $this->generator->showEvolutionValues();
        }
    }

    private function getCurrentData($dataTable)
    {
        if ($dataTable instanceof Map) { // will be true if calculating evolution values
            $childTables = $dataTable->getArray();
            return end($childTables);
        } else {
            return $dataTable;
        }
    }
}