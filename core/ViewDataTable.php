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

/**
 * This class is used to load (from the API) and customize the output of a given DataTable.
 * The main() method will create an object implementing Piwik_View_Interface
 * You can customize the dataTable using the disable* methods.
 *
 * You can also customize the dataTable rendering using row metadata:
 * - 'html_label_prefix': If this metadata is present on a row, it's contents will be prepended
 *                        the label in the HTML output.
 * - 'html_label_suffix': If this metadata is present on a row, it's contents will be appended
 *                        after the label in the HTML output.
 *
 * Example:
 * In the Controller of the plugin VisitorInterest
 * <pre>
 *    function getNumberOfVisitsPerVisitDuration( $fetch = false)
 *  {
 *        $view = Piwik_ViewDataTable::factory( 'cloud' );
 *        $view->init( $this->pluginName,  __FUNCTION__, 'VisitorInterest.getNumberOfVisitsPerVisitDuration' );
 *        $view->setColumnsToDisplay( array('label','nb_visits') );
 *        $view->disableSort();
 *        $view->disableExcludeLowPopulation();
 *        $view->disableOffsetInformation();
 *
 *        return $this->renderView($view, $fetch);
 *    }
 * </pre>
 *
 * @see factory() for all the available output (cloud tags, html table, pie chart, vertical bar chart)
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
abstract class Piwik_ViewDataTable
{
    /**
     * Template file that will be loaded for this view.
     * Usually set in the Piwik_ViewDataTable_*
     *
     * @var string eg. 'CoreHome/templates/cloud.tpl'
     */
    protected $dataTableTemplate = null;

    /**
     * Flag used to make sure the main() is only executed once
     *
     * @var bool
     */
    protected $mainAlreadyExecuted = false;

    /**
     * Contains the values set for the parameters
     *
     * @see getJavascriptVariablesToSet()
     * @var array
     */
    protected $variablesDefault = array();

    /**
     * Array of properties that are available in the view (from smarty)
     * Used to store UI properties, eg. "show_footer", "show_search", etc.
     *
     * @var array
     */
    protected $viewProperties = array();

    /**
     * If the current dataTable refers to a subDataTable (eg. keywordsBySearchEngineId for id=X) this variable is set to the Id
     *
     * @var bool|int
     */
    protected $idSubtable = false;

    /**
     * DataTable loaded from the API for this ViewDataTable.
     *
     * @var Piwik_DataTable
     */
    protected $dataTable = null;


    /**
     * List of filters to apply after the data has been loaded from the API
     *
     * @var array
     */
    protected $queuedFilters = array();

    /**
     * List of filter to apply just before the 'Generic' filters
     * These filters should delete rows from the table
     * @var array
     */
    protected $queuedFiltersPriority = array();

    /**
     * @see init()
     * @var string
     */
    protected $currentControllerAction;

    /**
     * @see init()
     * @var string
     */
    protected $currentControllerName;

    /**
     * @see init()
     * @var string
     */
    protected $controllerActionCalledWhenRequestSubTable = null;

    /**
     * @see init()
     * @var string
     */
    protected $apiMethodToRequestDataTable;

    /**
     * This view should be an implementation of the Interface Piwik_View_Interface
     * The $view object should be created in the main() method.
     *
     * @var Piwik_View_Interface
     */
    protected $view = null;

    /**
     * Array of columns names translations
     *
     * @var array
     */
    protected $columnsTranslations = array();

    /**
     * Documentation for the metrics used in the current report.
     * Received from the Plugin API, used for inline help.
     *
     * @var array
     */
    protected $metricsDocumentation = false;

    /**
     * Documentation for the report.
     * Received from the Plugin API, used for inline help.
     *
     * @var array
     */
    protected $documentation = false;

    /**
     * Array of columns set to display
     *
     * @var array
     */
    protected $columnsToDisplay = array();

    /**
     * Variable that is used as the DIV ID in the rendered HTML
     *
     * @var string
     */
    protected $uniqIdTable = null;

    /**
     * Method to be implemented by the ViewDataTable_*.
     * This method should create and initialize a $this->view object @see Piwik_View_Interface
     *
     * @return mixed either prints the result or returns the output string
     */
    abstract public function main();

    /**
     * Unique string ID that defines the format of the dataTable, eg. "pieChart", "table", etc.
     *
     * @return string
     */
    abstract protected function getViewDataTableId();

    /**
     * Returns a Piwik_ViewDataTable_* object.
     * By default it will return a ViewDataTable_Html
     * If there is a viewDataTable parameter in the URL, a ViewDataTable of this 'viewDataTable' type will be returned.
     * If defaultType is specified and if there is no 'viewDataTable' in the URL, a ViewDataTable of this $defaultType will be returned.
     * If force is set to true, a ViewDataTable of the $defaultType will be returned in all cases.
     *
     * @param string $defaultType Any of these: table, cloud, graphPie, graphVerticalBar, graphEvolution, sparkline, generateDataChart*
     * @param bool $force If set to true, returns a ViewDataTable of the $defaultType
     * @return Piwik_ViewDataTable
     */
    static public function factory($defaultType = null, $force = false)
    {
        if (is_null($defaultType)) {
            $defaultType = 'table';
        }

        if ($force === true) {
            $type = $defaultType;
        } else {
            $type = Piwik_Common::getRequestVar('viewDataTable', $defaultType, 'string');
        }
        switch ($type) {
            case 'cloud':
                return new Piwik_ViewDataTable_Cloud();
                break;

            case 'graphPie':
                return new Piwik_ViewDataTable_GenerateGraphHTML_ChartPie();
                break;

            case 'graphVerticalBar':
                return new Piwik_ViewDataTable_GenerateGraphHTML_ChartVerticalBar();
                break;

            case 'graphEvolution':
                return new Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution();
                break;

            case 'sparkline':
                return new Piwik_ViewDataTable_Sparkline();
                break;

            case 'generateDataChartVerticalBar':
                return new Piwik_ViewDataTable_GenerateGraphData_ChartVerticalBar();
                break;

            case 'generateDataChartPie':
                return new Piwik_ViewDataTable_GenerateGraphData_ChartPie();
                break;

            case 'generateDataChartEvolution':
                return new Piwik_ViewDataTable_GenerateGraphData_ChartEvolution();
                break;

            case 'tableAllColumns':
                return new Piwik_ViewDataTable_HtmlTable_AllColumns();
                break;

            case 'tableGoals':
                return new Piwik_ViewDataTable_HtmlTable_Goals();
                break;

            case 'table':
            default:
                return new Piwik_ViewDataTable_HtmlTable();
                break;
        }
    }

    /**
     * Inits the object given the $currentControllerName, $currentControllerAction of
     * the calling controller action, eg. 'Referers' 'getLongListOfKeywords'.
     * The initialization also requires the $apiMethodToRequestDataTable of the API method
     * to call in order to get the DataTable, eg. 'Referers.getKeywords'.
     * The optional $controllerActionCalledWhenRequestSubTable defines the method name of the API to call when there is a idSubtable.
     * This value would be used by the javascript code building the GET request to the API.
     *
     * Example:
     *    For the keywords listing, a click on the row loads the subTable of the Search Engines for this row.
     *  In this case $controllerActionCalledWhenRequestSubTable = 'getSearchEnginesFromKeywordId'.
     *  The GET request will hit 'Referers.getSearchEnginesFromKeywordId'.
     *
     * @param string $currentControllerName eg. 'Referers'
     * @param string $currentControllerAction eg. 'getKeywords'
     * @param string $apiMethodToRequestDataTable eg. 'Referers.getKeywords'
     * @param string $controllerActionCalledWhenRequestSubTable eg. 'getSearchEnginesFromKeywordId'
     */
    public function init($currentControllerName,
                         $currentControllerAction,
                         $apiMethodToRequestDataTable,
                         $controllerActionCalledWhenRequestSubTable = null)
    {
        $this->currentControllerName = $currentControllerName;
        $this->currentControllerAction = $currentControllerAction;
        $this->apiMethodToRequestDataTable = $apiMethodToRequestDataTable;
        $this->controllerActionCalledWhenRequestSubTable = $controllerActionCalledWhenRequestSubTable;
        $this->idSubtable = Piwik_Common::getRequestVar('idSubtable', false, 'int');

        $this->viewProperties['show_goals'] = false;
        $this->viewProperties['show_ecommerce'] = false;
        $this->viewProperties['show_search'] = Piwik_Common::getRequestVar('show_search', true);
        $this->viewProperties['show_table'] = Piwik_Common::getRequestVar('show_table', true);
        $this->viewProperties['show_table_all_columns'] = Piwik_Common::getRequestVar('show_table_all_columns', true);
        $this->viewProperties['show_all_views_icons'] = Piwik_Common::getRequestVar('show_all_views_icons', true);
        $this->viewProperties['hide_all_views_icons'] = Piwik_Common::getRequestVar('hide_all_views_icons', false);
        $this->viewProperties['hide_annotations_view'] = Piwik_Common::getRequestVar('hide_annotations_view', true);
        $this->viewProperties['show_bar_chart'] = Piwik_Common::getRequestVar('show_barchart', true);
        $this->viewProperties['show_pie_chart'] = Piwik_Common::getRequestVar('show_piechart', true);
        $this->viewProperties['show_tag_cloud'] = Piwik_Common::getRequestVar('show_tag_cloud', true);
        $this->viewProperties['show_export_as_image_icon'] = Piwik_Common::getRequestVar('show_export_as_image_icon', false);
        $this->viewProperties['show_export_as_rss_feed'] = Piwik_Common::getRequestVar('show_export_as_rss_feed', true);
        $this->viewProperties['show_exclude_low_population'] = Piwik_Common::getRequestVar('show_exclude_low_population', true);
        $this->viewProperties['show_offset_information'] = Piwik_Common::getRequestVar('show_offset_information', true);
        $this->viewProperties['show_pagination_control'] = Piwik_Common::getRequestVar('show_pagination_control', true);
        $this->viewProperties['show_limit_control'] = false;
        $this->viewProperties['show_footer'] = Piwik_Common::getRequestVar('show_footer', true);
        $this->viewProperties['show_footer_icons'] = ($this->idSubtable == false);
        $this->viewProperties['show_related_reports'] = Piwik_Common::getRequestVar('show_related_reports', true);
        $this->viewProperties['apiMethodToRequestDataTable'] = $this->apiMethodToRequestDataTable;
        $this->viewProperties['uniqueId'] = $this->getUniqueIdViewDataTable();
        $this->viewProperties['exportLimit'] = Piwik_Config::getInstance()->General['API_datatable_default_limit'];

        $this->viewProperties['highlight_summary_row'] = false;
        $this->viewProperties['metadata'] = array();

        $this->viewProperties['relatedReports'] = array();
        $this->viewProperties['title'] = 'unknown';
        $this->viewProperties['self_url'] = $this->getBaseReportUrl($currentControllerName, $currentControllerAction);
        $this->viewProperties['tooltip_metadata_name'] = false;

        $standardColumnNameToTranslation = array_merge(
            Piwik_API_API::getInstance()->getDefaultMetrics(),
            Piwik_API_API::getInstance()->getDefaultProcessedMetrics()
        );
        $this->setColumnsTranslations($standardColumnNameToTranslation);
    }

    /**
     * Forces the View to use a given template.
     * Usually the template to use is set in the specific ViewDataTable_*
     * eg. 'CoreHome/templates/cloud.tpl'
     * But some users may want to force this template to some other value
     *
     * @param string $tpl eg .'MyPlugin/templates/templateToUse.tpl'
     */
    public function setTemplate($tpl)
    {
        $this->dataTableTemplate = $tpl;
    }

    /**
     * Returns the View_Interface.
     * You can then call render() on this object.
     *
     * @return Piwik_View_Interface
     * @throws exception if the view object was not created
     */
    public function getView()
    {
        if (is_null($this->view)) {
            throw new Exception('The $this->view object has not been created.
					It should be created in the main() method of the Piwik_ViewDataTable_* subclass you are using.');
        }
        return $this->view;
    }

    public function getCurrentControllerAction()
    {
        return $this->currentControllerAction;
    }

    public function getCurrentControllerName()
    {
        return $this->currentControllerName;
    }

    public function getApiMethodToRequestDataTable()
    {
        return $this->apiMethodToRequestDataTable;
    }

    public function getControllerActionCalledWhenRequestSubTable()
    {
        return $this->controllerActionCalledWhenRequestSubTable;
    }

    /**
     * Returns the DataTable loaded from the API
     *
     * @return Piwik_DataTable
     * @throws exception if not yet defined
     */
    public function getDataTable()
    {
        if (is_null($this->dataTable)) {
            throw new Exception("The DataTable object has not yet been created");
        }
        return $this->dataTable;
    }

    /**
     * To prevent calling an API multiple times, the DataTable can be set directly.
     * It won't be loaded again from the API in this case
     *
     * @param $dataTable
     * @return void $dataTable Piwik_DataTable
     */
    public function setDataTable($dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * Function called by the ViewDataTable objects in order to fetch data from the API.
     * The function init() must have been called before, so that the object knows which API module and action to call.
     * It builds the API request string and uses Piwik_API_Request to call the API.
     * The requested Piwik_DataTable object is stored in $this->dataTable.
     */
    protected function loadDataTableFromAPI()
    {
        if (!is_null($this->dataTable)) {
            // data table is already there
            // this happens when setDataTable has been used
            return;
        }

        // we build the request string (URL) to call the API
        $requestString = $this->getRequestString();

        // we make the request to the API
        $request = new Piwik_API_Request($requestString);

        // and get the DataTable structure
        $dataTable = $request->process();

        $this->dataTable = $dataTable;
    }

    /**
     * Checks that the API returned a normal DataTable (as opposed to DataTable_Array)
     * @throws Exception
     * @return void
     */
    protected function checkStandardDataTable()
    {
        Piwik::checkObjectTypeIs($this->dataTable, array('Piwik_DataTable'));
    }

    /**
     * Hook called after the dataTable has been loaded from the API
     * Can be used to add, delete or modify the data freshly loaded
     *
     * @return bool
     */
    protected function postDataTableLoadedFromAPI()
    {
        if (empty($this->dataTable)) {
            return false;
        }

        // deal w/ table metadata
        if ($this->dataTable instanceof Piwik_DataTable) {
            $this->viewProperties['metadata'] = $this->dataTable->getAllTableMetadata();

            if (isset($this->viewProperties['metadata'][Piwik_DataTable::ARCHIVED_DATE_METADATA_NAME])) {
                $this->viewProperties['metadata'][Piwik_DataTable::ARCHIVED_DATE_METADATA_NAME] =
                    $this->makePrettyArchivedOnText();
            }
        }

        // First, filters that delete rows
        foreach ($this->queuedFiltersPriority as $filter) {
            $filterName = $filter[0];
            $filterParameters = $filter[1];
            $this->dataTable->filter($filterName, $filterParameters);
        }

        if (!$this->areGenericFiltersDisabled()) {
            // Second, generic filters (Sort, Limit, Replace Column Names, etc.)
            $requestString = $this->getRequestString();
            $request = Piwik_API_Request::getRequestArrayFromString($requestString);

            if (!empty($this->variablesDefault['enable_sort'])
                && $this->variablesDefault['enable_sort'] === 'false'
            ) {
                $request['filter_sort_column'] = $request['filter_sort_order'] = '';
            }

            $genericFilter = new Piwik_API_DataTableGenericFilter($request);
            $genericFilter->filter($this->dataTable);
        }

        if (!$this->areQueuedFiltersDisabled()) {
            // Finally, apply datatable filters that were queued (should be 'presentation' filters that
            // do not affect the number of rows)
            foreach ($this->queuedFilters as $filter) {
                $filterName = $filter[0];
                $filterParameters = $filter[1];
                $this->dataTable->filter($filterName, $filterParameters);
            }
        }
        return true;
    }

    /**
     * Returns true if generic filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    private function areGenericFiltersDisabled()
    {
        // if disable_generic_filters query param is set to '1', generic filters are disabled
        if (Piwik_Common::getRequestVar('disable_generic_filters', '0', 'string') == 1) {
            return true;
        }

        // if $this->disableGenericFilters() was called, generic filters are disabled
        if (isset($this->variablesDefault['disable_generic_filters'])
            && $this->variablesDefault['disable_generic_filters'] === true
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if queued filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    private function areQueuedFiltersDisabled()
    {
        return isset($this->variablesDefault['disable_queued_filters'])
            && $this->variablesDefault['disable_queued_filters'];
    }

    /**
     * Returns prettified and translated text that describes when a report was last updated.
     *
     * @return string
     */
    private function makePrettyArchivedOnText()
    {
        $dateText = $this->viewProperties['metadata'][Piwik_DataTable::ARCHIVED_DATE_METADATA_NAME];
        $date = Piwik_Date::factory($dateText);
        $today = mktime(0, 0, 0);
        if ($date->getTimestamp() > $today) {
            $elapsedSeconds = time() - $date->getTimestamp();
            $timeAgo = Piwik::getPrettyTimeFromSeconds($elapsedSeconds);

            return Piwik_Translate('CoreHome_ReportGeneratedXAgo', $timeAgo);
        }

        $prettyDate = $date->getLocalized("%longYear%, %longMonth% %day%") . $date->toString('S');
        return Piwik_Translate('CoreHome_ReportGeneratedOn', $prettyDate);
    }

    /**
     * @return string URL to call the API, eg. "method=Referers.getKeywords&period=day&date=yesterday"...
     */
    protected function getRequestString()
    {
        // we prepare the string to give to the API Request
        // we setup the method and format variable
        // - we request the method to call to get this specific DataTable
        // - the format = original specifies that we want to get the original DataTable structure itself, not rendered
        $requestString = 'method=' . $this->apiMethodToRequestDataTable;
        $requestString .= '&format=original';
        $requestString .= '&disable_generic_filters=' . Piwik_Common::getRequestVar('disable_generic_filters', 1, 'int');

        $toSetEventually = array(
            'filter_limit',
            'keep_summary_row',
            'filter_sort_column',
            'filter_sort_order',
            'filter_excludelowpop',
            'filter_excludelowpop_value',
            'filter_column',
            'filter_pattern',
            'disable_queued_filters',
        );

        foreach ($toSetEventually as $varToSet) {
            $value = $this->getDefaultOrCurrent($varToSet);
            if (false !== $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $requestString .= "&" . $varToSet . '[]=' . $v;
                    }
                } else {
                    $requestString .= '&' . $varToSet . '=' . $value;
                }
            }
        }

        $segment = $this->getRawSegmentFromRequest();
        if(!empty($segment)) {
            $requestString .= '&segment=' . $segment;
        }
        return $requestString;
    }

    /**
     * @return array|bool
     */
    static public function getRawSegmentFromRequest()
    {
        // we need the URL encoded segment parameter, we fetch it from _SERVER['QUERY_STRING'] instead of default URL decoded _GET
        $segmentRaw = false;
        $segment = Piwik_Common::getRequestVar('segment', '', 'string');
        if (!empty($segment)) {
            $request = Piwik_API_Request::getRequestParametersGET();
            if(!empty($request['segment'])) {
                $segmentRaw = $request['segment'];
            }
        }
        return $segmentRaw;
    }

    /**
     * For convenience, the client code can call methods that are defined in a specific children class
     * without testing the children class type, which would trigger an error with a different children class.
     *
     * Example:
     *  ViewDataTable/Html.php defines a setColumnsToDisplay(). The client code calls this methods even if
     *  the ViewDataTable object is a ViewDataTable_Cloud instance (he doesn't know because of the factory()).
     *  But ViewDataTable_Cloud doesn't define the setColumnsToDisplay() method.
     *  Because we don't want to force users to test for the object type we simply catch these
     *  calls when they are not defined in the child and do nothing.
     *
     * @param string $function
     * @param array $args
     */
    public function __call($function, $args)
    {
    }

    /**
     * Returns a unique ID for this ViewDataTable.
     * This unique ID is used in the Javascript code:
     *  Any ajax loaded data is loaded within a DIV that has id=$unique_id
     *  The jquery code then replaces the existing html div id=$unique_id in the code with this data.
     *
     * @see datatable.js
     * @return string
     */
    protected function loadUniqueIdViewDataTable()
    {
        // if we request a subDataTable the $this->currentControllerAction DIV ID is already there in the page
        // we make the DIV ID really unique by appending the ID of the subtable requested
        if ($this->idSubtable != 0 // parent DIV has a idSubtable = 0 but the html DIV must have the name of the module.action
            && $this->idSubtable !== false // case there is no idSubtable
        ) {
            // see also datatable.js (the ID has to match with the html ID created to be replaced by the result of the ajax call)
            $uniqIdTable = 'subDataTable_' . $this->idSubtable;
        } else {
            // the $uniqIdTable variable is used as the DIV ID in the rendered HTML
            // we use the current Controller action name as it is supposed to be unique in the rendered page
            $uniqIdTable = $this->currentControllerName . $this->currentControllerAction;
        }
        return $uniqIdTable;
    }

    /**
     *  Sets the $uniqIdTable variable that is used as the DIV ID in the rendered HTML
     * @param $uniqIdTable
     */
    public function setUniqueIdViewDataTable($uniqIdTable)
    {
        $this->viewProperties['uniqueId'] = $uniqIdTable;
        $this->uniqIdTable = $uniqIdTable;
    }

    /**
     * Returns current value of $uniqIdTable variable that is used as the DIV ID in the rendered HTML
     * @return null|string
     */
    public function getUniqueIdViewDataTable()
    {
        if ($this->uniqIdTable == null) {
            $this->uniqIdTable = $this->loadUniqueIdViewDataTable();
        }
        return $this->uniqIdTable;
    }

    /**
     * Returns array of properties, eg. "show_footer", "show_search", etc.
     *
     * @return array of boolean
     */
    protected function getViewProperties()
    {
        return $this->viewProperties;
    }

    /**
     * This functions reads the customization values for the DataTable and returns an array (name,value) to be printed in Javascript.
     * This array defines things such as:
     * - name of the module & action to call to request data for this table
     * - optional filters information, eg. filter_limit and filter_offset
     * - etc.
     *
     * The values are loaded:
     * - from the generic filters that are applied by default @see Piwik_API_DataTableGenericFilter.php::getGenericFiltersInformation()
     * - from the values already available in the GET array
     * - from the values set using methods from this class (eg. setSearchPattern(), setLimit(), etc.)
     *
     * @return array eg. array('show_offset_information' => 0, 'show_...
     */
    protected function getJavascriptVariablesToSet()
    {
        // build javascript variables to set
        $javascriptVariablesToSet = array();

        $genericFilters = Piwik_API_DataTableGenericFilter::getGenericFiltersInformation();
        foreach ($genericFilters as $filter) {
            foreach ($filter as $filterVariableName => $filterInfo) {
                // if there is a default value for this filter variable we set it
                // so that it is propagated to the javascript
                if (isset($filterInfo[1])) {
                    $javascriptVariablesToSet[$filterVariableName] = $filterInfo[1];

                    // we set the default specified column and Order to sort by
                    // when this javascript variable is not set already
                    // for example during an AJAX call this variable will be set in the URL
                    // so this will not be executed (and the default sorted not be used as the sorted column might have changed in the meanwhile)
                    if (false !== ($defaultValue = $this->getDefault($filterVariableName))) {
                        $javascriptVariablesToSet[$filterVariableName] = $defaultValue;
                    }
                }
            }
        }

        foreach ($_GET as $name => $value) {
            try {
                $requestValue = Piwik_Common::getRequestVar($name);
            } catch (Exception $e) {
                $requestValue = '';
            }
            $javascriptVariablesToSet[$name] = $requestValue;
        }

        // at this point there are some filters values we  may have not set,
        // case of the filter without default values and parameters set directly in this class
        // for example setExcludeLowPopulation
        // we go through all the $this->variablesDefault array and set the variables not set yet
        foreach ($this->variablesDefault as $name => $value) {
            if (!isset($javascriptVariablesToSet[$name])) {
                $javascriptVariablesToSet[$name] = $value;
            }
        }

        if ($this->dataTable instanceof Piwik_DataTable) {
            // we override the filter_sort_column with the column used for sorting,
            // which can be different from the one specified (eg. if the column doesn't exist)
            $javascriptVariablesToSet['filter_sort_column'] = $this->dataTable->getSortedByColumnName();
            // datatable can return "2" but we want to write "nb_visits" in the js
            if (isset(Piwik_Archive::$mappingFromIdToName[$javascriptVariablesToSet['filter_sort_column']])) {
                $javascriptVariablesToSet['filter_sort_column'] = Piwik_Archive::$mappingFromIdToName[$javascriptVariablesToSet['filter_sort_column']];
            }
        }

        $javascriptVariablesToSet['module'] = $this->currentControllerName;
        $javascriptVariablesToSet['action'] = $this->currentControllerAction;
        $javascriptVariablesToSet['viewDataTable'] = $this->getViewDataTableId();
        $javascriptVariablesToSet['controllerActionCalledWhenRequestSubTable'] = $this->controllerActionCalledWhenRequestSubTable;

        if ($this->dataTable &&
            // Piwik_DataTable_Array doesn't have the method
            !($this->dataTable instanceof Piwik_DataTable_Array)
            && empty($javascriptVariablesToSet['totalRows'])
        ) {
            $javascriptVariablesToSet['totalRows'] = $this->dataTable->getRowsCountBeforeLimitFilter();
        }

        // we escape the values that will be displayed in the javascript footer of each datatable
        // to make sure there is no malicious code injected (the value are already htmlspecialchar'ed as they
        // are loaded with Piwik_Common::getRequestVar()
        foreach ($javascriptVariablesToSet as &$value) {
            if (is_array($value)) {
                $value = array_map('addslashes', $value);
            } else {
                $value = addslashes($value);
            }
        }

        $deleteFromJavascriptVariables = array(
            'filter_excludelowpop',
            'filter_excludelowpop_value',
        );
        foreach ($deleteFromJavascriptVariables as $name) {
            if (isset($javascriptVariablesToSet[$name])) {
                unset($javascriptVariablesToSet[$name]);
            }
        }

        $rawSegment = $this->getRawSegmentFromRequest();
        if(!empty($rawSegment)) {
            $javascriptVariablesToSet['segment'] = $rawSegment;
        }


        return $javascriptVariablesToSet;
    }

    /**
     * Returns, for a given parameter, the value of this parameter in the REQUEST array.
     * If not set, returns the default value for this parameter @see getDefault()
     *
     * @param string $nameVar
     * @return string|mixed Value of this parameter
     */
    protected function getDefaultOrCurrent($nameVar)
    {
        if (isset($_GET[$nameVar])) {
            return Piwik_Common::sanitizeInputValue($_GET[$nameVar]);
        }
        $default = $this->getDefault($nameVar);
        return $default;
    }

    /**
     * Returns the default value for a given parameter.
     * For example, these default values can be set using the disable* methods.
     *
     * @param string $nameVar
     * @return mixed
     */
    protected function getDefault($nameVar)
    {
        if (!isset($this->variablesDefault[$nameVar])) {
            return false;
        }
        return $this->variablesDefault[$nameVar];
    }

    /**
     * The generic filters (limit, offset, sort by visit desc) will not be applied to this datatable.
     */
    public function disableGenericFilters()
    {
        $this->variablesDefault['disable_generic_filters'] = true;
    }

    /**
     * The queued filters (replace column names, enhance column with percentage signs, add logo metadata information, etc.)
     * will not be applied to this datatable. They can be manually applied by calling applyQueuedFilters on the datatable.
     */
    public function disableQueuedFilters()
    {
        $this->variablesDefault['disable_queued_filters'] = true;
    }

    /**
     * The "X-Y of Z" and the "< Previous / Next >"-Buttons won't be displayed under this table
     */
    public function disableOffsetInformationAndPaginationControls()
    {
        $this->viewProperties['show_offset_information'] = false;
        $this->viewProperties['show_pagination_control'] = false;
    }

    /**
     * The "< Previous / Next >"-Buttons won't be displayed under this table
     */
    public function disableShowPaginationControl()
    {
        $this->viewProperties['show_pagination_control'] = false;
    }

    /**
     * Ensures the limit dropdown will always be shown, even if pagination is disabled.
     */
    public function alwaysShowLimitDropdown()
    {
        $this->viewProperties['show_limit_control'] = true;
    }

    /**
     * The "X-Y of Z" won't be displayed under this table
     */
    public function disableOffsetInformation()
    {
        $this->viewProperties['show_offset_information'] = false;
    }

    /**
     * The search box won't be displayed under this table
     */
    public function disableSearchBox()
    {
        $this->viewProperties['show_search'] = false;
    }

    /**
     * Do not sort this table, leave it as it comes out of the API
     */
    public function disableSort()
    {
        $this->variablesDefault['enable_sort'] = 'false';
    }

    /**
     * Do not show the footer icons (show all columns icon, "plus" icon)
     */
    public function disableFooterIcons()
    {
        $this->viewProperties['show_footer_icons'] = false;
    }

    /**
     * When this method is called, the output will not contain the template datatable_footer.tpl
     */
    public function disableFooter()
    {
        $this->viewProperties['show_footer'] = false;
    }

    /**
     * The "Include low population" link won't be displayed under this table
     */
    public function disableExcludeLowPopulation()
    {
        $this->viewProperties['show_exclude_low_population'] = false;
    }

    /**
     * Whether or not to show the "View table" icon
     */
    public function disableShowTable()
    {
        $this->viewProperties['show_table'] = false;
    }

    /**
     * Whether or not to show the "View more data" icon
     */
    public function disableShowAllColumns()
    {
        $this->viewProperties['show_table_all_columns'] = false;
    }

    /**
     * Whether or not to show the tag cloud,  pie charts, bar chart icons
     */
    public function disableShowAllViewsIcons()
    {
        $this->viewProperties['show_all_views_icons'] = false;
    }

    /**
     * Whether or not to hide view icons altogether.
     * The difference to disableShowAllViewsIcons is that not even the single icon
     * will be shown. This icon might cause trouble because it reloads the graph on click.
     */
    public function hideAllViewsIcons()
    {
        $this->viewProperties['show_all_views_icons'] = false;
        $this->viewProperties['hide_all_views_icons'] = true;
    }

    /**
     * Whether or not to show the annotations view. This method has no effect if
     * the Annotations plugin is not loaded.
     */
    public function showAnnotationsView()
    {
        if (!Piwik_PluginsManager::getInstance()->isPluginLoaded('Annotations')) {
            return;
        }

        $this->viewProperties['hide_annotations_view'] = false;
    }

    /**
     * Whether or not to show the bar chart icon.
     */
    public function disableShowBarChart()
    {
        $this->viewProperties['show_bar_chart'] = false;
    }

    /**
     * Whether or not to show the pie chart icon.
     */
    public function disableShowPieChart()
    {
        $this->viewProperties['show_pie_chart'] = false;
    }

    /**
     * Whether or not to show the tag cloud icon.
     */
    public function disableTagCloud()
    {
        $this->viewProperties['show_tag_cloud'] = false;
    }

    /**
     * Whether or not to show related reports in the footer
     */
    public function disableShowRelatedReports()
    {
        $this->viewProperties['show_related_reports'] = false;
    }

    /**
     * Whether or not to show the export to RSS feed icon
     */
    public function disableShowExportAsRssFeed()
    {
        $this->viewProperties['show_export_as_rss_feed'] = false;
    }

    /**
     * Whether or not to show the "goal" icon
     */
    public function enableShowGoals()
    {
        if (Piwik_PluginsManager::getInstance()->isPluginActivated('Goals')) {
            $this->viewProperties['show_goals'] = true;
        }
    }

    /**
     * Whether or not to show the "Ecommerce orders/cart" icons
     */
    public function enableShowEcommerce()
    {
        $this->viewProperties['show_ecommerce'] = true;
    }

    /**
     * Whether or not to show the summary row on every page of results. The default behavior
     * is to treat the summary row like any other row.
     */
    public function alwaysShowSummaryRow()
    {
        $this->variablesDefault['keep_summary_row'] = true;
    }

    /**
     * Sets the value to use for the Exclude low population filter.
     *
     * @param int|float If a row value is less than this value, it will be removed from the dataTable
     * @param string The name of the column for which we compare the value to $minValue
     */
    public function setExcludeLowPopulation($columnName = null, $minValue = null)
    {
        if (is_null($columnName)) {
            $columnName = 'nb_visits';
        }
        $this->variablesDefault['filter_excludelowpop'] = $columnName;
        $this->variablesDefault['filter_excludelowpop_value'] = $minValue;
    }

    /**
     * Sets the pattern to look for in the table (only rows matching the pattern will be kept)
     *
     * @param string $pattern to look for
     * @param string $column to compare the pattern to
     */
    public function setSearchPattern($pattern, $column)
    {
        $this->variablesDefault['filter_pattern'] = $pattern;
        $this->variablesDefault['filter_column'] = $column;
    }

    /**
     * Sets the maximum number of rows of the table
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        if ($limit !== 0) {
            $this->variablesDefault['filter_limit'] = $limit;
        }
    }

    /**
     * Will display a message in the DataTable footer.
     *
     * @param string $message Message
     */
    public function setFooterMessage($message)
    {
        $this->viewProperties['show_footer_message'] = $message;
    }

    /**
     * Sets the dataTable column to sort by. This sorting will be applied before applying the (offset, limit) filter.
     *
     * @param int|string $columnId eg. 'nb_visits' for some tables, or Piwik_Archive::INDEX_NB_VISITS for others
     * @param string $order desc or asc
     */
    public function setSortedColumn($columnId, $order = 'desc')
    {
//		debug_print_backtrace();
        $this->variablesDefault['filter_sort_column'] = $columnId;
        $this->variablesDefault['filter_sort_order'] = $order;
    }

    /**
     * Returns the column name on which the table will be sorted
     *
     * @return string
     */
    public function getSortedColumn()
    {
        return isset($this->variablesDefault['filter_sort_column']) ? $this->variablesDefault['filter_sort_column'] : false;
    }

    /**
     * Sets translation string for given column
     *
     * @param string $columnName column name
     * @param string $columnTranslation column name translation
     * @throws Exception
     */
    public function setColumnTranslation($columnName, $columnTranslation)
    {
        if (empty($columnTranslation)) {
            throw new Exception('Unknown column: ' . $columnName);
        }

        $this->columnsTranslations[$columnName] = $columnTranslation;
    }

    /**
     * Returns column translation if available, in other case given column name
     *
     * @param string $columnName column name
     * @return string
     */
    public function getColumnTranslation($columnName)
    {
        if (isset($this->columnsTranslations[$columnName])) {
            return $this->columnsTranslations[$columnName];
        }
        return $columnName;
    }

    /**
     * Set the documentation of a metric used in the report.
     * Please note, that the default way of doing this is by using
     * getReportMetadata. Only use this method, if you have a good
     * reason to do so.
     *
     * @param string $metricIdentifier The idenentifier string of
     *                    the metric
     * @param string $documentation The metric documentation as a
     *                    translated string
     */
    public function setMetricDocumentation($metricIdentifier, $documentation)
    {
        $this->metricsDocumentation[$metricIdentifier] = $documentation;
    }

    /**
     * Returns metric documentation, or false
     *
     * @param string $columnName column name
     * @return bool
     */
    public function getMetricDocumentation($columnName)
    {
        if ($this->metricsDocumentation === false) {
            $this->loadDocumentation();
        }

        if (!empty($this->metricsDocumentation[$columnName])) {
            return $this->metricsDocumentation[$columnName];
        }

        return false;
    }

    /**
     * Set the documentation of the report.
     * Please note, that the default way of doing this is by using
     * getReportMetadata. Only use this method, if you have a good
     * reason to do so.
     *
     * @param string $documentation The report documentation as a
     *                    translated string
     */
    public function setReportDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * Returns report documentation, or false
     * @return array|bool
     */
    public function getReportDocumentation()
    {
        if ($this->metricsDocumentation === false) {
            $this->loadDocumentation();
        }

        return $this->documentation;
    }

    /** Load documentation from the API */
    private function loadDocumentation()
    {
        $this->metricsDocumentation = array();

        $report = Piwik_API_API::getInstance()->getMetadata(0, $this->currentControllerName, $this->currentControllerAction);
        $report = $report[0];

        if (isset($report['metricsDocumentation'])) {
            $this->metricsDocumentation = $report['metricsDocumentation'];
        }

        if (isset($report['documentation'])) {
            $this->documentation = $report['documentation'];
        }
    }

    /**
     * Sets the columns that will be displayed in the HTML output
     * By default all columns are displayed ($columnsNames = array() will display all columns)
     *
     * @param array $columnsNames Array of column names eg. array('nb_visits','nb_hits')
     */
    public function setColumnsToDisplay($columnsNames)
    {
        if (!is_array($columnsNames)) {
            if (strpos($columnsNames, ',') !== false) {
                // array values are comma separated
                $columnsNames = explode(',', $columnsNames);
            } else {
                $columnsNames = array($columnsNames);
            }
        }
        $this->columnsToDisplay = array_filter($columnsNames);
    }

    /**
     * Returns columns names to display, in order.
     * If no columns were specified to be displayed, return all columns found in the first row.
     * If the data table has empty_columns meta data set, those columns will be removed.
     * @param array PHP array conversion of the data table
     * @return array
     */
    public function getColumnsToDisplay()
    {
        if (empty($this->columnsToDisplay)) {
            $row = $this->dataTable->getFirstRow();
            if (empty($row)) {
                return array();
            }

            return array_keys($row->getColumns());
        }

        $this->columnsToDisplay = array_filter($this->columnsToDisplay);

        $this->removeEmptyColumnsFromDisplay();

        return $this->columnsToDisplay;
    }

    private function removeEmptyColumnsFromDisplay()
    {
        if(empty($this->dataTable)) {
            return;
        }
        if ($this->dataTable instanceof Piwik_DataTable_Array) {
            $emptyColumns = $this->dataTable->getMetadataIntersectArray(Piwik_DataTable::EMPTY_COLUMNS_METADATA_NAME);
        } else {
            $emptyColumns = $this->dataTable->getMetadata(Piwik_DataTable::EMPTY_COLUMNS_METADATA_NAME);
        }
        if (is_array($emptyColumns)) {
            foreach ($emptyColumns as $emptyColumn) {
                $key = array_search($emptyColumn, $this->columnsToDisplay);
                if ($key !== false) {
                    unset($this->columnsToDisplay[$key]);
                }
            }
            $this->columnsToDisplay = array_values($this->columnsToDisplay);
        }
    }

    /**
     * Set whether to highlight the summary row or not. If not highlighted, it will
     * look like every other row.
     */
    public function setHighlightSummaryRow($highlightSummaryRow)
    {
        $this->viewProperties['highlight_summary_row'] = $highlightSummaryRow;
    }

    /**
     * Sets the name of the metadata to use for a custom tooltip.
     */
    public function setTooltipMetadataName($metadataName)
    {
        $this->viewProperties['tooltip_metadata_name'] = $metadataName;
    }

    /**
     * Sets columns translations array.
     *
     * @param array $columnsTranslations An associative array indexed by column names, eg. array('nb_visit'=>"Numer of visits")
     */
    public function setColumnsTranslations($columnsTranslations)
    {
        $this->columnsTranslations += $columnsTranslations;
    }

    /**
     * Sets a custom parameter, that will be printed in the javascript array associated with each datatable
     *
     * @param string $parameter name
     * @param mixed $value
     * @throws Exception
     */
    public function setCustomParameter($parameter, $value)
    {
        if (isset($this->variablesDefault[$parameter])) {
            throw new Exception("$parameter is already defined for this DataTable.");
        }
        $this->variablesDefault[$parameter] = $value;
    }

    /**
     * Queues a Datatable filter, that will be applied once the datatable is loaded from the API.
     * Useful when the controller needs to add columns, or decorate existing columns, when these filters don't
     * necessarily make sense directly in the API.
     *
     * @param string $filterName
     * @param mixed $parameters
     * @param bool $runBeforeGenericFilters Set to true if the filter will delete rows from the table,
     *                                    and should therefore be ran before Sort, Limit, etc.
     * @return void
     */
    public function queueFilter($filterName, $parameters, $runBeforeGenericFilters = false)
    {
        if ($runBeforeGenericFilters) {
            $this->queuedFiltersPriority[] = array($filterName, $parameters);
        } else {
            $this->queuedFilters[] = array($filterName, $parameters);
        }
    }

    /**
     * Adds one report to the set of reports that are related to this one. Related reports
     * are displayed in the footer as links. When they are clicked, the report will change to
     * the related report.
     *
     * Make sure to call setReportTitle so this report will be displayed correctly.
     *
     * @param string $module The report's controller name, ie, 'UserSettings'.
     * @param string $action The report's controller action, ie, 'getBrowser'.
     * @param string $title The text used to describe the related report.
     * @param array $queryParams Any specific query params to use when loading the report.
     *                           This can be used to, for example, make a goal report a related
     *                           report (by adding an idGoal parameter).
     */
    public function addRelatedReport($module, $action, $title, $queryParams = array())
    {
        // don't add the related report if it references this report
        if ($this->currentControllerName == $module && $this->currentControllerAction == $action) {
            return;
        }

        $url = $this->getBaseReportUrl($module, $action, $queryParams);
        $this->viewProperties['relatedReports'][$url] = $title;
    }

    /**
     * Adds a set of reports that are related to this one. Related reports are displayed in
     * the footer as links. When they are clicked, the report will change to the related report.
     *
     * If you need to associate specific query params with a report, use the addRelatedReport
     * method instead of this one.
     *
     * @param string $thisReportTitle The title of this report.
     * @param array $relatedReports An array mapping report IDs ('Controller.methodName') with
     *                              display text.
     */
    public function addRelatedReports($thisReportTitle, $relatedReports)
    {
        $this->setReportTitle($thisReportTitle);
        foreach ($relatedReports as $report => $title) {
            list($module, $action) = explode('.', $report);
            $this->addRelatedReport($module, $action, $title);
        }
    }

    /**
     * Sets the title of this report.
     *
     * @param string $title
     */
    public function setReportTitle($title)
    {
        $this->viewProperties['title'] = $title;
    }

    /**
     * Sets a custom URL to use to reference this report.
     *
     * @param string  $module
     * @param string  $action
     * @param array   $queryParams
     */
    public function setReportUrl($module, $action, $queryParams = array())
    {
        $this->viewProperties['self_url'] = $this->getBaseReportUrl($module, $action, $queryParams);
    }

    /**
     * Returns true if it is likely that the data for this report has been purged and if the
     * user should be told about that.
     *
     * In order for this function to return true, the following must also be true:
     * - The data table for this report must either be empty or not have been fetched.
     * - The period of this report is not a multiple period.
     * - The date of this report must be older than the delete_reports_older_than config option.
     * @return bool
     */
    public function hasReportBeenPurged()
    {
        $strPeriod = Piwik_Common::getRequestVar('period', false);
        $strDate = Piwik_Common::getRequestVar('date', false);

        if ($strPeriod !== false
            && $strDate !== false
            && (is_null($this->dataTable) || $this->dataTable->getRowsCount() == 0)
        ) {
            // if range, only look at the first date
            if ($strPeriod == 'range') {
                $idSite = Piwik_Common::getRequestVar('idSite', '');
                if (intval($idSite) != 0) {
                    $site = new Piwik_Site($idSite);
                    $timezone = $site->getTimezone();
                } else {
                    $timezone = 'UTC';
                }

                $period = new Piwik_Period_Range('range', $strDate, $timezone);
                $reportDate = $period->getDateStart();
            } // if a multiple period, this function is irrelevant
            else if (Piwik_Archive::isMultiplePeriod($strDate, $strPeriod)) {
                return false;
            } // otherwise, use the date as given
            else {
                $reportDate = Piwik_Date::factory($strDate);
            }

            $reportYear = $reportDate->toString('Y');
            $reportMonth = $reportDate->toString('m');

            if (class_exists('Piwik_PrivacyManager')
                && Piwik_PrivacyManager::shouldReportBePurged($reportYear, $reportMonth)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns URL for this report w/o any filter parameters.
     *
     * @param string $module
     * @param string $action
     * @param array $queryParams
     * @return string
     */
    private function getBaseReportUrl($module, $action, $queryParams = array())
    {
        $params = array_merge($queryParams, array('module' => $module, 'action' => $action));

        // unset all filter query params so the related report will show up in its default state,
        // unless the filter param was in $queryParams
        $genericFiltersInfo = Piwik_API_DataTableGenericFilter::getGenericFiltersInformation();
        foreach ($genericFiltersInfo as $filter) {
            foreach ($filter as $queryParamName => $queryParamInfo) {
                if (!isset($params[$queryParamName])) {
                    $params[$queryParamName] = null;
                }
            }
        }

        // add the related report
        $url = Piwik_Url::getCurrentQueryStringWithParametersModified($params);
        return $url;
    }
}
