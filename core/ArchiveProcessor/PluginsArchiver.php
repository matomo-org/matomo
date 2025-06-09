<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\ArchiveProcessor;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\Performance\Logger;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable\Manager;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\Archiver;
use Piwik\Log;
use Piwik\Timer;
use Exception;

/**
 * This class creates the Archiver objects found in plugins and will trigger aggregation,
 * so each plugin can process their reports.
 */
class PluginsArchiver
{
    /**
     * @var string|null
     */
    private static $currentPluginBeingArchived = null;

    /**
     * @param ArchiveProcessor $archiveProcessor
     */
    public $archiveProcessor;

    /**
     * @var Parameters
     */
    protected $params;

    /**
     * @var LogAggregator
     */
    private $logAggregator;

    /**
     * Public only for tests. Won't be necessary after DI changes are complete.
     *
     * @var Archiver[] $archivers
     */
    public static $archivers = array();

    /**
     * Defines if we should aggregate from raw data by using MySQL queries (when true) or aggregate archives (when false)
     * @var bool
     */
    private $shouldAggregateFromRawData;

    /**
     * @var ArchiveWriter
     */
    private $archiveWriter;

    public function __construct(Parameters $params, ?ArchiveWriter $archiveWriter = null)
    {
        $this->params = $params;
        $this->archiveWriter = $archiveWriter ?: new ArchiveWriter($this->params);
        $this->archiveWriter->initNewArchive();

        $this->logAggregator = new LogAggregator($params);
        $this->logAggregator->allowUsageSegmentCache();

        $this->archiveProcessor = new ArchiveProcessor($this->params, $this->archiveWriter, $this->logAggregator);


        $shouldAggregateFromRawData = $this->params->isSingleSiteDayArchive();

        /**
         * Triggered to detect if the archiver should aggregate from raw data by using MySQL queries (when true)
         * or by aggregate archives (when false). Typically, data is aggregated from raw data for "day" period, and
         * aggregregated from archives for all other periods.
         *
         * @param bool $shouldAggregateFromRawData  Set to true, to aggregate from raw data, or false to aggregate multiple reports.
         * @param Parameters $params
         */
        Piwik::postEvent('ArchiveProcessor.shouldAggregateFromRawData', array(&$shouldAggregateFromRawData, $this->params));

        $this->shouldAggregateFromRawData = $shouldAggregateFromRawData;
    }

    /**
     * If period is day, will get the core metrics (including visits) from the logs.
     * If period is != day, will sum the core metrics from the existing archives.
     * @return array Core metrics
     */
    public function callAggregateCoreMetrics()
    {
        $this->logAggregator->cleanup();
        $this->logAggregator->setQueryOriginHint('Core');

        if ($this->shouldAggregateFromRawData) {
            $metrics = $this->aggregateDayVisitsMetrics();
        } else {
            $metrics = $this->aggregateMultipleVisitsMetrics();
        }

        if (empty($metrics)) {
            return array(
                'nb_visits' => false,
                'nb_visits_converted' => false
            );
        }
        return array(
            'nb_visits' => $metrics['nb_visits'],
            'nb_visits_converted' => $metrics['nb_visits_converted']
        );
    }

    /**
     * Instantiates the Archiver class in each plugin that defines it,
     * and triggers Aggregation processing on these plugins.
     */
    public function callAggregateAllPlugins($visits, $visitsConverted, $forceArchivingWithoutVisits = false)
    {
        Log::debug(
            "PluginsArchiver::%s: Initializing archiving process for all plugins [visits = %s, visits converted = %s]",
            __FUNCTION__,
            $visits,
            $visitsConverted
        );

        /** @var Logger $performanceLogger */
        $performanceLogger = StaticContainer::get(Logger::class);

        $this->archiveProcessor->setNumberOfVisits($visits, $visitsConverted);

        $archivers = static::getPluginArchivers();

        $archiveOnlyPlugin = $this->params->getRequestedPlugin();
        $archiveOnlyReports = $this->params->getArchiveOnlyReport();

        foreach ($archivers as $pluginName => $archiverClass) {
            // if we are archiving specific reports for a single plugin then we don't need or want to create
            // Archiver instances, since they will set the archive to partial even if the requested reports aren't
            // handled by the Archiver
            if (
                !empty($archiveOnlyReports)
                && $archiveOnlyPlugin != $pluginName
            ) {
                continue;
            }

            // We clean up below all tables created during this function call (and recursive calls)
            $latestUsedTableId = Manager::getInstance()->getMostRecentTableId();

            /** @var Archiver $archiver */
            $archiver = $this->makeNewArchiverObject($archiverClass, $pluginName);

            if (!$archiver->isEnabled()) {
                Log::debug("PluginsArchiver::%s: Skipping archiving for plugin '%s' (disabled).", __FUNCTION__, $pluginName);
                continue;
            }

            if (!$forceArchivingWithoutVisits && !$visits && !$archiver->shouldRunEvenWhenNoVisits()) {
                Log::debug("PluginsArchiver::%s: Skipping archiving for plugin '%s' (no visits).", __FUNCTION__, $pluginName);
                continue;
            }

            if ($this->shouldProcessReportsForPlugin($pluginName)) {
                $this->logAggregator->setQueryOriginHint($pluginName);

                try {
                    self::$currentPluginBeingArchived = $pluginName;

                    $period = $this->params->getPeriod()->getLabel();

                    $timer = new Timer();
                    if ($this->shouldAggregateFromRawData) {
                        Log::debug("PluginsArchiver::%s: Archiving $period reports for plugin '%s' from raw data.", __FUNCTION__, $pluginName);

                        $archiver->callAggregateDayReport();
                    } else {
                        Log::debug("PluginsArchiver::%s: Archiving $period reports for plugin '%s' using reports for smaller periods.", __FUNCTION__, $pluginName);

                        $archiver->callAggregateMultipleReports();
                    }

                    $this->logAggregator->setQueryOriginHint('');

                    $performanceLogger->logMeasurement('plugin', $pluginName, $this->params, $timer);

                    Log::debug(
                        "PluginsArchiver::%s: %s while archiving %s reports for plugin '%s' %s.",
                        __FUNCTION__,
                        $timer->getMemoryLeak(),
                        $this->params->getPeriod()->getLabel(),
                        $pluginName,
                        $this->params->getSegment() ? sprintf("(for segment = '%s')", $this->params->getSegment()->getString()) : ''
                    );
                } catch (Exception $e) {
                    throw new PluginsArchiverException($e->getMessage() . " - in plugin $pluginName.", $e->getCode(), $e);
                } finally {
                    self::$currentPluginBeingArchived = null;
                }
            } else {
                Log::debug("PluginsArchiver::%s: Not archiving reports for plugin '%s'.", __FUNCTION__, $pluginName);
            }

            Manager::getInstance()->deleteAll($latestUsedTableId);
            unset($archiver);
        }

        $this->logAggregator->cleanup();
    }

    public function finalizeArchive()
    {
        $this->params->logStatusDebug();
        $this->archiveWriter->finalizeArchive();
        $idArchive = $this->archiveWriter->getIdArchive();

        return $idArchive;
    }

    /**
     * Returns if any plugin archiver archives without visits
     */
    public static function doesAnyPluginArchiveWithoutVisits()
    {
        $archivers = static::getPluginArchivers();

        foreach ($archivers as $pluginName => $archiverClass) {
            if ($archiverClass::shouldRunEvenWhenNoVisits()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads Archiver class from any plugin that defines one.
     *
     * @return \Piwik\Plugin\Archiver[]
     */
    protected static function getPluginArchivers()
    {
        if (empty(static::$archivers)) {
            $pluginNames = \Piwik\Plugin\Manager::getInstance()->getActivatedPlugins();
            $archivers = array();
            foreach ($pluginNames as $pluginName) {
                $archivers[$pluginName] = self::getPluginArchiverClass($pluginName);
            }
            static::$archivers = array_filter($archivers);
        }
        return static::$archivers;
    }

    private static function getPluginArchiverClass(string $pluginName): ?string
    {
        $klassName = 'Piwik\\Plugins\\' . $pluginName . '\\Archiver';
        if (
            class_exists($klassName)
            && is_subclass_of($klassName, 'Piwik\\Plugin\\Archiver')
        ) {
            return $klassName;
        }
        if (Archiver::doesPluginHaveRecordBuilders($pluginName)) {
            return Archiver::class;
        }
        return null;
    }

    /**
     * Whether the specified plugin's reports should be archived
     * @param string $pluginName
     * @return bool
     */
    protected function shouldProcessReportsForPlugin($pluginName)
    {
        if ($this->params->getRequestedPlugin() == $pluginName) {
            return true;
        }

        if ($this->params->shouldOnlyArchiveRequestedPlugin()) {
            return false;
        }

        if (
            Rules::shouldProcessReportsAllPlugins(
                [$this->params->getSite()->getId()],
                $this->params->getSegment(),
                $this->params->getPeriod()->getLabel()
            )
        ) {
            return true;
        }

        if (
            $this->params->getRequestedPlugin() &&
            !\Piwik\Plugin\Manager::getInstance()->isPluginLoaded($this->params->getRequestedPlugin())
        ) {
            return false;
        }

        return false;
    }

    protected function aggregateDayVisitsMetrics()
    {
        $query = $this->archiveProcessor->getLogAggregator()->queryVisitsByDimension();
        $data = $query->fetch();

        $metrics = $this->convertMetricsIdToName($data);
        $this->archiveProcessor->insertNumericRecords($metrics);
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

    protected function aggregateMultipleVisitsMetrics()
    {
        $toSum = Metrics::getVisitsMetricNames();
        $metrics = $this->archiveProcessor->aggregateNumericMetrics($toSum);
        return $metrics;
    }


    /**
     * @param $archiverClass
     * @return Archiver
     */
    private function makeNewArchiverObject($archiverClass, $pluginName)
    {
        if ($archiverClass === Archiver::class) {
            $archiver = new Archiver($this->archiveProcessor, $pluginName);
        } else {
            $archiver = new $archiverClass($this->archiveProcessor);
        }

        /**
         * Triggered right after a new **plugin archiver instance** is created.
         * Subscribers to this event can configure the plugin archiver, for example prevent the archiving of a plugin's data
         * by calling `$archiver->disable()` method.
         *
         * @param \Piwik\Plugin\Archiver &$archiver The newly created plugin archiver instance.
         * @param string $pluginName The name of plugin of which archiver instance was created.
         * @param array $this->params Array containing archive parameters (Site, Period, Date and Segment)
         * @param bool false This parameter is deprecated and will be removed.
         */
        Piwik::postEvent('Archiving.makeNewArchiverObject', array($archiver, $pluginName, $this->params, false));

        return $archiver;
    }

    public static function isArchivingProcessActive()
    {
        return self::$currentPluginBeingArchived !== null;
    }
}
