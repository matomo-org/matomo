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

use Exception;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveWriter;

use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable\Manager;
use Piwik\Db;
use Piwik\Period;
use Piwik\Plugin\Archiver;

/**
 * Used to insert numeric and blob archive data.
 *
 * During the Archiving process a descendant of this class is used by plugins
 * to cache aggregated analytics statistics.
 * 
 * When the [Archive](#) class is used to query for archive data and that archive
 * data is found to be absent, the archiving process is launched. An ArchiveProcessor
 * instance is created based on the period type and the archiving logic of every
 * active plugin is executed through the [ArchiveProcessor.Day.compute](#) and
 * [ArchiveProcessor.aggregateMultipleReports](#) events.
 * 
 * Plugins receive ArchiveProcessor instances in those events and use them to
 * aggregate data for the requested site, period and segment. The aggregate
 * data is then persisted, again using the ArchiveProcessor instance.
 * 
 * ### Limitations
 * 
 * - It is currently only possible to aggregate statistics for one site and period
 * at a time. The archive.php cron script does, however, issue asynchronous HTTP
 * requests that initiate archiving, so statistics can be calculated in parallel.
 * 
 * ### See also
 * 
 * - **[Archiver](#)** - to learn how plugins should implement their own analytics
 *                       aggregation logic.
 * - **[LogAggregator](#)** - to learn how plugins can perform data aggregation
 *                            across Piwik's log tables.
 * 
 * ### Examples
 * 
 * **Inserting numeric data**
 * 
 *     // function in an Archiver descendent
 *     public function aggregateDayReport(ArchiveProcessor $archiveProcessor)
 *     {
 *         $myFancyMetric = // ... calculate the metric value ...
 *         $archiveProcessor->insertNumericRecord('MyPlugin_myFancyMetric', $myFancyMetric);
 *     }
 * 
 * **Inserting serialized DataTables**
 * 
 *     // function in an Archiver descendent
 *     public function aggregateDayReport(ArchiveProcessor $archiveProcessor)
 *     {
 *         $maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];j
 * 
 *         $myDataTable = // ... use LogAggregator to generate a report about some log data ...
 *     
 *         $dataTable = // ... build by aggregating visits ...
 *         $serializedData = $dataTable->getSerialized($maxRowsInTable, $maxRowsInSubtable = $maxRowsInTable,
 *                                                     $columnToSortBy = Metrics::INDEX_NB_VISITS);
 *         
 *         $archiveProcessor->insertBlobRecords('MyPlugin_myFancyReport', $serializedData);
 *     }
 * 
 * @package Piwik
 * @subpackage ArchiveProcessor
 */
class ArchiveProcessor
{

    /**
     * @var \Piwik\DataAccess\ArchiveWriter
     */
    protected $archiveWriter;

    /**
     * @var \Piwik\DataAccess\LogAggregator
     */
    protected $logAggregator;

    /**
     * @var Archive
     */
    public $archive = null;

    /**
     * @var int
     */
    protected $numberOfVisits;
    protected $numberOfVisitsConverted;

    public function __construct(Parameters $params, ArchiveWriter $archiveWriter, $visits, $visitsConverted)
    {
        $this->params = $params;
        $this->logAggregator = new LogAggregator($params);
        $this->archiveWriter = $archiveWriter;
        $this->numberOfVisits = $visits;
        $this->numberOfVisitsConverted = $visitsConverted;
    }

    /**
     * Returns the Parameters object containing Period, Site, Segment used for this archive.
     *
     * @return Parameters
     * @api
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns a [LogAggregator](#) instance for the site, period and segment this
     * ArchiveProcessor will insert archive data for.
     *
     * @return LogAggregator
     * @api
     */
    public function getLogAggregator()
    {
        return $this->logAggregator;
    }

    /**
     * @return ArchiveWriter
     */
    public function getArchiveWriter()
    {
        return $this->archiveWriter;
    }

    /**
     * Caches multiple numeric records in the archive for this processor's site, period
     * and segment.
     * 
     * @param array $numericRecords A name-value mapping of numeric values that should be
     *                              archived, eg,
     *                              ```
     *                              array('Referrers_distinctKeywords' => 23, 'Referrers_distinctCampaigns' => 234)
     *                              ```
     * @api
     */
    public function insertNumericRecords($numericRecords)
    {
        foreach ($numericRecords as $name => $value) {
            $this->insertNumericRecord($name, $value);
        }
    }

    /**
     * Caches a single numeric record in the archive for this processor's site, period and
     * segment.
     * 
     * Numeric values are not inserted if they equal 0.
     * 
     * @param string $name The name of the numeric value, eg, `'Referrers_distinctKeywords'`.
     * @param float $value The numeric value.
     * @api
     */
    public function insertNumericRecord($name, $value)
    {
        $value = round($value, 2);
        $this->getArchiveWriter()->insertRecord($name, $value);
    }

    public function getNumberOfVisits()
    {
        return $this->numberOfVisits;
    }

    public function getNumberOfVisitsConverted()
    {
        return $this->numberOfVisitsConverted;
    }

    /**
     * Caches one or more blob records in the archive for this processor's site, period
     * and segment.
     * 
     * @param string $name The name of the record, eg, 'Referrers_type'.
     * @param string|array $values A blob string or an array of blob strings. If an array
     *                             is used, the first element in the array will be inserted
     *                             with the `$name` name. The others will be inserted with
     *                             `$name . '_' . $index` as the record name (where $index is
     *                             the index of the blob record in `$values`).
     * @api
     */
    public function insertBlobRecord($name, $values)
    {
        if (is_array($values)) {
            $clean = array();
            foreach ($values as $id => $value) {
                // for the parent Table we keep the name
                // for example for the Table of searchEngines we keep the name 'referrer_search_engine'
                // but for the child table of 'Google' which has the ID = 9 the name would be 'referrer_search_engine_9'
                $newName = $name;
                if ($id != 0) {
                    //FIXMEA: refactor
                    $newName = $name . '_' . $id;
                }

                $value = $this->compress($value);
                $clean[] = array($newName, $value);
            }
            $this->getArchiveWriter()->insertBulkRecords($clean);
            return;
        }

        $values = $this->compress($values);
        $this->getArchiveWriter()->insertRecord($name, $values);
    }

    protected function compress($data)
    {
        if (Db::get()->hasBlobDataType()) {
            return gzcompress($data);
        }
        return $data;
    }

    /**
     * Array of (column name before => column name renamed) of the columns for which sum operation is invalid.
     * These columns will be renamed as per this mapping.
     * @var array
     */
    protected static $invalidSummedColumnNameToRenamedName = array(
        Metrics::INDEX_NB_UNIQ_VISITORS => Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS
    );

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
    public function aggregateDataTableRecords($recordNames,
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
        $nameToCount = array();
        foreach ($recordNames as $recordName) {
            $table = $this->aggregateDataTableRecord($recordName, $invalidSummedColumnNameToRenamedName, $columnAggregationOperations);

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
        $data = $this->archive->getNumeric($columns);
        $operationForColumn = $this->getOperationForColumns($columns, $operationToApply);
        $results = $this->aggregateDataArray($data, $operationForColumn);
        $results = $this->defaultColumnsToZero($columns, $results);
        $this->enrichWithUniqueVisitorsMetric($results);

        foreach ($results as $name => $value) {
            $this->getArchiveWriter()->insertRecord($name, $value);
        }

        // if asked for only one field to sum
        if (count($results) == 1) {
            return reset($results);
        }

        // returns the array of records once summed
        return $results;
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
    protected function aggregateDataTableRecord($name, $invalidSummedColumnNameToRenamedName, $columnAggregationOperations = null)
    {
        $table = new DataTable();
        if (!empty($columnAggregationOperations)) {
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnAggregationOperations);
        }

        $data = $this->archive->getDataTableExpanded($name, $idSubTable = null, $depth = null, $addMetadataSubtableId = false);
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
            if (SettingsPiwik::isUniqueVisitorsEnabled($this->getParams()->getPeriod()->getLabel())) {
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

