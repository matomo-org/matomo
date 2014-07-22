<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Goals\Goals;

abstract class BaseEcommerceItem extends BaseEcommerce
{
    protected function init()
    {
        parent::init();
        $this->processedMetrics = false;
        $this->metrics = array(
            'revenue', 'quantity', 'orders', 'avg_price', 'avg_quantity', 'nb_visits', 'conversion_rate'
        );
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();
        $metrics['revenue']         = Piwik::translate('General_ProductRevenue');
        $metrics['orders']          = Piwik::translate('General_UniquePurchases');
        $metrics['conversion_rate'] = Piwik::translate('General_ProductConversionRate');

        return $metrics;
    }

    public function getMetricsDocumentation()
    {
        if ($this->isAbandonedCart()) {
            return array(
                'revenue'         => Piwik::translate('Goals_ColumnRevenueDocumentation',
                                            Piwik::translate('Goals_DocumentationRevenueGeneratedByProductSales')),
                'quantity'        => Piwik::translate('Goals_ColumnQuantityDocumentation', $this->name),
                'orders'          => Piwik::translate('Goals_ColumnOrdersDocumentation', $this->name),
                'avg_price'       => Piwik::translate('Goals_ColumnAveragePriceDocumentation', $this->name),
                'avg_quantity'    => Piwik::translate('Goals_ColumnAverageQuantityDocumentation', $this->name),
                'nb_visits'       => Piwik::translate('Goals_ColumnVisitsProductDocumentation', $this->name),
                'conversion_rate' => Piwik::translate('Goals_ColumnConversionRateProductDocumentation', $this->name),
            );
        }

        return array();
    }

    public function configureView(ViewDataTable $view)
    {
        $idSite = Common::getRequestVar('idSite');

        $view->config->show_ecommerce = true;
        $view->config->show_table     = false;
        $view->config->show_all_views_icons      = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns      = false;

        $moneyColumns = array('revenue', 'avg_price');
        $formatter    = '\Piwik\MetricsFormatter::getPrettyMoney';
        $view->config->filters[] = array('ColumnCallbackReplace', array($moneyColumns, $formatter, array($idSite)));

        $view->requestConfig->filter_limit       = 10;
        $view->requestConfig->filter_sort_column = 'revenue';
        $view->requestConfig->filter_sort_order  = 'desc';

        $view->config->custom_parameters['isFooterExpandedInDashboard'] = true;

        // set columns/translations which differ based on viewDataTable TODO: shouldn't have to do this check...
        // amount of reports should be dynamic, but metadata should be static
        $columns = $this->getMetrics();

        $abandonedCart = $this->isAbandonedCart();
        if ($abandonedCart) {
            $columns['abandoned_carts'] = Piwik::translate('General_AbandonedCarts');
            $columns['revenue'] = Piwik::translate('Goals_LeftInCart', $columns['revenue']);
            $columns['quantity'] = Piwik::translate('Goals_LeftInCart', $columns['quantity']);
            $columns['avg_quantity'] = Piwik::translate('Goals_LeftInCart', $columns['avg_quantity']);
            unset($columns['orders']);
            unset($columns['conversion_rate']);

            $view->requestConfig->request_parameters_to_modify['abandonedCarts'] = '1';
        }

        $translations = array_merge(array('label' => $this->name), $columns);

        $view->config->addTranslations($translations);
        $view->config->columns_to_display = array_keys($translations);

        $view->config->custom_parameters['viewDataTable'] =
            $abandonedCart ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART : Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;
    }

    private function isAbandonedCart()
    {
        return Common::getRequestVar('viewDataTable', 'ecommerceOrder', 'string') == 'ecommerceAbandonedCart';
    }
}
