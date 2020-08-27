<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Tracker\GoalManager;

class Order extends BaseConversion
{
    protected $columnName = 'idorder';
    protected $type = self::TYPE_NUMBER;
    protected $category = 'Goals_Ecommerce';
    protected $nameSingular = 'Ecommerce_OrderId';
    protected $namePlural = 'Ecommerce_Orders';
    protected $metricId = 'orders';
    protected $segmentName = 'orderId';

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        $metric = $dimensionMetricFactory->createMetric(ArchivedMetric::AGGREGATION_UNIQUE);
        $metric->setTranslatedName(Piwik::translate('Ecommerce_OrderId'));
        $metricsList->addMetric($metric);
    }

    public function getDbDiscriminator()
    {
        return new Discriminator($this->dbTableName, 'idgoal', GoalManager::IDGOAL_ORDER);
    }

}