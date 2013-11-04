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
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * Initiates the archiving process for **day** periods via the [ArchiveProcessor.Day.compute](#)
 * event.
 *
 * @package Piwik
 * @subpackage ArchiveProcessor
 *
 * @api
 */
class Day extends ArchiveProcessor
{
    protected function aggregateCoreVisitsMetrics()
    {
        $query = $this->getLogAggregator()->queryVisitsByDimension();
        $data = $query->fetch();

        $metrics = $this->convertMetricsIdToName($data);
        $this->insertNumericRecords($metrics);
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

    protected function compute()
    {
        /**
         * Triggered when the archiving process is initiated for a day period.
         *
         * Plugins that compute analytics data should subscribe to this event. The
         * actual archiving logic, however, should not be in the event handler, but
         * in a class that descends from [Archiver](#).
         *
         * To learn more about single day archiving, see the [ArchiveProcessor\Day](#)
         * class.
         *
         * **Example**
         *
         *     public function aggregateDayReport(ArchiveProcessor\Day $archiveProcessor)
         *     {
         *         $archiving = new MyArchiver($archiveProcessor);
         *         if ($archiving->shouldArchive()) {
         *             $archiving->aggregateDayReport();
         *         }
         *     }
         *
         * @param \Piwik\ArchiveProcessor\Day $archiveProcessor
         *                                       The ArchiveProcessor that triggered the event.
         */
        Piwik::postEvent('ArchiveProcessor.aggregateDayReport', array(&$this));
    }
}