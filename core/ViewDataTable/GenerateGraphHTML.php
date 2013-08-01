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
use Piwik\Access;
use Piwik\Common;
use Piwik\JqplotDataGenerator;
use Piwik\ViewDataTable;
use Piwik\View;
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

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->viewProperties['show_offset_information'] = false;
        $this->viewProperties['show_pagination_control'] = false;
        $this->viewProperties['show_exclude_low_population'] = false;
        $this->viewProperties['show_search'] = false;
        $this->viewProperties['show_export_as_image_icon'] = true;
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
        if ($this->viewProperties['filter_sort_column'] != 'label') {
            $columns = $this->viewProperties['columns_to_display'];

            $firstColumn = reset($columns);
            if ($firstColumn == 'label') {
                $firstColumn = next($columns);
            }

            $this->viewProperties['filter_sort_column'] = $firstColumn;
            $this->viewProperties['filter_sort_order'] = 'desc';
        }

        // selectable columns
        if ($this->viewProperties['graph_type'] != 'evolution') {
            $selectableColumns = array('nb_visits', 'nb_actions');
            if (Common::getRequestVar('period', false) == 'day') {
                $selectableColumns[] = 'nb_uniq_visitors';
            }
            $this->viewProperties['selectable_columns'] = $selectableColumns;
        }
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
     * @throws \Exception|\Piwik\NoAccessException
     * @return null
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        // Graphs require the full dataset, so no filters
        $this->disable_generic_filters = true;
        
        // the queued filters will be manually applied later. This is to ensure that filtering using search
        // will be done on the table before the labels are enhanced (see ReplaceColumnNames)
        $this->disable_queued_filters = true;

        try {
            $this->loadDataTableFromAPI();
        } catch (\Piwik\NoAccessException $e) {
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

    protected function overrideViewProperties()
    {
        parent::overrideViewProperties();

        if ($this->viewProperties['show_goals']) {
            $goalMetrics = array('nb_conversions', 'revenue');
            $this->viewProperties['selectable_columns'] = array_merge($this->viewProperties['selectable_columns'], $goalMetrics);

            $this->translations['nb_conversions'] = Piwik_Translate('Goals_ColumnConversions');
            $this->translations['revenue'] = Piwik_Translate('General_TotalRevenue');
        }
    }
}
