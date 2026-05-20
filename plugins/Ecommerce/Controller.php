<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\FrontController;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\Live\Live;
use Piwik\View;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

class Controller extends \Piwik\Plugins\Goals\Controller
{
    public function __construct(\Piwik\Translation\Translator $translator)
    {
        parent::__construct($translator);
    }

    public function getSparklines()
    {
        $content = $this->renderSharedSparklineView(
            Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
            $allowMultiple = true,
            [
                'nb_conversions' => Piwik::translate('General_EcommerceOrders'),
                'nb_visits_converted' => Piwik::translate('General_NVisits'),
                'conversion_rate' => Piwik::translate('Goals_ConversionRate', Piwik::translate('General_EcommerceOrders')),
                'revenue' => Piwik::translate('General_TotalRevenue'),
                'items' => Piwik::translate('General_PurchasedProducts'),
                'avg_order_revenue' => Piwik::translate('General_AverageOrderValue'),
            ],
            [
                ['items', 40],
                ['avg_order_revenue', 50],
            ]
        );

        $content .= $this->renderSharedSparklineView(
            Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART,
            $allowMultiple = false,
            [
                'nb_conversions' => Piwik::translate('General_AbandonedCarts'),
                'conversion_rate' => Piwik::translate('Goals_ConversionRate', Piwik::translate('Goals_AbandonedCart')),
                'revenue' => Piwik::translate('Ecommerce_RevenueLeftInCart'),
            ]
        );

        return $content;
    }

    /**
     * Renders Ecommerce sparklines using the shared CoreVisualizations implementation.
     *
     * @param array<string, string> $translations
     * @param array<int, array{0: string|array<string>, 1: int}> $extraSparklineMetrics
     */
    private function renderSharedSparklineView(
        string $idGoal,
        bool $allowMultiple,
        array $translations,
        array $extraSparklineMetrics = []
    ): string {
        return \Piwik\Context::executeWithQueryParameters([
            'idSite' => $this->idSite,
            'idGoal' => $idGoal,
            'allow_multiple' => (int) $allowMultiple,
            'only_summary' => 0,
        ], function () use ($translations, $extraSparklineMetrics) {
            /** @var Sparklines $view */
            $view = ViewDataTableFactory::build(Sparklines::ID, 'Goals.get', 'Ecommerce.' . __METHOD__, true);
            $view->config->show_title = false;
            $view->config->addTranslations($translations);

            foreach ($extraSparklineMetrics as [$columns, $order]) {
                $view->config->addSparklineMetric($columns, $order);
            }

            return $view->render();
        });
    }

    public function getConversionsOverview()
    {
        $view    = new View('@Ecommerce/conversionOverview');
        $idGoal  = Common::getRequestVar('idGoal', null, 'string');
        $period  = Common::getRequestVar('period', null, 'string');
        $segment = Common::getRequestVar('segment', '', 'string');
        $date    = Common::getRequestVar('date', '', 'string');

        $goalMetrics = Request::processRequest('Goals.get', [
            'idGoal'       => $idGoal,
            'idSite'       => $this->idSite,
            'date'         => $date,
            'period'       => $period,
            'segment'      => Common::unsanitizeInputValue($segment),
            'filter_limit' => '-1',
        ], $default = []);

        $dataRow = $goalMetrics->getFirstRow();

        $view->visitorLogEnabled = Manager::getInstance()->isPluginActivated('Live')
            && Live::isVisitorLogEnabled($this->idSite);
        $view->idSite            = $this->idSite;
        $view->idGoal            = $idGoal;

        if ($dataRow) {
            $view->revenue          = $dataRow->getColumn('revenue');
            $view->revenue_subtotal = $dataRow->getColumn('revenue_subtotal');
            $view->revenue_tax      = $dataRow->getColumn('revenue_tax');
            $view->revenue_shipping = $dataRow->getColumn('revenue_shipping');
            $view->revenue_discount = $dataRow->getColumn('revenue_discount');
        }

        return $view->render();
    }

    public function getEcommerceLog($fetch = false)
    {
        $saveGET       = $_GET;
        $originalQuery = $_SERVER['QUERY_STRING'];

        if (!empty($_GET['segment'])) {
            $_GET['segment'] = $_GET['segment'] . ';' . 'visitEcommerceStatus!=none';
        } else {
            $_GET['segment'] = 'visitEcommerceStatus!=none';
        }
        $_SERVER['QUERY_STRING'] = Http::buildQuery($_GET);

        $_GET['widget']          = 1;
        $output                  = FrontController::getInstance()->dispatch('Live', 'getVisitorLog', [$fetch]);
        $_GET                    = $saveGET;
        $_SERVER['QUERY_STRING'] = $originalQuery;

        return $output;
    }
}
