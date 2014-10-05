<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;
use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\Date;
use Piwik\Period;

/**
 * This class uses PluginsArchiver class to trigger data aggregation and create archives.
 */
class Loader
{
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
     * @return bool
     */
    protected function isThereSomeVisits($visits)
    {
        return $visits > 0;
    }

    /**
     * @return bool
     */
    protected function mustProcessVisitCount($visits)
    {
        return $visits === false;
    }

    public function prepareArchive($pluginName)
    {
        $this->params->setRequestedPlugin($pluginName);

        list($idArchive, $visits, $visitsConverted) = $this->loadExistingArchiveIdFromDb();
        if (!empty($idArchive)) {
            return $idArchive;
        }

        list($visits, $visitsConverted) = $this->prepareCoreMetricsArchive($visits, $visitsConverted);
        list($idArchive, $visits) = $this->prepareAllPluginsArchive($visits, $visitsConverted);

        if ($this->isThereSomeVisits($visits)) {
            return $idArchive;
        }
        return false;
    }

    /**
     * Prepares the core metrics if needed.
     *
     * @param $visits
     * @return array
     */
    protected function prepareCoreMetricsArchive($visits, $visitsConverted)
    {
        $createSeparateArchiveForCoreMetrics = $this->mustProcessVisitCount($visits)
                                && !$this->doesRequestedPluginIncludeVisitsSummary();

        if ($createSeparateArchiveForCoreMetrics) {
            $requestedPlugin = $this->params->getRequestedPlugin();

            $this->params->setRequestedPlugin('VisitsSummary');

            $pluginsArchiver = new PluginsArchiver($this->params, $this->isArchiveTemporary());
            $metrics = $pluginsArchiver->callAggregateCoreMetrics();
            $pluginsArchiver->finalizeArchive();

            $this->params->setRequestedPlugin($requestedPlugin);

            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        return array($visits, $visitsConverted);
    }

    protected function prepareAllPluginsArchive($visits, $visitsConverted)
    {
        $pluginsArchiver = new PluginsArchiver($this->params, $this->isArchiveTemporary());

        if ($this->mustProcessVisitCount($visits)
            || $this->doesRequestedPluginIncludeVisitsSummary()
        ) {
            $metrics = $pluginsArchiver->callAggregateCoreMetrics();
            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        if ($this->isThereSomeVisits($visits)) {
            $pluginsArchiver->callAggregateAllPlugins($visits, $visitsConverted);
        }

        $idArchive = $pluginsArchiver->finalizeArchive();

        return array($idArchive, $visits);
    }

    protected function doesRequestedPluginIncludeVisitsSummary()
    {
        $processAllReportsIncludingVisitsSummary =
                Rules::shouldProcessReportsAllPlugins($this->params->getIdSites(), $this->params->getSegment(), $this->params->getPeriod()->getLabel());
        $doesRequestedPluginIncludeVisitsSummary = $processAllReportsIncludingVisitsSummary
                                                        || $this->params->getRequestedPlugin() == 'VisitsSummary';
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

        return (bool) Config::getInstance()->Debug[$debugSetting];
    }

    /**
     * Returns the idArchive if the archive is available in the database for the requested plugin.
     * Returns false if the archive needs to be processed.
     *
     * @return array
     */
    protected function loadExistingArchiveIdFromDb()
    {
        $noArchiveFound = array(false, false, false);

        // see isArchiveTemporary()
        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchiveProcessed();

        if ($this->isArchivingForcedToTrigger()) {
            return $noArchiveFound;
        }

        $idAndVisits = ArchiveSelector::getArchiveIdAndVisits($this->params, $minDatetimeArchiveProcessedUTC);

        if (!$idAndVisits) {
            return $noArchiveFound;
        }

        return $idAndVisits;
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

        $dateStart = $this->params->getDateStart();
        $period    = $this->params->getPeriod();
        $segment   = $this->params->getSegment();
        $site      = $this->params->getSite();

        // Temporary archive
        return Rules::getMinTimeProcessedForTemporaryArchive($dateStart, $period, $segment, $site);
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

}

