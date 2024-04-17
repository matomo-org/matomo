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
        $this->enrichWithConversionMetrics = $enrichWithConversionMetrics;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, $this->recordName),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        $report = new DataTable();

        $query = $logAggregator->queryVisitsByDimension(['label' => $this->labelSql]);
        while ($row = $query->fetch()) {
            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS => $row[Metrics::INDEX_NB_UNIQ_VISITORS],
                Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                Metrics::INDEX_NB_ACTIONS => $row[Metrics::INDEX_NB_ACTIONS],
                Metrics::INDEX_NB_USERS => $row[Metrics::INDEX_NB_USERS],
                Metrics::INDEX_MAX_ACTIONS => $row[Metrics::INDEX_MAX_ACTIONS],
                Metrics::INDEX_SUM_VISIT_LENGTH => $row[Metrics::INDEX_SUM_VISIT_LENGTH],
                Metrics::INDEX_BOUNCE_COUNT => $row[Metrics::INDEX_BOUNCE_COUNT],
                Metrics::INDEX_NB_VISITS_CONVERTED => $row[Metrics::INDEX_NB_VISITS_CONVERTED],
            ];

            $report->sumRowWithLabel($row['label'] ?? '', $columns);
        }

        if ($this->enrichWithConversionMetrics) {
            $labelSql = str_replace('log_visit.', 'log_conversion.', $this->labelSql);

            $query = $logAggregator->queryConversionsByDimension(['label' => $labelSql]);
            while ($conversionRow = $query->fetch()) {
                $label = $conversionRow['label'] ?? '';

                $idGoal = (int) $conversionRow['idgoal'];
                $columns = [
                    Metrics::INDEX_GOALS => [
                        $idGoal => Metrics::makeGoalColumnsRow($idGoal, $conversionRow),
                    ],
                ];

                $report->sumRowWithLabel($label, $columns);
            }

            $report->filter(DataTable\Filter\EnrichRecordWithGoalMetricSums::class);
        }

        return [$this->recordName => $report];
    }
}
