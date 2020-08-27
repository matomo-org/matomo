<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;
use Piwik\Columns\ComputedMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Common;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\ComputedMetric;
use Piwik\Plugins\Ecommerce\Columns\ProductCategory;

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
        return [
            'Metric.addComputedMetrics' => 'addComputedMetrics',
            'Actions.getCustomActionDimensionFieldsAndJoins' => 'provideActionDimensionFields'
        ];
    }

    public function provideActionDimensionFields(&$fields, &$joins)
    {
        $fields[] = 'log_link_visit_action.product_price as productViewPrice';
        $fields[] = 'log_action_productview_name.name as productViewName';
        $fields[] = 'log_action_productview_sku.name as productViewSku';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_productview_name
					ON  log_link_visit_action.idaction_product_name = log_action_productview_name.idaction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_productview_sku
					ON  log_link_visit_action.idaction_product_sku = log_action_productview_sku.idaction';

        for($i = 1; $i <= ProductCategory::PRODUCT_CATEGORY_COUNT; $i++) {
            $suffix = $i > 1 ? $i : '';
            $fields[] = "log_action_productview_category$i.name as productViewCategory$i";
            $joins[] = "LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_productview_category$i
					ON  log_link_visit_action.idaction_product_cat$suffix = log_action_productview_category$i.idaction";
        }
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
