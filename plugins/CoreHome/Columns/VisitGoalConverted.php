<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitGoalConverted extends VisitDimension
{
    protected $columnName = 'visit_goal_converted';
    protected $columnType = 'TINYINT(1) NULL';
    protected $type = self::TYPE_BOOL;
    protected $segmentName = 'visitConverted';
    protected $nameSingular = 'General_VisitConvertedGoal';
    protected $acceptValues = '0, 1';

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric1 = $dimensionMetricFactory->createCustomMetric('nb_visits_converted', Piwik::translate('General_ColumnVisitsWithConversions'), 'sum(case %s when 1 then 1 else 0 end)');
        $metric1->setType(Dimension::TYPE_NUMBER);
        $metricsList->addMetric($metric1);

        $metric = $dimensionMetricFactory->createComputedMetric($metric1->getName(), 'nb_visits', ComputedMetric::AGGREGATION_RATE);
        $metric->setTranslatedName(Piwik::translate('General_ColumnConversionRate'));
        $metric->setDocumentation(Piwik::translate('General_ColumnConversionRateDocumentation'));
        $metric->setName('visits_conversion_rate');
        $metricsList->addMetric($metric);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return 0;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return 1;
    }
}