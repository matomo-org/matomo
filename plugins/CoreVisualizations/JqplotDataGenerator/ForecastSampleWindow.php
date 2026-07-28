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
 * Per-tick sample window the seasonal-decomposition helpers in {@see ForecastBuilder} pass
 * around. Carries two read-only sample maps (daily + monthly, the running history for the
 * current series) and two write-side projection accumulators (day + month, the values this
 * tick produces that the caller then merges back into the running maps for the next tick's
 * analog walks).
 *
 * Grouping the four maps removes the parameter explosion they would otherwise create across
 * buildForecastValue / buildSeasonalForecastValue / forecast{Week,Month,Year}Seasonal, and
 * makes the read/write asymmetry visible in the method signature (the helpers ask the window
 * for samples and tell it about projections, instead of taking four positional arrays with
 * mixed by-value / by-reference semantics).
 *
 * The window is intentionally per-tick: the caller constructs one with the running sample
 * maps before each tick, lets the helpers populate the projections during the forecast
 * computation, then harvests the projections via getDayProjections() / getMonthProjections()
 * and discards the window. Sample maps are read-only by convention -- there is no
 * "add a sample" mutator -- because feeding projections back into the running history is the
 * caller's responsibility, not the helpers'.
 */
class ForecastSampleWindow
{
    /** @var array<string, float> Read-only daily sample map (Y-m-d → value). */
    private $dailySamples;

    /** @var array<string, float> Read-only monthly sample map (Y-m → value). */
    private $monthlySamples;

    /** @var array<string, float> Day projections produced during this tick (Y-m-d → value). */
    private $dayProjections = [];

    /** @var array<string, float> Month projections produced during this tick (Y-m → value). */
    private $monthProjections = [];

    /**
     * @param array<string, float> $dailySamples
     * @param array<string, float> $monthlySamples
     */
    public function __construct(array $dailySamples, array $monthlySamples)
    {
        $this->dailySamples = $dailySamples;
        $this->monthlySamples = $monthlySamples;
    }

    /** @return array<string, float> */
    public function getDailySamples(): array
    {
        return $this->dailySamples;
    }

    /** @return array<string, float> */
    public function getMonthlySamples(): array
    {
        return $this->monthlySamples;
    }

    public function projectDay(string $anchor, float $value): void
    {
        $this->dayProjections[$anchor] = $value;
    }

    public function projectMonth(string $anchor, float $value): void
    {
        $this->monthProjections[$anchor] = $value;
    }

    /** @return array<string, float> */
    public function getDayProjections(): array
    {
        return $this->dayProjections;
    }

    /** @return array<string, float> */
    public function getMonthProjections(): array
    {
        return $this->monthProjections;
    }
}
