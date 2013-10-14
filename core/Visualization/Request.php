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

namespace Piwik\Visualization;

/**
 * Renders a sparkline image given a PHP data array.
 * Using the Sparkline PHP Graphing Library sparkline.org
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Request
{

    /**
     * The list of ViewDataTable properties that are 'Client Side Parameters'.
     *
     * @see Piwik\ViewDataTable\Visualization::getClientSideParameters
     */
    public static $clientSideParameters = array(
        'filter_excludelowpop',
        'filter_excludelowpop_value',
        'filter_pattern',
        'filter_column',
        'filter_offset'
    );

    /**
     * The list of ViewDataTable properties that can be overriden by query parameters.
     *
     * @see Piwik\ViewDataTable\Visualization::getOverridableProperties
     */
    public static $overridableProperties = array(
        'filter_sort_column',
        'filter_sort_order',
        'filter_limit',
        'filter_offset',
        'filter_pattern',
        'filter_column',
        'filter_excludelowpop',
        'filter_excludelowpop_value',
    );

    /**
     * Controls which column to sort the DataTable by before truncating and displaying.
     *
     * Default value: If the report contains nb_uniq_visitors and nb_uniq_visitors is a
     *                displayed column, then the default value is 'nb_uniq_visitors'.
     *                Otherwise, it is 'nb_visits'.
     */
    public $filter_sort_column = 'nb_visits';

    /**
     * Controls the sort order. Either 'asc' or 'desc'.
     *
     * Default value: 'desc'
     *
     * @see self::SORTED_COLUMN
     */
    public $filter_sort_order = 'desc';

    /**
     * The number of items to truncate the data set to before rendering the DataTable view.
     *
     * Default value: false
     *
     * @see self::OFFSET
     */
    public $filter_limit = false;

    /**
     * The number of items from the start of the data set that should be ignored.
     *
     * Default value: 0
     *
     * @see self::LIMIT
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
     * @see also self::EXCLUDE_LOW_POPULATION_VALUE
     *
     * Default value: false
     */
    public $filter_excludelowpop = false;

    /**
     * Stores the value considered 'low' when filtering out rows w/ low values.
     *
     * @see also self::EXCLUDE_LOW_POPULATION_COLUMN
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

    public $apiMethodToRequestDataTable = '';

    public function getProperties()
    {
        return array(
            'filter_excludelowpop_value' => $this->filter_excludelowpop_value,
            'filter_excludelowpop' => $this->filter_excludelowpop,
            'filter_column' => $this->filter_column,
            'filter_pattern' => $this->filter_pattern,
            'filter_offset' => $this->filter_offset,
            'filter_limit' => $this->filter_limit,
            'filter_sort_order' => $this->filter_sort_order,
            'filter_sort_column' => $this->filter_sort_column,
            'request_parameters_to_modify' => $this->request_parameters_to_modify,
            'apiMethodToRequestDataTable' => $this->apiMethodToRequestDataTable
        );
    }
}
