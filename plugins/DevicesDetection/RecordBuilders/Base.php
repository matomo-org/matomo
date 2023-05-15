<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config as PiwikConfig;
use Piwik\DataTable;
use Piwik\Metrics;

abstract class Base extends RecordBuilder
{
    /**
     * @var string
     */
    private $recordName;

    /**
     * @var string
     */
    private $labelSql;

    /**
     * @var bool
     */
    private $enrichWithConversionMetrics;

    public function __construct(string $recordName, string $labelSql, bool $enrichWithConversionMetrics = false)
    {
        parent::__construct();

        $this->recordName = $recordName;
        $this->labelSql = $labelSql;

        $this->maxRowsInTable = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->maxRowsInSubtable = $this->maxRowsInTable;
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
        $this->enrichWithConversionMetrics = true;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor)
    {
        return [
            Record::make(Record::TYPE_BLOB, $this->recordName),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor)
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $report = new DataTable();

        $query = $logAggregator->queryVisitsByDimension([$this->labelSql]);
        while ($row = $query->fetch()) {
            $report->sumRowWithLabel($row['label'], $row);
        }

        if ($this->enrichWithConversionMetrics) {
            $labelSql = str_replace('log_visit.', 'log_conversion.', $this->labelSql);

            $query = $logAggregator->queryConversionsByDimension([$labelSql]);
            while ($conversionRow = $query->fetch()) {
                $label = $conversionRow[$labelSql] ?? null;
                $report->sumRowWithLabel($label, $conversionRow);
            }

            $report->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);
        }

        return [$this->recordName => $report];
    }
}