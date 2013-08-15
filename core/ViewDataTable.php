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

use Piwik\Config;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\API\Request;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\NoAccessException;
use Piwik\Common;
use Piwik\Date;
use Piwik\DataTable;
use Piwik\Url;
use Piwik\Site;
use Piwik\ViewDataTable\Properties;
use Piwik\ViewDataTable\VisualizationPropertiesProxy;
use Piwik\Plugins\API\API;

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
 * @see \Piwik\ViewDataTable\Properties - for core DataTable display properties.
 * @see factory() for all the available output (cloud tags, html table, pie chart, vertical bar chart)
 * @package Piwik
 * @subpackage ViewDataTable
 */
class ViewDataTable
{
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
        $visualizationClass = $visualizationId ? DataTableVisualization::getClassFromId($visualizationId) : null;
        $this->visualizationClass = $visualizationClass;

        list($currentControllerName, $currentControllerAction) = explode('.', $currentControllerAction);
        $this->currentControllerName = $currentControllerName;
        $this->currentControllerAction = $currentControllerAction;

        $this->viewProperties['visualization_properties'] = new VisualizationPropertiesProxy($visualizationClass);
        $this->viewProperties['metadata'] = array();
        $this->viewProperties['translations'] = array();
        $this->viewProperties['filters'] = array();
        $this->viewProperties['related_reports'] = array();
        $this->viewProperties['subtable_controller_action'] = $currentControllerAction;

        $this->setDefaultProperties();

        foreach ($viewProperties as $name => $value) {
            $this->setViewProperty($name, $value);
        }

        $queryParams = Url::getArrayFromCurrentQueryString();
        foreach ($this->getClientSideProperties() as $name) {
            if (isset($queryParams[$name])) {
                $this->setViewProperty($name, $queryParams[$name]);
            }
        }

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

        $this->loadDocumentation();
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
     * TODO
     *
     * @return array
     */
    public function getClientSideProperties()
    {
        $result = array(
            'show_search',
            'show_table',
            'show_table_all_columns',
            'show_all_views_icons',
            'show_active_view_icon',
            'show_bar_chart',
            'show_pie_chart',
            'show_tag_cloud',
            'show_export_as_image_icon',
            'show_export_as_rss_feed',
            'show_exclude_low_population',
            'show_offset_information',
            'show_pagination_control',
            'show_footer',
            'show_related_reports',
            'keep_summary_row',
            'subtable_controller_action',
        );

        if ($this->visualizationClass) {
            $klass = $this->visualizationClass;
            $result = array_merge($result, $klass::getClientSideProperties());
        }

        return $result;
    }

    /**
     * Returns the list of view properties that should be sent with the HTML response
     * as JSON. These properties can be manipulated via the ViewDataTable UI.
     *
     * @return array
     */
    public function getClientSideParameters()
    {
        $result = array(
            'enable_sort',
            'disable_generic_filters',
            'disable_queued_filters',
            'filter_excludelowpop',
            'filter_excludelowpop_value',
            'filter_pattern',
            'filter_column',
            'filter_limit',
            'filter_sort_column',
            'filter_sort_order',
        );

        if ($this->visualizationClass) {
            $klass = $this->visualizationClass;
            $result = array_merge($result, $klass::getClientSideParameters());
        }

        return $result;
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
     * Plugins can associate callbacks with the ViewDataTable.getReportDisplayProperties
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
            Piwik_PostEvent('ViewDataTable.getReportDisplayProperties', array(&self::$reportPropertiesCache));
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
     * TODO
     */
    private function setVisualizationPropertiesFromMetadata($properties)
    {
        if ($this->visualizationClass === null) {
            return null;
        }

        $visualizationIds = DataTableVisualization::getVisualizationIdsWithInheritance($this->visualizationClass);
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
     * Checks that the API returned a normal DataTable (as opposed to DataTable_Array)
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
        if (empty($this->dataTable)) {
            return false;
        }
        
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
            if ($haveNbUniqVisitors) {
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

        if (!$this->areQueuedFiltersDisabled()) {
            // Finally, apply datatable filters that were queued (should be 'presentation' filters that
            // do not affect the number of rows)
            foreach ($otherFilters as $filter) {
                $this->dataTable->filter($filter[0], $filter[1]);
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
            $timeAgo = Piwik::getPrettyTimeFromSeconds($elapsedSeconds);

            return Piwik_Translate('CoreHome_ReportGeneratedXAgo', $timeAgo);
        }

        $prettyDate = $date->getLocalized("%longYear%, %longMonth% %day%") . $date->toString('S');
        return Piwik_Translate('CoreHome_ReportGeneratedOn', $prettyDate);
    }

    /**
     * @return string URL to call the API, eg. "method=Referers.getKeywords&period=day&date=yesterday"...
     */
    protected function getRequestArray()
    {
        // we prepare the array to give to the API Request
        // we setup the method and format variable
        // - we request the method to call to get this specific DataTable
        // - the format = original specifies that we want to get the original DataTable structure itself, not rendered
        $requestArray = array(
            'method'                  => $this->viewProperties['apiMethodToRequestDataTable'],
            'format'                  => 'original',
            'disable_generic_filters' => Common::getRequestVar('disable_generic_filters', 1, 'int')
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
            'disable_queued_filters',
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

        $requestArray = array_merge($requestArray, $this->viewProperties['request_parameters_to_modify']);

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
    protected function getJavascriptVariablesToSet()
    {
        // build javascript variables to set
        $javascriptVariablesToSet = array();

        $genericFilters = \Piwik\API\DataTableGenericFilter::getGenericFiltersInformation();
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

        // at this point there are some filters values we  may have not set,
        // case of the filter without default values and parameters set directly in this class
        // for example setExcludeLowPopulation
        // we go through all the $this->viewProperties array and set the variables not set yet
        foreach ($this->getClientSideParameters() as $name) {
            if (!isset($javascriptVariablesToSet[$name])) {
                if (!empty($this->viewProperties[$name])) {
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

        // we escape the values that will be displayed in the javascript footer of each datatable
        // to make sure there is no malicious code injected (the value are already htmlspecialchar'ed as they
        // are loaded with Common::getRequestVar()
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
            && (is_null($this->dataTable) || $this->dataTable->getRowsCount() == 0)
        ) {
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

        if ($this->viewProperties['filter_limit'] == 0) {
            $this->viewProperties['filter_limit'] = false;
        }
    }

    protected function buildView()
    {
        $visualization = new $this->visualizationClass($this);
        $this->overrideViewProperties();

        try {
            $this->loadDataTableFromAPI();
            $this->postDataTableLoadedFromAPI();
        } catch (NoAccessException $e) {
            throw $e;
        } catch (\Exception $e) {
            Piwik::log("Failed to get data from API: " . $e->getMessage());

            $this->loadingError = array('message' => $e->getMessage());
        }

        $template = $this->viewProperties['datatable_template'];
        $view = new View($template);

        if (!empty($this->loadingError)) {
            $view->error = $this->loadingError;
        }

        $view->visualization = $visualization;
        $view->visualizationCssClass = $this->getDefaultDataTableCssClass();

        if (!$this->dataTable === null) {
            $view->dataTable = null;
        } else {
            $view->dataTable = $this->dataTable;

            // if it's likely that the report data for this data table has been purged,
            // set whether we should display a message to that effect.
            $view->showReportDataWasPurgedMessage = $this->hasReportBeenPurged();
            $view->deleteReportsOlderThan = Piwik_GetOption('delete_reports_older_than');
        }
        $view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
        $view->clientSidePropertiesToSet = $this->getClientSidePropertiesToSet();
        $view->properties = $this->viewProperties; // TODO: should be $this. need to move non-view properties from the class

        $nonCoreVisualizations = DataTableVisualization::getNonCoreVisualizations();
        $view->nonCoreVisualizations = DataTableVisualization::getVisualizationInfoFor($nonCoreVisualizations);
        $this->view = $view;
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
}
