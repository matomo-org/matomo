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
use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;

use Piwik\Db;
use Piwik\Period;

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
 * [ArchiveProcessor.Period.compute](#) events.
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
 *     public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
 *     {
 *         $myFancyMetric = // ... calculate the metric value ...
 *         $archiveProcessor->insertNumericRecord('MyPlugin_myFancyMetric', $myFancyMetric);
 *     }
 * 
 * **Inserting serialized DataTables**
 * 
 *     // function in an Archiver descendent
 *     public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
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
abstract class ArchiveProcessor
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
     * @var \Piwik\DataAccess\ArchiveWriter
     */
    protected $archiveWriter;

    /**
     * Is the current archive temporary. ie.
     * - today
     * - current week / month / year
     */
    protected $temporaryArchive;

    /**
     * @var LogAggregator
     */
    protected $logAggregator = null;

    /**
     * @var int Cached number of visits cached
     */
    protected $visitsMetricCached = false;

    /**
     * @var int Cached number of visits with conversions
     */
    protected $convertedVisitsMetricCached = false;

    /**
     * Site of the current archive
     * Can be accessed by plugins (that is why it's public)
     *
     * @var Site
     */
    private $site = null;

    /**
     * @var Period
     */
    private $period = null;

    /**
     * @var Segment
     */
    private $segment = null;

    public function __construct(Period $period, Site $site, Segment $segment)
    {
        $this->period = $period;
        $this->site = $site;
        $this->segment = $segment;
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
        if (empty($this->logAggregator)) {
            $this->logAggregator = new LogAggregator($this->getPeriod()->getDateStart(), $this->getPeriod()->getDateEnd(),
                $this->getSite(), $this->getSegment());
        }
        return $this->logAggregator;
    }

    /**
     * Returns the period we computing statistics for.
     * 
     * @return Period
     * @api
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Returns the site we are computing statistics for.
     * 
     * @return Site
     * @api
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * The Segment used to limit the set of visits that are being aggregated.
     * 
     * @return Segment
     * @api
     */
    public function getSegment()
    {
        return $this->segment;
    }

    public function getNumberOfVisitsConverted()
    {
        return $this->convertedVisitsMetricCached;
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
     * @param numeric $value The numeric value.
     * @api
     */
    public function insertNumericRecord($name, $value)
    {
        $value = round($value, 2);
        $this->archiveWriter->insertRecord($name, $value);
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

    protected function setRequestedPlugin($plugin)
    {
        $this->requestedPlugin = $plugin;
    }

    /**
     * Returns the idArchive if the archive is available in the database for the requested plugin.
     * Returns false if the archive needs to be processed.
     *
     * @param $requestedPlugin
     * @return int or false
     */
    protected function loadExistingArchiveIdFromDb($requestedPlugin)
    {
        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchiveProcessed();
        $site = $this->getSite();
        $period = $this->getPeriod();
        $segment = $this->getSegment();

        $idAndVisits = ArchiveSelector::getArchiveIdAndVisits($site, $period, $segment, $minDatetimeArchiveProcessedUTC, $requestedPlugin);
        if (!$idAndVisits) {
            return false;
        }
        list($idArchive, $visits, $visitsConverted) = $idAndVisits;
        $this->setNumberOfVisits($visits, $visitsConverted);
        return $idArchive;
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
        return Config::getInstance()->Debug[$debugSetting];
    }

    /**
     * A flag mechanism to store whether visits were selected from archive
     *
     * @param $visitsMetricCached
     * @param bool $convertedVisitsMetricCached
     */
    protected function setNumberOfVisits($visitsMetricCached, $convertedVisitsMetricCached = false)
    {
        if ($visitsMetricCached === false) {
            $this->visitsMetricCached = $this->convertedVisitsMetricCached = false;
        } else {
            $this->visitsMetricCached = (int)$visitsMetricCached;
            $this->convertedVisitsMetricCached = (int)$convertedVisitsMetricCached;
        }
    }

    public function getNumberOfVisits()
    {
        return $this->visitsMetricCached;
    }

    protected function doesRequestedPluginIncludeVisitsSummary($requestedPlugin)
    {
        $processAllReportsIncludingVisitsSummary = Rules::shouldProcessReportsAllPlugins($this->getSegment(), $this->getPeriod()->getLabel());
        $doesRequestedPluginIncludeVisitsSummary = $processAllReportsIncludingVisitsSummary || $requestedPlugin == 'VisitsSummary';
        return $doesRequestedPluginIncludeVisitsSummary;
    }

    protected function computeNewArchive($requestedPlugin, $enforceProcessCoreMetricsOnly)
    {
        $archiveWriter = new ArchiveWriter($this->getSite()->getId(), $this->getSegment(), $this->getPeriod(), $requestedPlugin, $this->isArchiveTemporary());
        $archiveWriter->initNewArchive();

        $this->archiveWriter = $archiveWriter;

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
        $this->logStatusDebug($requestedPlugin);

        $isVisitsToday = $this->getNumberOfVisits() > 0;
        if ($isVisitsToday
            && !$enforceProcessCoreMetricsOnly
        ) {
            $this->compute();
        }

        $archiveWriter->finalizeArchive();

        if ($isVisitsToday && $this->period->getLabel() != 'day') {
            ArchiveSelector::purgeOutdatedArchives($this->getPeriod()->getDateStart());
        }

        return $archiveWriter->getIdArchive();
    }

    /**
     * Returns the minimum archive processed datetime to look at. Only public for tests.
     *
     * @return int|bool  Datetime timestamp, or false if must look at any archive available
     */
    public function getMinTimeArchiveProcessed()
    {
        $endDateTimestamp = self::determineIfArchivePermanent($this->getDateEnd());
        $isArchiveTemporary = ($endDateTimestamp === false);
        $this->temporaryArchive = $isArchiveTemporary;

        if ($endDateTimestamp) {
            // Permanent archive
            return $endDateTimestamp;
        }
        // Temporary archive
        return Rules::getMinTimeProcessedForTemporaryArchive($this->getDateStart(), $this->getPeriod(), $this->getSegment(), $this->getSite());
    }

    public function isArchiveTemporary()
    {
        if (is_null($this->temporaryArchive)) {
            throw new Exception("getMinTimeArchiveProcessed() should be called prior to isArchiveTemporary()");
        }
        return $this->temporaryArchive;
    }

    abstract protected function aggregateCoreVisitsMetrics();

    /**
     * @param $requestedPlugin
     */
    protected function logStatusDebug($requestedPlugin)
    {
        $temporary = 'definitive archive';
        if ($this->isArchiveTemporary()) {
            $temporary = 'temporary archive';
        }
        Log::verbose(
            "'%s, idSite = %d (%s), segment '%s', report = '%s', UTC datetime [%s -> %s]",
            $this->getPeriod()->getLabel(),
            $this->getSite()->getId(),
            $temporary,
            $this->getSegment()->getString(),
            $requestedPlugin,
            $this->getDateStart()->getDateStartUTC(),
            $this->getDateEnd()->getDateEndUTC()
        );
    }

    /**
     * This methods reads the subperiods if necessary,
     * and computes the archive of the current period.
     */
    abstract protected function compute();

    protected static function determineIfArchivePermanent(Date $dateEnd)
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
     * @return Date
     */
    public function getDateEnd()
    {
        return $this->getPeriod()->getDateEnd()->setTimezone($this->getSite()->getTimezone());
    }

    /**
     * @return Date
     */
    public function getDateStart()
    {
        return $this->getPeriod()->getDateStart()->setTimezone($this->getSite()->getTimezone());
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
            $this->archiveWriter->insertBulkRecords($clean);
            return;
        }

        $values = $this->compress($values);
        $this->archiveWriter->insertRecord($name, $values);
    }

    protected function compress($data)
    {
        if (Db::get()->hasBlobDataType()) {
            return gzcompress($data);
        }
        return $data;
    }

    /**
     * Whether the specified plugin's reports should be archived
     * @param string $pluginName
     * @return bool
     */
    public function shouldProcessReportsForPlugin($pluginName)
    {
        if (Rules::shouldProcessReportsAllPlugins($this->getSegment(), $this->getPeriod()->getLabel())) {
            return true;
        }
        // If any other segment, only process if the requested report belong to this plugin
        $pluginBeingProcessed = $this->getRequestedPlugin();
        if ($pluginBeingProcessed == $pluginName) {
            return true;
        }
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginLoaded($pluginBeingProcessed)) {
            return true;
        }
        return false;
    }

    protected function getRequestedPlugin()
    {
        return $this->requestedPlugin;
    }
}