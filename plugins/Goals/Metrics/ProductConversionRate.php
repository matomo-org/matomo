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
// Product conversion rate = orders / visits
 */
class ProductConversionRate extends ProcessedMetric
{
    public function getName()
    {
        return 'conversion_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnConversionRate');
    }

    public function compute(Row $row)
    {
        $orders = $this->getColumn($row, 'orders');
        $abandonedCarts = $this->getColumn($row, 'abandoned_carts');
        $visits = $this->getColumn($row, 'nb_visits');

        return Piwik::getQuotientSafe($orders === false ? $abandonedCarts : $orders, $visits, GoalManager::REVENUE_PRECISION);
    }

    public function getDependenctMetrics()
    {
        return array('orders', 'abandoned_carts', 'nb_visits');
    }
}