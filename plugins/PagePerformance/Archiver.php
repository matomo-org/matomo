<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance;

use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeLatency;
use Piwik\Plugins\PagePerformance\Columns\TimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\TimeTransfer;
use Piwik\Tracker\Action;

/**
 * Class Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const PAGEPERFORMANCE_TOTAL_LATENCY_TIME = 'PagePerformance_latency_time';
    const PAGEPERFORMANCE_TOTAL_LATENCY_HITS = 'PagePerformance_latency_hits';
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
        $selects = [];
        $table  = 'log_link_visit_action';

        $performanceDimensions = [
            new TimeLatency(),
            new TimeTransfer(),
            new TimeDomProcessing(),
            new TimeDomCompletion(),
            new TimeOnLoad()
        ];

        foreach($performanceDimensions as $dimension) {
            $column = $dimension->getColumnName();
            $selects[] = "sum($table.$column) as {$column}_total";
            $selects[] = "sum(                    
                            case when $table.$column is null
                                then 0
                                else 1
                            end
                          ) as {$column}_hits";
        }

        $joinLogActionOnColumn = array('idaction_url');

        $query = $this->getLogAggregator()->queryActionsByDimension([], 'log_action1.type = ' . Action::TYPE_PAGE_URL, $selects, false, null, $joinLogActionOnColumn);

        $result = $query->fetchAll();

        $timeTotal = $hitsTotal = 0;

        $timeTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_LATENCY_TIME, 'time_latency_total');
        $hitsTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_LATENCY_HITS, 'time_latency_hits');
        $timeTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME, 'time_transfer_total');
        $hitsTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS, 'time_transfer_hits');
        $timeTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME, 'time_dom_processing_total');
        $hitsTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS, 'time_dom_processing_hits');
        $timeTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME, 'time_dom_completion_total');
        $hitsTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS, 'time_dom_completion_hits');
        $timeTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME, 'time_on_load_total');
        $hitsTotal += $this->sumAndInsertNumericRecord($result, self::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS, 'time_on_load_hits');

        $this->getProcessor()->insertNumericRecord(self::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME, $timeTotal);
        $this->getProcessor()->insertNumericRecord(self::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS, $hitsTotal / 5);

    }

    /**
     * @param $result
     * @param string $metric
     * @param string $field
     * @return int
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

        return $total;
    }

    public function aggregateMultipleReports()
    {
        $this->getProcessor()->aggregateNumericMetrics(array(
            self::PAGEPERFORMANCE_TOTAL_LATENCY_TIME,
            self::PAGEPERFORMANCE_TOTAL_LATENCY_HITS,
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
