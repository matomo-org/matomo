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
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Period;

/**
 * This class manages the ArchiveProcessor and
 */
class Loader
{
    /**
     * @var int Cached number of visits cached
     */
    protected $visitsMetricCached = false;

    /**
     * @var int Cached number of visits with conversions
     */
    protected $convertedVisitsMetricCached = false;

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

    public function prepareArchiveId()
    {
        $idArchive = $this->prepareArchive();

        if ($this->isThereSomeVisits()) {
            return $idArchive;
        }
        return false;
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

    protected function getNumberOfVisits()
    {
        return $this->visitsMetricCached;
    }

    protected function getNumberOfVisitsConverted()
    {
        return $this->convertedVisitsMetricCached;
    }

    /**
     * @return bool
     */
    protected function isThereSomeVisits()
    {
        return $this->getNumberOfVisits() > 0;
    }

    /**
     * @return bool
     */
    protected function isVisitsCountAlreadyProcessed()
    {
        return $this->getNumberOfVisits() !== false;
    }

    protected function prepareArchive()
    {
        $idArchive = $this->loadExistingArchiveIdFromDb();
        if (!empty($idArchive)) {
            return $idArchive;
        }
        $this->prepareCoreMetricsArchive();

        return $this->computeNewArchive($enforceProcessCoreMetricsOnly = false);
    }

    protected function prepareCoreMetricsArchive()
    {
        $createSeparateArchiveForCoreMetrics =
            !$this->doesRequestedPluginIncludeVisitsSummary()
            && !$this->isVisitsCountAlreadyProcessed();

        if ($createSeparateArchiveForCoreMetrics) {
            $requestedPlugin = $this->params->getRequestedPlugin();

            $this->params->setRequestedPlugin('VisitsSummary');

            $this->computeNewArchive($enforceProcessCoreMetricsOnly = true);

            $this->params->setRequestedPlugin($requestedPlugin);

            if (!$this->isVisitsCountAlreadyProcessed()) {
                throw new \Exception("prepareArchive() is expected to set number of visits to a numeric value.");
            }
        }
    }


    protected function computeNewArchive($enforceProcessCoreMetricsOnly)
    {
        $isArchiveDay = $this->params->isDayArchive();

        $archiveWriter = new ArchiveWriter($this->params->getSite()->getId(), $this->params->getSegment(), $this->params->getPeriod(), $this->params->getRequestedPlugin(), $this->isArchiveTemporary());
        $archiveWriter->initNewArchive();

        $archiveProcessor = $this->makeArchiveProcessor($archiveWriter);

        if (!$this->isVisitsCountAlreadyProcessed()
            || $this->doesRequestedPluginIncludeVisitsSummary()
            || $enforceProcessCoreMetricsOnly
        ) {

            if($isArchiveDay) {
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
        $this->params->logStatusDebug( $this->isArchiveTemporary() );

        $archiveProcessor = $this->makeArchiveProcessor($archiveWriter);

        if ($this->isThereSomeVisits()
            && !$enforceProcessCoreMetricsOnly
        ) {
            $pluginsArchiver = new PluginsArchiver($archiveProcessor);
            $pluginsArchiver->callPluginsAggregate();
        }

        $archiveWriter->finalizeArchive();

        if ($this->isThereSomeVisits() && !$isArchiveDay) {
            ArchiveSelector::purgeOutdatedArchives($this->params->getPeriod()->getDateStart());
        }

        return $archiveWriter->getIdArchive();
    }

    protected function doesRequestedPluginIncludeVisitsSummary()
    {
        $processAllReportsIncludingVisitsSummary =
                Rules::shouldProcessReportsAllPlugins($this->params->getSegment(), $this->params->getPeriod()->getLabel());
        $doesRequestedPluginIncludeVisitsSummary =
                $processAllReportsIncludingVisitsSummary || $this->params->getRequestedPlugin() == 'VisitsSummary';
        return $doesRequestedPluginIncludeVisitsSummary;
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
     * @return int or false
     */
    protected function loadExistingArchiveIdFromDb()
    {
        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchiveProcessed();
        $site = $this->params->getSite();
        $period = $this->params->getPeriod();
        $segment = $this->params->getSegment();
        $requestedPlugin = $this->params->getRequestedPlugin();

        $idAndVisits = ArchiveSelector::getArchiveIdAndVisits($site, $period, $segment, $minDatetimeArchiveProcessedUTC, $requestedPlugin);
        if (!$idAndVisits) {
            return false;
        }
        list($idArchive, $visits, $visitsConverted) = $idAndVisits;

        if ($this->isArchivingForcedToTrigger()) {
            $idArchive = false;
            $visits = $visitsConverted = false;
        }

        $this->setNumberOfVisits($visits, $visitsConverted);


        return $idArchive;
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
     * @param $archiveWriter
     * @return ArchiveProcessor
     */
    protected function makeArchiveProcessor($archiveWriter)
    {
        $archiveProcessor = new ArchiveProcessor($this->params, $archiveWriter, $this->getNumberOfVisits(), $this->getNumberOfVisitsConverted());

        if (!$this->params->isDayArchive()) {
            $subPeriods = $this->params->getPeriod()->getSubperiods();
            $archiveProcessor->archive = Archive::factory($this->params->getSegment(), $subPeriods, array($this->params->getSite()->getId()));
        }
        return $archiveProcessor;
    }
}

