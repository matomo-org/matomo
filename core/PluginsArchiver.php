<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_PluginArchiver
 */

/**
 * Plugins that archive metrics for websites can implement an Archiver that extends this class
 */
abstract class Piwik_PluginsArchiver
{
    protected $processor;

    public function __construct(Piwik_ArchiveProcessor $processing)
    {
        $this->maximumRows = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->processor = $processing;
    }

    abstract public function archiveDay();

    abstract public function archivePeriod();

    // TODO: Review this concept / each plugin should somehow maintain the list of report names they generate
    public function shouldArchive()
    {
        $pluginName = Piwik::unprefixClass(get_class($this));
        $pluginName = str_replace("_Archiver", "", $pluginName);
        return $this->getProcessor()->shouldProcessReportsForPlugin($pluginName);
    }

    /**
     * @return Piwik_ArchiveProcessor_Day|Piwik_ArchiveProcessor_Period
     */
    protected function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return Piwik_DataAccess_LogAggregator
     */
    protected function getLogAggregator()
    {
        return $this->getProcessor()->getLogAggregator();
    }
}