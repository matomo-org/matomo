<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Unit\Metrics;

use Piwik\Columns\Dimension;
use Piwik\Plugins\CoreVisualizations\Metrics\MetricTotalsTreatment;

/**
 * @group CoreVisualizations
 * @group MetricTotalsTreatment
 * @group Plugins
 */
class MetricTotalsTreatmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getTreatmentTestData
     */
    public function testGetTreatmentClassifiesMetric(
        string $expected,
        string $metricName,
        array $semanticTypes,
        array $processedMetricNames,
        array $aggregationOps
    ) {
        $this->assertSame(
            $expected,
            MetricTotalsTreatment::getTreatment($metricName, $semanticTypes, $processedMetricNames, $aggregationOps)
        );
    }

    public function getTreatmentTestData(): iterable
    {
        yield 'summed number is additive' => [
            MetricTotalsTreatment::TREATMENT_ADDITIVE,
            'nb_visits',
            ['nb_visits' => Dimension::TYPE_NUMBER],
            [],
            ['nb_visits' => 'sum'],
        ];

        yield 'money is additive' => [
            MetricTotalsTreatment::TREATMENT_ADDITIVE,
            'revenue',
            ['revenue' => Dimension::TYPE_MONEY],
            [],
            [],
        ];

        yield 'bytes are additive' => [
            MetricTotalsTreatment::TREATMENT_ADDITIVE,
            'sum_bandwidth',
            ['sum_bandwidth' => Dimension::TYPE_BYTE],
            [],
            [],
        ];

        yield 'percent is derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'exit_rate',
            ['exit_rate' => Dimension::TYPE_PERCENT],
            [],
            [],
        ];

        yield 'duration is derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'avg_time_on_page',
            ['avg_time_on_page' => Dimension::TYPE_DURATION_S],
            [],
            [],
        ];

        yield 'float is derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'avg_page_load_time',
            ['avg_page_load_time' => Dimension::TYPE_FLOAT],
            [],
            [],
        ];

        yield 'processed metric is derived even when typed as a number' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'nb_actions_per_visit',
            ['nb_actions_per_visit' => Dimension::TYPE_NUMBER],
            ['nb_actions_per_visit'],
            ['nb_actions_per_visit' => 'sum'],
        ];

        yield 'non sum aggregation wins over an additive semantic type' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'max_time_network',
            ['max_time_network' => Dimension::TYPE_NUMBER],
            [],
            ['max_time_network' => 'max'],
        ];

        yield 'callable aggregation is derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'custom_metric',
            [],
            [],
            ['custom_metric' => function () {
                return 0;
            }],
        ];

        yield 'sum aggregation of an unknown metric is additive' => [
            MetricTotalsTreatment::TREATMENT_ADDITIVE,
            'custom_metric',
            [],
            [],
            ['custom_metric' => 'SUM'],
        ];

        yield 'unknown metric is additive' => [
            MetricTotalsTreatment::TREATMENT_ADDITIVE,
            'custom_metric',
            [],
            [],
            [],
        ];

        yield 'unique visitors are overridden to derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'nb_uniq_visitors',
            ['nb_uniq_visitors' => Dimension::TYPE_NUMBER],
            [],
            ['nb_uniq_visitors' => 'sum'],
        ];

        yield 'users are overridden to derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'nb_users',
            ['nb_users' => Dimension::TYPE_NUMBER],
            [],
            ['nb_users' => 'sum'],
        ];

        yield 'max actions are overridden to derived' => [
            MetricTotalsTreatment::TREATMENT_DERIVED,
            'max_actions',
            ['max_actions' => Dimension::TYPE_NUMBER],
            [],
            [],
        ];
    }
}
