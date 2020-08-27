<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\Dimension\ActionDimension;

class LinkVisitActionId extends ActionDimension
{
    protected $columnName = 'idlink_va';
    protected $acceptValues = 'Any integer.';
    protected $category = 'General_Actions';
    protected $nameSingular = 'General_Actions';
    protected $metricId = 'hits';
    protected $type = self::TYPE_NUMBER;

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metric->setTranslatedName(Piwik::translate('General_ColumnHits'));
        $metric->setName('hits');
        $metricsList->addMetric($metric);
    }
}
