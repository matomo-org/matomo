<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\ArchiveProcessor;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\SettingsPiwik;

/**
 * Estimates the unique visitors of each row of a report whose rows are a dimension of the visit, for the periods
 * set to be estimated. Days store a sketch per row, and longer periods merge them by label.
 *
 * Proof of concept: rows only get `nb_uniq_visitors`, without a flag saying they are estimated, and the summary
 * row of a truncated report gets none.
 *
 * @internal
 */
class RowUniqueVisitorsEstimate
{
    public function __construct(private string $recordName, private string $labelSql)
    {
    }

    public function getSketchesRecordName(): string
    {
        return $this->recordName . '_uniq_visitors_sketches';
    }

    /**
     * Stores the sketch of each row of a day archive, if any period is estimated.
     */
    public function storeDaySketches(ArchiveProcessor $archiveProcessor): void
    {
        // not for several sites (eg Roll-Ups), as for the site totals
        if (
            !SettingsPiwik::isUniqueVisitorsEstimateEnabled()
            || count($archiveProcessor->getParams()->getIdSites()) !== 1
        ) {
            return;
        }

        $sketches = $archiveProcessor->getLogAggregator()->queryUniqueVisitorsSketchesByDimension(
            $this->labelSql,
            SettingsPiwik::getUniqueVisitorsEstimatePrecision()
        );
        if ($sketches === []) {
            return;
        }

        $archiveProcessor->insertBlobRecord(
            $this->getSketchesRecordName(),
            ArchiveProcessor::encodeUniqueVisitorsSketchesByLabel($sketches)
        );
    }

    /**
     * For {@link Record::setAggregatedRecordTransform()}: sets each row's unique visitors from the merged
     * sketches when the period is estimated, and stores the merged sketches when a longer period needs them.
     */
    public function transformAggregatedRecord(DataTable $table, ArchiveProcessor $archiveProcessor): void
    {
        $periodLabel = $archiveProcessor->getParams()->getPeriod()->getLabel();
        $isEstimated = SettingsPiwik::isUniqueVisitorsEstimated($periodLabel);
        $isSketchNeeded = SettingsPiwik::isUniqueVisitorsSketchNeeded($periodLabel);
        if (!$isEstimated && !$isSketchNeeded) {
            return;
        }

        $sketches = $archiveProcessor->aggregateUniqueVisitorsSketchesByLabel($this->getSketchesRecordName());
        if ($sketches === null) {
            return;
        }

        if ($isEstimated) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                $sketch = $sketches[(string) $row->getColumn('label')] ?? null;
                if ($sketch === null) {
                    continue;
                }

                // an estimate can't exceed the visits it was built from
                $visits = (int) $row->getColumn(Metrics::INDEX_NB_VISITS);
                $row->setColumn(Metrics::INDEX_NB_UNIQ_VISITORS, min($sketch->estimate(), $visits));
            }
        }

        if ($isSketchNeeded && $sketches !== []) {
            $archiveProcessor->insertBlobRecord(
                $this->getSketchesRecordName(),
                ArchiveProcessor::encodeUniqueVisitorsSketchesByLabel($sketches)
            );
        }
    }
}
