<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\ArchiveProcessor;

use Piwik\ArchiveProcessor;
use Piwik\Common;
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

    public function __construct(Parameters $params, $isTemporaryArchive, ArchiveWriter $archiveWriter = null)
    {
        $this->params = $params;
        $this->isTemporaryArchive = $isTemporaryArchive;
        $this->archiveWriter = $archiveWriter ?: new ArchiveWriter($this->params, $this->isTemporaryArchive);
        $this->archiveWriter->initNewArchive();

        $this->logAggregator = new LogAggregator($params);

        $this->archiveProcessor = new ArchiveProcessor($this->params, $this->archiveWriter, $this->logAggregator);

        $this->isSingleSiteDayArchive = $this->params->isSingleSiteDayArchive();
    }

    /**
     * If period is day, will get the core metrics (including visits) from the logs.
     * If period is != day, will sum the core metrics from the existing archives.
     * @return array Core metrics
     */
    public function callAggregateCoreMetrics()
    {
        $this->logAggregator->setQueryOriginHint('Core');

        if ($this->isSingleSiteDayArchive) {
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
        Log::debug("PluginsArchiver::%s: Initializing archiving process for all plugins [visits = %s, visits converted = %s]",
            __FUNCTION__, $visits, $visitsConverted);

        $this->archiveProcessor->setNumberOfVisits($visits, $visitsConverted);

        $archivers = static::getPluginArchivers();

        foreach ($archivers as $pluginName => $archiverClass) {
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
                    $timer = new Timer();
                    if ($this->isSingleSiteDayArchive) {
                        Log::debug("PluginsArchiver::%s: Archiving day reports for plugin '%s'.", __FUNCTION__, $pluginName);

                        $archiver->aggregateDayReport();
                    } else {
                        Log::debug("PluginsArchiver::%s: Archiving period reports for plugin '%s'.", __FUNCTION__, $pluginName);

                        $archiver->aggregateMultipleReports();
                    }

                    $this->logAggregator->setQueryOriginHint('');

                    Log::debug("PluginsArchiver::%s: %s while archiving %s reports for plugin '%s' %s.",
                        __FUNCTION__,
                        $timer->getMemoryLeak(),
                        $this->params->getPeriod()->getLabel(),
                        $pluginName,
                        $this->params->getSegment() ? sprintf("(for segment = '%s')", $this->params->getSegment()->getString()) : ''
                    );
                } catch (Exception $e) {
                    $className = get_class($e);

                    if ($className === 'PHPUnit_Framework_Exception' || (class_exists('PHPUnit_Framework_Exception', false) &&  is_subclass_of($className, 'PHPUnit_Framework_Exception'))) {
                        $exception = new $className($e->getMessage() . " - caused by plugin $pluginName", $e->getCode(), $e->getFile(), $e->getLine(), $e);
                    } else {
                        $exception = new $className($e->getMessage() . " - caused by plugin $pluginName", $e->getCode(), $e);
                    }

                    throw $exception;
                }
            } else {
                Log::debug("PluginsArchiver::%s: Not archiving reports for plugin '%s'.", __FUNCTION__, $pluginName);
            }

            Manager::getInstance()->deleteAll($latestUsedTableId);
            unset($archiver);
        }
    }

    public function finalizeArchive()
    {
        $this->params->logStatusDebug($this->archiveWriter->isArchiveTemporary);
        $this->archiveWriter->finalizeArchive();
        return $this->archiveWriter->getIdArchive();
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
        if ($this->params->getRequestedPlugin() == $pluginName) {
            return true;
        }
        if (Rules::shouldProcessReportsAllPlugins(
            $this->params->getIdSites(),
            $this->params->getSegment(),
            $this->params->getPeriod()->getLabel())) {
            return true;
        }

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginLoaded($this->params->getRequestedPlugin())) {
            return true;
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
        $archiver = new $archiverClass($this->archiveProcessor);

        /**
         * Triggered right after a new **plugin archiver instance** is created.
         * Subscribers to this event can configure the plugin archiver, for example prevent the archiving of a plugin's data
         * by calling `$archiver->disable()` method.
         *
         * @param \Piwik\Plugin\Archiver &$archiver The newly created plugin archiver instance.
         * @param string $pluginName The name of plugin of which archiver instance was created.
         * @param array $this->params Array containing archive parameters (Site, Period, Date and Segment)
         * @param bool $this->isTemporaryArchive Flag indicating whether the archive being processed is temporary (ie. the period isn't finished yet) or final (the period is already finished and in the past).
         */
        Piwik::postEvent('Archiving.makeNewArchiverObject', array($archiver, $pluginName, $this->params, $this->isTemporaryArchive));

        return $archiver;
    }
}
