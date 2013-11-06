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

use Piwik\ArchiveProcessor;
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
    protected $archiveProcessor;

    /**
     * @var Archiver[] $archivers
     */
    private static $archivers = array();

    public function __construct(ArchiveProcessor $archiveProcessor)
    {
        $this->archiveProcessor = $archiveProcessor;
    }

    public function callPluginsAggregate()
    {
        $pluginBeingProcessed = $this->archiveProcessor->getParams()->getRequestedPlugin();
        $isAggregateForDay = $this->archiveProcessor->getParams()->isDayArchive();
        $archivers = $this->getPluginArchivers();

        foreach($archivers as $pluginName => $archiverClass) {
            /** @var Archiver $archiver */
            $archiver = new $archiverClass($this->archiveProcessor);

            if($this->shouldProcessReportsForPlugin($pluginBeingProcessed, $pluginName)) {
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
    protected function shouldProcessReportsForPlugin($pluginBeingProcessed, $pluginName)
    {
        // If any other segment, only process if the requested report belong to this plugin
        if ($pluginBeingProcessed == $pluginName) {
            return true;
        }
        if (Rules::shouldProcessReportsAllPlugins(
                            $this->archiveProcessor->getParams()->getSegment(),
                            $this->archiveProcessor->getParams()->getPeriod()->getLabel())) {
            return true;
        }
        if (!\Piwik\Plugin\Manager::getInstance()->isPluginLoaded($pluginBeingProcessed)) {
            return true;
        }
        return false;
    }

}