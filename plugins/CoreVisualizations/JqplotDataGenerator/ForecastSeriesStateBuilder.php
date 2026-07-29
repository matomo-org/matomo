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
 * Aggregates per-series records into a {@see ForecastSeriesState}. Owns the six parallel
 * accumulator arrays so the caller's row × column collection loop does not have to thread
 * them as a six-ref-argument lockstep group, and exposes two distinct shapes:
 *
 * - {@see self::addDataOnlySeries()} for the forecast-off hot path, which only seeds the
 *   chart-rendering data and skips the classifier-driven forecast-only fields. Used on every
 *   dashboard evolution graph that does not have the forecast toggle enabled, so keeping the
 *   shape distinct (rather than passing nulls/defaults for the unused fields) makes the cold
 *   path's lower cost visible in the type signature.
 * - {@see self::addForecastSeries()} for the forecast-on path, which seeds the full per-series
 *   state the builder and sub-period fetcher consume.
 *
 * Build the state once at the end via {@see self::build()}; the builder is single-use.
 */
class ForecastSeriesStateBuilder
{
    /** @var array<string, array<int, float|int>> */
    private $allSeriesData = [];

    /** @var array<string, array<int, bool>> */
    private $allSeriesDataAvailability = [];

    /** @var array<string, string> */
    private $allSeriesMonotonicity = [];

    /** @var array<string, int> */
    private $allSeriesForecastPrecision = [];

    /** @var array<string, string> */
    private $allSeriesColumns = [];

    /** @var array<string, mixed> */
    private $allSeriesRows = [];

    /**
     * Record a series for which only the chart-rendering data has been collected (forecast
     * disabled). The forecast-only fields stay absent from the resulting state -- consumers
     * fall back to their default behaviour when the series label is missing from those maps.
     *
     * @param array<int, float|int> $seriesData
     */
    public function addDataOnlySeries(string $seriesLabel, array $seriesData): void
    {
        $this->allSeriesData[$seriesLabel] = $seriesData;
    }

    /**
     * Record a series with its full per-series state (data + the classifier-driven
     * monotonicity, precision, and row/column identifiers the builder and sub-period
     * fetcher consume).
     *
     * @param array<int, float|int> $seriesData
     * @param array<int, bool> $seriesDataAvailability
     * @param mixed $rowLabel
     */
    public function addForecastSeries(
        string $seriesLabel,
        array $seriesData,
        array $seriesDataAvailability,
        string $monotonicity,
        int $forecastPrecision,
        string $columnName,
        $rowLabel
    ): void {
        $this->allSeriesData[$seriesLabel] = $seriesData;
        $this->allSeriesDataAvailability[$seriesLabel] = $seriesDataAvailability;
        $this->allSeriesMonotonicity[$seriesLabel] = $monotonicity;
        $this->allSeriesForecastPrecision[$seriesLabel] = $forecastPrecision;
        $this->allSeriesColumns[$seriesLabel] = $columnName;
        $this->allSeriesRows[$seriesLabel] = $rowLabel;
    }

    public function build(): ForecastSeriesState
    {
        return new ForecastSeriesState(
            $this->allSeriesData,
            $this->allSeriesDataAvailability,
            $this->allSeriesMonotonicity,
            $this->allSeriesForecastPrecision,
            $this->allSeriesColumns,
            $this->allSeriesRows
        );
    }
}
