<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\Reports;

use Piwik\Piwik;
use Piwik\Plugins\CoreHome\Columns\Metrics\EvolutionMetric;
use Piwik\Plugins\MultiSites\API;
use Piwik\Plugins\MultiSites\Columns\Metrics\EcommerceOnlyEvolutionMetric;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->categoryId = 'General_MultiSitesSummary';

        $allMetricsInfo = API::getApiMetrics($enhanced = true);

        $metadataMetrics = array();
        $processedMetricsMetadata = array();

        foreach ($allMetricsInfo as $metricName => $metricSettings) {
            $evolutionMetricClass = $this->isEcommerceEvolutionMetric($metricSettings)
                ? EcommerceOnlyEvolutionMetric::class
                : EvolutionMetric::class;

            $metadataMetrics[$metricName] =
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]);

            $processedMetricsMetadata[$metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY]] =
                new $evolutionMetricClass(
                    $metricSettings[API::METRIC_RECORD_NAME_KEY],
                    null,
                    $metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY],
                    $quotientPrecision = 1,
                    null,
                    $metricSettings[API::METRIC_TRANSLATION_KEY],
                    $metricSettings[API::METRIC_WRAPPED_SEMANTIC_TYPE_KEY],
                    $metricSettings[API::METRIC_WRAPPED_AGGREGATION_TYPE_KEY]
                );
        }

        $this->metrics = array_keys($metadataMetrics);
        $this->processedMetrics = $processedMetricsMetadata;
    }

    private function isEcommerceEvolutionMetric($metricSettings): bool
    {
        return in_array($metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY], array(
            API::GOAL_REVENUE_METRIC . '_evolution',
            API::ECOMMERCE_ORDERS_METRIC . '_evolution',
            API::ECOMMERCE_REVENUE_METRIC . '_evolution'
        ));
    }
}
