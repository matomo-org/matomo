<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Map;
use Piwik\DataTable\Row;
use Piwik\Db;
use Piwik\Period;

/**
 * Used by {@link Piwik\Plugin\Archiver} instances to insert and aggregate archive data.
 *
 * ### See also
 *
 * - **{@link Piwik\Plugin\Archiver}** - to learn how plugins should implement their own analytics
 *                                       aggregation logic.
 * - **{@link Piwik\DataAccess\LogAggregator}** - to learn how plugins can perform data aggregation
 *                                                across Piwik's log tables.
 *
 * ### Examples
 *
 * **Inserting numeric data**
 *
 *     // function in an Archiver descendant
 *     public function aggregateDayReport()
 *     {
 *         $archiveProcessor = $this->getProcessor();
 *
 *         $myFancyMetric = // ... calculate the metric value ...
 *         $archiveProcessor->insertNumericRecord('MyPlugin_myFancyMetric', $myFancyMetric);
 *     }
 *
 * **Inserting serialized DataTables**
 *
 *     // function in an Archiver descendant
 *     public function aggregateDayReport()
 *     {
 *         $archiveProcessor = $this->getProcessor();
 *
 *         $maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];j
 *
 *         $dataTable = // ... build by aggregating visits ...
 *         $serializedData = $dataTable->getSerialized($maxRowsInTable, $maxRowsInSubtable = $maxRowsInTable,
 *                                                     $columnToSortBy = Metrics::INDEX_NB_VISITS);
 *
 *         $archiveProcessor->insertBlobRecords('MyPlugin_myFancyReport', $serializedData);
 *     }
 *
 * **Aggregating archive data**
 *
 *     // function in Archiver descendant
 *     public function aggregateMultipleReports()
 *     {
 *         $archiveProcessor = $this->getProcessor();
 *
 *         // aggregate a metric
 *         $archiveProcessor->aggregateNumericMetrics('MyPlugin_myFancyMetric');
 *         $archiveProcessor->aggregateNumericMetrics('MyPlugin_mySuperFancyMetric', 'max');
 *
 *         // aggregate a report
 *         $archiveProcessor->aggregateDataTableRecords('MyPlugin_myFancyReport');
 *     }
 *
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
     * @var Parameters
     */
    protected $params;

    /**
     * @var int
     */
    protected $numberOfVisits = false;

    protected $numberOfVisitsConverted = false;

    /**
     * If true, unique visitors are not calculated when we are aggregating data for multiple sites.
     * The `[General] enable_processing_unique_visitors_multiple_sites` INI config option controls
     * the value of this variable.
     *
     * @var bool
     */
    private $skipUniqueVisitorsCalculationForMultipleSites = true;

    const SKIP_UNIQUE_VISITORS_FOR_MULTIPLE_SITES = 'enable_processing_unique_visitors_multiple_sites';

    public function __construct(Parameters $params, ArchiveWriter $archiveWriter)
    {
        $this->params = $params;
        $this->logAggregator = new LogAggregator($params);
        $this->archiveWriter = $archiveWriter;

        $this->skipUniqueVisitorsCalculationForMultipleSites = Rules::shouldSkipUniqueVisitorsCalculationForMultipleSites();
    }

    protected function getArchive()
    {
        if(empty($this->archive)) {
            $subPeriods = $this->params->getSubPeriods();
            $idSites = $this->params->getIdSites();
            $this->archive = Archive::factory($this->params->getSegment(), $subPeriods, $idSites);
        }
        return $this->archive;
    }

    public function setNumberOfVisits($visits, $visitsConverted)
    {
        $this->numberOfVisits = $visits;
        $this->numberOfVisitsConverted = $visitsConverted;
    }

    /**
     * Returns the {@link Parameters} object containing the site, period and segment we're archiving
     * data for.
     *
     * @return Parameters
     * @api
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns a `{@link Piwik\DataAccess\LogAggregator}` instance for the site, period and segment this
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
     * Array of (column name before => column name renamed) of the columns for which sum operation is invalid.
     * These columns will be renamed as per this mapping.
     * @var array
     */
    protected static $columnsToRenameAfterAggregation = array(
        Metrics::INDEX_NB_UNIQ_VISITORS => Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
        Metrics::INDEX_NB_USERS         => Metrics::INDEX_SUM_DAILY_NB_USERS,
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
     * @param array $columnsAggregationOperation Operations for aggregating columns, see {@link Row::sumRow()}.
     * @param array $columnsToRenameAfterAggregation Columns mapped to new names for columns that must change names
     *                                               when summed because they cannot be summed, eg,
     *                                               `array('nb_uniq_visitors' => 'sum_daily_nb_uniq_visitors')`.
     * @return array Returns the row counts of each aggregated report before truncation, eg,
     *
     *                   array(
     *                       'report1' => array('level0' => $report1->getRowsCount,
     *                                          'recursive' => $report1->getRowsCountRecursive()),
     *                       'report2' => array('level0' => $report2->getRowsCount,
     *                                          'recursive' => $report2->getRowsCountRecursive()),
     *                       ...
     *                   )
     * @api
     */
    public function aggregateDataTableRecords($recordNames,
                                              $maximumRowsInDataTableLevelZero = null,
                                              $maximumRowsInSubDataTable = null,
                                              $columnToSortByBeforeTruncation = null,
                                              &$columnsAggregationOperation = null,
                                              $columnsToRenameAfterAggregation = null)
    {
        if (!is_array($recordNames)) {
            $recordNames = array($recordNames);
        }
        $nameToCount = array();
        foreach ($recordNames as $recordName) {
            $latestUsedTableId = Manager::getInstance()->getMostRecentTableId();

            $table = $this->aggregateDataTableRecord($recordName, $columnsAggregationOperation, $columnsToRenameAfterAggregation);

            $rowsCount = $table->getRowsCount();
            $nameToCount[$recordName]['level0'] = $rowsCount;

            $rowsCountRecursive = $rowsCount;
            if($this->isAggregateSubTables()) {
                $rowsCountRecursive = $table->getRowsCountRecursive();
            }
            $nameToCount[$recordName]['recursive'] = $rowsCountRecursive;

            $blob = $table->getSerialized($maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable, $columnToSortByBeforeTruncation);
            Common::destroy($table);
            $this->insertBlobRecord($recordName, $blob);

            unset($blob);
            DataTable\Manager::getInstance()->deleteAll($latestUsedTableId);
        }

        return $nameToCount;
    }

    /**
     * Aggregates one or more metrics for every subperiod of the current period and inserts the results
     * as metrics for the current period.
     *
     * @param array|string $columns Array of metric names to aggregate.
     * @param bool|string $operationToApply The operation to apply to the metric. Either `'sum'`, `'max'` or `'min'`.
     * @return array|int Returns the array of aggregate values. If only one metric was aggregated,
     *                   the aggregate value will be returned as is, not in an array.
     *                   For example, if `array('nb_visits', 'nb_hits')` is supplied for `$columns`,
     *
     *                       array(
     *                           'nb_visits' => 3040,
     *                           'nb_hits' => 405
     *                       )
     *
     *                   could be returned. If `array('nb_visits')` or `'nb_visits'` is used for `$columns`,
     *                   then `3040` would be returned.
     * @api
     */
    public function aggregateNumericMetrics($columns, $operationToApply = false)
    {
        $metrics = $this->getAggregatedNumericMetrics($columns, $operationToApply);

        foreach($metrics as $column => $value) {
            $this->archiveWriter->insertRecord($column, $value);
        }
        // if asked for only one field to sum
        if (count($metrics) == 1) {
            return reset($metrics);
        }

        // returns the array of records once summed
        return $metrics;
    }

    public function getNumberOfVisits()
    {
        if($this->numberOfVisits === false) {
            throw new Exception("visits should have been set here");
        }
        return $this->numberOfVisits;
    }

    public function getNumberOfVisitsConverted()
    {
        return $this->numberOfVisitsConverted;
    }

    /**
     * Caches multiple numeric records in the archive for this processor's site, period
     * and segment.
     *
     * @param array $numericRecords A name-value mapping of numeric values that should be
     *                              archived, eg,
     *
     *                                  array('Referrers_distinctKeywords' => 23, 'Referrers_distinctCampaigns' => 234)
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
     * Numeric values are not inserted if they equal `0`.
     *
     * @param string $name The name of the numeric value, eg, `'Referrers_distinctKeywords'`.
     * @param float $value The numeric value.
     * @api
     */
    public function insertNumericRecord($name, $value)
    {
        $value = round($value, 2);
        $this->archiveWriter->insertRecord($name, $value);
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
        $this->archiveWriter->insertBlobRecord($name, $values);
    }

    /**
     * This method selects all DataTables that have the name $name over the period.
     * All these DataTables are then added together, and the resulting DataTable is returned.
     *
     * @param string $name
     * @param array $columnsAggregationOperation Operations for aggregating columns, @see Row::sumRow()
     * @param array $columnsToRenameAfterAggregation columns in the array (old name, new name) to be renamed as the sum operation is not valid on them (eg. nb_uniq_visitors->sum_daily_nb_uniq_visitors)
     * @return DataTable
     */
    protected function aggregateDataTableRecord($name, $columnsAggregationOperation = null, $columnsToRenameAfterAggregation = null)
    {
        if($this->isAggregateSubTables()) {
            // By default we shall aggregate all sub-tables.
            $dataTable = $this->getArchive()->getDataTableExpanded($name, $idSubTable = null, $depth = null, $addMetadataSubtableId = false);
        } else {
            // In some cases (eg. Actions plugin when period=range),
            // for better performance we will only aggregate the parent table
            $dataTable = $this->getArchive()->getDataTable($name, $idSubTable = null);
        }

        if ($dataTable instanceof Map) {
            // see https://github.com/piwik/piwik/issues/4377
            $self = $this;
            $dataTable->filter(function ($table) use ($self, $columnsToRenameAfterAggregation) {
                $self->renameColumnsAfterAggregation($table, $columnsToRenameAfterAggregation);
            });
        }

        $dataTable = $this->getAggregatedDataTableMap($dataTable, $columnsAggregationOperation);
        $this->renameColumnsAfterAggregation($dataTable, $columnsToRenameAfterAggregation);
        return $dataTable;
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

    protected function enrichWithUniqueVisitorsMetric(Row $row)
    {
        // skip unique visitors metrics calculation if calculating for multiple sites is disabled
        if (!$this->getParams()->isSingleSite()
            && $this->skipUniqueVisitorsCalculationForMultipleSites
        ) {
            return;
        }
        if ($row->getColumn('nb_uniq_visitors') !== false
            || $row->getColumn('nb_users') !== false
        ) {
            if (SettingsPiwik::isUniqueVisitorsEnabled($this->getParams()->getPeriod()->getLabel())) {
                $metrics = array(Metrics::INDEX_NB_UNIQ_VISITORS, Metrics::INDEX_NB_USERS);
                $uniques = $this->computeNbUniques( $metrics );
                $row->setColumn('nb_uniq_visitors', $uniques[Metrics::INDEX_NB_UNIQ_VISITORS]);
                $row->setColumn('nb_users', $uniques[Metrics::INDEX_NB_USERS]);
            } else {
                $row->deleteColumn('nb_uniq_visitors');
                $row->deleteColumn('nb_users');
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
        return 'sum';
    }

    /**
     * Processes number of unique visitors for the given period
     *
     * This is the only Period metric (ie. week/month/year/range) that we process from the logs directly,
     * since unique visitors cannot be summed like other metrics.
     *
     * @param array Metrics Ids for which to aggregates count of values
     * @return int
     */
    protected function computeNbUniques($metrics)
    {
        $logAggregator = $this->getLogAggregator();
        $query = $logAggregator->queryVisitsByDimension(array(), false, array(), $metrics);
        $data = $query->fetch();
        return $data;
    }

    /**
     * If the DataTable is a Map, sums all DataTable in the map and return the DataTable.
     *
     *
     * @param $data DataTable|DataTable\Map
     * @param $columnsToRenameAfterAggregation array
     * @return DataTable
     */
    protected function getAggregatedDataTableMap($data, $columnsAggregationOperation)
    {
        $table = new DataTable();
        if (!empty($columnsAggregationOperation)) {
            $table->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
        }
        if ($data instanceof DataTable\Map) {
            // as $date => $tableToSum
            $this->aggregatedDataTableMapsAsOne($data, $table);
        } else {
            $table->addDataTable($data, $this->isAggregateSubTables());
        }
        return $table;
    }

    /**
     * Aggregates the DataTable\Map into the destination $aggregated
     * @param $map
     * @param $aggregated
     */
    protected function aggregatedDataTableMapsAsOne(Map $map, DataTable $aggregated)
    {
        foreach ($map->getDataTables() as $tableToAggregate) {
            if($tableToAggregate instanceof Map) {
                $this->aggregatedDataTableMapsAsOne($tableToAggregate, $aggregated);
            } else {
                $aggregated->addDataTable($tableToAggregate, $this->isAggregateSubTables());
            }
        }
    }

    /**
     * Note: public only for use in closure in PHP 5.3.
     */
    public function renameColumnsAfterAggregation(DataTable $table, $columnsToRenameAfterAggregation = null)
    {
        // Rename columns after aggregation
        if (is_null($columnsToRenameAfterAggregation)) {
            $columnsToRenameAfterAggregation = self::$columnsToRenameAfterAggregation;
        }
        foreach ($columnsToRenameAfterAggregation as $oldName => $newName) {
            $table->renameColumn($oldName, $newName, $this->isAggregateSubTables());
        }
    }

    protected function getAggregatedNumericMetrics($columns, $operationToApply)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        $operationForColumn = $this->getOperationForColumns($columns, $operationToApply);

        $dataTable = $this->getArchive()->getDataTableFromNumeric($columns);

        $results = $this->getAggregatedDataTableMap($dataTable, $operationForColumn);
        if ($results->getRowsCount() > 1) {
            throw new Exception("A DataTable is an unexpected state:" . var_export($results, true));
        }

        $rowMetrics = $results->getFirstRow();
        if($rowMetrics === false) {
            $rowMetrics = new Row;
        }
        $this->enrichWithUniqueVisitorsMetric($rowMetrics);
        $this->renameColumnsAfterAggregation($results);

        $metrics = $rowMetrics->getColumns();

        foreach ($columns as $name) {
            if (!isset($metrics[$name])) {
                $metrics[$name] = 0;
            }
        }
        return $metrics;
    }

    /**
     * @return bool
     */
    protected function isAggregateSubTables()
    {
        return !$this->getParams()->isSkipAggregationOfSubTables();
    }
}
