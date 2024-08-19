<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugin\Dimension\ActionDimension;

class TimeSpent extends ActionDimension
{
    protected $columnName = 'time_spent';
    protected $type = self::TYPE_DURATION_S;
    protected $nameSingular = 'General_TimeOnPage';

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        parent::configureMetrics($metricsList, $dimensionMetricFactory);

        // The parent should create total (sum) and max. We just need to create average
        $metric = $dimensionMetricFactory->createComputedMetric('sum_' . $this->getMetricId(), 'hits', ComputedMetric::AGGREGATION_AVG);
        $metric->setName(ComputedMetric::AGGREGATION_RATE . '_' . $this->getMetricId());
        $metric->setTranslatedName(Piwik::translate('General_AverageX', $this->getName()));
        $metricsList->addMetric($metric);
    }
}
