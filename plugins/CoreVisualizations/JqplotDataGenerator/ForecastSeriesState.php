<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

/**
 * Per-series state collected for forecast computation. Groups the parallel arrays the
 * data generator builds so they can be passed and cached as a single value instead of as
 * separate reference parameters that need to stay in lockstep. Constructed in one shot and
 * read-only thereafter so the lockstep invariant cannot be broken by an out-of-order mutation.
 */
class ForecastSeriesState
{
    /** @var array<string, array<int, float|int>> */
    private $allSeriesData;

    /** @var array<string, array<int, bool>> */
    private $allSeriesDataAvailability;

    /**
     * Per-series intra-period direction tag. Values are one of the
     * {@see ForecastMetricClassifier::MONOTONICITY_*} constants:
     * - MONOTONICITY_UP: counts/sums; gate forecast >= current.
     * - MONOTONICITY_DOWN: running mins; gate forecast <= current.
     * - MONOTONICITY_FREE: ratios/averages; no gate.
     *
     * @var array<string, string>
     */
    private $allSeriesMonotonicity;

    /** @var array<string, int> */
    private $allSeriesForecastPrecision;

    /**
     * Per-series map from the (translated) series label used as the array key in the other
     * parallel maps to the raw archive column name behind it. Needed because sub-period API
     * results are keyed by raw column name (after ReplaceColumnNames runs) while the rest of
     * the forecast pipeline keys by series label.
     *
     * @var array<string, string>
     */
    private $allSeriesColumns;

    /**
     * Per-series map from the (translated) series label to the row label/matcher the
     * displayed-series path passes to {@see DataTable::getRowFromLabel()}. `false` selects
     * the sub-table's first row (single-row reports). Multi-row evolution graphs
     * (selectable_rows) need this so the sub-period sample fetch pulls each series'
     * historical samples from its own row instead of whichever row happens to sort first.
     *
     * @var array<string, mixed>
     */
    private $allSeriesRows;

    /**
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, string> $allSeriesMonotonicity
     * @param array<string, int> $allSeriesForecastPrecision
     * @param array<string, string> $allSeriesColumns
     * @param array<string, mixed> $allSeriesRows
     */
    public function __construct(
        array $allSeriesData,
        array $allSeriesDataAvailability,
        array $allSeriesMonotonicity,
        array $allSeriesForecastPrecision,
        array $allSeriesColumns = [],
        array $allSeriesRows = []
    ) {
        $this->allSeriesData = $allSeriesData;
        $this->allSeriesDataAvailability = $allSeriesDataAvailability;
        $this->allSeriesMonotonicity = $allSeriesMonotonicity;
        $this->allSeriesForecastPrecision = $allSeriesForecastPrecision;
        $this->allSeriesColumns = $allSeriesColumns;
        $this->allSeriesRows = $allSeriesRows;
    }

    /** @return array<string, array<int, float|int>> */
    public function getAllSeriesData(): array
    {
        return $this->allSeriesData;
    }

    /** @return array<string, array<int, bool>> */
    public function getAllSeriesDataAvailability(): array
    {
        return $this->allSeriesDataAvailability;
    }

    /** @return array<string, string> */
    public function getAllSeriesMonotonicity(): array
    {
        return $this->allSeriesMonotonicity;
    }

    /** @return array<string, int> */
    public function getAllSeriesForecastPrecision(): array
    {
        return $this->allSeriesForecastPrecision;
    }

    /** @return array<string, string> */
    public function getAllSeriesColumns(): array
    {
        return $this->allSeriesColumns;
    }

    /** @return array<string, mixed> */
    public function getAllSeriesRows(): array
    {
        return $this->allSeriesRows;
    }
}
