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

namespace Piwik\Plugin;

use Piwik\ArchiveProcessor;
use Piwik\Config;

/**
 * Plugins that archive metrics for websites can implement an Archiver that extends this class
 */
abstract class Archiver
{
    protected $processor;

    /**
     * Constructor
     * @param ArchiveProcessor $processing
     */
    public function __construct(ArchiveProcessor $processing)
    {
        $this->maximumRows = Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->processor = $processing;
    }

    abstract public function archiveDay();

    abstract public function archivePeriod();

    // todo: review this concept, each plugin should somehow maintain the list of report names they generate
    public function shouldArchive()
    {
        $className = get_class($this);
        $pluginName = str_replace(array("Piwik\\Plugins\\", "\\Archiver"), "", $className);
        if (strpos($pluginName, "\\") !== false) {
            throw new \Exception("unexpected plugin name $pluginName in shouldArchive()");
        }
        return $this->getProcessor()->shouldProcessReportsForPlugin($pluginName);
    }

    /**
     * @return \Piwik\ArchiveProcessor\Day|\Piwik\ArchiveProcessor\Period
     */
    protected function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return \Piwik\DataAccess\LogAggregator
     */
    protected function getLogAggregator()
    {
        return $this->getProcessor()->getLogAggregator();
    }
}
