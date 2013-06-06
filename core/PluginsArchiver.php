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

    public function __construct(Piwik_ArchiveProcessing $processing)
    {
        $this->processor = $processing;
    }

    abstract public function archiveDay();

    abstract public function archivePeriod();

    public function shouldArchive()
    {
        $pluginName = Piwik::unprefixClass(get_class($this));
        $pluginName = str_replace("_Archiver", "", $pluginName);
        return $this->getProcessor()->shouldProcessReportsForPlugin($pluginName);
    }

    /**
     * @return Piwik_ArchiveProcessing_Day|Piwik_ArchiveProcessing_Period
     */
    protected function getProcessor()
    {
        return $this->processor;
    }
}