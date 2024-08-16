<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;

abstract class BaseProduct extends Dimension
{
    protected $baseProductSingular = null;

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric1 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_SUM);
        $metricsList->addMetric($metric1);

        $metric2 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MAX);
        $metricsList->addMetric($metric2);

        if (empty($this->baseProductSingular)) {
            throw new \Exception('A value for baseProductSingular is not configured');
        }

        $metric3 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_COUNT_WITH_NUMERIC_VALUE);
        $metric3->setName('conversion_items_with_' . $this->getMetricId());
        $metric3->setTranslatedName(Piwik::translate('Ecommerce_ProductsWithX', Piwik::translate($this->baseProductSingular)));
        $metricsList->addMetric($metric3);

        $metric = $dimensionMetricFactory->createComputedMetric($metric1->getName(), $metric3->getName(), ComputedMetric::AGGREGATION_AVG);
        $metric->setName(ComputedMetric::AGGREGATION_AVG . '_' . $this->getMetricId());
        $metric->setTranslatedName(Piwik::translate('General_AverageX', $this->getName()));
        $metricsList->addMetric($metric);
    }
}
