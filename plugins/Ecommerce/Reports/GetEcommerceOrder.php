<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Reports;

use Piwik\Piwik;

class GetEcommerceOrder extends Base
{
    protected function init()
    {
        parent::init();
        $this->action = 'get';
        $this->name = Piwik::translate('General_EcommerceOrders');
        $this->processedMetrics = array('avg_order_revenue');
        $this->order = 10;
        $this->metrics = array(
            'nb_conversions',
            'nb_visits_converted',
            'conversion_rate',
            'revenue',
            'revenue_subtotal',
            'revenue_tax',
            'revenue_shipping',
            'revenue_discount'
        );

        $this->parameters = array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();

        $metrics['nb_conversions'] = Piwik::translate('General_EcommerceOrders');
        $metrics['items']          = Piwik::translate('General_PurchasedProducts');

        return $metrics;
    }
}
