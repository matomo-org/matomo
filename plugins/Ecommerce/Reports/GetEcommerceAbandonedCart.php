<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Reports;

use Piwik\Piwik;

class GetEcommerceAbandonedCart extends Base
{
    protected function init()
    {
        parent::init();
        $this->action = 'get';
        $this->name = Piwik::translate('General_AbandonedCarts');
        $this->processedMetrics = array('avg_order_revenue');
        $this->order = 15;
        $this->metrics = array('nb_conversions', 'conversion_rate', 'revenue', 'items');

        $this->parameters = array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART);
    }

    public function getMetrics() {
        $metrics = parent::getMetrics();

        $metrics['nb_conversions'] = Piwik::translate('General_AbandonedCarts');
        $metrics['revenue']        = Piwik::translate('Goals_LeftInCart', Piwik::translate('General_ColumnRevenue'));
        $metrics['items']          = Piwik::translate('Goals_LeftInCart', Piwik::translate('Goals_Products'));

        return $metrics;
    }
}
