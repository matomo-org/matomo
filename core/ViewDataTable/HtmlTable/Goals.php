<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\ViewDataTable\HtmlTable;

use Piwik\Piwik;
use Piwik\Common;
use Piwik\Site;
use Piwik\ViewDataTable\HtmlTable;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\Plugins\Goals\API;

/**
 * @package Piwik
 * @subpackage ViewDataTable
 */
class Goals extends HtmlTable
{
    private $processOnlyIdGoal = null;
    private $isEcommerce = false;

    protected function getViewDataTableId()
    {
        return 'tableGoals';
    }

    public function main()
    {
        if (!empty($this->viewProperties['disable_subtable_when_show_goals'])) {
            $this->viewProperties['subtable_controller_action'] = null;
        }

        $this->idSite = Common::getRequestVar('idSite', null, 'int');
        $this->processOnlyIdGoal = Common::getRequestVar('idGoal', AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string');
        $this->isEcommerce = $this->processOnlyIdGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;
        $this->viewProperties['show_exclude_low_population'] = true;
        $this->viewProperties['show_goals'] = true;

        if (Common::getRequestVar('documentationForGoalsPage', 0, 'int') == 1) {
            $this->viewProperties['documentation'] = Piwik_Translate('Goals_ConversionByTypeReportDocumentation',
                array('<br />', '<br />', '<a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank">', '</a>'));
        }

        $this->viewProperties['metrics_documentation']['nb_visits'] = Piwik_Translate('Goals_ColumnVisits');
        if ($this->isEcommerce) {
            $this->viewProperties['metrics_documentation']['revenue_per_visit'] =
                Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik_Translate('General_EcommerceOrders'));
            $this->viewProperties['translations'] += array(
                'goal_%s_conversion_rate'   => Piwik_Translate('Goals_ConversionRate'),
                'goal_%s_nb_conversions'    => Piwik_Translate('General_EcommerceOrders'),
                'goal_%s_revenue'           => Piwik_Translate('General_TotalRevenue'),
                'goal_%s_revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
                'goal_%s_avg_order_revenue' => Piwik_Translate('General_AverageOrderValue'),
                'goal_%s_items'             => Piwik_Translate('General_PurchasedProducts')
            );

            $this->viewProperties['columns_to_display'] = array(
                'label', 'nb_visits', 'goal_%s_nb_conversions', 'goal_%s_revenue', 'goal_%s_conversion_rate',
                'goal_%s_avg_order_revenue', 'goal_%s_items', 'goal_%s_revenue_per_visit');

            // Default sort column
            $this->viewProperties['filter_sort_column'] = 'goal_ecommerceOrder_revenue';
            $this->viewProperties['filter_sort_order'] = 'desc';
        } else {
            $this->viewProperties['metrics_documentation'] =
                Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik_Translate('Goals_EcommerceAndGoalsMenu'));
            $this->viewProperties['translations'] += array(
               'goal_%s_conversion_rate'   => Piwik_Translate('Goals_ConversionRate'),
               'goal_%s_nb_conversions'    => Piwik_Translate('Goals_Conversions'),
               'goal_%s_revenue'           => '%s ' . Piwik_Translate('Goals_ColumnRevenue'),
               'goal_%s_revenue_per_visit' => '%s ' . Piwik_Translate('General_ColumnValuePerVisit'),
               'nb_conversions'            => Piwik_Translate('Goals_ColumnConversions'),
               'conversion_rate'           => Piwik_Translate('General_ColumnConversionRate'),
               'revenue'                   => Piwik_Translate('Goals_ColumnRevenue'),
               'revenue_per_visit'         => Piwik_Translate('General_ColumnValuePerVisit'),
            );

            $this->viewProperties['columns_to_display'] = array(
                'label', 'nb_visits', 'goal_%s_nb_conversions', 'goal_%s_conversion_rate', 'goal_%s_revenue',
                'goal_%s_revenue_per_visit', 'revenue_per_visit');

            // Default sort column
            $columnsToDisplay = $this->viewProperties['columns_to_display'];
            $columnNbConversionsCurrentGoal = $columnsToDisplay[2];
            if ($this->processOnlyIdGoal > 0
                && strpos($columnNbConversionsCurrentGoal, '_nb_conversions') !== false
            ) {
                $this->viewProperties['filter_sort_column'] = $columnNbConversionsCurrentGoal;
                $this->viewProperties['filter_sort_order'] = 'desc';
            }
        }

        parent::main();
    }

    /**
     * Find the appropriate metric documentation for a goal column
     * @param string $genericMetricName
     * @param string $metricName
     * @param string $goalName
     * @param int $idGoal
     */
    private function setDynamicMetricDocumentation($genericMetricName, $metricName, $goalName, $idGoal)
    {
        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            $goalName = Piwik_Translate('General_EcommerceOrders');
        } else {
            $goalName = '"' . $goalName . '"';
        }

        $langString = false;
        switch ($genericMetricName) {
            case 'goal_%s_nb_conversions':
                $langString = 'Goals_ColumnConversionsDocumentation';
                break;
            case 'goal_%s_conversion_rate':
                $langString = 'Goals_ColumnConversionRateDocumentation';
                break;
            case 'goal_%s_revenue_per_visit':
                $langString = 'Goals_ColumnRevenuePerVisitDocumentation';
                break;
            case 'goal_%s_revenue':
                $langString = 'Goals_ColumnRevenueDocumentation';
                break;
            case 'goal_%s_avg_order_revenue':
                $langString = 'Goals_ColumnAverageOrderRevenueDocumentation';
                break;
            case 'goal_%s_items':
                $langString = 'Goals_ColumnPurchasedProductsDocumentation';
                break;
        }

        if ($langString) {
            $doc = Piwik_Translate($langString, $goalName);
            $this->viewProperties['metrics_documentation'][$metricName] = $doc;
        }
    }

    protected function getRequestArray()
    {
        $requestArray = parent::getRequestArray();
        if ($this->processOnlyIdGoal > AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE
            || $this->isEcommerce
        ) {
            $requestArray["idGoal"] = $this->processOnlyIdGoal;
        }
        $requestArray['filter_update_columns_when_show_all_goals'] = 1;
        return $requestArray;
    }

    protected $columnsToRevenueFilter = array();
    protected $columnsToConversionFilter = array();
    protected $idSite = false;

    private function getIdSite()
    {
        return $this->idSite;
    }

    protected function postDataTableLoadedFromAPI()
    {
        $valid = parent::postDataTableLoadedFromAPI();
        if ($valid === false) return false;

        foreach ($this->viewProperties['columns_to_display'] as $columnName) {
            if (strpos($columnName, 'conversion_rate')) {
                $this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$rate', 'if($rate==0) return "0%"; else return $rate;')));
            }
        }
        $this->columnsToRevenueFilter[] = 'revenue_per_visit';
        foreach ($this->columnsToRevenueFilter as $columnName) {
            $this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$value', 'return sprintf("%.1f",$value);')));
            $this->dataTable->filter('ColumnCallbackReplace', array($columnName, '\Piwik\Piwik::getPrettyMoney', array($this->getIdSite())));
        }

        foreach ($this->columnsToConversionFilter as $columnName) {
            // this ensures that the value is set to zero for all rows where the value was not set (no conversion)
            $this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$value', 'return $value;')));
        }
        return true;
    }

    protected function overrideViewProperties()
    {
        parent::overrideViewProperties();

        $columnsNames = $this->viewProperties['columns_to_display'];

        $newColumnsNames = array();
        $goals = array();
        $idSite = $this->getIdSite();
        if ($idSite) {
            $goals = API::getInstance()->getGoals($idSite);

            $ecommerceGoal = array(
                'idgoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                'name'   => Piwik_Translate('Goals_EcommerceOrder')
            );

            $site = new Site($idSite);
            //Case Ecommerce report table
            if ($this->isEcommerce) {
                $goals = array($ecommerceGoal);
            } // Case tableGoals
            elseif ($site->isEcommerceEnabled()) {
                $goals = array_merge(
                    array($ecommerceGoal),
                    $goals
                );
            }
        }
        foreach ($columnsNames as $columnName) {
            if (in_array($columnName, array(
                                           'goal_%s_conversion_rate',
                                           'goal_%s_nb_conversions',
                                           'goal_%s_revenue_per_visit',
                                           'goal_%s_revenue',
                                           'goal_%s_avg_order_revenue',
                                           'goal_%s_items',

                                      ))
            ) {
                foreach ($goals as $goal) {
                    $idgoal = $goal['idgoal'];

                    $goal['name'] = Common::unsanitizeInputValue($goal['name']);

                    if ($this->processOnlyIdGoal > AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE
                        && $this->processOnlyIdGoal != $idgoal
                        && !$this->isEcommerce
                    ) {
                        continue;
                    }
                    $column = isset($this->viewProperties['translations'][$columnName]) ?
                        $this->viewProperties['translations'][$columnName] : $columnName;
                    $name = Piwik_Translate($column, $goal['name']);
                    $columnNameGoal = str_replace('%s', $idgoal, $columnName);
                    $this->viewProperties['translations'][$columnNameGoal] = $name;
                    $this->setDynamicMetricDocumentation($columnName, $columnNameGoal, $goal['name'], $goal['idgoal']);
                    if (strpos($columnNameGoal, '_rate') === false
                        // For the goal table (when the flag icon is clicked), we only display the per Goal Conversion rate
                        && $this->processOnlyIdGoal == AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW
                    ) {
                        continue;
                    }

                    if (strstr($columnNameGoal, '_revenue') !== false) {
                        $this->columnsToRevenueFilter[] = $columnNameGoal;
                    } else {
                        $this->columnsToConversionFilter[] = $columnNameGoal;
                    }
                    $newColumnsNames[] = $columnNameGoal;
                }
            } else {
                $newColumnsNames[] = $columnName;
            }
        }

        $this->viewProperties['columns_to_display'] = $newColumnsNames;
    }

    public function getDefaultDataTableCssClass()
    {
        return 'dataTableGoals';
    }
}
