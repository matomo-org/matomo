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

use Piwik\API\Request as ApiRequest;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\MetricsFormatter;
use Piwik\Piwik;

class Request
{

    /**
     * @var null|\Piwik\Visualization\Request
     */
    public $requestConfig;

    public function __construct($requestConfig)
    {
        $this->requestConfig = $requestConfig;
    }

    /**
     * Function called by the ViewDataTable objects in order to fetch data from the API.
     * The function init() must have been called before, so that the object knows which API module and action to call.
     * It builds the API request string and uses Request to call the API.
     * The requested DataTable object is stored in $this->dataTable.
     */
    public function loadDataTableFromAPI()
    {
        // we build the request (URL) to call the API
        $requestArray = $this->getRequestArray();

        // we make the request to the API
        $request = new ApiRequest($requestArray);

        // and get the DataTable structure
        $dataTable = $request->process();

        return $dataTable;
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
            'method'                  => $this->requestConfig->apiMethodToRequestDataTable,
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
        );

        foreach ($toSetEventually as $varToSet) {
            $value = $this->getDefaultOrCurrent($varToSet);
            if (false !== $value) {
                $requestArray[$varToSet] = $value;
            }
        }

        $segment = ApiRequest::getRawSegmentFromRequest();
        if (!empty($segment)) {
            $requestArray['segment'] = $segment;
        }

        if (self::shouldLoadExpanded()) {
            $requestArray['expanded'] = 1;
        }

        $requestArray = array_merge($requestArray, $this->requestConfig->request_parameters_to_modify);

        if (!empty($requestArray['filter_limit'])
            && $requestArray['filter_limit'] === 0
        ) {
            unset($requestArray['filter_limit']);
        }

        return $requestArray;
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
        if (isset($this->requestConfig->$nameVar)) {
            return $this->requestConfig->$nameVar;
        }

        return false;
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

}