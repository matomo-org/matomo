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

namespace Piwik\ArchiveProcessor;

use Exception;
use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Manager;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\SettingsPiwik;

/**
 * Initiates the archiving process for all non-day periods via the [ArchiveProcessor.Period.compute](#)
 * event.
 * 
 * Period archiving differs from archiving day periods in that log tables are not aggregated.
 * Instead the data from periods within the non-day period are aggregated. For example, if the data
 * for a month is being archived, this ArchiveProcessor will select the aggregated data for each
 * day in the month and add them together. This is much faster than running aggregation queries over
 * the entire set of visits.
 * 
 * If data has not been archived for the subperiods, archiving will be launched for those subperiods.
 *
 * ### Examples
 * 
 * **Archiving metric data**
 * 
 *     // function in an Archiver descendent
 *     public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
 *     {
 *         $archiveProcessor->aggregateNumericMetrics('myFancyMetric', 'sum');
 *         $archiveProcessor->aggregateNumericMetrics('myOtherFancyMetric', 'max');
 *     }
 * 
 * **Archiving report data**
 * 
 *     // function in an Archiver descendent
 *     public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
 *     {
 *         $maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];j
 * 
 *         $archiveProcessor->aggregateDataTableReports(
 *             'MyPlugin_myFancyReport',
 *             $maxRowsInTable,
 *             $maxRowsInSubtable = $maxRowsInTable,
 *             $columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS,
 *         );
 *     }
 * 
 * @package Piwik
 * @subpackage ArchiveProcessor
 *
 * @api
 */
class Period extends ArchiveProcessor
{
    /**
     * Array of (column name before => column name renamed) of the columns for which sum operation is invalid.
     * These columns will be renamed as per this mapping.
     * @var array
     */
    protected static $invalidSummedColumnNameToRenamedName = array(
        Metrics::INDEX_NB_UNIQ_VISITORS => Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS
    );

    /**
     * @var Archive
     */
    protected $archiver = null;

    /**
     * Sums records for every subperiod of the current period and inserts the result as the record
     * for this period.
     * 
     * DataTables are summed recursively so subtables will be summed as well.
     *
     * @param string|array $recordNames Name(s) of the report we are aggregating, eg, `'Referrers_type'`.
     * @param int $maximumRowsInDataTableLevelZero Maximum number of rows allowed in the top level DataTable.
     * @param int $maximumRowsInSubDataTable Maximum number of rows allowed in each subtable.
     * @param string $columnToSortByBeforeTruncation The name of the column to sort by before truncating a DataTable.
     * @param array $columnAggregationOperations Operations for aggregating columns, @see Row::sumRow().
     * @param array $invalidSummedColumnNameToRenamedName For columns that must change names when summed because they
     *                                                    cannot be summed, eg,
     *                                                    `array('nb_uniq_visitors' => 'sum_daily_nb_uniq_visitors')`.
     * @return array Returns the row counts of each aggregated report before truncation, eg,
     *               ```
     *               array(
     *                   'report1' => array('level0' => $report1->getRowsCount,
     *                                      'recursive' => $report1->getRowsCountRecursive()),
     *                   'report2' => array('level0' => $report2->getRowsCount,
     *                                      'recursive' => $report2->getRowsCountRecursive()),
     *                   ...
     *               )
     *               ```
     */
    public function aggregateDataTableReports($recordNames,
                                              $maximumRowsInDataTableLevelZero = null,
                                              $maximumRowsInSubDataTable = null,
                                              $columnToSortByBeforeTruncation = null,
                                              &$columnAggregationOperations = null,
                                              $invalidSummedColumnNameToRenamedName = null)
    {
        // We clean up below all tables created during this function call (and recursive calls)
        $latestUsedTableId = Manager::getInstance()->getMostRecentTableId();
        if (!is_array($recordNames)) {
            $recordNames = array($recordNames);
        }
        $this->initArchiver();
        $nameToCount = array();
        foreach ($recordNames as $recordName) {
            $table = $this->getRecordDataTableSum($recordName, $invalidSummedColumnNameToRenamedName, $columnAggregationOperations);

            $nameToCount[$recordName]['level0'] = $table->getRowsCount();
            $nameToCount[$recordName]['recursive'] = $table->getRowsCountRecursive();

            $blob = $table->getSerialized($maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable, $columnToSortByBeforeTruncation);
            Common::destroy($table);
            $this->insertBlobRecord($recordName, $blob);
        }
        Manager::getInstance()->deleteAll($latestUsedTableId);

        return $nameToCount;
    }

    /**
     * Aggregates metrics for every subperiod of the current period and inserts the result
     * as the metric for this period.
     *
     * @param array|string $columns Array of metric names to aggregate.
     * @param bool|string $operationToApply The operation to apply to the metric. Either `'sum'`, `'max'` or `'min'`.
     * @return array|int Returns the array of aggregate values. If only one metric was aggregated,
     *                   the aggregate value will be returned as is, not in an array.
     *                   For example, if `array('nb_visits', 'nb_hits')` is supplied for `$columns`,
     *                   ```
     *                   array(
     *                       'nb_visits' => 3040,
     *                       'nb_hits' => 405
     *                   )
     *                   ```
     *                   is returned.
     */
    public function aggregateNumericMetrics($columns, $operationToApply = false)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        $this->initArchiver();
        $data = $this->archiver->getNumeric($columns);
        $operationForColumn = $this->getOperationForColumns($columns, $operationToApply);
        $results = $this->aggregateDataArray($data, $operationForColumn);
        $results = $this->defaultColumnsToZero($columns, $results);
        $this->enrichWithUniqueVisitorsMetric($results);

        foreach ($results as $name => $value) {
            $this->archiveWriter->insertRecord($name, $value);
        }

        // if asked for only one field to sum
        if (count($results) == 1) {
            return reset($results);
        }

        // returns the array of records once summed
        return $results;
    }

    protected function initArchiver()
    {
        if (empty($this->archiver)) {
            $subPeriods = $this->getPeriod()->getSubperiods();
            $this->archiver = Archive::factory($this->getSegment(), $subPeriods, array($this->getSite()->getId()));
        }
    }

    /**
     * This method selects all DataTables that have the name $name over the period.
     * All these DataTables are then added together, and the resulting DataTable is returned.
     *
     * @param string $name
     * @param array $invalidSummedColumnNameToRenamedName columns in the array (old name, new name) to be renamed as the sum operation is not valid on them (eg. nb_uniq_visitors->sum_daily_nb_uniq_visitors)
     * @param array $columnAggregationOperations Operations for aggregating columns, @see Row::sumRow()
     * @return DataTable
     */
    protected function getRecordDataTableSum($name, $invalidSummedColumnNameToRenamedName, $columnAggregationOperations = null)
    {
        $table = new DataTable();
        if (!empty($columnAggregationOperations)) {
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnAggregationOperations);
        }

        $data = $this->archiver->getDataTableExpanded($name, $idSubTable = null, $depth = null, $addMetadataSubtableId = false);
        if ($data instanceof DataTable\Map) {
            foreach ($data->getDataTables() as $date => $tableToSum) {
                $table->addDataTable($tableToSum);
            }
        } else {
            $table->addDataTable($data);
        }

        if (is_null($invalidSummedColumnNameToRenamedName)) {
            $invalidSummedColumnNameToRenamedName = self::$invalidSummedColumnNameToRenamedName;
        }
        foreach ($invalidSummedColumnNameToRenamedName as $oldName => $newName) {
            $table->renameColumn($oldName, $newName);
        }
        return $table;
    }

    protected function compute()
    {
        /**
         * Triggered when the archiving process is initiated for a non-day period.
         * 
         * Plugins that compute analytics data should subscribe to this event. The
         * actual archiving logic, however, should not be in the event handler, but
         * in a class that descends from [Archiver](#).
         * 
         * To learn more about non-day period archiving, see the [ArchiveProcessor\Period](#)
         * class.
         * 
         * **Example**
         * 
         *     public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
         *     {
         *         $archiving = new MyArchiver($archiveProcessor);
         *         if ($archiving->shouldArchive()) {
         *             $archiving->archivePeriod();
         *         }
         *     }
         * 
         * @param Piwik\ArchiveProcessor\Period $archiveProcessor
         *                                          The ArchiveProcessor that triggered the event.
         */
        Piwik::postEvent('ArchiveProcessor.Period.compute', array(&$this));
    }

    protected function aggregateCoreVisitsMetrics()
    {
        $toSum = Metrics::getVisitsMetricNames();
        $metrics = $this->aggregateNumericMetrics($toSum);
        return $metrics;
    }

    protected function getOperationForColumns($columns, $defaultOperation)
    {
        $operationForColumn = array();
        foreach ($columns as $name) {
            $operation = $defaultOperation;
            if (empty($operation)) {
                $operation = $this->guessOperationForColumn($name);
            }
            $operationForColumn[$name] = $operation;
        }
        return $operationForColumn;
    }

    protected function aggregateDataArray(array $data, array $operationForColumn)
    {
        $results = array();
        foreach ($data as $row) {
            if (!is_array($row)) {
                // this is not a data array to aggregate
                return $data;
            }
            foreach ($row as $name => $value) {
                $operation = $operationForColumn[$name];
                switch ($operation) {
                    case 'sum':
                        if (!isset($results[$name])) {
                            $results[$name] = 0;
                        }
                        $results[$name] += $value;
                        break;

                    case 'max':
                        if (!isset($results[$name])) {
                            $results[$name] = 0;
                        }
                        $results[$name] = max($results[$name], $value);
                        break;

                    case 'min':
                        if (!isset($results[$name])) {
                            $results[$name] = $value;
                        }
                        $results[$name] = min($results[$name], $value);
                        break;

                    case false:
                        // do nothing if the operation is not known (eg. nb_uniq_visitors should be not be aggregated)
                        break;

                    default:
                        throw new Exception("Operation not applicable.");
                        break;
                }
            }
        }
        return $results;
    }

    protected function defaultColumnsToZero($columns, $results)
    {
        foreach ($columns as $name) {
            if (!isset($results[$name])) {
                $results[$name] = 0;
            }
        }
        return $results;
    }

    protected function enrichWithUniqueVisitorsMetric(&$results)
    {
        if (array_key_exists('nb_uniq_visitors', $results)) {
            if (SettingsPiwik::isUniqueVisitorsEnabled($this->getPeriod()->getLabel())) {
                $results['nb_uniq_visitors'] = (float)$this->computeNbUniqVisitors();
            } else {
                unset($results['nb_uniq_visitors']);
            }
        }
    }

    protected function guessOperationForColumn($column)
    {
        if (strpos($column, 'max_') === 0) {
            return 'max';
        }
        if (strpos($column, 'min_') === 0) {
            return 'min';
        }
        if ($column === 'nb_uniq_visitors') {
            return false;
        }
        return 'sum';
    }

    /**
     * Processes number of unique visitors for the given period
     *
     * This is the only Period metric (ie. week/month/year/range) that we process from the logs directly,
     * since unique visitors cannot be summed like other metrics.
     *
     * @return int
     */
    protected function computeNbUniqVisitors()
    {
        $logAggregator = $this->getLogAggregator();
        $query = $logAggregator->queryVisitsByDimension(array(), false, array(), array(Metrics::INDEX_NB_UNIQ_VISITORS));
        $data = $query->fetch();
        return $data[Metrics::INDEX_NB_UNIQ_VISITORS];
    }
}