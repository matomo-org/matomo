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
namespace Piwik;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Plugins\API\API;
use Piwik\ViewDataTable\Properties;
use Piwik\ViewDataTable\VisualizationPropertiesProxy;
use Piwik\ViewDataTable\Visualization;

/**
 * This class is used to load (from the API) and customize the output of a given DataTable.
 * The main() method will create an object implementing ViewInterface
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
 *        $view = ViewDataTable::factory( 'cloud' );
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
 * @property string default_view_type
 * @property string datatable_css_class
 * @property string datatable_js_type
 * @property string show_visualization_only
 * @property array footer_icons
 * @property bool hide_annotations_view
 * @property bool show_goals
 * @property bool show_exclude_low_population
 * @property bool show_table
 * @property bool show_table_all_columns
 * @property bool show_footer
 * @property bool show_footer_icons
 * @property bool show_all_views_icons
 * @property bool show_active_view_icon
 * @property bool show_flatten_table
 * @property bool show_limit_control
 * @property bool show_bar_chart
 * @property bool show_pie_chart
 * @property bool show_tag_cloud
 * @property bool show_export_as_rss_feed
 * @property bool show_ecommerce
 * @property bool show_footer_message
 * @property bool show_export_as_image_icon
 * @property bool show_non_core_visualizations
 * @property bool show_search
 * @property bool show_related_reports
 * @property string search_recursive
 * @property string metrics_documentation
 * @property string tooltip_metadata_name
 * @property string self_url
 * @property string filter_excludelowpop
 * @property string filter_excludelowpop_value
 * @property string enable_sort
 * @property string disable_generic_filters
 * @property string disable_queued_filters
 * @property array related_reports
 * @property string title
 * @property string documentation
 * @property array request_parameters_to_modify
 * @property array columns_to_display
 * @property array custom_parameters
 * @property string translations
 * @property string filter_sort_column
 * @property string filter_sort_order
 * @property string filter_column
 * @property integer filter_limit
 * @property integer filter_offset
 * @property string filter_pattern
 * @property string export_limit
 * @property string y_axis_unit
 * @property VisualizationPropertiesProxy visualization_properties
 * @property array filters
 * @property array after_data_loaded_functions
 * @property array subtable_controller_action
 * @property string show_pagination_control
 * @property string show_offset_information
 *
 * @see \Piwik\ViewDataTable\Properties - for core DataTable display properties.
 * @see factory() for all the available output (cloud tags, html table, pie chart, vertical bar chart)
 * @package Piwik
 * @subpackage ViewDataTable
 *
 * @api
 */
class ViewDataTable
{
    const CONFIGURE_VIEW_EVENT = 'Visualization.initView';
    const CONFIGURE_FOOTER_ICONS_EVENT = 'Visualization.configureFooterIcons';

    /**
     * The class name of the visualization to use.
     * 
     * @var string|null
     */
    private $visualizationClass;

    /**
     * Cache for getAllReportDisplayProperties result.
     * 
     * @var array
     */
    public static $reportPropertiesCache = null;

    /**
     * Array of properties that are available in the view
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
     * @var DataTable
     */
    protected $dataTable = null;

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
     * This view should be an implementation of the Interface ViewInterface
     * The $view object should be created in the main() method.
     *
     * @var \Piwik\View\ViewInterface
     */
    protected $view = null;

    /**
     * Default constructor.
     */
    public function __construct($currentControllerAction,
                                $apiMethodToRequestDataTable,
                                $viewProperties = array(),
                                $visualizationId = null)
    {
        if (class_exists($visualizationId)
            && is_subclass_of($visualizationId, "Piwik\\ViewDataTable\\Visualization")
        ) {
            $visualizationClass = $visualizationId;
        } else {
            $visualizationClass = $visualizationId ? Visualization::getClassFromId($visualizationId) : null;
        }

        $this->visualizationClass = $visualizationClass;

        list($currentControllerName, $currentControllerAction) = explode('.', $currentControllerAction);
        $this->currentControllerName = $currentControllerName;
        $this->currentControllerAction = $currentControllerAction;

        $this->viewProperties['visualization_properties'] = new VisualizationPropertiesProxy($visualizationClass);
        $this->viewProperties['metadata'] = array();
        $this->viewProperties['translations'] = array();
        $this->viewProperties['filters'] = array();
        $this->viewProperties['after_data_loaded_functions'] = array();
        $this->viewProperties['related_reports'] = array();
        $this->viewProperties['subtable_controller_action'] = $currentControllerAction;

        $this->setDefaultProperties();
        $this->setViewProperties($viewProperties);

        $this->idSubtable = Common::getRequestVar('idSubtable', false, 'int');
        $this->viewProperties['show_footer_icons'] = ($this->idSubtable == false);
        $this->viewProperties['apiMethodToRequestDataTable'] = $apiMethodToRequestDataTable;

        $this->viewProperties['report_id'] = $currentControllerName . '.' . $currentControllerAction;
        $this->viewProperties['self_url'] = $this->getBaseReportUrl($currentControllerName, $currentControllerAction);

        // the exclude low population threshold value is sometimes obtained by requesting data.
        // to avoid issuing unecessary requests when display properties are determined by metadata,
        // we allow it to be a closure.
        if (isset($this->viewProperties['filter_excludelowpop_value'])
            && $this->viewProperties['filter_excludelowpop_value'] instanceof \Closure
        ) {
            $function = $this->viewProperties['filter_excludelowpop_value'];
            $this->viewProperties['filter_excludelowpop_value'] = $function();
        }

        $this->overrideViewPropertiesWithQueryParams();

        $this->loadDocumentation();
    }

    /**
     * Returns the API method that will be called to obatin the report data.
     * 
     * @return string e.g. 'Actions.getPageUrls'
     */
    public function getReportApiMethod()
    {
        return $this->viewProperties['apiMethodToRequestDataTable'];
    }

    /**
     * Returns the view's associated visualization class name.
     * 
     * @return string
     */
    public function getVisualizationClass()
    {
        return $this->visualizationClass;
    }

    /**
     * Gets a view property by reference.
     * 
     * @param string $name A valid view property name. @see Properties for all
     *                     valid view properties.
     * @return mixed
     * @throws \Exception if the property name is invalid.
     */
    public function &__get($name)
    {
        Properties::checkValidPropertyName($name);
        return $this->viewProperties[$name];
    }

    /**
     * Sets a view property.
     * 
     * @param string $name A valid view property name. @see Properties for all
     *                     valid view properties.
     * @param mixed $value
     * @return mixed Returns $value.
     * @throws \Exception if the property name is invalid.
     */
    public function __set($name, $value)
    {
        Properties::checkValidPropertyName($name);
        return $this->viewProperties[$name] = $value;
    }

    /**
     * Hack to allow property access in Twig (w/ property name checking).
     */
    public function __call($name, $arguments)
    {
        return $this->$name;
    }

    /**
     * Unique string ID that defines the format of the dataTable, eg. "pieChart", "table", etc.
     *
     * @return string
     */
    public function getViewDataTableId()
    {
        $klass = $this->visualizationClass;
        return $klass::getViewDataTableId($this);
    }

    /**
     * Returns a Piwik_ViewDataTable_* object.
     * By default it will return a ViewDataTable_Html
     * If there is a viewDataTable parameter in the URL, a ViewDataTable of this 'viewDataTable' type will be returned.
     * If defaultType is specified and if there is no 'viewDataTable' in the URL, a ViewDataTable of this $defaultType will be returned.
     * If force is set to true, a ViewDataTable of the $defaultType will be returned in all cases.
     *
     * @param string      $defaultType Any of these: table, cloud, graphPie, graphVerticalBar, graphEvolution, sparkline, generateDataChart*
     * @param string|bool $apiAction
     * @param string|bool $controllerAction
     * @param bool        $forceDefault
     *
     * @return ViewDataTable
     */
    static public function factory($defaultType = null, $apiAction = false, $controllerAction = false, $forceDefault = false)
    {
        if ($controllerAction === false) {
            $controllerAction = $apiAction;
        }

        $defaultProperties = self::getDefaultPropertiesForReport($apiAction);
        if (!empty($defaultProperties['default_view_type'])
            && !$forceDefault
        ) {
            $defaultType = $defaultProperties['default_view_type'];
        }

        $type = Common::getRequestVar('viewDataTable', $defaultType ?: 'table', 'string');

        if ($type == 'sparkline') {
            $result = new ViewDataTable\Sparkline($controllerAction, $apiAction, $defaultProperties);
        } else {
            $result = new ViewDataTable($controllerAction, $apiAction, $defaultProperties, $type);
        }

        return $result;
    }

    /**
     * Returns the list of view properties that should be sent with the HTML response
     * as JSON. These properties are visible to the UI JavaScript, but are not passed
     * with every request.
     *
     * @return array
     */
    public function getClientSideProperties()
    {
        return $this->getPropertyNameListWithMetaProperty(Properties::$clientSideProperties, __FUNCTION__);
    }

    /**
     * Returns the list of view properties that should be sent with the HTML response
     * and resent by the UI JavaScript in every subsequent AJAX request.
     *
     * @return array
     */
    public function getClientSideParameters()
    {
        return $this->getPropertyNameListWithMetaProperty(Properties::$clientSideParameters, __FUNCTION__);
    }

    /**
     * Returns the list of view properties that can be overriden by query parameters.
     * 
     * @return array
     */
    public function getOverridableProperties()
    {
        return $this->getPropertyNameListWithMetaProperty(Properties::$overridableProperties, __FUNCTION__);
    }

    public function getCurrentControllerAction()
    {
        return $this->currentControllerAction;
    }

    public function getCurrentControllerName()
    {
        return $this->currentControllerName;
    }

    /**
     * Returns the DataTable loaded from the API
     *
     * @return DataTable
     * @throws \Exception if not yet defined
     */
    public function getDataTable()
    {
        if (is_null($this->dataTable)) {
            throw new \Exception("The DataTable object has not yet been created");
        }
        return $this->dataTable;
    }

    /**
     * To prevent calling an API multiple times, the DataTable can be set directly.
     * It won't be loaded again from the API in this case
     *
     * @param $dataTable
     * @return void $dataTable DataTable
     */
    public function setDataTable($dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * Returns the defaut view properties for a report, if any.
     *
     * Plugins can associate callbacks with the Visualization.getReportDisplayProperties
     * event to set the default properties of reports.
     *
     * @param string $apiAction
     * @return array
     */
    private static function getDefaultPropertiesForReport($apiAction)
    {
        $reportDisplayProperties = self::getAllReportDisplayProperties();
        return isset($reportDisplayProperties[$apiAction]) ? $reportDisplayProperties[$apiAction] : array();
    }

    /**
     * Returns the list of display properties for all available reports.
     * 
     * @return array
     */
    private static function getAllReportDisplayProperties()
    {
        if (self::$reportPropertiesCache === null) {
            self::$reportPropertiesCache = array();
            Piwik_PostEvent('Visualization.getReportDisplayProperties', array(&self::$reportPropertiesCache));
        }

        return self::$reportPropertiesCache;
    }

    /**
     * Sets a view property by name. This function handles special view properties
     * like 'translations' & 'related_reports' that store arrays.
     *
     * @param string $name
     * @param mixed $value For array properties, $value can be a comma separated string.
     * @throws \Exception
     */
    private function setViewProperty($name, $value)
    {
        if (isset($this->viewProperties[$name])
            && is_array($this->viewProperties[$name])
            && is_string($value)
        ) {
            $value = Piwik::getArrayFromApiParameter($value);
        }

        if ($name == 'translations'
            || $name == 'filters'
            || $name == 'after_data_loaded_functions'
        ) {
            $this->viewProperties[$name] = array_merge($this->viewProperties[$name], $value);
        } else if ($name == 'related_reports') { // TODO: should process after (in overrideViewProperties)
            $this->addRelatedReports($value);
        } else if ($name == 'visualization_properties') {
            $this->setVisualizationPropertiesFromMetadata($value);
        } else if (Properties::isCoreViewProperty($name)) {
            $this->viewProperties[$name] = $value;
        } else {
            $report = $this->currentControllerName . '.' . $this->currentControllerAction;
            throw new \Exception("Invalid view property '$name' specified in view property metadata for '$report'.");
        }
    }

    /**
     * Sets visualization properties using data in a visualization's default property values
     * array.
     */
    private function setVisualizationPropertiesFromMetadata($properties)
    {
        if ($this->visualizationClass === null) {
            return null;
        }

        $visualizationIds = Visualization::getVisualizationIdsWithInheritance($this->visualizationClass);
        foreach ($visualizationIds as $visualizationId) {
            if (empty($properties[$visualizationId])) {
                continue;
            }

            foreach ($properties[$visualizationId] as $key => $value) {
                if (Properties::isCoreViewProperty($key)) {
                    $this->viewProperties[$key] = $value;
                } else {
                    $this->viewProperties['visualization_properties']->$key = $value;
                }
            }
        }
    }

    /**
     * Function called by the ViewDataTable objects in order to fetch data from the API.
     * The function init() must have been called before, so that the object knows which API module and action to call.
     * It builds the API request string and uses Request to call the API.
     * The requested DataTable object is stored in $this->dataTable.
     */
    protected function loadDataTableFromAPI()
    {
        if (!is_null($this->dataTable)) {
            // data table is already there
            // this happens when setDataTable has been used
            return;
        }

        // we build the request (URL) to call the API
        $requestArray = $this->getRequestArray();

        // we make the request to the API
        $request = new Request($requestArray);

        // and get the DataTable structure
        $dataTable = $request->process();

        $this->dataTable = $dataTable;
    }

    /**
     * Checks that the API returned a normal DataTable (as opposed to DataTable\Map)
     * @throws \Exception
     * @return void
     */
    protected function checkStandardDataTable()
    {
        Piwik::checkObjectTypeIs($this->dataTable, array('\Piwik\DataTable'));
    }

    private function getFiltersToRun()
    {
        $priorityFilters = array();
        $presentationFilters = array();

        foreach ($this->viewProperties['filters'] as $filterInfo) {
            if ($filterInfo instanceof \Closure) {
                $nameOrClosure = $filterInfo;
                $parameters = array();
                $priority = false;
            } else {
                @list($nameOrClosure, $parameters, $priority) = $filterInfo;
            }

            if ($nameOrClosure instanceof \Closure) {
                $parameters[] = $this;
            }

            if ($priority) {
                $priorityFilters[] = array($nameOrClosure, $parameters);
            } else {
                $presentationFilters[] = array($nameOrClosure, $parameters);
            }
        }

        return array($priorityFilters, $presentationFilters);
    }

    /**
     * Hook called after the dataTable has been loaded from the API
     * Can be used to add, delete or modify the data freshly loaded
     *
     * @return bool
     */
    protected function postDataTableLoadedFromAPI()
    {
        $columns = $this->dataTable->getColumns();
        $haveNbVisits = in_array('nb_visits', $columns);
        $haveNbUniqVisitors = in_array('nb_uniq_visitors', $columns);

        // default columns_to_display to label, nb_uniq_visitors/nb_visits if those columns exist in the
        // dataset. otherwise, default to all columns in dataset.
        if (empty($this->viewProperties['columns_to_display'])) {
            if ($haveNbVisits
                || $haveNbUniqVisitors
            ) {
                $columnsToDisplay = array('label');
                
                // if unique visitors data is available, show it, otherwise just visits
                if ($haveNbUniqVisitors) {
                    $columnsToDisplay[] = 'nb_uniq_visitors';
                } else {
                    $columnsToDisplay[] = 'nb_visits';
                }
            } else {
                $columnsToDisplay = $columns;
            }

            $this->viewProperties['columns_to_display'] = array_filter($columnsToDisplay);
        }

        $this->removeEmptyColumnsFromDisplay();

        // default sort order to visits/visitors data
        if (empty($this->viewProperties['filter_sort_column'])) {
            if ($haveNbUniqVisitors
                && in_array('nb_uniq_visitors', $this->viewProperties['columns_to_display'])
            ) {
                $this->viewProperties['filter_sort_column'] = 'nb_uniq_visitors';
            } else {
                $this->viewProperties['filter_sort_column'] = 'nb_visits';
            }
            $this->viewProperties['filter_sort_order'] = 'desc';
        }

        // deal w/ table metadata
        if ($this->dataTable instanceof DataTable) {
            $this->viewProperties['metadata'] = $this->dataTable->getAllTableMetadata();

            if (isset($this->viewProperties['metadata'][DataTable::ARCHIVED_DATE_METADATA_NAME])) {
                $this->viewProperties['metadata'][DataTable::ARCHIVED_DATE_METADATA_NAME] =
                    $this->makePrettyArchivedOnText();
            }
        }

        list($priorityFilters, $otherFilters) = $this->getFiltersToRun();

        // First, filters that delete rows
        foreach ($priorityFilters as $filter) {
            $this->dataTable->filter($filter[0], $filter[1]);
        }

        if (!$this->areGenericFiltersDisabled()) {
            // Second, generic filters (Sort, Limit, Replace Column Names, etc.)
            $requestArray = $this->getRequestArray();
            $request = Request::getRequestArrayFromString($requestArray);

            if ($this->viewProperties['enable_sort'] === false) {
                $request['filter_sort_column'] = $request['filter_sort_order'] = '';
            }

            $genericFilter = new \Piwik\API\DataTableGenericFilter($request);
            $genericFilter->filter($this->dataTable);
        }

        // queue other filters so they can be applied later if queued filters are disabled
        foreach ($otherFilters as $filter) {
            $this->dataTable->queueFilter($filter[0], $filter[1]);
        }

        // Finally, apply datatable filters that were queued (should be 'presentation' filters that
        // do not affect the number of rows)
        if (!$this->areQueuedFiltersDisabled()) {
            $this->dataTable->applyQueuedFilters();
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
        if (Common::getRequestVar('disable_generic_filters', '0', 'string') == 1) {
            return true;
        }

        // if $this->disableGenericFilters() was called, generic filters are disabled
        if (isset($this->viewProperties['disable_generic_filters'])
            && $this->viewProperties['disable_generic_filters'] === true
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
        return isset($this->viewProperties['disable_queued_filters'])
            && $this->viewProperties['disable_queued_filters'];
    }

    /**
     * Returns prettified and translated text that describes when a report was last updated.
     *
     * @return string
     */
    private function makePrettyArchivedOnText()
    {
        $dateText = $this->viewProperties['metadata'][DataTable::ARCHIVED_DATE_METADATA_NAME];
        $date = Date::factory($dateText);
        $today = mktime(0, 0, 0);
        if ($date->getTimestamp() > $today) {
            $elapsedSeconds = time() - $date->getTimestamp();
            $timeAgo = MetricsFormatter::getPrettyTimeFromSeconds($elapsedSeconds);

            return Piwik_Translate('CoreHome_ReportGeneratedXAgo', $timeAgo);
        }

        $prettyDate = $date->getLocalized("%longYear%, %longMonth% %day%") . $date->toString('S');
        return Piwik_Translate('CoreHome_ReportGeneratedOn', $prettyDate);
    }

    /**
     * @return string URL to call the API, eg. "method=Referrers.getKeywords&period=day&date=yesterday"...
     */
    public function getRequestArray()
    {
        // we prepare the array to give to the API Request
        // we setup the method and format variable
        // - we request the method to call to get this specific DataTable
        // - the format = original specifies that we want to get the original DataTable structure itself, not rendered
        $requestArray = array(
            'method'                  => $this->viewProperties['apiMethodToRequestDataTable'],
            'format'                  => 'original',
            'disable_generic_filters' => Common::getRequestVar('disable_generic_filters', 1, 'int'),
            'disable_queued_filters'  => Common::getRequestVar('disable_queued_filters', 1, 'int')
        );

        $toSetEventually = array(
            'filter_limit',
            'keep_summary_row',
            'filter_sort_column',
            'filter_sort_order',
            'filter_excludelowpop',
            'filter_excludelowpop_value',
            'filter_column',
            'filter_pattern',
        );

        foreach ($toSetEventually as $varToSet) {
            $value = $this->getDefaultOrCurrent($varToSet);
            if (false !== $value) {
                $requestArray[$varToSet] = $value;
            }
        }
        
        $segment = Request::getRawSegmentFromRequest();
        if(!empty($segment)) {
            $requestArray['segment'] = $segment;
        }

        if (self::shouldLoadExpanded()) {
            $requestArray['expanded'] = 1;
        }

        $requestArray = array_merge($requestArray, $this->request_parameters_to_modify);

        if (!empty($requestArray['filter_limit'])
            && $requestArray['filter_limit'] === 0
        ) {
            unset($requestArray['filter_limit']);
        }

        return $requestArray;
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
    protected function getClientSideParametersToSet()
    {
        // build javascript variables to set
        $javascriptVariablesToSet = array();

        foreach ($this->viewProperties['custom_parameters'] as $name => $value) {
            $javascriptVariablesToSet[$name] = $value;
        }

        foreach ($_GET as $name => $value) {
            try {
                $requestValue = Common::getRequestVar($name);
            } catch (\Exception $e) {
                $requestValue = '';
            }
            $javascriptVariablesToSet[$name] = $requestValue;
        }

        foreach ($this->getClientSideParameters() as $name) {
            if (!isset($javascriptVariablesToSet[$name])) {
                if (isset($this->viewProperties[$name])
                    && $this->viewProperties[$name] !== false
                ) {
                    $javascriptVariablesToSet[$name] = $this->convertForJson($this->viewProperties[$name]);
                } else if (Properties::isValidVisualizationProperty($this->visualizationClass, $name)) {
                    $javascriptVariablesToSet[$name] =
                        $this->convertForJson($this->viewProperties['visualization_properties']->$name);
                }
            }
        }

        if ($this->dataTable instanceof DataTable) {
            // we override the filter_sort_column with the column used for sorting,
            // which can be different from the one specified (eg. if the column doesn't exist)
            $javascriptVariablesToSet['filter_sort_column'] = $this->dataTable->getSortedByColumnName();
            // datatable can return "2" but we want to write "nb_visits" in the js
            if (isset(Metrics::$mappingFromIdToName[$javascriptVariablesToSet['filter_sort_column']])) {
                $javascriptVariablesToSet['filter_sort_column'] = Metrics::$mappingFromIdToName[$javascriptVariablesToSet['filter_sort_column']];
            }
        }

        $javascriptVariablesToSet['module'] = $this->currentControllerName;
        $javascriptVariablesToSet['action'] = $this->currentControllerAction;
        if (!isset($javascriptVariablesToSet['viewDataTable'])) {
            $javascriptVariablesToSet['viewDataTable'] = $this->getViewDataTableId();
        }

        if ($this->dataTable &&
            // Set doesn't have the method
            !($this->dataTable instanceof DataTable\Map)
            && empty($javascriptVariablesToSet['totalRows'])
        ) {
            $javascriptVariablesToSet['totalRows'] = $this->dataTable->getRowsCountBeforeLimitFilter();
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

        $rawSegment = Request::getRawSegmentFromRequest();
        if(!empty($rawSegment)) {
            $javascriptVariablesToSet['segment'] = $rawSegment;
        }

        return $javascriptVariablesToSet;
    }

    /**
     * Returns array of properties that should be visible to client side JavaScript. The data
     * will be available in the data-props HTML attribute of the .dataTable div.
     * 
     * @return array Maps property names w/ property values.
     */
    private function getClientSidePropertiesToSet()
    {
        $result = array();
        foreach ($this->getClientSideProperties() as $name) {
            if (isset($this->viewProperties[$name])) {
                $result[$name] = $this->convertForJson($this->viewProperties[$name]);
            } else if (Properties::isValidVisualizationProperty($this->visualizationClass, $name)) {
                $result[$name] = $this->convertForJson($this->viewProperties['visualization_properties']->$name);
            }
        }
        return $result;
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
            return Common::sanitizeInputValue($_GET[$nameVar]);
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
        if (!isset($this->viewProperties[$nameVar])) {
            return false;
        }
        return $this->viewProperties[$nameVar];
    }

    /** Load documentation from the API */
    private function loadDocumentation()
    {
        $this->viewProperties['metrics_documentation'] = array();

        $report = API::getInstance()->getMetadata(0, $this->currentControllerName, $this->currentControllerAction);
        $report = $report[0];

        if (isset($report['metricsDocumentation'])) {
            $this->viewProperties['metrics_documentation'] = $report['metricsDocumentation'];
        }

        if (isset($report['documentation'])) {
            $this->viewProperties['documentation'] = $report['documentation'];
        }
    }

    private function removeEmptyColumnsFromDisplay()
    {
        if (empty($this->dataTable)) {
            return;
        }
        if ($this->dataTable instanceof DataTable\Map) {
            $emptyColumns = $this->dataTable->getMetadataIntersectArray(DataTable::EMPTY_COLUMNS_METADATA_NAME);
        } else {
            $emptyColumns = $this->dataTable->getMetadata(DataTable::EMPTY_COLUMNS_METADATA_NAME);
        }
        if (is_array($emptyColumns)) {
            foreach ($emptyColumns as $emptyColumn) {
                $key = array_search($emptyColumn, $this->viewProperties['columns_to_display']);
                if ($key !== false) {
                    unset($this->viewProperties['columns_to_display'][$key]);
                }
            }
            $this->viewProperties['columns_to_display'] = array_values($this->viewProperties['columns_to_display']);
        }
    }

    private function addRelatedReport($module, $action, $title, $queryParams = array())
    {
        // don't add the related report if it references this report
        if ($this->currentControllerName == $module && $this->currentControllerAction == $action) {
            return;
        }

        $url = $this->getBaseReportUrl($module, $action, $queryParams);
        $this->viewProperties['related_reports'][$url] = $title;
    }

    private function addRelatedReports($relatedReports)
    {
        foreach ($relatedReports as $report => $title) {
            list($module, $action) = explode('.', $report);
            $this->addRelatedReport($module, $action, $title);
        }
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
        $strPeriod = Common::getRequestVar('period', false);
        $strDate = Common::getRequestVar('date', false);

        if ($strPeriod !== false
            && $strDate !== false
            && (is_null($this->dataTable)
                || (!empty($this->dataTable) && $this->dataTable->getRowsCount() == 0))) {
            // if range, only look at the first date
            if ($strPeriod == 'range') {
                $idSite = Common::getRequestVar('idSite', '');
                if (intval($idSite) != 0) {
                    $site = new Site($idSite);
                    $timezone = $site->getTimezone();
                } else {
                    $timezone = 'UTC';
                }

                $period = new Range('range', $strDate, $timezone);
                $reportDate = $period->getDateStart();
            } // if a multiple period, this function is irrelevant
            else if (Period::isMultiplePeriod($strDate, $strPeriod)) {
                return false;
            } // otherwise, use the date as given
            else {
                $reportDate = Date::factory($strDate);
            }

            $reportYear = $reportDate->toString('Y');
            $reportMonth = $reportDate->toString('m');

            if (PluginsManager::getInstance()->isPluginActivated('PrivacyManager')
                && Plugins\PrivacyManager\PrivacyManager::shouldReportBePurged($reportYear, $reportMonth)
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
        return Request::getCurrentUrlWithoutGenericFilters($params);
    }

    /**
     * Convenience method that creates and renders a ViewDataTable for a API method.
     *
     * @param string $pluginName The name of the plugin (eg, UserSettings).
     * @param string $apiAction The name of the API action (eg, getResolution).
     * @param bool $fetch If true, the result is returned, if false it is echo'd.
     * @throws \Exception
     * @return string|null See $fetch.
     */
    static public function renderReport($pluginName, $apiAction, $fetch = true)
    {
        $namespacedApiClassName = "\\Piwik\\Plugins\\$pluginName\\API";
        if (!method_exists($namespacedApiClassName::getInstance(), $apiAction)) {
            throw new \Exception("$namespacedApiClassName Invalid action name '$apiAction' for '$pluginName' plugin.");
        }
        
        $view = self::factory(null, $pluginName.'.'.$apiAction);
        $rendered = $view->render();
        
        if ($fetch) {
            return $rendered;
        } else {
            echo $rendered;
        }
    }

    /**
     * Convenience function. Calls main() & renders the view that gets built.
     * 
     * @return string The result of rendering.
     */
    public function render()
    {
        $this->buildView();
        return $this->view->render();
    }
    
    /**
     * Returns whether the DataTable result will have to be expanded for the
     * current request before rendering.
     *
     * @return bool
     */
    public static function shouldLoadExpanded()
    {
        // if filter_column_recursive & filter_pattern_recursive are supplied, and flat isn't supplied
        // we have to load all the child subtables.
        return Common::getRequestVar('filter_column_recursive', false) !== false
            && Common::getRequestVar('filter_pattern_recursive', false) !== false
            && Common::getRequestVar('flat', false) === false;
    }

    protected function overrideViewProperties()
    {
        if (!PluginsManager::getInstance()->isPluginActivated('Goals')) {
            $this->viewProperties['show_goals'] = false;
        }

        if (empty($this->viewProperties['footer_icons'])) {
            $this->viewProperties['footer_icons'] = $this->getDefaultFooterIconsToShow();
        }
    }

    protected function buildView()
    {
        $visualization = new $this->visualizationClass($this);

        /**
         * This event is called before a visualization is created. Plugins can use this event to
         * override view properties for individual reports or visualizations.
         *
         * Themes can use this event to make sure reports look nice with their themes. Plugins
         * that provide new visualizations can use this event to make sure certain reports
         * are configured differently when viewed with the new visualization.
         */
        Piwik_PostEvent(self::CONFIGURE_VIEW_EVENT, array($viewDataTable = $this));
        $this->overrideViewProperties();
        
        try {
            $this->loadDataTableFromAPI();
            $this->postDataTableLoadedFromAPI();
            $this->executeAfterDataLoadedCallbacks();
        } catch (NoAccessException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning("Failed to get data from API: " . $e->getMessage());

            $loadingError = array('message' => $e->getMessage());
        }

        $view = new View("@CoreHome/_dataTable");

        if (!empty($loadingError)) {
            $view->error = $loadingError;
        }

        $view->visualization = $visualization;
        $view->visualizationCssClass = $this->getDefaultDataTableCssClass();

        if (!$this->dataTable === null) {
            $view->dataTable = null;
        } else {
            // TODO: this hook seems inappropriate. should be able to find data that is requested for (by site/date) and check if that
            //       has data.
            if (method_exists($visualization, 'isThereDataToDisplay')) {
                $view->dataTableHasNoData = !$visualization->isThereDataToDisplay($this->dataTable, $this);
            }

            $view->dataTable = $this->dataTable;

            // if it's likely that the report data for this data table has been purged,
            // set whether we should display a message to that effect.
            $view->showReportDataWasPurgedMessage = $this->hasReportBeenPurged();
            $view->deleteReportsOlderThan = Piwik_GetOption('delete_reports_older_than');
        }
        $view->idSubtable = $this->idSubtable;
        $view->clientSideParameters = $this->getClientSideParametersToSet();
        $view->clientSideProperties = $this->getClientSidePropertiesToSet();
        $view->properties = $this->viewProperties;
        $view->footerIcons = $this->viewProperties['footer_icons'];
        $view->isWidget = Common::getRequestVar('widget', 0, 'int');

        $this->view = $view;
    }

    private function executeAfterDataLoadedCallbacks()
    {
        foreach ($this->after_data_loaded_functions as $callback) {
            $callback($this->dataTable, $this);
        }
    }

    private function getDefaultFooterIconsToShow()
    {
        $result = array();

        // add normal view icons (eg, normal table, all columns, goals)
        $normalViewIcons = array(
            'class' => 'tableAllColumnsSwitch',
            'buttons' => array(),
        );

        if ($this->show_table) {
            $normalViewIcons['buttons'][] = array(
                'id' => 'table',
                'title' => Piwik_Translate('General_DisplaySimpleTable'),
                'icon' => 'plugins/Zeitgeist/images/table.png',
            );
        }

        if ($this->show_table_all_columns) {
            $normalViewIcons['buttons'][] = array(
                'id' => 'tableAllColumns',
                'title' => Piwik_Translate('General_DisplayTableWithMoreMetrics'),
                'icon' => 'plugins/Zeitgeist/images/table_more.png'
            );
        }

        if ($this->show_goals) {
            if (Common::getRequestVar('idGoal', false) == 'ecommerceOrder') {
                $icon = 'plugins/Zeitgeist/images/ecommerceOrder.gif';
            } else {
                $icon = 'plugins/Zeitgeist/images/goal.png';
            }
            
            $normalViewIcons['buttons'][] = array(
                'id' => 'tableGoals',
                'title' => Piwik_Translate('General_DisplayTableWithGoalMetrics'),
                'icon' => $icon
            );
        }

        if ($this->show_ecommerce) {
            $normalViewIcons['buttons'][] = array(
                'id' => 'ecommerceOrder',
                'title' => Piwik_Translate('General_EcommerceOrders'),
                'icon' => 'plugins/Zeitgeist/images/ecommerceOrder.gif',
                'text' => Piwik_Translate('General_EcommerceOrders')
            );

            $normalViewIcons['buttons'][] = array(
                'id' => 'ecommerceAbandonedCart',
                'title' => Piwik_Translate('General_AbandonedCarts'),
                'icon' => 'plugins/Zeitgeist/images/ecommerceAbandonedCart.gif',
                'text' => Piwik_Translate('General_AbandonedCarts')
            );
        }

        if (!empty($normalViewIcons['buttons'])) {
            $result[] = $normalViewIcons;
        }

        // add graph views
        $graphViewIcons = array(
            'class' => 'tableGraphViews tableGraphCollapsed',
            'buttons' => array(),
        );

        if ($this->show_all_views_icons) {
            if ($this->show_bar_chart) {
                $graphViewIcons['buttons'][] = array(
                    'id' => 'graphVerticalBar',
                    'title' => Piwik_Translate('General_VBarGraph'),
                    'icon' => 'plugins/Zeitgeist/images/chart_bar.png'
                );
            }

            if ($this->show_pie_chart) {
                $graphViewIcons['buttons'][] = array(
                    'id' => 'graphPie',
                    'title' => Piwik_Translate('General_Piechart'),
                    'icon' => 'plugins/Zeitgeist/images/chart_pie.png'
                );
            }

            if ($this->show_tag_cloud) {
                $graphViewIcons['buttons'][] = array(
                    'id' => 'cloud',
                    'title' => Piwik_Translate('General_TagCloud'),
                    'icon' => 'plugins/Zeitgeist/images/tagcloud.png'
                );
            }

            if ($this->show_non_core_visualizations) {
                $nonCoreVisualizations = Visualization::getNonCoreVisualizations();
                $nonCoreVisualizationInfo = Visualization::getVisualizationInfoFor($nonCoreVisualizations);

                foreach ($nonCoreVisualizationInfo as $format => $info) {
                    $graphViewIcons['buttons'][] = array(
                        'id' => $format,
                        'title' => Piwik_Translate($info['title']),
                        'icon' => $info['table_icon']
                    );
                }
            }
        }

        if (!empty($graphViewIcons['buttons'])) {
            $result[] = $graphViewIcons;
        }

        /**
         * This event is called when determining the default set of footer icons to display
         * below a report.
         *
         * Plugins can use this event to modify the default set of footer icons. You can
         * add new icons or remove existing ones.
         *
         * $result must have the following format:
         *
         * ```
         * array(
         *     array( // footer icon group 1
         *         'class' => 'footerIconGroup1CssClass',
         *         'buttons' => array(
         *             'id' => 'myid',
         *             'title' => 'My Tooltip',
         *             'icon' => 'path/to/my/icon.png'
         *         )
         *     ),
         *     array( // footer icon group 2
         *         'class' => 'footerIconGroup2CssClass',
         *         'buttons' => array(...)
         *     ),
         *     ...
         * )
         * ```
         */
        Piwik_PostEvent(self::CONFIGURE_FOOTER_ICONS_EVENT, array(&$result, $viewDataTable = $this));
        
        return $result;
    }

    public function getDefaultDataTableCssClass()
    {
        return 'dataTableViz' . Piwik::getUnnamespacedClassName($this->visualizationClass);
    }

    private function setViewProperties($values)
    {
        foreach ($values as $name => $value) {
            $this->setViewProperty($name, $value);
        }
    }

    private function setDefaultProperties()
    {
        // set core default properties
        $this->setViewProperties(Properties::getDefaultPropertyValues());

        // set visualization default properties
        if ($this->visualizationClass === null) {
            return;
        }

        $visualizationClass = $this->visualizationClass;
        $this->setViewProperties($visualizationClass::getDefaultPropertyValues());
    }

    private function convertForJson($value)
    {
        return is_bool($value) ? (int)$value : $value;
    }

    private function overrideViewPropertiesWithQueryParams()
    {
        $properties = $this->getOverridableProperties();
        foreach ($properties as $name) {
            if (Properties::isCoreViewProperty($name)) {
                $default = $this->viewProperties[$name];

                $this->viewProperties[$name] = $this->getPropertyFromQueryParam($name, $default);
            } else if (Properties::isValidVisualizationProperty($this->visualizationClass, $name)) {
                $default = $this->viewProperties['visualization_properties']->$name;

                $this->viewProperties['visualization_properties']->$name =
                    $this->getPropertyFromQueryParam($name, $default);
            }
        }

        // handle special 'columns' query parameter
        $columns = Common::getRequestVar('columns', false);
        if ($columns !== false) {
            $this->columns_to_display = Piwik::getArrayFromApiParameter($columns);
            array_unshift($this->columns_to_display, 'label');
        }
    }

    private function getPropertyFromQueryParam($name, $defaultValue)
    {
        $type = is_numeric($defaultValue) ? 'int' : null;
        return Common::getRequestVar($name, $defaultValue, $type);
    }

    /**
     * Helper function for getCliendSiteProperties/getClientSideParameters/etc.
     */
    private function getPropertyNameListWithMetaProperty($propertyNames, $getPropertiesFunctionName)
    {
        if ($this->visualizationClass) {
            $klass = $this->visualizationClass;
            $propertyNames = array_merge($propertyNames, $klass::$getPropertiesFunctionName());
        }
        return $propertyNames;
    }
}