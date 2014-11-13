<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * The average price for each ecommerce order or abandoned cart. Calculated as:
 *
 *     price / (orders or abandoned_carts)
 *
 * price, orders and abandoned_carts are calculated by the Goals archiver.
 */
class AveragePrice extends ProcessedMetric
{
    private $idSite;

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

    public function getDependentMetrics()
    {
        return array('price', 'orders', 'abandoned_carts');
    }

    public function format($value)
    {
        return MetricsFormatter::getPrettyMoney(sprintf("%.2f", $value), $this->idSite, $isHtml = false);
    }

    public function beforeFormat($report, DataTable $table)
    {
        $this->idSite = DataTable::getSiteIdFromMetadata($table);
        return !empty($this->idSite); // skip formatting if there is no site to get currency info from
    }
}