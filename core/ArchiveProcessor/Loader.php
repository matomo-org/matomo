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
use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Log;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Plugin\Archiver;
use Piwik\Segment;
use Piwik\Site;

/**
 * This class manages the ArchiveProcessor
 */
class Loader
{
    /**
     * @var LogAggregator
     */
    private $logAggregator = null;

    /**
     * @var int Cached number of visits cached
     */
    protected $visitsMetricCached = false;

    /**
     * @var int Cached number of visits with conversions
     */
    protected $convertedVisitsMetricCached = false;

    /**
     * @var string Plugin name which triggered this archive processor
     */
    protected $requestedPlugin = false;

    /**
     * Is the current archive temporary. ie.
     * - today
     * - current week / month / year
     */
    protected $temporaryArchive;

    /**
     * Idarchive in the DB for the requested archive
     *
     * @var int
     */
    protected $idArchive;

    /**
     * @var Parameters
     */
    protected $params;

    public function __construct(Parameters $params)
    {
        $this->params = $params;
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

    public function getNumberOfVisitsConverted()
    {
        return $this->convertedVisitsMetricCached;
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
                    throw new \Exception("preProcessArchive() is expected to set number of visits to a numeric value.");
                }
            }
        }

        return $this->computeNewArchive($requestedPlugin, $enforceProcessCoreMetricsOnly);
    }

    protected function doesRequestedPluginIncludeVisitsSummary($requestedPlugin)
    {
        $processAllReportsIncludingVisitsSummary = Rules::shouldProcessReportsAllPlugins($this->params->getSegment(), $this->params->getPeriod()->getLabel());
        $doesRequestedPluginIncludeVisitsSummary = $processAllReportsIncludingVisitsSummary || $requestedPlugin == 'VisitsSummary';
        return $doesRequestedPluginIncludeVisitsSummary;
    }

    protected function setRequestedPlugin($plugin)
    {
        $this->requestedPlugin = $plugin;
    }

    protected function isArchivingForcedToTrigger()
    {
        $period = $this->params->getPeriod()->getLabel();
        $debugSetting = 'always_archive_data_period'; // default
        if ($period == 'day') {
            $debugSetting = 'always_archive_data_day';
        } elseif ($period == 'range') {
            $debugSetting = 'always_archive_data_range';
        }
        return Config::getInstance()->Debug[$debugSetting];
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
        $site = $this->params->getSite();
        $period = $this->params->getPeriod();
        $segment = $this->params->getSegment();

        $idAndVisits = ArchiveSelector::getArchiveIdAndVisits($site, $period, $segment, $minDatetimeArchiveProcessedUTC, $requestedPlugin);
        if (!$idAndVisits) {
            return false;
        }
        list($idArchive, $visits, $visitsConverted) = $idAndVisits;
        $this->setNumberOfVisits($visits, $visitsConverted);
        return $idArchive;
    }

    protected function computeNewArchive($requestedPlugin, $enforceProcessCoreMetricsOnly)
    {
        $archiveWriter = new ArchiveWriter($this->params->getSite()->getId(), $this->params->getSegment(), $this->params->getPeriod(), $requestedPlugin, $this->isArchiveTemporary());
        $archiveWriter->initNewArchive();

        $archiveProcessor = $this->makeArchiveProcessor($archiveWriter);

        $visitsNotKnownYet = $this->getNumberOfVisits() === false;
        if ($visitsNotKnownYet
            || $this->doesRequestedPluginIncludeVisitsSummary($requestedPlugin)
            || $enforceProcessCoreMetricsOnly
        ) {

            if($this->isDayArchive()) {
                $metrics = $this->aggregateDayVisitsMetrics($archiveProcessor);
            } else {
                $metrics = $this->aggregateMultipleVisitMetrics($archiveProcessor);
            }

            if (empty($metrics)) {
                $this->setNumberOfVisits(false);
            } else {
                $this->setNumberOfVisits($metrics['nb_visits'], $metrics['nb_visits_converted']);
            }
        }
        $this->logStatusDebug($requestedPlugin);

        $archiveProcessor = $this->makeArchiveProcessor($archiveWriter);

        $isVisitsToday = $this->getNumberOfVisits() > 0;
        if ($isVisitsToday
            && !$enforceProcessCoreMetricsOnly
        ) {
            $this->compute($archiveProcessor);
        }

        $archiveWriter->finalizeArchive();

        if ($isVisitsToday && $this->params->getPeriod()->getLabel() != 'day') {
            ArchiveSelector::purgeOutdatedArchives($this->params->getPeriod()->getDateStart());
        }

        return $archiveWriter->getIdArchive();
    }

    protected function aggregateDayVisitsMetrics(ArchiveProcessor $archiveProcessor)
    {
        $query = $archiveProcessor->getLogAggregator()->queryVisitsByDimension();
        $data = $query->fetch();

        $metrics = $this->convertMetricsIdToName($data);
        $archiveProcessor->insertNumericRecords($metrics);
        return $metrics;
    }

    protected function convertMetricsIdToName($data)
    {
        $metrics = array();
        foreach ($data as $metricId => $value) {
            $readableMetric = Metrics::$mappingFromIdToName[$metricId];
            $metrics[$readableMetric] = $value;
        }
        return $metrics;
    }

    protected function aggregateMultipleVisitMetrics(ArchiveProcessor $archiveProcessor)
    {
        $toSum = Metrics::getVisitsMetricNames();
        $metrics = $archiveProcessor->aggregateNumericMetrics($toSum);
        return $metrics;
    }

    /**
     * Returns the minimum archive processed datetime to look at. Only public for tests.
     *
     * @return int|bool  Datetime timestamp, or false if must look at any archive available
     */
    protected function getMinTimeArchiveProcessed()
    {
        $endDateTimestamp = self::determineIfArchivePermanent($this->params->getDateEnd());
        $isArchiveTemporary = ($endDateTimestamp === false);
        $this->temporaryArchive = $isArchiveTemporary;

        if ($endDateTimestamp) {
            // Permanent archive
            return $endDateTimestamp;
        }
        // Temporary archive
        return Rules::getMinTimeProcessedForTemporaryArchive($this->params->getDateStart(), $this->params->getPeriod(), $this->params->getSegment(), $this->params->getSite());
    }

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

    protected function isArchiveTemporary()
    {
        if (is_null($this->temporaryArchive)) {
            throw new \Exception("getMinTimeArchiveProcessed() should be called prior to isArchiveTemporary()");
        }
        return $this->temporaryArchive;
    }

    /**
     * @return bool
     */
    protected function isDayArchive()
    {
        return $this->params->getPeriod()->getLabel() == 'day';
    }

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
            $this->params->getPeriod()->getLabel(),
            $this->params->getSite()->getId(),
            $temporary,
            $this->params->getSegment()->getString(),
            $requestedPlugin,
            $this->params->getDateStart()->getDateStartUTC(),
            $this->params->getDateEnd()->getDateEndUTC()
        );
    }

    /**
     * This methods reads the subperiods if necessary,
     * and computes the archive of the current period.
     */
    protected function compute($archiveProcessor)
    {
        $archivers = $this->getPluginArchivers();

        foreach($archivers as $pluginName => $archiverClass) {
            /** @var Archiver $archiver */
            $archiver = new $archiverClass($archiveProcessor);

            if($this->shouldProcessReportsForPlugin($pluginName)) {
                if($this->isDayArchive()) {
                    $archiver->aggregateDayReport();
                } else {
                    $archiver->aggregateMultipleReports();
                }
            }
        }
    }

    /**
     * @var Archiver[] $archivers
     */
    private static $archivers = array();


    /**
     * Loads Archiver class from any plugin that defines one.
     *
     * @return \Piwik\Plugin\Archiver[]
     */
    protected function getPluginArchivers()
    {
        if (empty(static::$archivers)) {
            $pluginNames = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
            $archivers = array();
            foreach ($pluginNames as $pluginName) {
                $archivers[$pluginName] = self::getPluginArchiverClass($pluginName);
            }
            static::$archivers = array_filter($archivers);
        }
        return static::$archivers;
    }

    private static function getPluginArchiverClass($pluginName)
    {
        $klassName = 'Piwik\\Plugins\\' . $pluginName . '\\Archiver';
        if (class_exists($klassName)
            && is_subclass_of($klassName, 'Piwik\\Plugin\\Archiver')) {
            return $klassName;
        }
        return false;
    }

    /**
     * Whether the specified plugin's reports should be archived
     * @param string $pluginName
     * @return bool
     */
    protected function shouldProcessReportsForPlugin($pluginName)
    {
        if (Rules::shouldProcessReportsAllPlugins($this->params->getSegment(), $this->params->getPeriod()->getLabel())) {
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

    /**
     * @param $archiveWriter
     * @return ArchiveProcessor
     */
    protected function makeArchiveProcessor($archiveWriter)
    {
        $archiveProcessor = new ArchiveProcessor($this->params, $archiveWriter, $this->getNumberOfVisits(), $this->getNumberOfVisitsConverted());

        if (!$this->isDayArchive()) {
            $subPeriods = $this->params->getPeriod()->getSubperiods();
            $archiveProcessor->archive = Archive::factory($this->params->getSegment(), $subPeriods, array($this->params->getSite()->getId()));
        }
        return $archiveProcessor;
    }
}
