<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;
use Piwik\Columns\ComputedMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;

/**
 *
 */
class Ecommerce extends \Piwik\Plugin
{

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $hooks = array(
            'Metric.addComputedMetrics' => 'addComputedMetrics'
        );
        return $hooks;
    }

    public function addComputedMetrics(MetricsList $list, ComputedMetricFactory $computedMetricFactory)
    {
        $category = 'Goals_Ecommerce';

        $metrics = $list->getMetrics();
        foreach ($metrics as $metric) {
            if ($metric instanceof ArchivedMetric && $metric->getDimension()) {
                $metricName = $metric->getName();
                if ($metric->getDbTableName() === 'log_conversion'
                    && $metricName !== 'nb_uniq_orders'
                    && strpos($metricName, ArchivedMetric::AGGREGATION_SUM_PREFIX) === 0
                    && $metric->getCategoryId() === $category) {
                    $metric = $computedMetricFactory->createComputedMetric($metric->getName(), 'nb_uniq_orders', ComputedMetric::AGGREGATION_AVG);
                    $list->addMetric($metric);
                }
            }
        }
    }

}
