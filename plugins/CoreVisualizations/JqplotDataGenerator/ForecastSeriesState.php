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
 * Per-series state collected for forecast computation. Groups the four parallel arrays the
 * data generator builds so they can be passed and cached as a single value instead of four
 * reference parameters that need to stay in lockstep. Constructed in one shot and read-only
 * thereafter so the lockstep invariant cannot be broken by an out-of-order mutation.
 */
class ForecastSeriesState
{
    /** @var array<string, array<int, float|int>> */
    private $allSeriesData;

    /** @var array<string, array<int, bool>> */
    private $allSeriesDataAvailability;

    /**
     * Per-series intra-period direction tag. Values are one of the
     * {@see Evolution::MONOTONICITY_*} constants:
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
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, string> $allSeriesMonotonicity
     * @param array<string, int> $allSeriesForecastPrecision
     */
    public function __construct(
        array $allSeriesData,
        array $allSeriesDataAvailability,
        array $allSeriesMonotonicity,
        array $allSeriesForecastPrecision
    ) {
        $this->allSeriesData = $allSeriesData;
        $this->allSeriesDataAvailability = $allSeriesDataAvailability;
        $this->allSeriesMonotonicity = $allSeriesMonotonicity;
        $this->allSeriesForecastPrecision = $allSeriesForecastPrecision;
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
}
