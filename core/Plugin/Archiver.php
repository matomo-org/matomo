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
 *         public function aggregateDayReport()
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
 *         public function aggregateMultipleReports()
 *         {
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->aggregateDataTableRecords('MyPlugin_myReport', 500);
 *         }
 *     }
 * 
 * @api
 */
abstract class Archiver
{
    /**
     * @var \Piwik\ArchiveProcessor
     */
    protected $processor;

    /**
     * Constructor.
     * 
     * @param ArchiveProcessor $aggregator The ArchiveProcessor instance sent to the archiving
     *                                     event observer.
     */
    public function __construct(ArchiveProcessor $aggregator)
    {
        $this->maximumRows = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->processor = $aggregator;
    }

    /**
     * Triggered when the archiving process is initiated for a day period.
     *
     * Plugins that compute analytics data should create an Archiver class that descends from [Plugin\Archiver](#).
     */
    abstract public function aggregateDayReport();

    /**
     * Archive data for a non-day period.
     */
    abstract public function aggregateMultipleReports();

    /**
     * @return \Piwik\ArchiveProcessor
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