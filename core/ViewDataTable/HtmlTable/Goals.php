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

/**
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_HtmlTable_Goals extends Piwik_ViewDataTable_HtmlTable
{
    protected function getViewDataTableId()
    {
        return 'tableGoals';
    }

    public function main()
    {
        $this->idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
        $this->processOnlyIdGoal = Piwik_Common::getRequestVar('idGoal', Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string');
        $this->isEcommerce = $this->processOnlyIdGoal == Piwik_Archive::LABEL_ECOMMERCE_ORDER;
        $this->viewProperties['show_exclude_low_population'] = true;
        $this->viewProperties['show_goals'] = true;

        if (Piwik_Common::getRequestVar('documentationForGoalsPage', 0, 'int') == 1) {
            $this->setReportDocumentation(Piwik_Translate('Goals_ConversionByTypeReportDocumentation',
                array('<br />', '<br />', '<a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank">', '</a>')));
        }


        $this->setMetricDocumentation('nb_visits', Piwik_Translate('Goals_ColumnVisits'));
        if ($this->isEcommerce) {
            $this->setMetricDocumentation('revenue_per_visit', Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik_Translate('General_EcommerceOrders')));
            $this->setColumnsTranslations(array(
                                               'goal_%s_conversion_rate'   => Piwik_Translate('Goals_ConversionRate'),
                                               'goal_%s_nb_conversions'    => Piwik_Translate('General_EcommerceOrders'),
                                               'goal_%s_revenue'           => Piwik_Translate('General_TotalRevenue'),
                                               'goal_%s_revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
                                               'goal_%s_avg_order_revenue' => Piwik_Translate('General_AverageOrderValue'),
                                               'goal_%s_items'             => Piwik_Translate('General_PurchasedProducts'),
                                          ));
            $this->setColumnsToDisplay(array(
                                            'label',
                                            'nb_visits',
                                            'goal_%s_nb_conversions',
                                            'goal_%s_revenue',
                                            'goal_%s_conversion_rate',
                                            'goal_%s_avg_order_revenue',
                                            'goal_%s_items',
                                            'goal_%s_revenue_per_visit',
                                       ));

            // Default sort column
            $this->setSortedColumn('goal_ecommerceOrder_revenue', 'desc');
        } else {
            $this->setMetricDocumentation('revenue_per_visit', Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik_Translate('Goals_EcommerceAndGoalsMenu')));
            $this->setColumnsTranslations(array(
                                               'goal_%s_conversion_rate'   => Piwik_Translate('Goals_ConversionRate'),
                                               'goal_%s_nb_conversions'    => Piwik_Translate('Goals_Conversions'),
                                               'goal_%s_revenue'           => '%s ' . Piwik_Translate('Goals_ColumnRevenue'),
                                               'goal_%s_revenue_per_visit' => '%s ' . Piwik_Translate('General_ColumnValuePerVisit'),

                                               'nb_conversions'            => Piwik_Translate('Goals_ColumnConversions'),
                                               'conversion_rate'           => Piwik_Translate('General_ColumnConversionRate'),
                                               'revenue'                   => Piwik_Translate('Goals_ColumnRevenue'),
                                               'revenue_per_visit'         => Piwik_Translate('General_ColumnValuePerVisit'),
                                          ));
            $this->setColumnsToDisplay(array(
                                            'label',
                                            'nb_visits',
                                            'goal_%s_nb_conversions',
                                            'goal_%s_conversion_rate',
                                            'goal_%s_revenue',
                                            'goal_%s_revenue_per_visit',
                                            'revenue_per_visit',
                                       ));

            // Default sort column
            $columnsToDisplay = $this->getColumnsToDisplay();
            $columnNbConversionsCurrentGoal = $columnsToDisplay[2];
            if ($this->processOnlyIdGoal > 0
                && strpos($columnNbConversionsCurrentGoal, '_nb_conversions') !== false
            ) {
                $this->setSortedColumn($columnNbConversionsCurrentGoal, 'desc');
            }
        }

        parent::main();
    }

    public function disableSubTableWhenShowGoals()
    {
        $this->controllerActionCalledWhenRequestSubTable = null;
    }

    public function setColumnsToDisplay($columnsNames)
    {
        $newColumnsNames = array();
        $goals = array();
        $idSite = $this->getIdSite();
        if ($idSite) {
            $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);

            $ecommerceGoal = array(
                'idgoal' => Piwik_Archive::LABEL_ECOMMERCE_ORDER,
                'name'   => Piwik_Translate('Goals_EcommerceOrder')
            );

            $site = new Piwik_Site($idSite);
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

                    // Columns names are escaped in smarty via | escape:'html'
                    $goal['name'] = Piwik_Common::unsanitizeInputValue($goal['name']);

                    if ($this->processOnlyIdGoal > Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE
                        && $this->processOnlyIdGoal != $idgoal
                        && !$this->isEcommerce
                    ) {
                        continue;
                    }
                    $name = Piwik_Translate($this->getColumnTranslation($columnName), $goal['name']);
                    $columnNameGoal = str_replace('%s', $idgoal, $columnName);
                    $this->setColumnTranslation($columnNameGoal, $name);
                    $this->setDynamicMetricDocumentation($columnName, $columnNameGoal, $goal['name'], $goal['idgoal']);
                    if (strpos($columnNameGoal, '_rate') === false
                        // For the goal table (when the flag icon is clicked), we only display the per Goal Conversion rate
                        && $this->processOnlyIdGoal == Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW
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
        parent::setColumnsToDisplay($newColumnsNames);
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
        if ($idGoal == Piwik_Archive::LABEL_ECOMMERCE_ORDER) {
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
            $this->setMetricDocumentation($metricName, $doc);
        }
    }

    protected function getRequestString()
    {
        $requestString = parent::getRequestString();
        if ($this->processOnlyIdGoal > Piwik_DataTable_Filter_AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE
            || $this->isEcommerce
        ) {
            $requestString .= "&idGoal=" . $this->processOnlyIdGoal;
        }
        return $requestString . '&filter_update_columns_when_show_all_goals=1';
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

        foreach ($this->getColumnsToDisplay() as $columnName) {
            if (strpos($columnName, 'conversion_rate')) {
                $this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$rate', 'if($rate==0) return "0%"; else return $rate;')));
            }
        }
        $this->columnsToRevenueFilter[] = 'revenue_per_visit';
        foreach ($this->columnsToRevenueFilter as $columnName) {
            $this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$value', 'return sprintf("%.1f",$value);')));
            $this->dataTable->filter('ColumnCallbackReplace', array($columnName, array("Piwik", "getPrettyMoney"), array($this->getIdSite())));
        }

        foreach ($this->columnsToConversionFilter as $columnName) {
            // this ensures that the value is set to zero for all rows where the value was not set (no conversion)
            $this->dataTable->filter('ColumnCallbackReplace', array($columnName, create_function('$value', 'return $value;')));
        }
        return true;
    }
}
