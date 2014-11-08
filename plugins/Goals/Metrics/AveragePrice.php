<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Metrics;

use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * TODO
// Average price = sum product revenue / quantity
 */
class AveragePrice extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_price';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_AveragePrice');
    }

    public function compute(Row $row)
    {
        $price = $this->getMetric($row, 'price');
        $orders = $this->getMetric($row, 'orders');
        $abandonedCarts = $this->getMetric($row, 'abandoned_carts');

        return Piwik::getQuotientSafe($price, $orders === false ? $abandonedCarts : $orders, GoalManager::REVENUE_PRECISION);
    }

    public function getDependenctMetrics()
    {
        return array('price', 'orders', 'abandoned_carts');
    }
}