<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance;

use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\TimeServer;
use Piwik\Plugins\PagePerformance\Columns\TimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\TimeTransfer;

/**
 * Class Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const PAGEPERFORMANCE_TOTAL_NETWORK_TIME = 'PagePerformance_network_time';
    const PAGEPERFORMANCE_TOTAL_NETWORK_HITS = 'PagePerformance_network_hits';
    const PAGEPERFORMANCE_TOTAL_SERVER_TIME = 'PagePerformance_servery_time';
    const PAGEPERFORMANCE_TOTAL_SERVER_HITS = 'PagePerformance_server_hits';
    const PAGEPERFORMANCE_TOTAL_TRANSFER_TIME = 'PagePerformance_transfer_time';
    const PAGEPERFORMANCE_TOTAL_TRANSFER_HITS = 'PagePerformance_transfer_hits';
    const PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME = 'PagePerformance_domprocessing_time';
    const PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS = 'PagePerformance_domprocessing_hits';
    const PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME = 'PagePerformance_domcompletion_time';
    const PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS = 'PagePerformance_domcompletion_hits';
    const PAGEPERFORMANCE_TOTAL_ONLOAD_TIME = 'PagePerformance_onload_time';
    const PAGEPERFORMANCE_TOTAL_ONLOAD_HITS = 'PagePerformance_onload_hits';
    const PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME = 'PagePerformance_pageload_time';
    const PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS = 'PagePerformance_pageload_hits';

    public function aggregateDayReport()
    {
        $selects = $totalColumns = $hitsColumns = $allColumns = [];
        $table  = 'log_link_visit_action';


        $performanceDimensions = [
            new TimeNetwork(),
            new TimeServer(),
            new TimeTransfer(),
            new TimeDomProcessing(),
            new TimeDomCompletion(),
            new TimeOnLoad()
        ];

        foreach($performanceDimensions as $dimension) {
            $column = $dimension->getColumnName();
            $selects[] = "sum($table.$column) as {$column}_total";
            $selects[] = "sum(if($table.$column is null, 0, 1)) as {$column}_hits";
            $totalColumns[] = "IFNULL($table.$column,0)";
            $hitsColumns[] = "if($table.$column is null, 0, 1)";
            $allColumns[]  = "$table.$column";
        }

        $selects[] = sprintf('SUM(%s) as page_load_total', implode(' + ', $totalColumns));
        $selects[] = "count($table.idlink_va) as page_load_hits";

        $joinLogActionOnColumn = array('idaction_url');
        $where = sprintf("COALESCE(%s) IS NOT NULL", implode(',', $allColumns));

        $query = $this->getLogAggregator()->queryActionsByDimension([], $where, $selects, false, null,
          $joinLogActionOnColumn, null, -1);

        $result = $query->fetchAll();

        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_NETWORK_TIME, 'time_network_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_NETWORK_HITS, 'time_network_hits');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_SERVER_TIME, 'time_server_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_SERVER_HITS, 'time_server_hits');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME, 'time_transfer_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS, 'time_transfer_hits');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME, 'time_dom_processing_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS, 'time_dom_processing_hits');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME, 'time_dom_completion_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS, 'time_dom_completion_hits');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME, 'time_on_load_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS, 'time_on_load_hits');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME, 'page_load_total');
        $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS, 'page_load_hits');
    }

    /**
     * @param $result
     * @param string $metric
     * @param string $field
     */
    private function sumAndInsertNumericRecord($result, $metric, $field)
    {
        $total = 0;

        foreach ($result as $row) {
            if (!empty($row[$field])) {
                $total += (int) $row[$field];
            }
        }

        $this->getProcessor()->insertNumericRecord($metric, $total);
    }

    public function aggregateMultipleReports()
    {
        $this->getProcessor()->aggregateNumericMetrics(array(
            self::PAGEPERFORMANCE_TOTAL_NETWORK_TIME,
            self::PAGEPERFORMANCE_TOTAL_NETWORK_HITS,
            self::PAGEPERFORMANCE_TOTAL_SERVER_TIME,
            self::PAGEPERFORMANCE_TOTAL_SERVER_HITS,
            self::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME,
            self::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS,
            self::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME,
            self::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS,
            self::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME,
            self::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS,
            self::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME,
            self::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS,
            self::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME,
            self::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS,
        ));
    }

}
