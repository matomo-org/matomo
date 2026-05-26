<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Columns\Dimension;
use Piwik\Metrics;

/**
 * Classifies a metric for the evolution-graph forecast pipeline. Owns two related decisions:
 *
 * - intra-period monotonicity, used by {@see ForecastBuilder} to pick the "forecast vs current"
 *   gate (additive count, running min, or free-moving ratio);
 * - rendered payload precision, used to round the forecast value to a sensible number of decimals.
 *
 * Pure value logic — no dependency on the chart's data table or the inner API request. Tests
 * pass a fixed `$semanticTypes` map into the constructor to avoid seeding the global transient
 * cache that backs {@see Metrics::getDefaultMetricSemanticTypes()}.
 */
class ForecastMetricClassifier
{
    public const MONOTONICITY_UP = 'up';
    public const MONOTONICITY_DOWN = 'down';
    public const MONOTONICITY_FREE = 'free';
    public const MONOTONICITY_MAX = 'max';

    /** @var array<string, string> */
    private $semanticTypes;

    /**
     * @param array<string, string>|null $semanticTypes Semantic-type map (column name → one of
     *        the {@see Dimension}::TYPE_* constants). Pass null in production to use Matomo's
     *        default registry; pass a fixed map in tests.
     */
    public function __construct(?array $semanticTypes = null)
    {
        $this->semanticTypes = $semanticTypes ?? Metrics::getDefaultMetricSemanticTypes();
    }

    /**
     * Classify a column into one of three intra-period directions:
     *
     * - MONOTONICITY_UP: counts/sums/totals that can only grow within the period
     *   ("forecast >= current" gate applies).
     * - MONOTONICITY_DOWN: running mins that can only fall within the period
     *   ("forecast <= current" gate applies).
     * - MONOTONICITY_MAX: running maxes that can only rise within the period (same
     *   "forecast >= current" gate as UP), but whose period value is the max -- not the sum --
     *   of its sub-periods, so the seasonal decomposition combines sub-periods with max().
     * - MONOTONICITY_FREE: ratios, rates, percentages, averages whose value can move in either
     *   direction within the period (no gate).
     *
     * Driven by column unit, semantic type, and a small name-convention layer. The convention
     * layer cannot disambiguate metrics whose names look like counts but are actually ratios
     * (ctr, position, web-vitals percentiles); those need a dedicated plugin signal.
     *
     * @param string|false $columnUnit
     * @return self::MONOTONICITY_*
     */
    public function getColumnMonotonicity(string $columnName, $columnUnit): string
    {
        if ($columnUnit === '%') {
            return self::MONOTONICITY_FREE;
        }

        // TYPE_PERCENT and TYPE_FLOAT are non-monotonic by construction (a percentage's or a
        // ratio's value can move in either direction within a partial period). Plugins extend
        // the semantic-type map via the Metrics.getDefaultMetricSemanticTypes event, so a
        // custom metric declared as TYPE_PERCENT or TYPE_FLOAT classifies correctly without
        // needing a magic name.
        $semanticType = $this->semanticTypes[$columnName] ?? null;
        if ($semanticType === Dimension::TYPE_PERCENT || $semanticType === Dimension::TYPE_FLOAT) {
            return self::MONOTONICITY_FREE;
        }

        // Name-pattern fallback for metrics whose semantic type is the ambiguous TYPE_NUMBER
        // but whose name reveals ratio shape (e.g. nb_actions_per_visit is TYPE_NUMBER yet
        // genuinely non-monotonic). The avg_ prefix also disambiguates TYPE_DURATION_*/TYPE_BYTE
        // averages from their additive sum_ siblings.
        if ($this->hasRatioShapedColumnName($columnName)) {
            return self::MONOTONICITY_FREE;
        }

        // min_* metrics carry a structural invariant: more samples within the period can only
        // pull the running min down or leave it unchanged. The default monotonic-up gate would
        // render upward-projecting forecasts on a metric that cannot rise, so flip to a
        // monotonic-down gate instead.
        if (strpos($columnName, 'min_') === 0) {
            return self::MONOTONICITY_DOWN;
        }

        // max_* metrics (max_actions, max_event_value, …) are the mirror of min_*: the period
        // value is the max over its sub-periods, not their sum. The default UP path would
        // SUM the per-day maxes (≈ days × per-day max), inflating the forecast by an order of
        // magnitude. The "forecast >= current" gate still holds (a running max only rises),
        // so MAX shares UP's gate but combines sub-periods with max() instead of sum().
        if (strpos($columnName, 'max_') === 0) {
            return self::MONOTONICITY_MAX;
        }

        // Default unknown metrics to monotonic-up count behaviour. The "forecast >= current"
        // gate then suppresses obviously-wrong forecasts on metrics whose semantics we cannot
        // classify, which is safer than emitting a downward forecast on a metric that turns
        // out to be additive (visits, conversions, revenue, …).
        return self::MONOTONICITY_UP;
    }

    /**
     * Derive conservative raw forecast payload precision for a metric.
     *
     * Integer/count-like metrics should not emit fractional forecast values. Ratios, averages,
     * durations, money, bytes, floats, and unknown numeric metrics keep up to two decimals.
     *
     * MONOTONICITY_UP, MONOTONICITY_DOWN, and MONOTONICITY_MAX are all treated as "monotonic"
     * for precision — a running min_ or max_ count metric should round to integers the same way
     * an additive nb_ count does. Only MONOTONICITY_FREE (ratios/averages/percentages) keeps the
     * two-decimal default for TYPE_NUMBER metrics, which is the original allowsDownward = true
     * behaviour.
     *
     * @param string|false $columnUnit
     * @param self::MONOTONICITY_* $monotonicity
     */
    public function getForecastPrecisionForColumn(string $columnName, $columnUnit, string $monotonicity): int
    {
        if ($columnUnit !== false) {
            return 2;
        }

        $semanticType = $this->semanticTypes[$columnName] ?? null;

        if (
            in_array($semanticType, [
                Dimension::TYPE_BYTE,
                Dimension::TYPE_DURATION_MS,
                Dimension::TYPE_DURATION_S,
                Dimension::TYPE_FLOAT,
                Dimension::TYPE_MONEY,
                Dimension::TYPE_PERCENT,
            ], true)
        ) {
            return 2;
        }

        // Word-boundary check: an underscore-delimited "time"/"length" segment in the column
        // name signals a duration- or length-shaped metric (sum_time_spent, time_per_action,
        // nb_visit_length, length_score). Anchored substring matches avoid false positives on
        // unrelated names that happen to contain the literal letters (lifetime_*, wavelength).
        if (
            $this->hasRatioShapedColumnName($columnName)
            || strpos($columnName, '_time') !== false
            || strpos($columnName, 'time_') === 0
            || strpos($columnName, '_length') !== false
            || strpos($columnName, 'length_') === 0
        ) {
            return 2;
        }

        if ($semanticType === Dimension::TYPE_NUMBER && $monotonicity !== self::MONOTONICITY_FREE) {
            return 0;
        }

        if (
            strpos($columnName, 'nb_') === 0
            || strpos($columnName, '_nb_') !== false
            || strpos($columnName, '_count') !== false
            || in_array($columnName, ['hits', 'items', 'quantity', 'orders', 'goals'], true)
        ) {
            return 0;
        }

        return 2;
    }

    /**
     * True when a column name carries one of the ratio/average/rate name patterns Matomo uses
     * for non-monotonic metrics. Shared between the monotonicity classifier and the forecast
     * precision picker so the two cannot drift on the same set of name fragments.
     */
    private function hasRatioShapedColumnName(string $columnName): bool
    {
        return strpos($columnName, '_rate') !== false
            || strpos($columnName, '_percentage') !== false
            || strpos($columnName, 'avg_') === 0
            || strpos($columnName, '_per_') !== false;
    }
}
