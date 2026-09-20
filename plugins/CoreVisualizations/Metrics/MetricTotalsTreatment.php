<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\Metrics;

use Piwik\Columns\Dimension;

/**
 * Classifies how the total of a metric relates to the totals of the rows it was calculated from.
 *
 * A metric is treated as additive when its report total is the plain sum of the row values, which
 * makes the total of a subset of the rows a share of the report total. Every other metric is
 * treated as derived: its total is recalculated rather than summed, so the total of a subset of the
 * rows cannot be read as a part of the report total.
 *
 * @internal
 */
class MetricTotalsTreatment
{
    public const TREATMENT_ADDITIVE = 'additive';

    public const TREATMENT_DERIVED = 'derived';

    /**
     * Semantic types whose values are totalled by summing them.
     *
     * Every other type is treated as derived, including `float`, because the float metrics Matomo
     * shows are averages and ratios rather than values that add up.
     */
    private const ADDITIVE_SEMANTIC_TYPES = [
        Dimension::TYPE_NUMBER,
        Dimension::TYPE_MONEY,
        Dimension::TYPE_BYTE,
    ];

    /**
     * Metrics whose treatment cannot be derived from their metadata.
     *
     * - `nb_uniq_visitors` and `nb_users` are plain numbers that the totals row sums, but a visitor
     *   or user counted in several rows is only one visitor or user, so their total is not the sum
     *   of the rows it covers.
     * - `max_actions` is aggregated with `max` through a special case in
     *   {@link \Piwik\DataTable\Row::sumRow()} instead of through the aggregation operations, so the
     *   aggregation metadata cannot tell that it is not summed.
     */
    private const TREATMENT_OVERRIDES = [
        'nb_uniq_visitors' => self::TREATMENT_DERIVED,
        'nb_users' => self::TREATMENT_DERIVED,
        'max_actions' => self::TREATMENT_DERIVED,
    ];

    /**
     * Returns how the total of the given metric should be treated.
     *
     * @param array<string, string> $semanticTypes Metric name => semantic type, as returned by
     *                                             {@link \Piwik\Plugin\Report::getMetricSemanticTypes()}.
     * @param string[] $processedMetricNames Names of the metrics that are computed from other metrics.
     * @param array<string, string|callable> $aggregationOps Column name => aggregation operation, as
     *                                                       stored in the
     *                                                       {@link \Piwik\DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME}
     *                                                       metadata.
     * @return string One of the `TREATMENT_*` constants.
     */
    public static function getTreatment(
        string $metricName,
        array $semanticTypes = [],
        array $processedMetricNames = [],
        array $aggregationOps = []
    ): string {
        if (isset(self::TREATMENT_OVERRIDES[$metricName])) {
            return self::TREATMENT_OVERRIDES[$metricName];
        }

        if (in_array($metricName, $processedMetricNames, true)) {
            return self::TREATMENT_DERIVED;
        }

        if (
            isset($aggregationOps[$metricName])
            && !self::isSumOperation($aggregationOps[$metricName])
        ) {
            return self::TREATMENT_DERIVED;
        }

        if (!empty($semanticTypes[$metricName])) {
            return in_array($semanticTypes[$metricName], self::ADDITIVE_SEMANTIC_TYPES, true)
                ? self::TREATMENT_ADDITIVE
                : self::TREATMENT_DERIVED;
        }

        return self::TREATMENT_ADDITIVE;
    }

    /**
     * @param string|callable $operation
     */
    private static function isSumOperation($operation): bool
    {
        return is_string($operation) && 'sum' === strtolower($operation);
    }
}
