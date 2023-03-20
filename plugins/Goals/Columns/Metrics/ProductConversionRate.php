<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Columns\Metrics;

use Piwik\Columns\Dimension;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * The conversion rate for ecommerce orders. Calculated as:
 *
 *     (orders or abandoned_carts) / nb_visits
 *
 * orders and abandoned_carts are calculated by the Goals archiver.
 */
class ProductConversionRate extends ProcessedMetric
{
    public function getName()
    {
        return 'conversion_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ProductConversionRate');
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function compute(Row $row)
    {
        $orders = $this->getMetric($row, 'orders');
        $abandonedCarts = $this->getMetric($row, 'abandoned_carts');
        $visits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($orders === false ? $abandonedCarts : $orders, $visits, GoalManager::REVENUE_PRECISION + 2);
    }

    public function getDependentMetrics()
    {
        return array('orders', 'abandoned_carts', 'nb_visits');
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }
}