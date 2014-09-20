<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\ViewDataTable;
use Piwik\Common;

/**
 * Contains base request properties for {@link Piwik\Plugin\ViewDataTable} instances. Manipulating
 * these properties will change the way a {@link Piwik\Plugin\ViewDataTable} loads report data.
 *
 * <a name="client-side-parameters-desc"></a>
 * **Client Side Parameters**
 *
 * Client side parameters are request properties that should be passed on to the browser so
 * client side JavaScript can use them. These properties will also be passed to the server with
 * every AJAX request made.
 *
 * Only affects ViewDataTables that output HTML.
 *
 * <a name="overridable-properties-desc"></a>
 * **Overridable Properties**
 *
 * Overridable properties are properties that can be set via the query string.
 * If a request has a query parameter that matches an overridable property, the property
 * will be set to the query parameter value.
 *
 * **Reusing base properties**
 *
 * Many of the properties in this class only have meaning for the {@link Piwik\Plugin\Visualization}
 * class, but can be set for other visualizations that extend {@link Piwik\Plugin\ViewDataTable}
 * directly.
 *
 * Visualizations that extend {@link Piwik\Plugin\ViewDataTable} directly and want to re-use these
 * properties must make sure the properties are used in the exact same way they are used in
 * {@link Piwik\Plugin\Visualization}.
 *
 * **Defining new request properties**
 *
 * If you are creating your own visualization and want to add new request properties for
 * it, extend this class and add your properties as fields.
 *
 * Properties are marked as client side parameters by calling the
 * {@link addPropertiesThatShouldBeAvailableClientSide()} method.
 *
 * Properties are marked as overridable by calling the
 * {@link addPropertiesThatCanBeOverwrittenByQueryParams()} method.
 *
 * ### Example
 *
 * **Defining new request properties**
 *
 *     class MyCustomVizRequestConfig extends RequestConfig
 *     {
 *         /**
 *          * My custom property. It is overridable.
 *          *\/
 *         public $my_custom_property = false;
 *
 *         /**
 *          * Another custom property. It is available client side.
 *          *\/
 *         public $another_custom_property = true;
 *
 *         public function __construct()
 *         {
 *             parent::__construct();
 *
 *             $this->addPropertiesThatShouldBeAvailableClientSide(array('another_custom_property'));
 *             $this->addPropertiesThatCanBeOverwrittenByQueryParams(array('my_custom_property'));
 *         }
 *     }
 *
 * @api
 */
class RequestConfig
{
    /**
     * The list of request parameters that are 'Client Side Parameters'.
     */
    public $clientSideParameters = array(
        'filter_excludelowpop',
        'filter_excludelowpop_value',
        'filter_pattern',
        'filter_column',
        'filter_offset',
        'flat',
        'expanded',
        'pivotBy',
        'pivotByColumn',
        'pivotByColumnLimit'
    );

    /**
     * The list of ViewDataTable properties that can be overriden by query parameters.
     */
    public $overridableProperties = array(
        'filter_sort_column',
        'filter_sort_order',
        'filter_limit',
        'filter_offset',
        'filter_pattern',
        'filter_column',
        'filter_excludelowpop',
        'filter_excludelowpop_value',
        'disable_generic_filters',
        'disable_queued_filters',
        'flat',
        'expanded',
        'pivotBy',
        'pivotByColumn',
        'pivotByColumnLimit'
    );

    /**
     * Controls which column to sort the DataTable by before truncating and displaying.
     *
     * Default value: If the report contains nb_uniq_visitors and nb_uniq_visitors is a
     *                displayed column, then the default value is 'nb_uniq_visitors'.
     *                Otherwise, it is 'nb_visits'.
     */
    public $filter_sort_column = false;

    /**
     * Controls the sort order. Either 'asc' or 'desc'.
     *
     * Default value: 'desc'
     */
    public $filter_sort_order = 'desc';

    /**
     * The number of items to truncate the data set to before rendering the DataTable view.
     *
     * Default value: false
     */
    public $filter_limit = false;

    /**
     * If set to true, the returned data will contain the flattened view of the table data set.
     * The children of all first level rows will be aggregated under one row.
     *
     * Default value: false
     */
    public $flat = false;

    /**
     * If set to true, the returned data will contain the first level results, as well as all sub-tables.
     *
     * Default value: false
     */
    public $expanded = false;

    /**
     * The number of items from the start of the data set that should be ignored.
     *
     * Default value: 0
     */
    public $filter_offset = 0;

    /**
     * A regex pattern to use to filter the DataTable before it is shown.
     *
     * @see also self::FILTER_PATTERN_COLUMN
     *
     * Default value: false
     */
    public $filter_pattern = false;

    /**
     * The column to apply a filter pattern to.
     *
     * @see also self::FILTER_PATTERN
     *
     * Default value: false
     */
    public $filter_column = false;

    /**
     * Stores the column name to filter when filtering out rows with low values.
     *
     * Default value: false
     */
    public $filter_excludelowpop = false;

    /**
     * Stores the value considered 'low' when filtering out rows w/ low values.
     *
     * Default value: false
     * @var \Closure|string
     */
    public $filter_excludelowpop_value = false;

    /**
     * An array property that contains query parameter name/value overrides for API requests made
     * by ViewDataTable.
     *
     * E.g. array('idSite' => ..., 'period' => 'month')
     *
     * Default value: array()
     */
    public $request_parameters_to_modify = array();

    /**
     * Whether to run generic filters on the DataTable before rendering or not.
     *
     * @see Piwik\API\DataTableGenericFilter
     *
     * Default value: false
     */
    public $disable_generic_filters = false;

    /**
     * Whether to run ViewDataTable's list of queued filters or not.
     *
     * _NOTE: Priority queued filters are always run._
     *
     * Default value: false
     */
    public $disable_queued_filters = false;

    /**
     * returns 'Plugin.apiMethodName' used for this ViewDataTable,
     * eg. 'Actions.getPageUrls'
     *
     * @var string
     */
    public $apiMethodToRequestDataTable = '';

    /**
     * If the current dataTable refers to a subDataTable (eg. keywordsBySearchEngineId for id=X) this variable is set to the Id
     *
     * @var bool|int
     */
    public $idSubtable = false;

    /**
     * Dimension ID to pivot by. See {@link Piwik\DataTable\Filter\PivotByDimension} for more info.
     *
     * @var string
     */
    public $pivotBy = false;

    /**
     * The column to display in a pivot table, eg, `'nb_visits'`. See {@link Piwik\DataTable\Filter\PivotByDimension}
     * for more info.
     *
     * @var string
     */
    public $pivotByColumn = false;

    /**
     * The maximum number of columns to display in a pivot table. See {@link Piwik\DataTable\Filter\PivotByDimension}
     * for more info.
     *
     * @var int
     */
    public $pivotByColumnLimit = false;

    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * Marks request properties as client side properties. [Read this](#client-side-properties-desc)
     * to learn more.
     *
     * @param array $propertyNames List of property names, eg, `array('disable_queued_filters', 'filter_column')`.
     */
    public function addPropertiesThatShouldBeAvailableClientSide(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->clientSideParameters[] = $propertyName;
        }
    }

    /**
     * Marks display properties as overridable. [Read this](#overridable-properties-desc) to
     * learn more.
     *
     * @param array $propertyNames List of property names, eg, `array('disable_queued_filters', 'filter_column')`.
     */
    public function addPropertiesThatCanBeOverwrittenByQueryParams(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->overridableProperties[] = $propertyName;
        }
    }

    public function setDefaultSort($columnsToDisplay, $hasNbUniqVisitors, $actualColumns)
    {
        // default sort order to visits/visitors data
        if ($hasNbUniqVisitors && in_array('nb_uniq_visitors', $columnsToDisplay)) {
            $this->filter_sort_column = 'nb_uniq_visitors';
        } else {
            $this->filter_sort_column = 'nb_visits';
        }

        // if the default sort column does not exist, sort by the first non-label column
        if (!in_array($this->filter_sort_column, $actualColumns)) {
            foreach ($actualColumns as $column) {
                if ($column != 'label') {
                    $this->filter_sort_column = $column;
                    break;
                }
            }
        }

        $this->filter_sort_order = 'desc';
    }

    /**
     * Returns `true` if queued filters have been disabled, `false` if otherwise.
     *
     * @return bool
     */
    public function areQueuedFiltersDisabled()
    {
        return isset($this->disable_queued_filters) && $this->disable_queued_filters;
    }

    /**
     * Returns `true` if generic filters have been disabled, `false` if otherwise.
     *
     * @return bool
     */
    public function areGenericFiltersDisabled()
    {
        // if disable_generic_filters query param is set to '1', generic filters are disabled
        if (Common::getRequestVar('disable_generic_filters', '0', 'string') == 1) {
            return true;
        }

        if (isset($this->disable_generic_filters) && true === $this->disable_generic_filters) {
            return true;
        }

        return false;
    }

    public function getApiModuleToRequest()
    {
        list($module, $method) = explode('.', $this->apiMethodToRequestDataTable);

        return $module;
    }

    public function getApiMethodToRequest()
    {
        list($module, $method) = explode('.', $this->apiMethodToRequestDataTable);

        return $method;
    }
}
