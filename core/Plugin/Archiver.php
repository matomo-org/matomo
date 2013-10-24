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
use Piwik\Config as PiwikConfig;

/**
 * The base class that should be extended by plugins that archive their own
 * metrics.
 * 
 * ### Examples
 * 
 * **Extending Archiver**
 * 
 *     class MyArchiver extends Archiver
 *     {
 *         public function archiveDay()
 *         {
 *             $logAggregator = $this->getLogAggregator();
 *             
 *             $data = $logAggregator->queryVisitsByDimension(...);
 *             
 *             $dataTable = new DataTable();
 *             $dataTable->addRowsFromSimpleArray($data);
 * 
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->insertBlobRecords('MyPlugin_myReport', $dataTable->getSerialized(500));
 *         }
 *         
 *         public function archivePeriod()
 *         {
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->aggregateDataTableReports('MyPlugin_myReport', 500);
 *         }
 *     }
 * 
 * **Using Archiver in archiving events**
 * 
 *     // event observer for ArchiveProcessor.Day.compute
 *     public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
 *     {
 *         $archiving = new Archiver($archiveProcessor);
 *         if ($archiving->shouldArchive()) {
 *             $archiving->archiveDay();
 *         }
 *     }
 * 
 *     // event observer for ArchiveProcessor.Period.compute
 *     public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
 *     {
 *         $archiving = new Archiver($archiveProcessor);
 *         if ($archiving->shouldArchive()) {
 *             $archiving->archivePeriod();
 *         }
 *     }
 * 
 * @api
 */
abstract class Archiver
{
    protected $processor;

    /**
     * Constructor.
     * 
     * @param ArchiveProcessor $processing The ArchiveProcessor instance sent to the archiving
     *                                     event observer.
     */
    public function __construct(ArchiveProcessor $processing)
    {
        $this->maximumRows = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->processor = $processing;
    }

    /**
     * Archive data for a day period.
     */
    abstract public function archiveDay();

    /**
     * Archive data for a non-day period.
     */
    abstract public function archivePeriod();

    // todo: review this concept, each plugin should somehow maintain the list of report names they generate
    /**
     * Returns true if the current plugin should be archived or not.
     * 
     * @return bool
     */
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