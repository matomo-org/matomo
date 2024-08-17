<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Piwik;
use Piwik\Plugin\ComputedMetric;

class ProductPrice extends BaseProduct
{
    protected $type = self::TYPE_MONEY;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'price';
    protected $nameSingular = 'Goals_ProductPrice';
    protected $category = 'Goals_Ecommerce';
    protected $segmentName = 'productPrice';
    protected $baseProductSingular = 'General_Price';

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        parent::configureMetrics($metricsList, $dimensionMetricFactory);

        $productRevenueTranslation = Piwik::translate('General_ProductRevenue');
        $translatedName = Piwik::translate('General_ComputedMetricSum', $productRevenueTranslation);
        $metric1 = $dimensionMetricFactory->createCustomMetric('sum_product_revenue', $translatedName, 'sum(case %s when price > 0 then price * quantity else 0 end)');
        $metric1->setDocumentation(Piwik::translate('General_ComputedMetricSumDocumentation', $productRevenueTranslation));
        $metricsList->addMetric($metric1);

        $translatedName = Piwik::translate('General_ComputedMetricMax', $productRevenueTranslation);
        $metric2 = $dimensionMetricFactory->createCustomMetric('max_product_revenue', $translatedName, 'max(case %s when price > 0 then price * quantity else 0 end)');
        $metric2->setDocumentation(Piwik::translate('General_ComputedMetricMaxDocumentation', $productRevenueTranslation));
        $metricsList->addMetric($metric2);

        $metric = $dimensionMetricFactory->createComputedMetric($metric1->getName(), 'conversion_items_with_ecommerce_productprice', ComputedMetric::AGGREGATION_AVG);
        $metric->setName('avg_product_revenue');
        $metric->setTranslatedName(Piwik::translate('General_AverageX', $productRevenueTranslation));
        $productWithPriceTranslation = Piwik::translate('Ecommerce_ProductsWithX', Piwik::translate('General_Price'));
        $metric->setDocumentation(Piwik::translate('General_ComputedMetricAverageDocumentation', [$productRevenueTranslation, $productWithPriceTranslation]));
        $metricsList->addMetric($metric);
    }
}
