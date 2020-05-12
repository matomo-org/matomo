<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance;

use Piwik\Archive;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePageLoadTime;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeServer;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AverageTimeTransfer;

/**
 * @method static \Piwik\Plugins\PagePerformance\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function get($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, $segment);

        $columns = array(
            Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_HITS,
            Archiver::PAGEPERFORMANCE_TOTAL_SERVER_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_SERVER_HITS,
            Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS,
            Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS,
            Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS,
            Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS,
            Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS,
        );

        $dataTable = $archive->getDataTableFromNumeric($columns);

        $precision = 2;

        $dataTable->filter('ColumnCallbackReplace', [[
                                                         Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_TIME,
                                                         Archiver::PAGEPERFORMANCE_TOTAL_SERVER_TIME,
                                                         Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME,
                                                         Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME,
                                                         Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME,
                                                         Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME,
                                                         Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME,
        ], function($value) { return $value / 1000; }]);

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AverageTimeNetwork::class),
            Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_HITS,
            $precision
        ));

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AverageTimeServer::class),
            Archiver::PAGEPERFORMANCE_TOTAL_SERVER_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_SERVER_HITS,
            $precision
        ));

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AverageTimeTransfer::class),
            Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS,
            $precision
        ));

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AverageTimeDomProcessing::class),
            Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS,
            $precision
        ));

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AverageTimeDomCompletion::class),
            Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS,
            $precision
        ));

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AverageTimeOnLoad::class),
            Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS,
            $precision
        ));

        $dataTable->filter('ColumnCallbackAddColumnQuotient', array(
            $this->getMetricColumn(AveragePageLoadTime::class),
            Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME,
            Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS,
            $precision
        ));

        $dataTable->queueFilter('ColumnDelete', array($columns));

        return $dataTable;
    }

    /**
     * @param string $class
     * @return string
     */
    private function getMetricColumn($class) {
        /** @var ProcessedMetric $metric */
        $metric = new $class();
        return $metric->getName();
    }
}
