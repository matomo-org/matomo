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
 * Handles the archiving process for a period
 *
 * This class provides generic methods to archive data for a period (week / month / year).
 *
 * These methods are called by the plugins that do the logic of archiving their own data. \
 * They hook on the event 'ArchiveProcessing_Period.compute'
 *
 * @package Piwik
 * @subpackage Piwik_ArchiveProcessing
 */
class Piwik_ArchiveProcessing_Period extends Piwik_ArchiveProcessing
{
    /**
     * Array of (column name before => column name renamed) of the columns for which sum operation is invalid.
     * The summed value is not accurate and these columns will be renamed accordingly.
     * @var array
     */
    static public $invalidSummedColumnNameToRenamedName = array(
        Piwik_Archive::INDEX_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_SUM_DAILY_NB_UNIQ_VISITORS
    );

    /**
     * @var Piwik_Archive_Single[]
     */
    public $archives = array();

    /**
     * Sums all values for the given field names $aNames over the period
     * See @archiveNumericValuesGeneral for more information
     *
     * @param string|array $aNames
     * @return array
     */
    public function archiveNumericValuesSum($aNames)
    {
        return $this->archiveNumericValuesGeneral($aNames, 'sum');
    }

    /**
     * Get the maximum value for all values for the given field names $aNames over the period
     * See @archiveNumericValuesGeneral for more information
     *
     * @param string|array $aNames
     * @return array
     */
    public function archiveNumericValuesMax($aNames)
    {
        return $this->archiveNumericValuesGeneral($aNames, 'max');
    }

    /**
     * Given a list of fields names, the method will fetch all their values over the period, and archive them using the given operation.
     *
     * For example if $operationToApply = 'sum' and $aNames = array('nb_visits', 'sum_time_visit')
     *  it will sum all values of nb_visits for the period (for example give the number of visits for the month by summing the visits of every day)
     *
     * @param array|string $aNames            Array of strings or string containg the field names to select
     * @param string $operationToApply  Available operations = sum, max, min
     * @throws Exception
     * @return array
     */
    private function archiveNumericValuesGeneral($aNames, $operationToApply)
    {
        $this->loadSubPeriods();
        if (!is_array($aNames)) {
            $aNames = array($aNames);
        }

        // fetch the numeric values and apply the operation on them
        $results = array();
        foreach ($this->archives as $id => $archive) {
            foreach ($aNames as $name) {
                if (!isset($results[$name])) {
                    $results[$name] = 0;
                }
                if ($name == 'nb_uniq_visitors') continue;

                $valueToSum = $archive->getNumeric($name);

                if ($valueToSum !== false) {
                    switch ($operationToApply) {
                        case 'sum':
                            $results[$name] += $valueToSum;
                            break;
                        case 'max':
                            $results[$name] = max($results[$name], $valueToSum);
                            break;
                        case 'min':
                            $results[$name] = min($results[$name], $valueToSum);
                            break;
                        default:
                            throw new Exception("Operation not applicable.");
                            break;
                    }
                }
            }
        }

        if (!Piwik::isUniqueVisitorsEnabled($this->period->getLabel())) {
            unset($results['nb_uniq_visitors']);
        }

        foreach ($results as $name => $value) {
            if ($name == 'nb_uniq_visitors') {
                $value = (float)$this->computeNbUniqVisitors();
            }
            $this->insertRecord($name, $value);
        }

        // if asked for only one field to sum
        if (count($results) == 1) {
            return $results[$name];
        }

        // returns the array of records once summed
        return $results;
    }

    /**
     * This method will compute the sum of DataTables over the period for the given fields $aRecordName.
     * The resulting DataTable will be then added to queue of data to be recorded in the database.
     * It will usually be called in a plugin that listens to the hook 'ArchiveProcessing_Period.compute'
     *
     * For example if $aRecordName = 'UserCountry_country' the method will select all UserCountry_country DataTable for the period
     * (eg. the 31 dataTable of the last month), sum them, then record it in the DB
     *
     *
     * This method works on recursive dataTable. For example for the 'Actions' it will select all subtables of all dataTable of all the sub periods
     *  and get the sum.
     *
     * It returns an array that gives information about the "final" DataTable. The array gives for every field name, the number of rows in the
     *  final DataTable (ie. the number of distinct LABEL over the period) (eg. the number of distinct keywords over the last month)
     *
     * @param string|array $aRecordName                           Field name(s) of DataTable to select so we can get the sum
     * @param array $invalidSummedColumnNameToRenamedName  (current_column_name => new_column_name) for columns that must change names when summed
     *                                                             (eg. unique visitors go from nb_uniq_visitors to sum_daily_nb_uniq_visitors)
     * @param int $maximumRowsInDataTableLevelZero       Max row count of parent datatable to archive
     * @param int $maximumRowsInSubDataTable             Max row count of children datatable(s) to archive
     * @param string $columnToSortByBeforeTruncation     Column name to sort by, before truncating rows (ie. if there are more rows than the specified max row count)
     * @param array $columnAggregationOperations         Operations for aggregating columns, @see Piwik_DataTable_Row::sumRow()
     *
     * @return array  array (
     *                    nameTable1 => number of rows,
     *                nameTable2 => number of rows,
     *                )
     */
    public function archiveDataTable($aRecordName,
                                     $invalidSummedColumnNameToRenamedName = null,
                                     $maximumRowsInDataTableLevelZero = null,
                                     $maximumRowsInSubDataTable = null,
                                     $columnToSortByBeforeTruncation = null,
                                     &$columnAggregationOperations = null)
    {
        // We clean up below all tables created during this function call (and recursive calls)
        $latestUsedTableId = Piwik_DataTable_Manager::getInstance()->getMostRecentTableId();

        $this->loadSubPeriods();
        if (!is_array($aRecordName)) {
            $aRecordName = array($aRecordName);
        }

        $nameToCount = array();
        foreach ($aRecordName as $recordName) {
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
     * This method selects all DataTables that have the name $name over the period.
     * It calls the appropriate methods that sum all these tables together.
     * The resulting DataTable is returned.
     *
     * @param string $name
     * @param array $invalidSummedColumnNameToRenamedName  columns in the array (old name, new name) to be renamed as the sum operation is not valid on them (eg. nb_uniq_visitors->sum_daily_nb_uniq_visitors)
     * @param array $columnAggregationOperations           Operations for aggregating columns, @see Piwik_DataTable_Row::sumRow()
     * @return Piwik_DataTable
     */
    protected function getRecordDataTableSum($name, $invalidSummedColumnNameToRenamedName, &$columnAggregationOperations = null)
    {
        $table = new Piwik_DataTable();
        
        if (is_array($columnAggregationOperations)) {
            $table->setColumnAggregationOperations($columnAggregationOperations);
        }
        
        foreach ($this->archives as $archive) {
            $archive->preFetchBlob($name);
            $datatableToSum = $archive->getDataTable($name);
            $archive->loadSubDataTables($name, $datatableToSum);
            $table->addDataTable($datatableToSum);
            $archive->freeBlob($name);
        }

        if (is_null($invalidSummedColumnNameToRenamedName)) {
            $invalidSummedColumnNameToRenamedName = self::$invalidSummedColumnNameToRenamedName;
        }
        foreach ($invalidSummedColumnNameToRenamedName as $oldName => $newName) {
            $table->renameColumn($oldName, $newName);
        }
        return $table;
    }

    protected function initCompute()
    {
        parent::initCompute();
    }

    /**
     * Returns the ID of the archived subperiods.
     *
     * @return array  Array of the idArchive of the subperiods
     */
    protected function loadSubperiodsArchive()
    {
        $periods = array();

        // we first compute every subperiod of the archive
        foreach ($this->period->getSubperiods() as $period) {
            $archivePeriod = new Piwik_Archive_Single();
            $archivePeriod->setSite($this->site);
            $archivePeriod->setPeriod($period);
            $archivePeriod->setSegment($this->getSegment());
            $archivePeriod->setRequestedReport($this->getRequestedReport());

            $periods[] = $archivePeriod;
        }
        return $periods;
    }

    /**
     * Main method to process logs for a period.
     * The only logic done here is computing the number of visits, actions, etc.
     *
     * All the other reports are computed inside plugins listening to the event 'ArchiveProcessing_Period.compute'.
     * See some of the plugins for an example.
     */
    protected function compute()
    {
        if (!$this->isThereSomeVisits()) {
            return;
        }
        Piwik_PostEvent('ArchiveProcessing_Period.compute', $this);
    }

    protected function loadSubPeriods()
    {
        if (empty($this->archives)) {
            $this->archives = $this->loadSubperiodsArchive();
        }
    }

    /**
     *
     * @see Piwik_ArchiveProcessing_Day::isThereSomeVisits()
     * @return bool|null
     */
    public function isThereSomeVisits()
    {
        if (!is_null($this->isThereSomeVisits)) {
            return $this->isThereSomeVisits;
        }

        $this->loadSubPeriods();
        if (self::getPluginBeingProcessed($this->getRequestedReport()) == 'VisitsSummary'
            || $this->shouldProcessReportsAllPlugins($this->getSegment(), $this->period)
        ) {
            $toSum = self::getCoreMetrics();
            $record = $this->archiveNumericValuesSum($toSum);
            $this->archiveNumericValuesMax('max_actions');

            $nbVisitsConverted = $record['nb_visits_converted'];
            $nbVisits = $record['nb_visits'];
        } else {
            $archive = new Piwik_Archive_Single();
            $archive->setSite($this->site);
            $archive->setPeriod($this->period);
            $archive->setSegment($this->getSegment());

            $nbVisits = $archive->getNumeric('nb_visits');
            $nbVisitsConverted = 0;
            if ($nbVisits > 0) {
                $nbVisitsConverted = $archive->getNumeric('nb_visits_converted');
            }
        }

        $this->setNumberOfVisits($nbVisits);
        $this->setNumberOfVisitsConverted($nbVisitsConverted);
        $this->isThereSomeVisits = ($nbVisits > 0);
        return $this->isThereSomeVisits;
    }

    /**
     * Processes number of unique visitors for the given period
     *
     * This is the only metric we process from the logs directly,
     * since unique visitors cannot be summed like other metrics.
     *
     * @return int
     */
    protected function computeNbUniqVisitors()
    {
        $select = "count(distinct log_visit.idvisitor) as nb_uniq_visitors";
        $from = "log_visit";
        $where = "log_visit.visit_last_action_time >= ?
	    		AND log_visit.visit_last_action_time <= ? 
	    		AND log_visit.idsite = ?";

        $bind = array($this->getStartDatetimeUTC(), $this->getEndDatetimeUTC(), $this->idsite);

        $query = $this->getSegment()->getSelectQuery($select, $from, $where, $bind);

        return Zend_Registry::get('db')->fetchOne($query['sql'], $query['bind']);
    }

    /**
     * Called at the end of the archiving process.
     * Does some cleaning job in the database.
     */
    protected function postCompute()
    {
        parent::postCompute();

        $numericTable = $this->tableArchiveNumeric->getTableName();
        self::doPurgeOutdatedArchives($numericTable, $this->isArchiveTemporary());

        if (!isset($this->archives)) {
            return;
        }
        foreach ($this->archives as $archive) {
            destroy($archive);
        }
        $this->archives = array();
    }

    const FLAG_TABLE_PURGED = 'lastPurge_';

    // Used to disable Purge Outdated reports during test data setup
    static public $enablePurgeOutdated = true;

    /**
     * Given a monthly archive table, will delete all reports that are now outdated,
     * or reports that ended with an error
     */
    static public function doPurgeOutdatedArchives($numericTable)
    {
        if (!self::$enablePurgeOutdated) {
            return;
        }
        $blobTable = str_replace("numeric", "blob", $numericTable);
        $key = self::FLAG_TABLE_PURGED . $blobTable;
        $timestamp = Piwik_GetOption($key);

        // we shall purge temporary archives after their timeout is finished, plus an extra 6 hours
        // in case archiving is disabled or run once a day, we give it this extra time to run
        // and re-process more recent records...
        // TODO: Instead of hardcoding 6 we should put the actual number of hours between 2 archiving runs
        $temporaryArchivingTimeout = self::getTodayArchiveTimeToLive();
        $purgeEveryNSeconds = max($temporaryArchivingTimeout, 6 * 3600);

        // we only delete archives if we are able to process them, otherwise, the browser might process reports
        // when &segment= is specified (or custom date range) and would below, delete temporary archives that the
        // browser is not able to process until next cron run (which could be more than 1 hour away)
        if (self::isRequestAuthorizedToArchive()
            && (!$timestamp
                || $timestamp < time() - $purgeEveryNSeconds)
        ) {
            Piwik_SetOption($key, time());

            // If Browser Archiving is enabled, it is likely there are many more temporary archives
            // We delete more often which is safe, since reports are re-processed on demand
            if (self::isBrowserTriggerArchivingEnabled()) {
                $purgeArchivesOlderThan = Piwik_Date::factory(time() - 2 * $temporaryArchivingTimeout)->getDateTime();
            } // If archive.php via Cron is building the reports, we should keep all temporary reports from today
            else {
                $purgeArchivesOlderThan = Piwik_Date::factory('today')->getDateTime();
            }
            $result = Piwik_FetchAll("
				SELECT idarchive
				FROM $numericTable
				WHERE name LIKE 'done%'
					AND ((  value = " . Piwik_ArchiveProcessing::DONE_OK_TEMPORARY . "
						    AND ts_archived < ?)
						 OR value = " . Piwik_ArchiveProcessing::DONE_ERROR . ")",
                array($purgeArchivesOlderThan)
            );

            $idArchivesToDelete = array();
            if (!empty($result)) {
                foreach ($result as $row) {
                    $idArchivesToDelete[] = $row['idarchive'];
                }
                $query = "DELETE
    						FROM %s
    						WHERE idarchive IN (" . implode(',', $idArchivesToDelete) . ")
    						";

                Piwik_Query(sprintf($query, $numericTable));

                // Individual blob tables could be missing
                try {
                    Piwik_Query(sprintf($query, $blobTable));
                } catch (Exception $e) {
                }
            }
            Piwik::log("Purging temporary archives: done [ purged archives older than $purgeArchivesOlderThan from $blobTable and $numericTable ] [Deleted IDs: " . implode(',', $idArchivesToDelete) . "]");

            // Deleting "Custom Date Range" reports after 1 day, since they can be re-processed
            // and would take up unecessary space
            $yesterday = Piwik_Date::factory('yesterday')->getDateTime();
            $query = "DELETE
    					FROM %s
    					WHERE period = ?
    						AND ts_archived < ?";
            $bind = array(Piwik::$idPeriods['range'], $yesterday);
            Piwik::log("Purging Custom Range archives: done [ purged archives older than $yesterday from $blobTable and $numericTable ]");

            Piwik_Query(sprintf($query, $numericTable), $bind);

            // Individual blob tables could be missing
            try {
                Piwik_Query(sprintf($query, $blobTable), $bind);
            } catch (Exception $e) {
            }

            // these tables will be OPTIMIZEd daily in a scheduled task, to claim lost space
        } else {
            Piwik::log("Purging temporary archives: skipped.");
        }
    }
}