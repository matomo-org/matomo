<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API;

use Exception;
use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\DataTable;

class DataTableGenericFilter
{
    /**
     * List of filter names not to run.
     *
     * @var string[]
     */
    private $disabledFilters = array();

    /**
     * Constructor
     *
     * @param $request
     */
    function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Filters the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $this->applyGenericFilters($table);
    }

    /**
     * Makes sure a set of filters are not run.
     *
     * @param string[] $filterNames The name of each filter to disable.
     */
    public function disableFilters($filterNames)
    {
        $this->disabledFilters = array_unique(array_merge($this->disabledFilters, $filterNames));
    }

    /**
     * Returns an array containing the information of the generic Filter
     * to be applied automatically to the data resulting from the API calls.
     *
     * Order to apply the filters:
     * 1 - Filter that remove filtered rows
     * 2 - Filter that sort the remaining rows
     * 3 - Filter that keep only a subset of the results
     * 4 - Presentation filters
     *
     * @return array  See the code for spec
     */
    public static function getGenericFiltersInformation()
    {
        return array(
            array('Pattern',
                  array(
                      'filter_column'  => array('string', 'label'),
                      'filter_pattern' => array('string')
                  )),
            array('PatternRecursive',
                  array(
                      'filter_column_recursive'  => array('string', 'label'),
                      'filter_pattern_recursive' => array('string'),
                  )),
            array('ExcludeLowPopulation',
                  array(
                      'filter_excludelowpop'       => array('string'),
                      'filter_excludelowpop_value' => array('float', '0'),
                  )),
            array('AddColumnsProcessedMetrics',
                  array(
                      'filter_add_columns_when_show_all_columns' => array('integer')
                  )),
            array('AddColumnsProcessedMetricsGoal',
                  array(
                      'filter_update_columns_when_show_all_goals' => array('integer'),
                      'idGoal'                                    => array('string', AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW),
                  )),
            array('Sort',
                  array(
                      'filter_sort_column' => array('string'),
                      'filter_sort_order'  => array('string', 'desc'),
                  )),
            array('Truncate',
                  array(
                      'filter_truncate' => array('integer'),
                  )),
            array('Limit',
                  array(
                      'filter_offset'    => array('integer', '0'),
                      'filter_limit'     => array('integer'),
                      'keep_summary_row' => array('integer', '0'),
                  )),
        );
    }

    /**
     * Apply generic filters to the DataTable object resulting from the API Call.
     * Disable this feature by setting the parameter disable_generic_filters to 1 in the API call request.
     *
     * @param DataTable $datatable
     * @return bool
     */
    protected function applyGenericFilters($datatable)
    {
        if ($datatable instanceof DataTable\Map) {
            $tables = $datatable->getDataTables();
            foreach ($tables as $table) {
                $this->applyGenericFilters($table);
            }
            return;
        }

        $genericFilters = self::getGenericFiltersInformation();

        $filterApplied = false;
        foreach ($genericFilters as $filterMeta) {
            $filterName = $filterMeta[0];
            $filterParams = $filterMeta[1];
            $filterParameters = array();
            $exceptionRaised = false;

            if (in_array($filterName, $this->disabledFilters)) {
                continue;
            }

            foreach ($filterParams as $name => $info) {
                // parameter type to cast to
                $type = $info[0];

                // default value if specified, when the parameter doesn't have a value
                $defaultValue = null;
                if (isset($info[1])) {
                    $defaultValue = $info[1];
                }

                try {
                    $value = Common::getRequestVar($name, $defaultValue, $type, $this->request);
                    settype($value, $type);
                    $filterParameters[] = $value;
                } catch (Exception $e) {
                    $exceptionRaised = true;
                    break;
                }
            }

            if (!$exceptionRaised) {
                $datatable->filter($filterName, $filterParameters);
                $filterApplied = true;
            }
        }
        return $filterApplied;
    }
}
