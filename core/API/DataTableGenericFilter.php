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
use Piwik\DataTable;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

class DataTableGenericFilter
{
    /**
     * List of filter names not to run.
     *
     * @var string[]
     */
    private $disabledFilters = array();

    /**
     * @var Report
     */
    private $report;

    /**
     * @var array
     */
    private $request;

    /**
     * Constructor
     *
     * @param $request
     */
    public function __construct($request, $report)
    {
        $this->request = $request;
        $this->report  = $report;
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
                  ))
        );
    }

    private function getGenericFiltersHavingDefaultValues()
    {
        $filters = self::getGenericFiltersInformation();

        if ($this->report && $this->report->getDefaultSortColumn()) {
            foreach ($filters as $index => $filter) {
                if ($filter[0] === 'Sort') {
                    $filters[$index][1]['filter_sort_column'] = array('string', $this->report->getDefaultSortColumn());
                    $filters[$index][1]['filter_sort_order']  = array('string', $this->report->getDefaultSortOrder());
                }
            }
        }

        return $filters;
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

        $genericFilters = $this->getGenericFiltersHavingDefaultValues();

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

    public function areProcessedMetricsNeededFor($metrics)
    {
        $columnQueryParameters = array(
            'filter_column',
            'filter_column_recursive',
            'filter_excludelowpop',
            'filter_sort_column'
        );

        foreach ($columnQueryParameters as $queryParamName) {
            $queryParamValue = Common::getRequestVar($queryParamName, false, $type = null, $this->request);
            if (!empty($queryParamValue)
                && $this->containsProcessedMetric($metrics, $queryParamValue)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ProcessedMetric[] $metrics
     * @param string $name
     * @return bool
     */
    private function containsProcessedMetric($metrics, $name)
    {
        foreach ($metrics as $metric) {
            if ($metric instanceof ProcessedMetric
                && $metric->getName() == $name
            ) {
                return true;
            }
        }
        return false;
    }
}
