<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;

class TimeDomCompletion extends Base
{
    const COLUMN_TYPE = 'MEDIUMINT(10) UNSIGNED NULL';
    const COLUMN_NAME = 'time_dom_completion';

    protected $columnName = self::COLUMN_NAME;
    protected $columnType = self::COLUMN_TYPE;
    protected $nameSingular = 'PagePerformance_ColumnTimeDomCompletion';

    public function getRequestParam()
    {
        return 'pf_dm2';
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric1 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_SUM);
        $metric1->setName('sum_time_dom_completion');
        $metricsList->addMetric($metric1);

        $metric2 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MAX);
        $metric2->setName('max_time_dom_completion');
        $metricsList->addMetric($metric2);

        $metric3 = $dimensionMetricFactory->createMetric('sum(if(%s is null, 0, 1))');
        $metric3->setName('pageviews_with_time_dom_completion');
        $metric3->setType(self::TYPE_NUMBER);
        $metric3->setTranslatedName(Piwik::translate('PagePerformance_ColumnViewsWithTimeDomCompletion'));
        $metricsList->addMetric($metric3);

        $metric4 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MIN);
        $metric4->setName('min_time_dom_completion');
        $metricsList->addMetric($metric4);

        $metric = $dimensionMetricFactory->createComputedMetric($metric1->getName(), $metric3->getName(), ComputedMetric::AGGREGATION_AVG);
        $metric->setName('avg_time_dom_completion');
        $metric->setTranslatedName(Piwik::translate('PagePerformance_ColumnAverageTimeDomCompletion'));
        $metric->setDocumentation(Piwik::translate('PagePerformance_ColumnAverageTimeDomCompletionDocumentation'));
        $metricsList->addMetric($metric);
    }
}
