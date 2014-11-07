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

/**
 * TODO
// Average quantity = sum product quantity / abandoned carts
 */
class AverageQuantity extends ProcessedMetric
{
    public function getName()
    {
        return 'avg_quantity';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_AverageQuantity');
    }

    public function compute(Row $row)
    {
        $quantity = $this->getColumn($row, 'quantity');
        $orders = $this->getColumn($row, 'orders');
        $abandonedCarts = $this->getColumn($row, 'abandoned_carts');

        return Piwik::getQuotientSafe($quantity, $orders === false ? $abandonedCarts : $orders, $precision = 1);
    }

    public function getDependenctMetrics()
    {
        return array('quantity', 'orders', 'abandoned_carts');
    }
}