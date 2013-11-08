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
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Metrics;
use Piwik\Plugin\Archiver;

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
     * @var Archiver[] $archivers
     */
    private static $archivers = array();

    public function __construct(ArchiveWriter $archiveWriter, Parameters $params)
    {
        $this->params = $params;
        $this->archiveProcessor = $this->makeArchiveProcessor($archiveWriter);
    }

    /**
     * If period is day, will get the core metrics (including visits) from the logs.
     * If period is != day, will sum the core metrics from the existing archives.
     * @return array Core metrics
     */
    public function callAggregateCoreMetrics()
    {
        if($this->params->isDayArchive()) {
            $metrics = $this->aggregateDayVisitsMetrics();
        } else {
            $metrics = $this->aggregateMultipleVisitsMetrics();
        }

        if (empty($metrics)) {
            return array(
                'nb_visits' => 0,
                'nb_visits_converted' => 0
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
     *
     */
    public function callAggregateAllPlugins()
    {
        $isAggregateForDay = $this->archiveProcessor->getParams()->isDayArchive();

        $archivers = $this->getPluginArchivers();

        foreach($archivers as $pluginName => $archiverClass) {
            /** @var Archiver $archiver */
            $archiver = new $archiverClass($this->archiveProcessor);

            if($this->shouldProcessReportsForPlugin($pluginName)) {
                if($isAggregateForDay) {
                    $archiver->aggregateDayReport();
                } else {
                    $archiver->aggregateMultipleReports();
                }
            }
        }
    }

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
        // If any other segment, only process if the requested report belong to this plugin
        if ($this->params->getRequestedPlugin() == $pluginName) {
            return true;
        }
        if (Rules::shouldProcessReportsAllPlugins(
                            $this->archiveProcessor->getParams()->getSegment(),
                            $this->archiveProcessor->getParams()->getPeriod()->getLabel())) {
            return true;
        }
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginLoaded($this->params->getRequestedPlugin())) {
            return true;
        }
        return false;
    }


    /**
     * @param $archiveWriter
     * @return ArchiveProcessor
     */
    protected function makeArchiveProcessor($archiveWriter)
    {
        $archiveProcessor = new ArchiveProcessor($this->params, $archiveWriter);

        if (!$this->params->isDayArchive()) {
            $subPeriods = $this->params->getPeriod()->getSubperiods();
            $archiveProcessor->archive = Archive::factory($this->params->getSegment(), $subPeriods, array($this->params->getSite()->getId()));
        }
        return $archiveProcessor;
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

        if ($metrics['nb_visits'] > 0) {
            ArchiveSelector::purgeOutdatedArchives($this->params->getPeriod()->getDateStart());
        }
        return $metrics;
    }

}