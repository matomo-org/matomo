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

use Exception;
use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\JqplotDataGenerator;
use Piwik\ViewDataTable;
use Piwik\View;
use Piwik_Access_NoAccessException;
use Piwik\Visualization\JqplotGraph;

/**
 * This class generates the HTML code to embed graphs in the page.
 * It doesn't call the API but simply prints the html snippet.
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
abstract class GenerateGraphHTML extends ViewDataTable
{
    const DEFAULT_GRAPH_HEIGHT = 250;

    protected $graphType;

    public function __construct()
    {
        parent::__construct();

        $this->disableOffsetInformationAndPaginationControls();
        $this->disableExcludeLowPopulation();
        $this->disableSearchBox();
        $this->enableShowExportAsImageIcon();

        $this->viewProperties['display_percentage_in_tooltip'] = true;
        $this->viewProperties['y_axis_unit'] = '';
        $this->viewProperties['show_all_ticks'] = 0;
        $this->viewProperties['add_total_row'] = 0;
        $this->viewProperties['graph_limit'] = null;
        $this->viewProperties['allow_multi_select_series_picker'] = true;
        $this->viewProperties['row_picker_mach_rows_by'] = false;
        $this->viewProperties['row_picker_visible_rows'] = array();
        $this->viewProperties['selectable_columns'] = array();
        $this->viewProperties['graph_width'] = '100%';
        $this->viewProperties['graph_height'] = self::DEFAULT_GRAPH_HEIGHT . 'px';
    }

    /**
     * Default constructor.
     */
    public function setAxisYUnit($unit)
    {
        $this->viewProperties['y_axis_unit'] = $unit;
    }

    /**
     * Sets the number max of elements to display (number of pie slice, vertical bars, etc.)
     * If the data has more elements than $limit then the last part of the data will be the sum of all the remaining data.
     *
     * @param int $limit
     */
    public function setGraphLimit($limit)
    {
        $this->viewProperties['graph_limit'] = $limit;
    }

    /**
     * The percentage in tooltips is computed based on the sum of all values for the plotted column.
     * If the sum of the column in the data set is not the number of elements in the data set,
     * for example when plotting visits that have a given plugin enabled:
     * one visit can have several plugins, hence the sum is much greater than the number of visits.
     * In this case displaying the percentage doesn't make sense.
     */
    public function disallowPercentageInGraphTooltip()
    {
        $this->viewProperties['display_percentage_in_tooltip'] = false;
    }

    /**
     * Sets the columns that can be added/removed by the user
     * This is done on data level (not html level) because the columns might change after reloading via sparklines
     * @param array $columnsNames Array of column names eg. array('nb_visits','nb_hits')
     */
    public function setSelectableColumns($columnsNames)
    {
        // the array contains values if enableShowGoals() has been used
        // add $columnsNames to the beginning of the array
        $this->viewProperties['selectable_columns'] = array_merge($columnsNames, $this->viewProperties['selectable_columns']);
    }

    /**
     * The implementation of this method in ViewDataTable passes to the graph whether the
     * goals icon should be displayed or not. Here, we use it to implicitly add the goal metrics
     * to the metrics picker.
     */
    public function enableShowGoals()
    {
        parent::enableShowGoals();

        $goalMetrics = array('nb_conversions', 'revenue');
        $this->viewProperties['selectable_columns'] = array_merge($this->viewProperties['selectable_columns'], $goalMetrics);

        $this->setColumnTranslation('nb_conversions', Piwik_Translate('Goals_ColumnConversions'));
        $this->setColumnTranslation('revenue', Piwik_Translate('General_TotalRevenue'));
    }

    public function init($currentControllerName,
                         $currentControllerAction,
                         $apiMethodToRequestDataTable,
                         $controllerActionCalledWhenRequestSubTable = null,
                         $defaultProperties = array())
    {
        parent::init($currentControllerName, $currentControllerAction, $apiMethodToRequestDataTable,
            $controllerActionCalledWhenRequestSubTable, $defaultProperties);

        // in the case this controller is being executed by another controller
        // eg. when being widgetized in an IFRAME
        // we need to put in the URL of the graph data the real module and action
        $this->viewProperties['request_parameters_to_modify']['module'] = $currentControllerName;
        $this->viewProperties['request_parameters_to_modify']['action'] = $currentControllerAction;

        // do not sort if sorted column was initially "label" or eg. it would make "Visits by Server time" not pretty
        if ($this->getSortedColumn() != 'label') {
            $columns = $this->viewProperties['columns_to_display'];

            $firstColumn = reset($columns);
            if ($firstColumn == 'label') {
                $firstColumn = next($columns);
            }

            $this->setSortedColumn($firstColumn);
        }

        // selectable columns
        if ($this->viewProperties['graph_type'] != 'evolution') {
            $selectableColumns = array('nb_visits', 'nb_actions');
            if (Common::getRequestVar('period', false) == 'day') {
                $selectableColumns[] = 'nb_uniq_visitors';
            }
            $this->viewProperties['selectable_columns'] = $selectableColumns;
        }

        if ($this->viewProperties['show_goals']) {
            $this->enableShowGoals();
        }
    }

    public function enableShowExportAsImageIcon()
    {
        $this->viewProperties['show_export_as_image_icon'] = true;
    }

    public function addRowEvolutionSeriesToggle($initiallyShowAllMetrics)
    {
        $this->viewProperties['external_series_toggle'] = 'RowEvolutionSeriesToggle';
        $this->viewProperties['external_series_toggle_show_all'] = $initiallyShowAllMetrics;
    }

    /**
     * Show every x-axis tick instead of just every other one.
     */
    public function showAllTicks()
    {
        $this->viewProperties['show_all_ticks'] = 1;
    }

    /**
     * Adds a row to the report containing totals for contained metrics. Mainly useful
     * for evolution graphs where displaying the totals w/ the metrics is useful.
     */
    public function addTotalRow()
    {
        $this->viewProperties['add_total_row'] = 1;
    }

    /**
     * Adds the same series picker as parent::setSelectableColumns but the selectable series are not
     * columns of a single row but the same column across multiple rows, e.g. the number of visits
     * for each referrer type.
     * @param array $visibleRows the rows that are initially visible
     * @param string $matchBy the way the items in $visibleRows are matched with the data. possible values:
     *                            - label: matches the label of the row
     */
    public function addRowPicker($visibleRows, $matchBy = 'label')
    {
        $this->viewProperties['row_picker_mach_rows_by'] = $matchBy;
        $this->viewProperties['row_picker_visible_rows'] = is_array($visibleRows) ? $visibleRows : array($visibleRows);
    }

    /**
     * We persist the 'request_parameters_to_modify' values in the javascript footer.
     * This is used by the "export links" that use the "date" attribute
     * from the json properties array in the datatable footer.
     * @return array
     */
    protected function getJavascriptVariablesToSet()
    {
        $original = parent::getJavascriptVariablesToSet();
        $originalViewDataTable = $original['viewDataTable'];

        $result = $this->viewProperties['request_parameters_to_modify'] + $original;
        $result['viewDataTable'] = $originalViewDataTable;

        return $result;
    }

    /**
     * @see ViewDataTable::main()
     * @return null
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        // Graphs require the full dataset, so no filters
        $this->disableGenericFilters();

        // the queued filters will be manually applied later. This is to ensure that filtering using search
        // will be done on the table before the labels are enhanced (see ReplaceColumnNames)
        $this->disableQueuedFilters();

        try {
            $this->loadDataTableFromAPI();
        } catch (Piwik_Access_NoAccessException $e) {
            throw $e;
        } catch (Exception $e) {
            Piwik::log("Failed to get data from API: " . $e->getMessage());

            $this->loadingError = array('message' => $e->getMessage());
        }

        if ($this->viewProperties['graph_type'] != 'evolution') {
            $this->checkStandardDataTable();
        }
        $this->postDataTableLoadedFromAPI();

        // re-enable generic & queued filters so they do not appear in JS output
        $this->viewProperties['disable_generic_filters'] = false;
        $this->viewProperties['disable_queued_filters'] = false;

        $visualization = new JqplotGraph();
        $this->view = $this->buildView($visualization);
    }

    public function getDefaultDataTableCssClass()
    {
        return 'dataTableGraph';
    }
}
