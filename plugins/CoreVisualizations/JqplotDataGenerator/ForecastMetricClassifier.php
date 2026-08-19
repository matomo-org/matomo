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
 *   gate and the sub-period reducer (additive count, running min, running max, deduplicated
 *   count, or free-moving ratio);
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
    public const MONOTONICITY_UNIQUE = 'unique';

    /**
     * Base names of the deduplicated counts Matomo recomputes per period from the log data
     * instead of aggregating them from the sub-period archives. Matched as substrings, because
     * both the prefixed and the suffixed variants of the same metric carry the same semantics:
     * the entry_/exit_ page variants, and the segment-derived copies plugins publish by appending
     * a suffix to VisitsSummary's own columns (nb_uniq_visitors_new / _returning in VisitFrequency,
     * nb_uniq_visitors_human / _ai_agent in AIAgents) are all the same log-derived count under
     * another name. nb_uniq_fingerprints is the metric core substitutes for nb_uniq_visitors when
     * counting uniques across several sites, so it is the same measurement again.
     *
     * nb_uniq_pageviews, nb_uniq_downloads and nb_uniq_outlinks are deliberately absent: those
     * count the *visits* that included an action, and a visit belongs to a single day, so Matomo
     * archives them as plain summed numeric records and the additive UP path is right for them.
     *
     * @var array<int, string>
     */
    private const DEDUPLICATED_COUNT_BASE_NAMES = [
        'nb_uniq_visitors',
        'nb_uniq_fingerprints',
        'nb_users',
    ];

    /**
     * Deduplicated counts Matomo derives from the aggregated blob rather than from the log data.
     * A record declared with {@see \Piwik\ArchiveProcessor\Record::setIsCountOfBlobRecordRows()}
     * is left out of the numeric sum for a multi-day period and set to the row count of the
     * re-aggregated sub-period tables instead, so a week's value is a distinct-label count over
     * the week. Summing the daily samples overstates these by anything from a few percent (a
     * referrer URL usually appears on one day) to more than an order of magnitude for the ones
     * that count a closed set -- distinct search engines, social networks or AI chatbots stay
     * flat over any period length while the additive path projects toward the daily sum.
     *
     * Matched exactly: these are full column names with no prefixed or suffixed variants, unlike
     * the base names above. The shared "_distinct" fragment of the Referrers and UserCountry
     * entries is a naming coincidence, not a convention -- nb_keywords and the AI chatbot count
     * carry the same mechanism under unrelated names -- so grep for setIsCountOfBlobRecordRows()
     * rather than the name shape when adding an entry.
     *
     * @var array<int, string>
     */
    private const BLOB_ROW_COUNT_METRIC_NAMES = [
        'nb_keywords',
        'UserCountry_distinctCountries',
        'BotTracking_AIChatbotsUniqueChatbots',
        'Referrers_distinctSearchEngines',
        'Referrers_distinctSocialNetworks',
        'Referrers_distinctAIAssistants',
        'Referrers_distinctKeywords',
        'Referrers_distinctCampaigns',
        'Referrers_distinctWebsites',
        'Referrers_distinctWebsitesUrls',
    ];

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
     * Classify a column into one of five intra-period directions:
     *
     * - MONOTONICITY_UP: counts/sums/totals that can only grow within the period
     *   ("forecast >= current" gate applies).
     * - MONOTONICITY_DOWN: running mins that can only fall within the period
     *   ("forecast <= current" gate applies).
     * - MONOTONICITY_MAX: running maxes that can only rise within the period (same
     *   "forecast >= current" gate as UP), but whose period value is the max -- not the sum --
     *   of its sub-periods, so the seasonal decomposition combines sub-periods with max().
     * - MONOTONICITY_UNIQUE: deduplicated counts -- unique visitors and users, and the
     *   distinct-label counts such as site-search keywords or distinct referrers -- that can
     *   only grow within the period (same "forecast >= current" gate as UP), but whose period
     *   value is a fresh count over the whole period rather than any reduction of its
     *   sub-period values, so no sub-period decomposition applies.
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

        // Deduplicated counts have no reducer over their sub-periods at all: a week's unique
        // visitors is a distinct-visitor count over the week, which sits somewhere between the
        // largest single day and the sum of all days -- and the archiver derives it from the log
        // data rather than from the daily archives, which is why aggregating daily archives
        // renames the column to sum_daily_nb_uniq_visitors instead of keeping it. Summing the
        // daily samples (the UP path) therefore overstates the forecast by roughly the
        // deduplication factor, several-fold on a site with returning visitors. The blob-row
        // counts reach the same shape by a different route -- their period value is the row
        // count of the re-aggregated sub-period tables -- so both families share this branch.
        // Runs after the ratio checks so the percentage and average views of the same metric
        // (nb_uniq_visitors_row_percentage, avg_nb_uniq_visitors) keep their FREE classification.
        if (
            $this->hasDeduplicatedCountColumnName($columnName)
            || $this->isBlobRowCountColumnName($columnName)
        ) {
            return self::MONOTONICITY_UNIQUE;
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
     * {@see self::BLOB_ROW_COUNT_METRIC_NAMES} count whole things and are pinned to integers by
     * name as well as by semantic type, so they stay integral for a classifier built without
     * Matomo's registry and if a plugin ever stops declaring one of them.
     *
     * MONOTONICITY_UP, MONOTONICITY_DOWN, MONOTONICITY_MAX, and MONOTONICITY_UNIQUE are all
     * treated as "monotonic" for precision — a running min_ or max_ count metric, and a
     * deduplicated unique count, should round to integers the same way an additive nb_ count
     * does. Only MONOTONICITY_FREE (ratios/averages/percentages) keeps the two-decimal default
     * for TYPE_NUMBER metrics, which is the original allowsDownward = true behaviour.
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

        // Counts of whole things. The blob-row counts are listed by name too: every one of them
        // is registered TYPE_NUMBER today, so the branch above already returns 0 for them, but
        // the name is what keeps a fractional country or URL count from appearing if a plugin
        // stops declaring the type, or when the classifier is built without Matomo's registry.
        // The list carries nine of the ten: nb_keywords already matches the nb_ prefix on the
        // first line, so it reaches 0 either way and is not what the name check is here for.
        if (
            strpos($columnName, 'nb_') === 0
            || strpos($columnName, '_nb_') !== false
            || strpos($columnName, '_count') !== false
            || $this->isBlobRowCountColumnName($columnName)
            || in_array($columnName, ['hits', 'items', 'quantity', 'orders', 'goals'], true)
        ) {
            return 0;
        }

        return 2;
    }

    /**
     * True when a column name carries one of the {@see self::DEDUPLICATED_COUNT_BASE_NAMES}.
     *
     * The sum_ prefix excludes the already-summed siblings: when a period archive aggregates
     * these columns, core renames the summed result rather than letting it keep the base name
     * ({@see \Piwik\ArchiveProcessor}), which is where sum_daily_nb_uniq_visitors and
     * sum_daily_nb_users come from. Those columns really are the sum of their sub-period values,
     * so they belong on the additive path. The archiving layer does record which metrics are
     * non-aggregatable -- {@see \Piwik\Plugin\ArchivedMetric::getAggregation()} and
     * {@see \Piwik\ArchiveProcessor\Record::getCountOfRecordName()} both carry it -- but neither
     * is reachable from the visualization layer, which only ever sees a column name, so the
     * name convention has to stand in for them here.
     */
    private function hasDeduplicatedCountColumnName(string $columnName): bool
    {
        if (strpos($columnName, 'sum_') === 0) {
            return false;
        }

        foreach (self::DEDUPLICATED_COUNT_BASE_NAMES as $baseName) {
            if (strpos($columnName, $baseName) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isBlobRowCountColumnName(string $columnName): bool
    {
        return in_array($columnName, self::BLOB_ROW_COUNT_METRIC_NAMES, true);
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
