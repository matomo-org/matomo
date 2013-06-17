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
 * The ArchiveProcessor class is used by the Archive object to make sure the given Archive is processed and available in the DB.
 *
 * @package Piwik
 * @subpackage Piwik_ArchiveProcessor
 */
abstract class Piwik_ArchiveProcessor
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
     * Is the current archive temporary. ie.
     * - today
     * - current week / month / year
     */
    protected $temporaryArchive;

    protected $logAggregator = null;

    /**
     * @var int Number of visits cached as early as possible
     */
    protected $visitsMetricCached = false;

    /**
     * @var int Number of visits with conversions, cached when selecting
     */
    protected $convertedVisitsMetricCached = false;

    /**
     * Site of the current archive
     * Can be accessed by plugins (that is why it's public)
     *
     * @var Piwik_Site
     */
    private $site = null;

    /**
     * @var Piwik_Period
     */
    private $period = null;

    /**
     * @var Piwik_Segment
     */
    private $segment = null;

    public function __construct(Piwik_Period $period, Piwik_Site $site, Piwik_Segment $segment)
    {
        $this->period = $period;
        $this->site = $site;
        $this->segment = $segment;
    }

    /**
     * @return Piwik_DataAccess_LogAggregator
     */
    public function getLogAggregator()
    {
        if (empty($this->logAggregator)) {
            $this->logAggregator = new Piwik_DataAccess_LogAggregator($this->getPeriod()->getDateStart(), $this->getPeriod()->getDateEnd(),
                $this->getSite(), $this->getSegment());
        }
        return $this->logAggregator;
    }

    public function preProcessArchive($requestedPlugin, $enforceProcessCoreMetricsOnly = false)
    {
        $this->idArchive = false;

        $this->setRequestedPlugin($requestedPlugin);

        if (!$enforceProcessCoreMetricsOnly) {
            $this->idArchive = $this->loadExistingArchiveIdFromDb($requestedPlugin);
            if ($this->isArchivingForcedToTrigger()) {
                $this->idArchive = false;
                $this->setNumberOfVisits(false);
            }
            if (!empty($this->idArchive)) {
                return $this->idArchive;
            }

            $visitsNotKnownYet = $this->getNumberOfVisits() === false;

            $createAnotherArchiveForVisitsSummary = !$this->doesRequestedPluginIncludeVisitsSummary($requestedPlugin) && $visitsNotKnownYet;

            if ($createAnotherArchiveForVisitsSummary) {
                // recursive archive creation in case we create another separate one, for VisitsSummary core metrics
                // We query VisitsSummary here, as it is needed in the call below ($this->getNumberOfVisits() > 0)
                $requestedPlugin = $this->getRequestedPlugin();
                $this->preProcessArchive('VisitsSummary', $pleaseProcessCoreMetricsOnly = true);
                $this->setRequestedPlugin($requestedPlugin);
                if ($this->getNumberOfVisits() === false) {
                    throw new Exception("preProcessArchive() is expected to set number of visits to a numeric value.");
                }
            }
        }

        return $this->computeNewArchive($requestedPlugin, $enforceProcessCoreMetricsOnly);
    }

    public function setRequestedPlugin($plugin)
    {
        $this->requestedPlugin = $plugin;
    }

    /**
     * Returns the idArchive if the archive is available in the database for the requested plugin.
     * Returns false if the archive needs to be processed.
     *
     * @return int|false
     */
    protected function loadExistingArchiveIdFromDb($requestedPlugin)
    {
        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchivedProcessed();
        $site = $this->getSite();
        $period = $this->getPeriod();
        $segment = $this->getSegment();
        $numericTableName = $this->getTableArchiveNumericName();

        $idAndVisits = Piwik_DataAccess_ArchiveSelector::getArchiveIdAndVisits($numericTableName, $site, $period, $segment, $minDatetimeArchiveProcessedUTC, $requestedPlugin);
        if (!$idAndVisits) {
            return false;
        }
        list($idArchive, $visits, $visitsConverted) = $idAndVisits;
        $this->setNumberOfVisits($visits, $visitsConverted);
        return $idArchive;
    }

    protected static function determineIfArchivePermanent(Piwik_Date $dateEnd)
    {
        $now = time();
        $endTimestampUTC = strtotime($dateEnd->getDateEndUTC());
        if ($endTimestampUTC <= $now) {
            // - if the period we are looking for is finished, we look for a ts_archived that
            //   is greater than the last day of the archive
            return $endTimestampUTC;
        }
        return false;
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

        $endDateTimestamp = self::determineIfArchivePermanent($this->getDateEnd());
        $isArchiveTemporary = ($endDateTimestamp === false);
        $this->temporaryArchive = $isArchiveTemporary;

        if($endDateTimestamp) {
            // Permanent archive
            return $endDateTimestamp;
        }
        // Temporary archive
        return Piwik_ArchiveProcessor_Rules::getMinTimeProcessedForTemporaryArchive($this->getDateStart(), $this->getPeriod(), $this->getSegment(), $this->getSite());
    }

    /**
     * @return Piwik_Date
     */
    public function getDateStart()
    {
        return $this->getPeriod()->getDateStart()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * @return Piwik_Date
     */
    public function getDateEnd()
    {
        return $this->getPeriod()->getDateEnd()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * A flag mechanism to store whether
     * @param $visitsMetricCached
     * @param bool $convertedVisitsMetricCached
     */
    protected function setNumberOfVisits($visitsMetricCached, $convertedVisitsMetricCached = false)
    {
        if (empty($visitsMetricCached)) {
            $visitsMetricCached = 0;
        }
        if (empty($convertedVisitsMetricCached)) {
            $convertedVisitsMetricCached = 0;
        }
        $this->visitsMetricCached = (int)$visitsMetricCached;
        $this->convertedVisitsMetricCached = (int)$convertedVisitsMetricCached;
    }

    protected function isArchivingForcedToTrigger()
    {
        $period = $this->getPeriod()->getLabel();
        $debugSetting = 'always_archive_data_period'; // default
        if ($period == 'day') {
            $debugSetting = 'always_archive_data_day';
        } elseif ($period == 'range') {
            $debugSetting = 'always_archive_data_range';
        }
        return Piwik_Config::getInstance()->Debug[$debugSetting];
    }

    public function getNumberOfVisits()
    {
        return $this->visitsMetricCached;
    }

    protected function doesRequestedPluginIncludeVisitsSummary($requestedPlugin)
    {
        $processAllReportsIncludingVisitsSummary = Piwik_ArchiveProcessor_Rules::shouldProcessReportsAllPlugins($this->getSegment(), $this->getPeriod()->getLabel());

        $doesRequestedPluginIncludeVisitsSummary = $processAllReportsIncludingVisitsSummary || $requestedPlugin == 'VisitsSummary';
        return $doesRequestedPluginIncludeVisitsSummary;
    }

    /**
     * @param $requestedPlugin
     * @param $enforceProcessCoreMetricsOnly
     * @return mixed
     */
    protected function computeNewArchive($requestedPlugin, $enforceProcessCoreMetricsOnly)
    {
        Piwik_DataAccess_ArchiveSelector::getArchiveProcessorLock($this->getSite()->getId(), $this->getPeriod(), $this->getSegment());

        $this->idArchive = Piwik_DataAccess_ArchiveSelector::allocateNewArchiveId($this->getTableArchiveNumericName(), $this->getSite()->getId());

        $doneFlag = Piwik_ArchiveProcessor_Rules::getDoneStringFlagFor($this->getSegment(), $this->getPeriod()->getLabel(), $requestedPlugin);
        $this->insertNumericRecord($doneFlag, Piwik_ArchiveProcessor::DONE_ERROR);

        $visitsNotKnownYet = $this->getNumberOfVisits() === false;

        if ($visitsNotKnownYet
            || $this->doesRequestedPluginIncludeVisitsSummary($requestedPlugin)
            || $enforceProcessCoreMetricsOnly
        ) {
            $metrics = $this->aggregateCoreVisitsMetrics();

            if (empty($metrics)) {
                $this->setNumberOfVisits(false);
            } else {
                $this->setNumberOfVisits($metrics['nb_visits'], $metrics['nb_visits_converted']);
            }
        }

        $this->logStatus($requestedPlugin);

        if ($this->getNumberOfVisits() > 0
            && !$enforceProcessCoreMetricsOnly
        ) {
            $this->compute();
        }

        Piwik_DataAccess_ArchiveSelector::deletePreviousArchiveStatus($this->getTableArchiveNumericName(), $doneFlag, $this->getIdArchive());

        $flag = Piwik_ArchiveProcessor::DONE_OK;
        if ($this->isArchiveTemporary()) {
            $flag = Piwik_ArchiveProcessor::DONE_OK_TEMPORARY;
        }
        $this->insertNumericRecord($doneFlag, $flag);

        Piwik_DataAccess_ArchiveSelector::releaseArchiveProcessorLock($this->getSite()->getId(), $this->getPeriod(), $this->getSegment());

        return $this->idArchive;
    }

    abstract protected function aggregateCoreVisitsMetrics();

    /**
     * @param $requestedPlugin
     */
    protected function logStatus($requestedPlugin)
    {
        $temporary = 'definitive archive';
        if ($this->isArchiveTemporary()) {
            $temporary = 'temporary archive';
        }
        Piwik::log(sprintf("'%s, idSite = %d (%s), segment '%s', report = '%s', UTC datetime [%s -> %s]",
            $this->getPeriod()->getLabel(),
            $this->getSite()->getId(),
            $temporary,
            $this->getSegment()->getString(),
            $requestedPlugin,
            $this->getDateStart()->getDateStartUTC(),
            $this->getDateEnd()->getDateEndUTC()
        ));
    }

    public function isArchiveTemporary()
    {
        return $this->temporaryArchive;
    }

    /**
     * This methods reads the subperiods if necessary,
     * and computes the archive of the current period.
     */
    abstract protected function compute();

    public function getNumberOfVisitsConverted()
    {
        return $this->convertedVisitsMetricCached;
    }

    public function insertNumericRecords($numericRecords)
    {
        foreach ($numericRecords as $name => $value) {
            $this->insertNumericRecord($name, $value);
        }
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

    protected function compress($data)
    {
        if (Zend_Registry::get('db')->hasBlobDataType()) {
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
        $bindSql = $this->getInsertRecordBind();
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

    /**
     * Inserts a record in the right table (either NUMERIC or BLOB)
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    protected function insertRecord($name, $value)
    {
        if ($this->isRecordZero($name, $value)) {
            return;
        }

        $tableName = $this->getTableNameToInsert($value);

        // duplicate idarchives are Ignored, see http://dev.piwik.org/trac/ticket/987

        $query = "INSERT IGNORE INTO " . $tableName . "
					(" . implode(", ", $this->getInsertFields()) . ")
					VALUES (?,?,?,?,?,?,?,?)";
        $bindSql = $this->getInsertRecordBind();
        $bindSql[] = $name;
        $bindSql[] = $value;
        Piwik_Query($query, $bindSql);
        return true;
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    protected function isRecordZero($name, $value)
    {
        return ($value === '0' || $value === false || $value === 0 || $value === 0.0);
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
     * Returns the name of the numeric table where the archive numeric values are stored
     *
     * @return string
     */
    public function getTableArchiveNumericName()
    {
        if (empty($this->tableArchiveNumeric)) {
            $this->tableArchiveNumeric = new Piwik_TablePartitioning_Monthly('archive_numeric');
            $this->tableArchiveNumeric->setTimestamp($this->getPeriod()->getDateStart()->getTimestamp());
        }
        return $this->tableArchiveNumeric->getTableName();
    }

    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Returns the name of the blob table where the archive blob values are stored
     *
     * @return string
     */
    public function getTableArchiveBlobName()
    {
        if (empty($this->tableArchiveBlob)) {
            $this->tableArchiveBlob = new Piwik_TablePartitioning_Monthly('archive_blob');
            $this->tableArchiveBlob->setTimestamp($this->getPeriod()->getDateStart()->getTimestamp());
        }
        return $this->tableArchiveBlob->getTableName();
    }

    protected function getInsertFields()
    {
        return array('idarchive', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'name', 'value');
    }

    protected function getInsertRecordBind()
    {
        return array($this->getIdArchive(),
                     $this->getSite()->getId(),
                     $this->getPeriod()->getDateStart()->toString('Y-m-d'),
                     $this->getPeriod()->getDateEnd()->toString('Y-m-d'),
                     $this->getPeriod()->getId(),
                     date("Y-m-d H:i:s"));
    }

    public function getIdArchive()
    {
        return $this->idArchive;
    }

    /**
     * @return Piwik_Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Whether the specified plugin's reports should be archived
     * @param string $pluginName
     * @return bool
     */
    public function shouldProcessReportsForPlugin($pluginName)
    {
        if (Piwik_ArchiveProcessor_Rules::shouldProcessReportsAllPlugins($this->getSegment(), $this->getPeriod()->getLabel())) {
            return true;
        }
        // If any other segment, only process if the requested report belong to this plugin
        $pluginBeingProcessed = $this->getRequestedPlugin();
        if ($pluginBeingProcessed == $pluginName) {
            return true;
        }
        if (!Piwik_PluginsManager::getInstance()->isPluginLoaded($pluginBeingProcessed)) {
            return true;
        }
        return false;
    }

    /**
     * @return Piwik_Segment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    protected function getRequestedPlugin()
    {
        return $this->requestedPlugin;
    }
}
