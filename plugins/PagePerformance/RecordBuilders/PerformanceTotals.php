<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Plugins\PagePerformance\Archiver;
use Piwik\Plugins\PagePerformance\Columns\Base;
use Piwik\Plugins\PagePerformance\Columns\TimeDomCompletion;
use Piwik\Plugins\PagePerformance\Columns\TimeDomProcessing;
use Piwik\Plugins\PagePerformance\Columns\TimeNetwork;
use Piwik\Plugins\PagePerformance\Columns\TimeOnLoad;
use Piwik\Plugins\PagePerformance\Columns\TimeServer;
use Piwik\Plugins\PagePerformance\Columns\TimeTransfer;

class PerformanceTotals extends RecordBuilder
{
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_HITS),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_SERVER_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_SERVER_HITS),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME),
            Record::make(Record::TYPE_NUMERIC, Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $totals = [];

        $selects = $totalColumns = $allColumns = [];
        $table  = 'log_link_visit_action';

        /**
         * @var Base[] $performanceDimensions
         */
        $performanceDimensions = [
            new TimeNetwork(),
            new TimeServer(),
            new TimeTransfer(),
            new TimeDomProcessing(),
            new TimeDomCompletion(),
            new TimeOnLoad()
        ];

        foreach ($performanceDimensions as $dimension) {
            $column = $dimension->getColumnName();
            $selects[] = "sum(" . sprintf($dimension->getSqlCappedValue(), $table . '.' . $column) . ") as {$column}_total";
            $selects[] = "sum(if($table.$column is null, 0, 1)) as {$column}_hits";
            $totalColumns[] = sprintf($dimension->getSqlCappedValue(), $table . '.' . $column);
            $allColumns[]  = "$table.$column";
        }

        $selects[] = sprintf('SUM(%s) as page_load_total', implode(' + ', $totalColumns));
        $selects[] = "count($table.idlink_va) as page_load_hits";

        $joinLogActionOnColumn = array('idaction_url');
        $where = sprintf("COALESCE(%s) IS NOT NULL", implode(',', $allColumns));

        $query = $logAggregator->queryActionsByDimension(
            [],
            $where,
            $selects,
            false,
            null,
            $joinLogActionOnColumn,
            null,
            -1
        );

        $result = $query->fetchAll();

        $totals[Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_TIME] = $this->sumMetric($result, 'time_network_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_NETWORK_HITS] = $this->sumMetric($result, 'time_network_hits');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_SERVER_TIME] = $this->sumMetric($result, 'time_server_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_SERVER_HITS] = $this->sumMetric($result, 'time_server_hits');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_TIME] = $this->sumMetric($result, 'time_transfer_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_TRANSFER_HITS] = $this->sumMetric($result, 'time_transfer_hits');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME] = $this->sumMetric($result, 'time_dom_processing_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS] = $this->sumMetric($result, 'time_dom_processing_hits');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME] = $this->sumMetric($result, 'time_dom_completion_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS] = $this->sumMetric($result, 'time_dom_completion_hits');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_TIME] = $this->sumMetric($result, 'time_on_load_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_ONLOAD_HITS] = $this->sumMetric($result, 'time_on_load_hits');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME] = $this->sumMetric($result, 'page_load_total');
        $totals[Archiver::PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS] = $this->sumMetric($result, 'page_load_hits');

        return $totals;
    }

    private function sumMetric(array $result, string $field): int
    {
        $total = 0;

        foreach ($result as $row) {
            if (!empty($row[$field])) {
                $total += (int) $row[$field];
            }
        }

        return $total;
    }
}
