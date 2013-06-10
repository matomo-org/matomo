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
 * The ArchiveProcessing module is a module that reads the Piwik logs from the DB and
 * compute all the reports, which are then stored in the database.
 *
 * The ArchiveProcessing class is used by the Archive object to make sure the given Archive is processed and available in the DB.
 *
 * A record in the Database for a given report is defined by
 * - idarchive    = unique ID that is associated to all the data of this archive (idsite+period+date)
 * - idsite        = the ID of the website
 * - date1        = starting day of the period
 * - date2        = ending day of the period
 * - period    = integer that defines the period (day/week/etc.). @see period::getId()
 * - ts_archived = timestamp when the archive was processed (UTC)
 * - name        = the name of the report (ex: uniq_visitors or search_keywords_by_search_engines)
 * - value        = the actual data
 *
 * @package Piwik
 * @subpackage Piwik_ArchiveProcessing
 */
abstract class Piwik_ArchiveProcessing
{
    /**
     * Flag stored at the end of the archiving
     *
     * @var int
     */
    const DONE_OK = 1;

    /**
     * Flag stored at the start of the archiving
     * When requesting an Archive, we make sure that non-finished archive are not considered valid
     *
     * @var int
     */
    const DONE_ERROR = 2;

    /**
     * Flag indicates the archive is over a period that is not finished, eg. the current day, current week, etc.
     * Archives flagged will be regularly purged from the DB.
     *
     * @var int
     */
    const DONE_OK_TEMPORARY = 3;

    /**
     * A row is created to lock an idarchive for the current archive being processed
     * @var string
     */
    const PREFIX_SQL_LOCK = "locked_";

    /**
     * Idarchive in the DB for the requested archive
     *
     * @var int
     */
    protected $idArchive;

    /**
     * Object used to generate (depending on the $dateStart) the name of the DB table to use to store numeric values
     *
     * @var Piwik_TablePartitioning
     */
    protected $tableArchiveNumeric;

    /**
     * Object used to generate (depending on the $dateStart)  the name of the DB table to use to store numeric values
     *
     * @var Piwik_TablePartitioning
     */
    protected $tableArchiveBlob;

    /**
     * Minimum timestamp looked at for processed archives
     *
     * @var int
     */
    protected $minDatetimeArchiveProcessedUTC = false;

    /**
     * Is the current archive temporary. ie.
     * - today
     * - current week / month / year
     */
    protected $temporaryArchive;

    /**
     * When set to true, we always archive, even if the archive is already available.
     * You can change this settings automatically in the config/global.ini.php always_archive_data under the [Debug] section
     *
     * @var bool
     */
    public $debugAlwaysArchive = false;

    /**
     * If the archive has at least 1 visit, this is set to true.
     *
     * @var bool
     */
    public $isThereSomeVisits = null;

    /**
     * Flag that will forcefully disable the archiving process. Only set by the tests.
     */
    public static $forceDisableArchiving = false;


    /**
     * This methods reads the subperiods if necessary,
     * and computes the archive of the current period.
     */
    abstract protected function compute();

    abstract public function isThereSomeVisits();

    /**
     * Returns the Piwik_ArchiveProcessing_Day or Piwik_ArchiveProcessing_Period object
     * depending on $name period string
     *
     * @param string $name day|week|month|year
     * @throws Exception
     * @return Piwik_ArchiveProcessing Piwik_ArchiveProcessing_Day|Piwik_ArchiveProcessing_Period
     */
    public static function factory($name)
    {
        switch ($name) {
            case 'day':
                $process = new Piwik_ArchiveProcessing_Day();
                $process->debugAlwaysArchive = Piwik_Config::getInstance()->Debug['always_archive_data_day'];
                break;

            case 'week':
            case 'month':
            case 'year':
                $process = new Piwik_ArchiveProcessing_Period();
                $process->debugAlwaysArchive = Piwik_Config::getInstance()->Debug['always_archive_data_period'];
                break;

            case 'range':
                $process = new Piwik_ArchiveProcessing_Period();
                $process->debugAlwaysArchive = Piwik_Config::getInstance()->Debug['always_archive_data_range'];
                break;

            default:
                throw new Exception("Unknown Archiving period specified '$name'");
                break;
        }
        return $process;
    }

    /**
     * @return Piwik_Archive
     */
    protected function makeNewArchive()
    {
        $params = new Piwik_Archive_Parameters();
        $params->setSegment($this->getSegment());
        $params->setIdSites($this->getSite()->getId());
        $params->setPeriods($this->getPeriod());

        $archive = new Piwik_Archive($params);
        return $archive;
    }



    const OPTION_TODAY_ARCHIVE_TTL = 'todayArchiveTimeToLive';
    const OPTION_BROWSER_TRIGGER_ARCHIVING = 'enableBrowserTriggerArchiving';

    public static function getCoreMetrics()
    {
        return array(
            'nb_uniq_visitors',
            'nb_visits',
            'nb_actions',
            'sum_visit_length',
            'bounce_count',
            'nb_visits_converted',
        );
    }

    public static function setTodayArchiveTimeToLive($timeToLiveSeconds)
    {
        $timeToLiveSeconds = (int)$timeToLiveSeconds;
        if ($timeToLiveSeconds <= 0) {
            throw new Exception(Piwik_TranslateException('General_ExceptionInvalidArchiveTimeToLive'));
        }
        Piwik_SetOption(self::OPTION_TODAY_ARCHIVE_TTL, $timeToLiveSeconds, $autoload = true);
    }

    public static function getTodayArchiveTimeToLive()
    {
        $timeToLive = Piwik_GetOption(self::OPTION_TODAY_ARCHIVE_TTL);
        if ($timeToLive !== false) {
            return $timeToLive;
        }
        return Piwik_Config::getInstance()->General['time_before_today_archive_considered_outdated'];
    }

    public static function setBrowserTriggerArchiving($enabled)
    {
        if (!is_bool($enabled)) {
            throw new Exception('Browser trigger archiving must be set to true or false.');
        }
        Piwik_SetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING, (int)$enabled, $autoload = true);
        Piwik_Tracker_Cache::clearCacheGeneral();
    }

    public static function isBrowserTriggerArchivingEnabled()
    {
        $browserArchivingEnabled = Piwik_GetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING);
        if ($browserArchivingEnabled !== false) {
            return (bool)$browserArchivingEnabled;
        }
        return (bool)Piwik_Config::getInstance()->General['enable_browser_archiving_triggering'];
    }

    public function getIdArchive()
    {
        return $this->idArchive;
    }

    private function getDateEnd()
    {
        return $this->getPeriod()->getDateEnd()->setTimezone($this->getSite()->getTimezone());
    }

    private function getDateStart()
    {
        return $this->getPeriod()->getDateStart()->setTimezone($this->getSite()->getTimezone());
    }

    public function getStartDatetimeUTC()
    {
        return $this->getDateStart()->getDateStartUTC();
    }

    public function getEndDatetimeUTC()
    {
        return $this->getDateEnd()->getDateEndUTC();
    }

    public function isArchiveTemporary()
    {
        return $this->temporaryArchive;
    }

    /**
     * Returns the minimum archive processed datetime to look at
     *
     * @return string Datetime string, or false if must look at any archive available
     *
     * @public for tests
     */
    public function getMinTimeArchivedProcessed()
    {
        $startTimestampUTC = $this->getDateStart()->getTimestamp();
        $endTimestampUTC = strtotime($this->getEndDatetimeUTC());
        $now = time();

        $this->temporaryArchive = false;
        // if the current archive is a DAY and if it's today,
        // we set this minDatetimeArchiveProcessedUTC that defines the lifetime value of today's archive
        if ($this->getPeriod()->getNumberOfSubperiods() == 0
            && ($startTimestampUTC <= $now && $endTimestampUTC > $now)
        ) {
            $this->temporaryArchive = true;
            $minDatetimeArchiveProcessedUTC = $now - self::getTodayArchiveTimeToLive();
            // see #1150; if new archives are not triggered from the browser,
            // we still want to try and return the latest archive available for today (rather than return nothing)
            if ($this->isArchivingDisabled()) {
                return false;
            }
        } // - if the period we are looking for is finished, we look for a ts_archived that
        //   is greater than the last day of the archive
        elseif ($endTimestampUTC <= $now) {
            $minDatetimeArchiveProcessedUTC = $endTimestampUTC;
        } // - if the period we're looking for is not finished, we look for a recent enough archive
        else {
            $this->temporaryArchive = true;

            // We choose to only look at archives that are newer than the specified timeout
            $minDatetimeArchiveProcessedUTC = $now - self::getTodayArchiveTimeToLive();

            // However, if archiving is disabled for this request, we shall
            // accept any archive that was processed today after 00:00:01 this morning
            if ($this->isArchivingDisabled()) {
                $timezone = $this->getSite()->getTimezone();
                $minDatetimeArchiveProcessedUTC = Piwik_Date::factory(Piwik_Date::factory('now', $timezone)->getDateStartUTC())->setTimezone($timezone)->getTimestamp();
            }
        }
        return $minDatetimeArchiveProcessedUTC;
    }

    /**
     * This method returns the idArchive ; if necessary, it triggers the archiving process.
     *
     * If the archive was not processed yet, it will launch the archiving process.
     * If the current archive needs sub-archives (eg. a month archive needs all the days archive)
     *  it will recursively launch the archiving (using this loadArchive() on the sub-periods)
     *
     * @return int|false The idarchive of the archive, false if the archive is not archived yet
     */
    public function loadArchive()
    {
        if ($this->debugAlwaysArchive) {
            return false;
        }
        $this->idArchive = $this->isArchived();
        return $this->getIdArchive();
    }

    /**
     * @see loadArchive()
     */
    public function launchArchiving()
    {
        if (!Piwik::getArchiveProcessingLock($this->getSite()->getId(), $this->getPeriod(), $this->getSegment())) {
            Piwik::log('Unable to get lock for idSite = ' . $this->getSite()->getId()
                . ', period = ' . $this->getPeriod()->getLabel()
                . ', UTC datetime [' . $this->getStartDatetimeUTC() . ' -> ' . $this->getEndDatetimeUTC() . ' ]...');
            return;
        }

        $this->initCompute();
        $this->compute();
        $this->postCompute();

        // we execute again the isArchived that does some initialization work
        $this->idArchive = $this->isArchived();
        Piwik::releaseArchiveProcessingLock($this->getSite()->getId(), $this->getPeriod(), $this->getSegment());
    }

    /**
     * Returns the name of the archive field used to tell the status of an archive, (ie,
     * whether the archive was created successfully or not).
     *
     * @param bool $flagArchiveAsAllPlugins
     * @return string
     */
    public function getDoneStringFlag($flagArchiveAsAllPlugins = false)
    {
        return self::getDoneStringFlagFor(
            $this->getSegment(), $this->getPeriod()->getLabel(), $this->getRequestedPlugin(), $flagArchiveAsAllPlugins);
    }

    /**
     * Returns the name of the archive field used to tell the status of an archive, (ie,
     * whether the archive was created successfully or not).
     *
     * @param Piwik_Segment $segment
     * @param string $periodLabel
     * @param string $plugin
     * @param bool $flagArchiveAsAllPlugins
     * @return string
     */
    public static function getDoneStringFlagFor($segment, $periodLabel, $plugin, $flagArchiveAsAllPlugins = false)
    {
        $segmentHash = $segment->getHash();
        if (!self::shouldProcessReportsAllPlugins($segment, $periodLabel)) {
            if (!Piwik_PluginsManager::getInstance()->isPluginLoaded($plugin)
                || $flagArchiveAsAllPlugins
            ) {
                $plugin = 'all';
            }
            $segmentHash .= '.' . $plugin;
        }
        return 'done' . $segmentHash;
    }

    /**
     * Init the object before launching the real archive processing
     */
    protected function initCompute()
    {
        $this->loadNextIdArchive();
        $done = $this->getDoneStringFlag();
        $this->insertNumericRecord($done, Piwik_ArchiveProcessing::DONE_ERROR);

        $temporary = 'definitive archive';
        if ($this->isArchiveTemporary()) {
            $temporary = 'temporary archive';
        }
        Piwik::log(sprintf("'%s, idSite = %d (%s), segment '%s', report = '%s', UTC datetime [%s -> %s]",
            $this->getPeriod()->getLabel(),
            $this->getSite()->getId(),
            $temporary,
            $this->getSegment()->getString(),
            $this->getRequestedPlugin(),
            $this->getStartDatetimeUTC(),
            $this->getEndDatetimeUTC()
        ));
    }

    /**
     * Post processing called at the end of the main archive processing.
     * Makes sure the new archive is marked as "successful" in the DB
     *
     * We also try to delete some stuff from memory but really there is still a lot...
     */
    protected function postCompute()
    {
        // delete the first done = ERROR
        $done = $this->getDoneStringFlag();
        Piwik_Query("DELETE FROM " . $this->getTableArchiveNumericName() . "
					WHERE idarchive = ? AND (name = '" . $done . "' OR name LIKE '" . self::PREFIX_SQL_LOCK . "%')",
            array($this->getIdArchive())
        );

        $flag = Piwik_ArchiveProcessing::DONE_OK;
        if ($this->isArchiveTemporary()) {
            $flag = Piwik_ArchiveProcessing::DONE_OK_TEMPORARY;
        }
        $this->insertNumericRecord($done, $flag);
    }
    
    /**
     * Returns the name of the numeric table where the archive numeric values are stored
     * 
     * @return string
     */
    public function getTableArchiveNumericName()
    {
        if(empty($this->tableArchiveNumeric)) {
            $this->tableArchiveNumeric = new Piwik_TablePartitioning_Monthly('archive_numeric');
            $this->tableArchiveNumeric->setTimestamp($this->getPeriod()->getDateStart()->getTimestamp());
        }
        return $this->tableArchiveNumeric->getTableName();
    }

    /**
     * Returns the name of the blob table where the archive blob values are stored
     *
     * @return string
     */
    public function getTableArchiveBlobName()
    {
        if(empty($this->tableArchiveBlob)) {
            $this->tableArchiveBlob = new Piwik_TablePartitioning_Monthly('archive_blob');
            $this->tableArchiveBlob->setTimestamp($this->getPeriod()->getDateStart()->getTimestamp());
        }
        return $this->tableArchiveBlob->getTableName();
    }


    public function setRequestedPlugin($plugin)
    {
        $this->requestedPlugin = $plugin;
    }
    
    protected function getRequestedPlugin()
    {
        return $this->requestedPlugin;
    }

    // exposing the number of visits publicly (number used to compute conversions rates)
    protected $nb_visits = null;
    protected $nb_visits_converted = null;

    protected function setNumberOfVisits($nb_visits)
    {
        $this->nb_visits = $nb_visits;
    }

    public function getNumberOfVisits()
    {
        return $this->nb_visits;
    }

    protected function setNumberOfVisitsConverted($nb_visits_converted)
    {
        $this->nb_visits_converted = $nb_visits_converted;
    }

    public function getNumberOfVisitsConverted()
    {
        return $this->nb_visits_converted;
    }


    /**
     * Returns the idArchive we will use for the current archive
     *
     * @throws Exception
     * @return int IdArchive to use when saving the current Archive
     */
    protected function loadNextIdArchive()
    {
        $table = $this->getTableArchiveNumericName();
        $dbLockName = "loadNextIdArchive.$table";

        $db = Zend_Registry::get('db');
        $locked = self::PREFIX_SQL_LOCK . Piwik_Common::generateUniqId();
        $date = date("Y-m-d H:i:s");

        if (Piwik_GetDbLock($dbLockName, $maxRetries = 30) === false) {
            throw new Exception("loadNextIdArchive: Cannot get named lock for table $table.");
        }
        $db->exec("INSERT INTO $table "
            . " SELECT ifnull(max(idarchive),0)+1,
								'" . $locked . "',
								" . (int)$this->getSite()->getId() . ",
								'" . $date . "',
								'" . $date . "',
								0,
								'" . $date . "',
								0 "
            . " FROM $table as tb1");
        Piwik_ReleaseDbLock($dbLockName);
        $id = $db->fetchOne("SELECT idarchive FROM $table WHERE name = ? LIMIT 1", $locked);

        $this->idArchive = $id;
    }

    /**
     * @param string $name
     * @param int|float $value
     */
    public function insertNumericRecord($name, $value)
    {
        $value = round($value, 2);
        return $this->insertRecord($name, $value);
    }

    public function insertNumericRecords($numericRecords)
    {
        foreach ($numericRecords as $name => $value) {
            $this->insertNumericRecord($name, $value);
        }
    }

    /**
     * @param string $name
     * @param string|array $values
     * @return bool|array
     */
    public function insertBlobRecord($name, $values)
    {
        if (is_array($values)) {
            $clean = array();
            foreach ($values as $id => $value) {
                // for the parent Table we keep the name
                // for example for the Table of searchEngines we keep the name 'referer_search_engine'
                // but for the child table of 'Google' which has the ID = 9 the name would be 'referer_search_engine_9'
                $newName = $name;
                if ($id != 0) {
                    //FIXMEA: refactor
                    $newName = $name . '_' . $id;
                }

                $value = $this->compress($value);
                $clean[] = array($newName, $value);
            }
            return $this->insertBulkRecords($clean);
        }

        $values = $this->compress($values);

        $this->insertRecord($name, $values);
        return array($name => $values);
    }

    protected function compress( $data)
    {
        if(Zend_Registry::get('db')->hasBlobDataType()) {
            return gzcompress($data);
        }
        return $data;
    }

    protected function insertBulkRecords($records)
    {
        // Using standard plain INSERT if there is only one record to insert
        if ($DEBUG_DO_NOT_USE_BULK_INSERT = false
            || count($records) == 1
        ) {
            foreach ($records as $record) {
                $this->insertRecord($record[0], $record[1]);
            }
            return;
        }
        $bindSql = $this->getBindArray();
        $values = array();

        $valueSeen = false;
        foreach ($records as $record) {
            // don't record zero
            if (empty($record[1])) continue;

            $bind = $bindSql;
            $bind[] = $record[0]; // name
            $bind[] = $record[1]; // value
            $values[] = $bind;

            $valueSeen = $record[1];
        }
        if (empty($values)) return;

        $tableName = $this->getTableNameToInsert($valueSeen);

        Piwik::tableInsertBatch($tableName, $this->getInsertFields(), $values);
        return true;
    }

    protected function getBindArray()
    {
        return array($this->getIdArchive(),
                     $this->getSite()->getId(),
                     $this->getPeriod()->getDateStart()->toString('Y-m-d'),
                     $this->getPeriod()->getDateEnd()->toString('Y-m-d'),
                     $this->getPeriod()->getId(),
                     date("Y-m-d H:i:s"));
    }

    protected function getInsertFields()
    {
        return array('idarchive', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'name', 'value');
    }

    /**
     * Inserts a record in the right table (either NUMERIC or BLOB)
     *
     * @param string  $name
     * @param mixed   $value
     *
     * @return void
     */
    protected function insertRecord($name, $value)
    {
        // We choose not to record records with a value of 0
        if ($value == 0) {
            return;
        }
        $tableName = $this->getTableNameToInsert($value);

        // duplicate idarchives are Ignored, see http://dev.piwik.org/trac/ticket/987

        $query = "INSERT IGNORE INTO " . $tableName . "
					(" . implode(", ", $this->getInsertFields()) . ")
					VALUES (?,?,?,?,?,?,?,?)";
        $bindSql = $this->getBindArray();
        $bindSql[] = $name;
        $bindSql[] = $value;
        Piwik_Query($query, $bindSql);
    }

    protected function getTableNameToInsert($value)
    {
        if (is_numeric($value)) {
            $tableName = $this->getTableArchiveNumericName();
            return $tableName;
        }
        $tableName = $this->getTableArchiveBlobName();
        return $tableName;
    }

    /**
     * Returns the idArchive if the archive is available in the database.
     * Returns false if the archive needs to be computed.
     *
     * An archive is available if
     * - for today, the archive was computed less than minDatetimeArchiveProcessedUTC seconds ago
     * - for any other day, if the archive was computed once this day was finished
     * - for other periods, if the archive was computed once the period was finished
     *
     * @return int|false
     */
    protected function isArchived()
    {
        $bindSQL = array($this->getSite()->getId(),
                         $this->getPeriod()->getDateStart()->toString('Y-m-d'),
                         $this->getPeriod()->getDateEnd()->toString('Y-m-d'),
                         $this->getPeriod()->getId(),
        );

        $timeStampWhere = '';

        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchivedProcessed();
        if ($minDatetimeArchiveProcessedUTC) {
            $timeStampWhere = " AND ts_archived >= ? ";
            $bindSQL[] = Piwik_Date::factory($minDatetimeArchiveProcessedUTC)->getDatetime();
        }

        // When a Segment is specified, we try and only process the requested report in the archive
        // As a limitation, we don't know all the time which plugin should process which report
        // There is a catch all flag 'all' appended to archives containing all reports already
        // We look for this 'done.ABCDEFG.all', or for an archive that contains only our plugin data 'done.ABDCDEFG.Referers'
        $done = $this->getDoneStringFlag();
        $doneAllPluginsProcessed = $this->getDoneStringFlag($flagArchiveAsAllPlugins = true);

        $sqlSegmentsFindArchiveAllPlugins = '';

        if ($done != $doneAllPluginsProcessed) {
            $sqlSegmentsFindArchiveAllPlugins = "OR (name = '" . $doneAllPluginsProcessed . "' AND value = " . Piwik_ArchiveProcessing::DONE_OK . ")
					OR (name = '" . $doneAllPluginsProcessed . "' AND value = " . Piwik_ArchiveProcessing::DONE_OK_TEMPORARY . ")";
        }
        $sqlQuery = "	SELECT idarchive, value, name, date1 as startDate
						FROM " . $this->getTableArchiveNumericName() . "
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ( (name = '" . $done . "' AND value = " . Piwik_ArchiveProcessing::DONE_OK . ")
									OR (name = '" . $done . "' AND value = " . Piwik_ArchiveProcessing::DONE_OK_TEMPORARY . ")
									$sqlSegmentsFindArchiveAllPlugins
									OR name = 'nb_visits')
							$timeStampWhere
						ORDER BY idarchive DESC";
        $results = Piwik_FetchAll($sqlQuery, $bindSQL);
        if (empty($results)) {
            return false;
        }

        $idarchive = false;
        // we look for the more recent idarchive
        foreach ($results as $result) {
            if ($result['name'] == $done
                || $result['name'] == $doneAllPluginsProcessed
            ) {
                $idarchive = $result['idarchive'];
                break;
            }
        }

        // case when we have a nb_visits entry in the archive, but the process is not finished yet or failed to finish
        // therefore we don't have the done=OK
        if ($idarchive === false) {
            return false;
        }

        if ($this->isVisitsSummaryRequested()) {
            $this->isThereSomeVisits = false;
        }

        // we look for the nb_visits result for this most recent archive
        foreach ($results as $result) {
            if ($result['name'] == 'nb_visits'
                && $result['idarchive'] == $idarchive
            ) {
                $this->isThereSomeVisits = ($result['value'] > 0);
                $this->setNumberOfVisits($result['value']);
                break;
            }
        }
        return $idarchive;
    }

    protected function isVisitsSummaryRequested()
    {
        return $this->getRequestedPlugin() == 'VisitsSummary';
    }
    /**
     * Returns true if, for some reasons, triggering the archiving is disabled.
     * Note that when a segment is passed to the function, archiving will always occur
     * (since segments are by default not pre-processed)
     *
     * @return bool
     */
    public function isArchivingDisabled()
    {
        return self::isArchivingDisabledFor($this->getSegment(), $this->getPeriod()->getLabel());
    }

    public static function isArchivingDisabledFor($segment, $periodLabel)
    {
        if ($periodLabel == 'range') {
            return false;
        }
        $processOneReportOnly = !self::shouldProcessReportsAllPlugins($segment, $periodLabel);
        $isArchivingDisabled = !self::isRequestAuthorizedToArchive();

        if ($processOneReportOnly) {
            // When there is a segment, archiving is not necessary allowed
            // If browser archiving is allowed, then archiving is enabled
            // if browser archiving is not allowed, then archiving is disabled
            if (!$segment->isEmpty()
                && $isArchivingDisabled
                && Piwik_Config::getInstance()->General['browser_archiving_disabled_enforce']
            ) {
                Piwik::log("Archiving is disabled because of config setting browser_archiving_disabled_enforce=1");
                return true;
            }
            return false;
        }
        return $isArchivingDisabled;
    }

    protected static function isRequestAuthorizedToArchive()
    {
        return !self::$forceDisableArchiving &&
            (self::isBrowserTriggerArchivingEnabled()
                || Piwik_Common::isPhpCliMode()
                || (Piwik::isUserIsSuperUser()
                    && Piwik_Common::isArchivePhpTriggered()));
    }


    /**
     * Returns true when
     * - there is no segment and period is not range
     * - there is a segment that is part of the preprocessed [Segments] list

     * @param Piwik_Segment $segment
     * @param string $period
     * @return bool
     */
    private static function shouldProcessReportsAllPlugins($segment, $periodLabel)
    {
        if ($segment->isEmpty() && $periodLabel != 'range') {
            return true;
        }

        $segmentsToProcess = Piwik::getKnownSegmentsToArchive();
        if (!empty($segmentsToProcess)) {
            // If the requested segment is one of the segments to pre-process
            // we ensure that any call to the API will trigger archiving of all reports for this segment
            $segment = $segment->getString();
            if (in_array($segment, $segmentsToProcess)) {
                return true;
            }
        }
        return false;
    }

    // We check if there is visits for the requested date / site / segment
    //  If no specified Segment
    //  Or if a segment is passed and we specifically process VisitsSummary
    //   Then we check the logs. This is to ensure that this query is ran only once for this day/site/segment (rather than running it for every plugin)
    protected function isProcessingEnabled()
    {
        return $this->shouldProcessReportsAllPlugins($this->getSegment(), $this->getPeriod()->getLabel())
               || $this->isVisitsSummaryRequested();
    }

    /**
     * When a segment is set, we shall only process the requested report (no more).
     * The requested data set will return a lot faster if we only process these reports rather than all plugins.
     * Similarly, when a period=range is requested, we shall only process the requested report for the range itself.
     *
     * @param string $pluginName
     * @return bool
     */
    public function shouldProcessReportsForPlugin($pluginName)
    {
        if ($this->shouldProcessReportsAllPlugins($this->getSegment(), $this->getPeriod()->getLabel())) {
            return true;
        }

        // If any other segment, only process if the requested report belong to this plugin
        // or process all plugins if the requested report plugin couldn't be guessed
        $pluginBeingProcessed = $this->getRequestedPlugin();
        return $pluginBeingProcessed == $pluginName
            || !Piwik_PluginsManager::getInstance()->isPluginLoaded($pluginBeingProcessed);
    }

    /**
     * Site of the current archive
     * Can be accessed by plugins (that is why it's public)
     *
     * @var Piwik_Site
     */
    private $site = null;


    /**
     * Period of the current archive
     * Can be accessed by plugins (that is why it's public)
     *
     * @var Piwik_Period
     */
    private $period = null;



    /**
     * @var Piwik_Segment
     */
    private $segment = null;


    /**
     * Set the period
     *
     * @param Piwik_Period $period
     */
    public function setPeriod(Piwik_Period $period)
    {
        $this->period = $period;
    }

    //FIXMEA remove
    public function setSegment(Piwik_Segment $segment)
    {
        $this->segment = $segment;
    }

    public function getSegment()
    {
        return $this->segment;
    }

    //FIXMEA remove, only via constructor
    /**
     * Set the site
     *
     * @param Piwik_Site $site
     */
    public function setSite(Piwik_Site $site)
    {
        $this->site = $site;
    }

    /**
     * @return Piwik_Site
     */
    public function getSite()
    {
        return $this->site;
    }

    public function getPeriod()
    {
        return $this->period;
    }
}
