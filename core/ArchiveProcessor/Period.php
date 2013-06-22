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

/**
 * This class provides generic methods to archive data for a period (week / month / year).
 * The archiving for a period is done by aggregating "sub periods" contained within this period.
 * For example to process a week's data, we sum each day's data.
 *
 * Public methods can be called by the plugins that hook on the event 'ArchiveProcessing_Period.compute'
 *
 * @package Piwik
 * @subpackage Piwik_ArchiveProcessor
 */
class Piwik_ArchiveProcessor_Period extends Piwik_ArchiveProcessor
{
    /**
     * Array of (column name before => column name renamed) of the columns for which sum operation is invalid.
     * These columns will be renamed as per this mapping.
     * @var array
     */
    protected static $invalidSummedColumnNameToRenamedName = array(
        Piwik_Metrics::INDEX_NB_UNIQ_VISITORS => Piwik_Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS
    );

    /**
     * @var Piwik_Archive
     */
    protected $archiver = null;

    /**
     * This method will compute the sum of DataTables over the period for the given fields $recordNames.
     * The resulting DataTable will be then added to queue of data to be recorded in the database.
     * It will usually be called in a plugin that listens to the hook 'ArchiveProcessing_Period.compute'
     *
     * For example if $recordNames = 'UserCountry_country' the method will select all UserCountry_country DataTable for the period
     * (eg. the 31 dataTable of the last month), sum them, then record it in the DB
     *
     *
     * This method works on recursive dataTable. For example for the 'Actions' it will select all subtables of all dataTable of all the sub periods
     *  and get the sum.
     *
     * It returns an array that gives information about the "final" DataTable. The array gives for every field name, the number of rows in the
     *  final DataTable (ie. the number of distinct LABEL over the period) (eg. the number of distinct keywords over the last month)
     *
     * @param string|array $recordNames                           Field name(s) of DataTable to select so we can get the sum
     * @param int $maximumRowsInDataTableLevelZero       Max row count of parent datatable to archive
     * @param int $maximumRowsInSubDataTable             Max row count of children datatable(s) to archive
     * @param string $columnToSortByBeforeTruncation     Column name to sort by, before truncating rows (ie. if there are more rows than the specified max row count)
     * @param array $columnAggregationOperations         Operations for aggregating columns, @see Piwik_DataTable_Row::sumRow()
     * @param array $invalidSummedColumnNameToRenamedName  (current_column_name => new_column_name) for columns that must change names when summed
     *                                                             (eg. unique visitors go from nb_uniq_visitors to sum_daily_nb_uniq_visitors)
     *
     * @return array  array (
     *                    nameTable1 => number of rows,
     *                nameTable2 => number of rows,
     *                )
     */
    public function aggregateDataTableReports($recordNames,
                                              $maximumRowsInDataTableLevelZero = null,
                                              $maximumRowsInSubDataTable = null,
                                              $columnToSortByBeforeTruncation = null,
                                              &$columnAggregationOperations = null,
                                              $invalidSummedColumnNameToRenamedName = null)
    {
        // We clean up below all tables created during this function call (and recursive calls)
        $latestUsedTableId = Piwik_DataTable_Manager::getInstance()->getMostRecentTableId();
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
            destroy($table);
            $this->insertBlobRecord($recordName, $blob);
        }
        Piwik_DataTable_Manager::getInstance()->deleteAll($latestUsedTableId);

        return $nameToCount;
    }

    /**
     * Given a list of records names, the method will fetch all their values over the period, and aggregate them.
     *
     * For example $columns = array('nb_visits', 'sum_time_visit')
     *  it will sum all values of nb_visits for the period (eg. get number of visits for the month by summing the visits of every day)
     *
     * The aggregate metrics are then stored in the Archive and the values are returned.
     *
     * @param array|string $columns            Array of strings or string containing the field names to select
     * @param bool|string $operationToApply Available operations = sum, max, min. If false, the operation will be guessed from the column name (guesses from column names min_ and max_)
     * @return array
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
            $this->archiver = Piwik_Archive::factory($this->getSegment(), $subPeriods, array($this->getSite()->getId()));
        }
    }

    /**
     * This method selects all DataTables that have the name $name over the period.
     * All these DataTables are then added together, and the resulting DataTable is returned.
     *
     * @param string $name
     * @param array $invalidSummedColumnNameToRenamedName  columns in the array (old name, new name) to be renamed as the sum operation is not valid on them (eg. nb_uniq_visitors->sum_daily_nb_uniq_visitors)
     * @param array $columnAggregationOperations           Operations for aggregating columns, @see Piwik_DataTable_Row::sumRow()
     * @return Piwik_DataTable
     */
    protected function getRecordDataTableSum($name, $invalidSummedColumnNameToRenamedName, $columnAggregationOperations = null)
    {
        $table = new Piwik_DataTable();
        if (!empty($columnAggregationOperations)) {
            $table->setColumnAggregationOperations($columnAggregationOperations);
        }

        $data = $this->archiver->getDataTableExpanded($name, $idSubTable = null, $addMetadataSubtableId = false);
        if ($data instanceof Piwik_DataTable_Array) {
            foreach ($data->getArray() as $date => $tableToSum) {
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
        Piwik_PostEvent('ArchiveProcessing_Period.compute', $this);
    }

    protected function aggregateCoreVisitsMetrics()
    {
        $toSum = Piwik_Metrics::getVisitsMetricNames();
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
            if (Piwik::isUniqueVisitorsEnabled($this->getPeriod()->getLabel())) {
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
        $query = $logAggregator->queryVisitsByDimension(array(), false, array(), array(Piwik_Metrics::INDEX_NB_UNIQ_VISITORS));
        $data = $query->fetch();
        return $data[Piwik_Metrics::INDEX_NB_UNIQ_VISITORS];
    }
}
