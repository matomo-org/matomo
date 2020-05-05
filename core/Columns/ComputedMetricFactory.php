<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;

/**
 * A factory to create computed metrics.
 *
 * @api since Piwik 3.2.0
 */
class ComputedMetricFactory
{
    /**
     * @var MetricsList
     */
    private $metricsList = null;

    /**
     * Generates a new report metric factory.
     * @param MetricsList $list A report list instance
     * @ignore
     */
    public function __construct(MetricsList $list)
    {
        $this->metricsList = $list;
    }

    /**
     * @return \Piwik\Plugin\ComputedMetric
     */
    public function createComputedMetric($metricName1, $metricName2, $aggregation)
    {
        $metric1 = $this->metricsList->getMetric($metricName1);

        if (!$metric1 instanceof ArchivedMetric || !$metric1->getDimension()) {
            throw new \Exception('Only possible to create computed metric for an archived metric with a dimension');
        }

        $dimension1 = $metric1->getDimension();

        $metric = new ComputedMetric($metricName1, $metricName2, $aggregation);
        $metric->setCategory($dimension1->getCategoryId());

        return $metric;
    }

}