<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;

class EventValue extends ActionDimension
{
    protected $nameSingular = 'Events_EventValue';
    protected $columnName = 'custom_float';
    protected $category = 'Events_Events';
    protected $type = self::TYPE_FLOAT;
    protected $segmentName = 'eventValue';

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_EVENT);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric1 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_SUM);
        $metricsList->addMetric($metric1);

        $metric2 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MAX);
        $metric2->setDocumentation(Piwik::translate('Events_MaxValueDocumentation'));
        $metricsList->addMetric($metric2);

        $metric4 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_MIN);
        $metric4->setDocumentation(Piwik::translate('Events_MinValueDocumentation'));
        $metricsList->addMetric($metric4);

        $metric3 = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_COUNT_WITH_NUMERIC_VALUE);
        $metric3->setName('events_with_event_value');
        $metric3->setTranslatedName(Piwik::translate('Events_EventsWithValue'));
        $metric3->setDocumentation(Piwik::translate('Events_EventsWithValueDocumentation'));
        $metricsList->addMetric($metric3);

        $metric = $dimensionMetricFactory->createComputedMetric($metric1->getName(), $metric3->getName(), ComputedMetric::AGGREGATION_AVG);
        $metric->setName('avg_event_value');
        $metric->setTranslatedName(Piwik::translate('Events_AvgValue'));
        $metricsList->addMetric($metric);
    }
}
