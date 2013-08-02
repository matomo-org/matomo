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
use Piwik_API_API;

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
     * TODO
     * TODO: change to private
     */
    protected $visualization;

    /**
     * Cache for getAllReportDisplayProperties result.
     * 
     * @var array
     */
    public static $reportPropertiesCache = null;

    /**
     * Flag used to make sure the main() is only executed once
     *
     * @var bool
     */
    protected $mainAlreadyExecuted = false;

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
     * This view should be an implementation of the Interface ViewInterface
     * The $view object should be created in the main() method.
     *
     * @var \Piwik\View\ViewInterface
     */
    protected $view = null;

    /**
     * Default constructor.
     */
    public function __construct($visualization = null)
    {
        $this->visualization = $visualization;

        $this->viewProperties['visualization_properties'] = new VisualizationPropertiesProxy(null);
        $this->viewProperties['datatable_template'] = '@CoreHome/_dataTable';
        $this->viewProperties['show_goals'] = false;
        $this->viewProperties['show_ecommerce'] = false;
        $this->viewProperties['show_search'] = true;
        $this->viewProperties['show_table'] = true;
        $this->viewProperties['show_table_all_columns'] = true;
        $this->viewProperties['show_all_views_icons'] = true;
        $this->viewProperties['show_active_view_icon'] = true;
        $this->viewProperties['hide_annotations_view'] = true;
        $this->viewProperties['show_bar_chart'] = true;
        $this->viewProperties['show_pie_chart'] = true;
        $this->viewProperties['show_tag_cloud'] = true;
        $this->viewProperties['show_export_as_image_icon'] = false;
        $this->viewProperties['show_export_as_rss_feed'] = true;
        $this->viewProperties['show_exclude_low_population'] = true;
        $this->viewProperties['show_offset_information'] = true;
        $this->viewProperties['show_pagination_control'] = true;
        $this->viewProperties['show_limit_control'] = false;
        $this->viewProperties['show_footer'] = true;
        $this->viewProperties['show_related_reports'] = true;
        $this->viewProperties['exportLimit'] = Config::getInstance()->General['API_datatable_default_limit'];
        $this->viewProperties['highlight_summary_row'] = false;
        $this->viewProperties['metadata'] = array();
        $this->viewProperties['relatedReports'] = array();
        $this->viewProperties['title'] = 'unknown';
        $this->viewProperties['tooltip_metadata_name'] = false;
        $this->viewProperties['enable_sort'] = true;
        $this->viewProperties['disable_generic_filters'] = false;
        $this->viewProperties['disable_queued_filters'] = false;
        $this->viewProperties['keep_summary_row'] = false;
        $this->viewProperties['filter_excludelowpop'] = false;
        $this->viewProperties['filter_excludelowpop_value'] = false;
        $this->viewProperties['filter_pattern'] = false;
        $this->viewProperties['filter_column'] = false;
        $this->viewProperties['filter_limit'] = false;
        $this->viewProperties['filter_sort_column'] = false;
        $this->viewProperties['filter_sort_order'] = false;
        $this->viewProperties['custom_parameters'] = array();
        $this->viewProperties['translations'] = array_merge(
            Metrics::getDefaultMetrics(),
            Metrics::getDefaultProcessedMetrics()
        );
        $this->viewProperties['request_parameters_to_modify'] = array();
        $this->viewProperties['documentation'] = false;
        $this->viewProperties['subtable_controller_action'] = false;
        $this->viewProperties['datatable_css_class'] = $this->getDefaultDataTableCssClass();
        $this->viewProperties['selectable_columns'] = array(); // TODO: only valid for graphs... shouldn't be here.
        $this->viewProperties['columns_to_display'] = array();

        $columns = Common::getRequestVar('columns', false);
        if ($columns !== false) {
            $this->viewProperties['columns_to_display'] = Piwik::getArrayFromApiParameter($columns);
            array_unshift($this->viewProperties['columns_to_display'], 'label');
        }
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
     * TODO
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        try {
            $this->loadDataTableFromAPI();
        } catch (NoAccessException $e) {
            throw $e;
        } catch (\Exception $e) {
            Piwik::log("Failed to get data from API: " . $e->getMessage());

            $this->loadingError = array('message' => $e->getMessage());
        }

        $this->postDataTableLoadedFromAPI();

        $this->view = $this->buildView($this->visualization);
    }

    /**
     * Unique string ID that defines the format of the dataTable, eg. "pieChart", "table", etc.
     *
     * @return string
     */
    protected function getViewDataTableId()
    {
        if (method_exists($this->visualization, 'getViewDataTableId')) {
            return $this->visualization->getViewDataTableId();
        }
        return false;
    }

    /**
     * Returns a Piwik_ViewDataTable_* object.
     * By default it will return a ViewDataTable_Html
     * If there is a viewDataTable parameter in the URL, a ViewDataTable of this 'viewDataTable' type will be returned.
     * If defaultType is specified and if there is no 'viewDataTable' in the URL, a ViewDataTable of this $defaultType will be returned.
     * If force is set to true, a ViewDataTable of the $defaultType will be returned in all cases.
     *
     * @param string $defaultType Any of these: table, cloud, graphPie, graphVerticalBar, graphEvolution, sparkline, generateDataChart*
     * @param string|bool $apiAction
     * @param string|bool $controllerAction
     * @return ViewDataTable
     */
    static public function factory($defaultType = null, $apiAction = false, $controllerAction = false)
    {
        if ($apiAction !== false) {
            $defaultProperties = self::getDefaultPropertiesForReport($apiAction);
            if (isset($defaultProperties['default_view_type'])) {
                $defaultType = $defaultProperties['default_view_type'];
            }

            if ($controllerAction === false) {
                $controllerAction = $apiAction;
            }
        }

        if ($defaultType === null) {
            $defaultType = 'table';
        }

        $type = Common::getRequestVar('viewDataTable', $defaultType, 'string');
        switch ($type) {
            case 'cloud':
                $result = new ViewDataTable\Cloud();
                break;

            case 'graphPie':
                $result = new ViewDataTable\GenerateGraphHTML\ChartPie();
                break;

            case 'graphVerticalBar':
                $result = new ViewDataTable\GenerateGraphHTML\ChartVerticalBar();
                break;

            case 'graphEvolution':
                $result = new ViewDataTable\GenerateGraphHTML\ChartEvolution();
                break;

            case 'sparkline':
                $result = new ViewDataTable\Sparkline();
                break;

            case 'tableAllColumns':
                $result = new ViewDataTable\HtmlTable\AllColumns();
                break;

            case 'tableGoals':
                $result = new ViewDataTable\HtmlTable\Goals();
                break;

            case 'table':
            default:
                $result = new ViewDataTable(new Visualization\HtmlTable());
                break;
        }
        
        if ($apiAction !== false) {
            list($plugin, $controllerAction) = explode('.', $controllerAction);
            
            $subtableAction = $controllerAction;
            if (isset($defaultProperties['subtable_action'])) {
                $subtableAction = $defaultProperties['subtable_action'];
            }
            
            $result->init($plugin, $controllerAction, $apiAction, $subtableAction, $defaultProperties);
        }

        return $result;
    }

    /**
     * Returns the list of view properties that can be overridden by query parameters.
     *
     * @return array
     */
    public function getOverridableProperties()
    {
        return array(
            'show_search',
            'show_table',
            'show_table_all_columns',
            'show_all_views_icons',
            'show_active_view_icon',
            'hide_annotations_view',
            'show_barchart',
            'show_piechart',
            'show_tag_cloud',
            'show_export_as_image_icon',
            'show_export_as_rss_feed',
            'show_exclude_low_population',
            'show_offset_information',
            'show_pagination_control',
            'show_footer',
            'show_related_reports',
            'columns'
        );
    }

    /**
     * Returns the list of view properties that should be sent with the HTML response
     * as JSON. These properties can be manipulated via the ViewDataTable UI.
     *
     * @return array
     */
    public function getJavaScriptProperties()
    {
        $result = array(
            'enable_sort',
            'disable_generic_filters',
            'disable_queued_filters',
            'keep_summary_row',
            'filter_excludelowpop',
            'filter_excludelowpop_value',
            'filter_pattern',
            'filter_column',
            'filter_limit',
            'filter_sort_column',
            'filter_sort_order',
        );

        if (method_exists($this->visualization, 'getJavaScriptProperties')) {
            $result = array_merge($result, $this->visualization->getJavaScriptProperties());
        }

        return $result;
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
     * @param array $defaultProperties
     */
    public function init($currentControllerName,
                         $currentControllerAction,
                         $apiMethodToRequestDataTable,
                         $controllerActionCalledWhenRequestSubTable = null,
                         $defaultProperties = array())
    {
        $this->currentControllerName = $currentControllerName;
        $this->currentControllerAction = $currentControllerAction;
        $this->viewProperties['subtable_controller_action'] = $controllerActionCalledWhenRequestSubTable;
        $this->idSubtable = Common::getRequestVar('idSubtable', false, 'int');
        
        foreach ($defaultProperties as $name => $value) {
            $this->setViewProperty($name, $value);
        }

        $queryParams = Url::getArrayFromCurrentQueryString();
        foreach ($this->getOverridableProperties() as $name) {
            if (isset($queryParams[$name])) {
                $this->setViewProperty($name, $queryParams[$name]);
            }
        }

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
     * Forces the View to use a given template.
     * Usually the template to use is set in the specific ViewDataTable_*
     * eg. 'CoreHome/templates/cloud'
     * But some users may want to force this template to some other value
     *
     * TODO: after visualization refactor, should remove this.
     * 
     * @param string $tpl eg .'@MyPlugin/templateToUse'
     */
    public function setTemplate($tpl)
    {
        $this->viewProperties['datatable_template'] = $tpl;
    }

    /**
     * Returns the View_Interface.
     * You can then call render() on this object.
     *
     * @return View\ViewInterface
     * @throws \Exception if the view object was not created
     */
    public function getView()
    {
        if (is_null($this->view)) {
            throw new \Exception('The $this->view object has not been created.
					It should be created in the main() method of the ViewDataTable_* subclass you are using.');
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
     * like 'translations' & 'relatedReports' that store arrays.
     *
     * @param string $name
     * @param mixed $value For array properties, $value can be a comma separated string.
     */
    private function setViewProperty($name, $value)
    {
        if (isset($this->viewProperties[$name])
            && is_array($this->viewProperties[$name])
            && is_string($value)
        ) {
            $value = Piwik::getArrayFromApiParameter($value);
        }

        if ($name == 'translations') {
            $this->viewProperties[$name] = array_merge($this->viewProperties[$name], $value);
        } else if ($name == 'relatedReports') {
            $this->addRelatedReports($value);
        } else if ($name == 'filters') {
            foreach ($value as $filterInfo) {
                if (!is_array($filterInfo)) {
                    $this->queueFilter($filterInfo);
                } else {
                    @list($filter, $params, $isPriority) = $filterInfo;
                    $this->queueFilter($filter, $params, $isPriority);
                }
            }
        } else {
            $this->viewProperties[$name] = $value;
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
        $request = new API\Request($requestArray);

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

    /**
     * Hook called after the dataTable has been loaded from the API
     * Can be used to add, delete or modify the data freshly loaded
     *
     * @return bool
     */
    protected function postDataTableLoadedFromAPI()
    {
        $this->overrideViewProperties();

        if (empty($this->dataTable)) {
            return false;
        }
        
        // default columns_to_display to label, nb_uniq_visitors/nb_visits if those columns exist in the
        // dataset. otherwise, default to all columns in dataset.
        $columns = $this->dataTable->getColumns();
        if (empty($this->viewProperties['columns_to_display'])) {
            if ($this->dataTableColumnsContains($columns, array('nb_visits', 'nb_uniq_visitors'))) {
                $columnsToDisplay = array('label');
                
                // if unique visitors data is available, show it, otherwise just visits
                if ($this->dataTableColumnsContains($columns, 'nb_uniq_visitors')) {
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
            if ($this->dataTableColumnsContains($columns, 'nb_uniq_visitors')) {
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

        // First, filters that delete rows
        foreach ($this->queuedFiltersPriority as $filter) {
            $filterName = $filter[0];
            
            $filterParameters = $filter[1];
            if ($filterName instanceof \Closure) {
                $filterParameters[] = $this;
            }

            $this->dataTable->filter($filterName, $filterParameters);
        }

        if (!$this->areGenericFiltersDisabled()) {
            // Second, generic filters (Sort, Limit, Replace Column Names, etc.)
            $requestArray = $this->getRequestArray();
            $request = API\Request::getRequestArrayFromString($requestArray);

            if ($this->viewProperties['enable_sort'] === false) {
                $request['filter_sort_column'] = $request['filter_sort_order'] = '';
            }

            $genericFilter = new API\DataTableGenericFilter($request);
            $genericFilter->filter($this->dataTable);
        }

        if (!$this->areQueuedFiltersDisabled()) {
            // Finally, apply datatable filters that were queued (should be 'presentation' filters that
            // do not affect the number of rows)
            foreach ($this->queuedFilters as $filter) {
                $filterName = $filter[0];
            
                $filterParameters = $filter[1];
                if ($filterName instanceof \Closure) {
                    $filterParameters[] = $this;
                }
                
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
        
        $segment = \Piwik\API\Request::getRawSegmentFromRequest();
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

        $genericFilters = API\DataTableGenericFilter::getGenericFiltersInformation();
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
        foreach ($this->getJavaScriptProperties() as $name) {
            if (!isset($javascriptVariablesToSet[$name])
                && !empty($this->viewProperties[$name])
            ) {
                $javascriptVariablesToSet[$name] = $this->viewProperties[$name];
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
        $javascriptVariablesToSet['controllerActionCalledWhenRequestSubTable'] = $this->viewProperties['subtable_controller_action'];

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

        $rawSegment = \Piwik\API\Request::getRawSegmentFromRequest();
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

        $report = Piwik_API_API::getInstance()->getMetadata(0, $this->currentControllerName, $this->currentControllerAction);
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
    public function queueFilter($filterName, $parameters = array(), $runBeforeGenericFilters = false)
    {
        if ($runBeforeGenericFilters) {
            $this->queuedFiltersPriority[] = array($filterName, $parameters);
        } else {
            $this->queuedFilters[] = array($filterName, $parameters);
        }
    }

    private function addRelatedReport($module, $action, $title, $queryParams = array())
    {
        // don't add the related report if it references this report
        if ($this->currentControllerName == $module && $this->currentControllerAction == $action) {
            return;
        }

        $url = $this->getBaseReportUrl($module, $action, $queryParams);
        $this->viewProperties['relatedReports'][$url] = $title;
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

            if (class_exists('Piwik_PrivacyManager')
                && \Piwik_PrivacyManager::shouldReportBePurged($reportYear, $reportMonth)
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
        return API\Request::getCurrentUrlWithoutGenericFilters($params);
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
        $apiClassName = 'Piwik_' . $pluginName . '_API';
        if (!method_exists($apiClassName::getInstance(), $apiAction)) {
            throw new \Exception("Invalid action name '$apiAction' for '$pluginName' plugin.");
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
        $this->main();
        return $this->getView()->render();
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

    /**
     * Returns true if the first array contains one or more of the specified
     * column names or their associated integer INDEX_ value.
     *
     * @param array $columns Row columns.
     * @param array|string $columnsToCheckFor eg, array('nb_visits', 'nb_uniq_visitors')
     * @return bool
     */
    protected function dataTableColumnsContains($columns, $columnsToCheckFor)
    {
        if (!is_array($columnsToCheckFor)) {
            $columnsToCheckFor = array($columnsToCheckFor);
        }

        foreach ($columnsToCheckFor as $columnToCheckFor) {
            foreach ($columns as $column) {
                // check for the column name and its associated integer INDEX_ value
                if ($column == $columnToCheckFor
                    || (isset(Metrics::$mappingFromNameToId[$columnToCheckFor])
                        && $column == Metrics::$mappingFromNameToId[$columnToCheckFor])
                ) {
                    return true;
                }
            }
        }
        
        return false;
    }

    protected function overrideViewProperties()
    {
        if (!\Piwik\PluginsManager::getInstance()->isPluginActivated('Goals')) {
            $this->viewProperties['show_goals'] = false;
        }

        if (!\Piwik\PluginsManager::getInstance()->isPluginLoaded('Annotations')) {
            $this->viewProperties['hide_annotations_view'] = true;
        }

        if ($this->idSubtable) {
            $this->viewProperties['datatable_template'] = $this->viewProperties['subtable_template'];
        }
    }

    protected function buildView($visualization)
    {
        if (method_exists($visualization, 'getDefaultPropertyValues')) {
            $this->setPropertyDefaults($visualization->getDefaultPropertyValues());
        }

        $template = $this->viewProperties['datatable_template'];
        $view = new View($template);

        if (!empty($this->loadingError)) {
            $view->error = $this->loadingError;
        }

        $view->visualization = $visualization;
        
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
        $view->properties = $this->viewProperties;
        return $view;
    }

    public function getDefaultDataTableCssClass()
    {
        $parts = explode('\\', get_class($this->visualization));
        return 'dataTableViz' . end($parts);
    }

    /**
     * Sets view properties if they have not been set already.
     */
    private function setPropertyDefaults($defaultValues)
    {
        foreach ($defaultValues as $name => $value) {
            if (empty($this->viewProperties[$name])) {
                $this->viewProperties[$name] = $value;
            }
        }
    }
}