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
use Piwik\Common;

/**
 * TODO
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
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
        'filter_offset'
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
        'disable_queued_filters'
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
     * @see Piwik_API_DataTableGenericFilter
     *
     * Default value: false
     */
    public $disable_generic_filters = false;

    /**
     * Whether to run ViewDataTable's list of queued filters or not.
     *
     * NOTE: Priority queued filters are always run.
     *
     * Default value: false
     */
    public $disable_queued_filters = false;

    public $apiMethodToRequestDataTable = '';

    /**
     * If the current dataTable refers to a subDataTable (eg. keywordsBySearchEngineId for id=X) this variable is set to the Id
     *
     * @var bool|int
     */
    public $idSubtable = false;

    public function getProperties()
    {
        return get_object_vars($this);
    }

    public function addPropertiesThatShouldBeAvailableClientSide(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->clientSideParameters[] = $propertyName;
        }
    }

    public function addPropertiesThatCanBeOverwrittenByQueryParams(array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->overridableProperties[] = $propertyName;
        }
    }

    public function setDefaultSort($columnsToDisplay, $hasNbUniqVisitors)
    {
        // default sort order to visits/visitors data
        if ($hasNbUniqVisitors && in_array('nb_uniq_visitors', $columnsToDisplay)) {
            $this->filter_sort_column = 'nb_uniq_visitors';
        } else {
            $this->filter_sort_column = 'nb_visits';
        }

        $this->filter_sort_order = 'desc';
    }

    /**
     * Returns true if queued filters have been disabled, false if otherwise.
     *
     * @return bool
     */
    public function areQueuedFiltersDisabled()
    {
        return isset($this->disable_queued_filters) && $this->disable_queued_filters;
    }

    /**
     * Returns true if generic filters have been disabled, false if otherwise.
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