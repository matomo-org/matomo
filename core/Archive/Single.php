<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik_Archive_Single is used to store the data of a single archive,
 * for example the statistics for the 'day' '2008-02-21' for the website idSite '2'
 *
 * @package Piwik
 * @subpackage Piwik_Archive
 */
class Piwik_Archive_Single extends Piwik_Archive
{
    /**
     * The Piwik_ArchiveProcessing object used to check that the archive is available
     * and launch the processing if the archive was not yet processed
     *
     * @var Piwik_ArchiveProcessing
     */
    public $archiveProcessing = null;

    /**
     * @var bool Set to true if the archive has at least 1 visit
     */
    public $isThereSomeVisits = null;

    /**
     * Period of this Archive
     *
     * @var Piwik_Period
     */
    protected $period = null;

    /**
     * Set to true will activate numeric value caching for this archive.
     *
     * @var bool
     */
    protected $cacheEnabledForNumeric = true;

    /**
     * Array of cached numeric values, used to make requests faster
     * when requesting the same value again and again
     *
     * @var array of numeric
     */
    protected $numericCached = array();

    /**
     * Array of cached blob, used to make requests faster when requesting the same blob again and again
     *
     * @var array of mixed
     */
    protected $blobCached = array();

    /**
     * idarchive of this Archive in the database
     *
     * @var int
     */
    protected $idArchive = null;

    /**
     * name of requested report
     *
     * @var string
     */
    protected $requestedReport = null;

    /**
     * Flag set to true once the archive has been checked (when we make sure it is archived)
     *
     * @var bool
     */
    protected $alreadyChecked = array();

    protected function clearCache()
    {
        foreach ($this->blobCached as $name => $blob) {
            $this->freeBlob($name);
        }
        $this->blobCached = array();
    }

    public function __destruct()
    {
        $this->clearCache();
    }

    /**
     * Returns the blob cache. For testing.
     *
     * @return array
     */
    public function getBlobCache()
    {
        return $this->blobCached;
    }

    /**
     * Returns the pretty date of this Archive, eg. 'Thursday 20th March 2008'
     *
     * @return string
     */
    public function getPrettyDate()
    {
        return $this->period->getPrettyString();
    }

    /**
     * Returns the idarchive of this Archive used to index this archive in the DB
     *
     * @throws Exception
     * @return int
     */
    public function getIdArchive()
    {
        if (is_null($this->idArchive)) {
            throw new Exception("idArchive is null");
        }
        return $this->idArchive;
    }

    /**
     * Set the period
     *
     * @param Piwik_Period $period
     */
    public function setPeriod(Piwik_Period $period)
    {
        $this->period = $period;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Returns the timestamp of the first date in the period for this Archive.
     * This is used to sort archives by date when working on a Archive_Array
     *
     * @return int Unix timestamp
     */
    public function getTimestampStartDate()
    {
        if (!is_null($this->archiveProcessing)) {
            $timestamp = $this->archiveProcessing->getTimestampStartDate();
            if (!empty($timestamp)) {
                return $timestamp;
            }
        }
        return $this->period->getDateStart()->getTimestamp();
    }

    /**
     * Prepares the archive. Gets the idarchive from the ArchiveProcessing.
     *
     * This will possibly launch the archiving process if the archive was not available.
     * @return bool
     */
    public function prepareArchive()
    {
        $archiveJustProcessed = false;


        $periodString = $this->period->getLabel();
        $plugin = Piwik_ArchiveProcessing::getPluginBeingProcessed($this->getRequestedReport());

        $cacheKey = 'all';
        if ($periodString == 'range') {
            $cacheKey = $plugin;
        }
        if (!isset($this->alreadyChecked[$cacheKey])) {
            $this->isThereSomeVisits = false;
            $this->alreadyChecked[$cacheKey] = true;
            $dayString = $this->period->getPrettyString();
            $logMessage = sprintf("%s (%s), plugin %s", $periodString, $dayString, $plugin);
            // if the END of the period is BEFORE the website creation date
            // we already know there are no stats for this period
            // we add one day to make sure we don't miss the day of the website creation
            if ($this->period->getDateEnd()->addDay(2)->isEarlier($this->site->getCreationDate())) {
                Piwik::log(sprintf("Archive %s skipped, archive is before the website was created.", $logMessage));
                return;
            }

            // if the starting date is in the future we know there is no visit
            if ($this->period->getDateStart()->subDay(2)->isLater(Piwik_Date::today())) {
                Piwik::log(sprintf("Archive %s skipped, archive is after today.", $logMessage));
                return;
            }

            // we make sure the archive is available for the given date
            $periodLabel = $this->period->getLabel();
            $this->archiveProcessing = Piwik_ArchiveProcessing::factory($periodLabel);
            $this->archiveProcessing->setSite($this->site);
            $this->archiveProcessing->setPeriod($this->period);
            $this->archiveProcessing->setSegment($this->segment);

            $this->archiveProcessing->init();

            $this->archiveProcessing->setRequestedReport($this->getRequestedReport());

            $archivingDisabledArchiveNotProcessed = false;
            $idArchive = $this->archiveProcessing->loadArchive();
            if (empty($idArchive)) {
                if ($this->archiveProcessing->isArchivingDisabled()) {
                    $archivingDisabledArchiveNotProcessed = true;
                    $logMessage = sprintf("Archiving disabled, for %s", $logMessage);
                } else {
                    Piwik::log(sprintf("Processing %s, not archived yet...", $logMessage));
                    $archiveJustProcessed = true;

                    // Process the reports
                    $this->archiveProcessing->launchArchiving();

                    $idArchive = $this->archiveProcessing->getIdArchive();
                    $logMessage = sprintf("Processed %d, for %s", $idArchive, $logMessage);
                }
            } else {
                $logMessage = sprintf("Already processed, fetching idArchive = %d (idSite=%d), for %s", $idArchive, $this->site->getId(), $logMessage);
            }
            Piwik::log(sprintf("%s, Visits = %d", $logMessage, $this->archiveProcessing->getNumberOfVisits()));
            $this->isThereSomeVisits = !$archivingDisabledArchiveNotProcessed
                && $this->archiveProcessing->isThereSomeVisits();
            $this->idArchive = $idArchive;
        }
        return $archiveJustProcessed;
    }

    /**
     * Returns a value from the current archive with the name = $name
     * Method used by getNumeric or getBlob
     *
     * @param string $name
     * @param string $typeValue     numeric|blob
     * @param string|bool $archivedDate  Value to store date of archive info in. If false, not stored.
     * @return mixed|bool  false if no result
     */
    protected function get($name, $typeValue = 'numeric', &$archivedDate = false)
    {
        $this->setRequestedReport($name);
        $this->prepareArchive();

        // values previously "get" and now cached
        if ($typeValue == 'numeric'
            && $this->cacheEnabledForNumeric
            && isset($this->numericCached[$name])
        ) {
            return $this->numericCached[$name];
        }

        // During archiving we prefetch the blobs recursively
        // and we get them faster from memory after
        if ($typeValue == 'blob'
            && isset($this->blobCached[$name])
        ) {
            return $this->blobCached[$name];
        }

        if ($name == 'idarchive') {
            return $this->idArchive;
        }

        if (!$this->isThereSomeVisits) {
            return false;
        }

        // select the table to use depending on the type of the data requested
        switch ($typeValue) {
            case 'blob':
                $table = $this->archiveProcessing->getTableArchiveBlobName();
                break;

            case 'numeric':
            default:
                $table = $this->archiveProcessing->getTableArchiveNumericName();
                break;
        }

        $db = Zend_Registry::get('db');
        $row = $db->fetchRow("SELECT value, ts_archived
								FROM $table
								WHERE idarchive = ? AND name = ?",
            array($this->idArchive, $name)
        );

        $value = $tsArchived = false;
        if (is_array($row)) {
            $value = $row['value'];
            $tsArchived = $row['ts_archived'];
        }

        if ($archivedDate !== false) {
            $archivedDate = $tsArchived;
        }

        if ($value === false) {
            if ($typeValue == 'numeric'
                && $this->cacheEnabledForNumeric
            ) {
                $this->numericCached[$name] = false;
            }
            return $value;
        }

        // uncompress when selecting from the BLOB table
        if ($typeValue == 'blob' && $db->hasBlobDataType()) {
            $value = $this->uncompress($value);
        }

        if ($typeValue == 'numeric'
            && $this->cacheEnabledForNumeric
        ) {
            $this->numericCached[$name] = $value;
        }
        return $value;
    }


    /**
     * This method loads in memory all the subtables for the main table called $name.
     * You have to give it the parent table $dataTableToLoad so we can lookup the sub tables ids to load.
     *
     * If $addMetadataSubtableId set to true, it will add for each row a 'metadata' called 'databaseSubtableId'
     *  containing the child ID of the subtable  associated to this row.
     *
     * @param string $name
     * @param Piwik_DataTable $dataTableToLoad
     * @param bool $addMetadataSubtableId
     */
    public function loadSubDataTables($name, Piwik_DataTable $dataTableToLoad, $addMetadataSubtableId = false)
    {
        // we have to recursively load all the subtables associated to this table's rows
        // and update the subtableID so that it matches the newly instanciated table
        foreach ($dataTableToLoad->getRows() as $row) {
            $subTableID = $row->getIdSubDataTable();

            if ($subTableID !== null) {
                $subDataTableLoaded = $this->getDataTable($name, $subTableID);

                $row->setSubtable($subDataTableLoaded);

                $this->loadSubDataTables($name, $subDataTableLoaded, $addMetadataSubtableId);

                // we edit the subtable ID so that it matches the newly table created in memory
                // NB: we dont overwrite the datatableid in the case we are displaying the table expanded.
                if ($addMetadataSubtableId) {
                    // this will be written back to the column 'idsubdatatable' just before rendering, see Renderer/Php.php
                    $row->addMetadata('idsubdatatable_in_db', $row->getIdSubDataTable());
                }
            }
        }
    }


    /**
     * Free the blob cache memory array
     * @param $name
     */
    public function freeBlob($name)
    {
        unset($this->blobCached[$name]);
        $this->blobCached[$name] = null;
    }

    protected function uncompress($data)
    {
        return @gzuncompress($data);
    }

    /**
     * Fetches all blob fields name_* at once for the current archive for performance reasons.
     *
     * @param string  $name
     *
     * @return void
     */
    public function preFetchBlob($name)
    {
        $this->setRequestedReport($name);
        $this->prepareArchive();
        if (!$this->isThereSomeVisits) {
            return;
        }

        $tableBlob = $this->archiveProcessing->getTableArchiveBlobName();

        $db = Zend_Registry::get('db');
        $hasBlobs = $db->hasBlobDataType();

        // select blobs w/ name like "$name_[0-9]+" w/o using RLIKE
        $nameEnd = strlen($name) + 2;
        $query = $db->query("SELECT value, name
								FROM $tableBlob
								WHERE idarchive = ?
									AND (name = ? OR
											(name LIKE ? AND SUBSTRING(name, $nameEnd, 1) >= '0'
														 AND SUBSTRING(name, $nameEnd, 1) <= '9') )",
            array($this->idArchive, $name, $name . '%')
        );

        while ($row = $query->fetch()) {
            $value = $row['value'];
            $name = $row['name'];

            if ($hasBlobs) {
                $this->blobCached[$name] = $this->uncompress($value);
                if ($this->blobCached[$name] === false) {
                    //throw new Exception("Error gzuncompress $name ");
                }
            } else {
                $this->blobCached[$name] = $value;
            }
        }
    }

    /**
     * Returns a numeric value from this Archive, with the name '$name'
     *
     * @param string $name
     * @return int|float
     */
    public function getNumeric($name)
    {
        return $this->formatNumericValue($this->get($name, 'numeric'));
    }


    /**
     * Returns a blob value from this Archive, with the name '$name'
     * Blob values are all values except int and float.
     *
     * @param string $name
     * @return mixed
     */
    public function getBlob($name)
    {
        return $this->get($name, 'blob');
    }

    /**
     * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Simple
     * containing one row per field name.
     *
     * For example $fields = array(    'max_actions',
     *                        'nb_uniq_visitors',
     *                        'nb_visits',
     *                        'nb_actions',
     *                        'sum_visit_length',
     *                        'bounce_count',
     *                        'nb_visits_converted'
     *                    );
     *
     * @param string|array $fields Name or array of names of Archive fields
     *
     * @return Piwik_DataTable_Simple
     */
    public function getDataTableFromNumeric($fields)
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        $values = array();
        foreach ($fields as $field) {
            $values[$field] = $this->getNumeric($field);
        }

        $table = new Piwik_DataTable_Simple();
        $table->addRowsFromArray($values);
        return $table;
    }

    /**
     * Returns a DataTable that has the name '$name' from the current Archive.
     * If $idSubTable is specified, returns the subDataTable called '$name_$idSubTable'
     *
     * @param string $name
     * @param int $idSubTable  optional id SubDataTable
     * @return Piwik_DataTable
     */
    public function getDataTable($name, $idSubTable = null)
    {
        if (!is_null($idSubTable)) {
            $name .= sprintf("_%s", $idSubTable);
        }

        $this->setRequestedReport($name);

        $data = $this->get($name, 'blob', $tsArchived);

        $table = $this->makeDataTable();

        if ($data !== false) {
            $table->addRowsFromSerializedArray($data);
            $table->setMetadata(Piwik_DataTable::ARCHIVED_DATE_METADATA_NAME, $tsArchived);
        }
        if ($data === false
            && $idSubTable !== null
        ) {
            // This is not expected, but somehow happens in some unknown cases and very rarely.
            // Do not throw error in this case
            //throw new Exception("not expected");
            return new Piwik_DataTable();
        }

        return $table;
    }

    /**
     * Creates a new DataTable with some metadata set. Sets the following metadata:
     * - 'site' => Piwik_Site instance for this archive
     * - 'period' => Piwik_Period instance for this archive
     * - 'timestamp' => the timestamp of the first date in this archive
     *
     * @param bool $isSimple Whether the DataTable should be a DataTable_Simple
     *                       instance or not.
     * @return Piwik_DataTable
     */
    public function makeDataTable($isSimple = false)
    {
        if ($isSimple) {
            $result = new Piwik_DataTable_Simple();
        } else {
            $result = new Piwik_DataTable();
        }

        $result->setMetadata('site', $this->getSite());
        $result->setMetadata('period', $this->getPeriod());
        $result->setMetadata('timestamp', $this->getTimestampStartDate());

        return $result;
    }

    public function setRequestedReport($requestedReport)
    {
        $this->requestedReport = $requestedReport;
    }

    /**
     * Returns the report (the named collection of metrics) this Archive instance is
     * currently going to query/process.
     *
     * @return string
     */
    protected function getRequestedReport()
    {
        return self::getRequestedReportFor($this->requestedReport);
    }

    /**
     * Returns the name of the report (the named collection of metrics) that contains the
     * specified metric.
     *
     * @param string $metric The metric whose report is being requested. If this does
     *                       not belong to a known report, its assumed to be the report
     *                       itself.
     * @return string
     */
    public static function getRequestedReportFor($metric)
    {
        // Core metrics are always processed in Core, for the requested date/period/segment
        if (in_array($metric, Piwik_ArchiveProcessing::getCoreMetrics())
            || $metric == 'max_actions'
        ) {
            return 'VisitsSummary_CoreMetrics';
        }
        // VisitFrequency metrics don't follow the same naming convention (HACK)
        if (strpos($metric, '_returning') > 0
            // ignore Goal_visitor_returning_1_1_nb_conversions
            && strpos($metric, 'Goal_') === false
        ) {
            return 'VisitFrequency_Metrics';
        }
        // Goal_* metrics are processed by the Goals plugin (HACK)
        if (strpos($metric, 'Goal_') === 0) {
            return 'Goals_Metrics';
        }
        // Actions metrics are processed by the Actions plugin (HACK) (3RD HACK IN FACT) (YES, THIS IS TOO MUCH HACKING)
        // (FIXME PLEASE).
        if (in_array($metric, Piwik_Archive::$actionsMetrics)) {
            return 'Actions_Metrics';
        }
        return $metric;
    }

    /**
     * Returns a DataTable that has the name '$name' from the current Archive.
     * Also loads in memory all subDataTable for this DataTable.
     *
     * For example, if $name = 'Referers_keywordBySearchEngine' it will load all DataTable
     *  named 'Referers_keywordBySearchEngine_*' and they will be set as subDataTable to the
     *  rows. You can then go through the rows
     *        $rows = DataTable->getRows();
     *  and for each row request the subDataTable (in this case the DataTable of the keywords for each search engines)
     *        $idSubTable = $row->getIdSubDataTable();
     *        $subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
     *
     * @param string $name
     * @param int $idSubTable  Optional subDataTable to load instead of loading the parent DataTable
     * @return Piwik_DataTable
     */
    public function getDataTableExpanded($name, $idSubTable = null)
    {
        $this->preFetchBlob($name);
        $dataTableToLoad = $this->getDataTable($name, $idSubTable);
        $this->loadSubDataTables($name, $dataTableToLoad, $addMetadataSubtableId = true);
        $dataTableToLoad->enableRecursiveFilters();
        $this->freeBlob($name);
        return $dataTableToLoad;
    }

    /**
     * Returns true if Piwik can launch the archiving process for this archive,
     * false if otherwise.
     *
     * @return bool
     */
    public function isArchivingDisabled()
    {
        return Piwik_ArchiveProcessing::isArchivingDisabledFor($this->segment, $this->period);
    }
}
