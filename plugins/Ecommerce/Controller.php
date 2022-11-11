<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Ecommerce;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\FrontController;
use Piwik\Http;
use Piwik\Metrics;
use Piwik\NumberFormatter;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Live\Live;
use Piwik\Site;
use Piwik\Tracker\GoalManager;
use Piwik\Translation\Translator;
use Piwik\View;

class Controller extends \Piwik\Plugins\Goals\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct($translator);
    }

    public function getSparklines()
    {
        $view = new View('@Ecommerce/getSparklines');

        $this->setGeneralVariablesView($view);

        $goal = $this->getMetricsForGoal(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
        foreach ($goal as $name => $value) {
            if ($name === 'conversion_rate') {
                $value = $value * 100;
            }
            $view->$name = $value;
        }

        $goal = $this->getMetricsForGoal(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART);
        foreach ($goal as $name => $value) {
            if ($name === 'conversion_rate') {
                $value = $value * 100;
            }
            $name        = 'cart_' . $name;
            $view->$name = $value;
        }

        return $view->render();
    }

    /**
     * Get metrics for an ecommerce goal and add evolution values
     *
     * @param      $idGoal
     * @param null $dataRow
     *
     * @return array
     */
    protected function getMetricsForGoal($idGoal, $dataRow = null)
    {
        $request = new Request("method=Goals.get&format=original&format_metrics=0&idGoal=$idGoal");
        $datatable = $request->process();
        $dataRow = $datatable->getFirstRow();

        $return = parent::getMetricsForGoal($idGoal, $dataRow);

        // Previous period data for evolution
        list($lastPeriodDate, $ignore) = Range::getLastDate();
        if ($lastPeriodDate !== false) {
            $date = Common::getRequestVar('date');

            /** @var DataTable $previousData */
            $previousData = Request::processRequest('Goals.get', ['date' => $lastPeriodDate, 'format_metrics' => 0]);
            $previousDataRow = $previousData->getFirstRow();

            $return = $this->addSparklineEvolutionValues($return, $idGoal, $date, $lastPeriodDate, $dataRow, $previousDataRow);

        }
        return $return;
    }

    /**
     * Add sparkline evolution figures to the metrics in the supplied array
     *
     * @param array         $return
     * @param string|int    $idGoal
     * @param string        $date
     * @param string        $lastPeriodDate
     * @param DataTable\Row $currentDataRow
     * @param DataTable\Row $previousDataRow
     *
     * @return array
     */
    private function addSparklineEvolutionValues(array $return, $idGoal, string $date, string $lastPeriodDate,
                                                 DataTable\Row $currentDataRow, DataTable\Row $previousDataRow) : array
    {
        $metrics = [
            'nb_conversions' => Piwik::translate('General_EcommerceOrders'),
            'nb_visits_converted' => Piwik::translate('General_NVisits'),
            'conversion_rate' => Piwik::translate('Goals_ConversionRate', Piwik::translate('General_EcommerceOrders')),
            'revenue' => Piwik::translate('General_TotalRevenue')
        ];

        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            $metrics = array_merge($metrics, [
                'items' => Piwik::translate('General_PurchasedProducts'),
                'avg_order_revenue' => Piwik::translate('General_AverageOrderValue')
            ]);
        }

        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
            $abandonedCart = Piwik::translate('Goals_AbandonedCart');;
            $metrics['nb_conversions'] = Piwik::translate('General_VisitsWith', $abandonedCart);
            $metrics['conversion_rate'] = Piwik::translate('General_VisitsWith', $abandonedCart);
            $metrics['revenue'] = Piwik::translate('Ecommerce_RevenueLeftInCart', Piwik::translate('General_ColumnRevenue'));
            unset($metrics['nb_visits_converted']);
        }

        $currentPeriod = PeriodFactory::build(Piwik::getPeriod(), $date);
        $currentPrettyDate = ($currentPeriod instanceof Month ? $currentPeriod->getLocalizedLongString() : $currentPeriod->getPrettyString());
        $lastPeriod = PeriodFactory::build(Piwik::getPeriod(), $lastPeriodDate);
        $lastPrettyDate = ($currentPeriod instanceof Month ? $lastPeriod->getLocalizedLongString() : $lastPeriod->getPrettyString());

        $formatter = new Metrics\Formatter();

        foreach ($return as $columnName => $value) {

            if (array_key_exists($columnName, $metrics) && array_key_exists($columnName, $return)) {

                $pastValue = $previousDataRow ? $previousDataRow->getColumn($columnName) : 0;

                if (in_array($columnName, ['revenue', 'avg_order_revenue'])) {
                    $numberFormatter = NumberFormatter::getInstance();
                    $currencySymbol = Site::getCurrencySymbolFor($this->idSite);
                    $currentValueFormatted = $numberFormatter->formatCurrency($value, $currencySymbol, GoalManager::REVENUE_PRECISION);
                    $pastValueFormatted = $numberFormatter->formatCurrency($pastValue, $currencySymbol, GoalManager::REVENUE_PRECISION);
                } elseif ($columnName == 'conversion_rate') {
                    $currentValueFormatted = $formatter->getPrettyPercentFromQuotient($value);
                    $pastValueFormatted = $formatter->getPrettyPercentFromQuotient($pastValue);
                } else {
                    $currentValueFormatted = NumberFormatter::getInstance()->format($value, 1, 1);
                    $pastValueFormatted = NumberFormatter::getInstance()->format($pastValue, 1, 1);
                }

                $metricTranslationKey = '';
                if (array_key_exists($columnName, $metrics)) {
                    $metricTranslationKey = $metrics[$columnName];
                }
                $trend = CalculateEvolutionFilter::calculate($value, $pastValue, $precision = 1);

                $return[$columnName.'_trend'] = ($pastValue - $value > 0 ? -1 : ($pastValue - $value < 0 ? 1 : 0));
                $return[$columnName.'_trend_percent'] = $trend;
                $return[$columnName.'_tooltip'] = Piwik::translate('General_EvolutionSummaryGeneric', array(
                    $currentValueFormatted.' '.Piwik::translate($metricTranslationKey),
                    $currentPrettyDate,
                    $pastValueFormatted.' '.Piwik::translate($metricTranslationKey),
                    $lastPrettyDate,
                    $trend));
            }
        }

        return $return;
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
