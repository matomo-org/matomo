<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Visualization\Config;
use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\MetricsFormatter;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Site;
use Piwik\View;
use Piwik\ViewDataTable\Visualization;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_goals_columns to true.
 */
class Goals extends HtmlTable
{
    const ID = 'tableGoals';

    public function configureVisualization(Config $properties)
    {
        $properties->visualization_properties->show_goals_columns = true;

        $properties->datatable_css_class = 'dataTableVizGoals';
        $properties->show_exclude_low_population = true;
        $properties->show_goals = true;

        $properties->translations += array(
            'nb_conversions'    => Piwik::translate('Goals_ColumnConversions'),
            'conversion_rate'   => Piwik::translate('General_ColumnConversionRate'),
            'revenue'           => Piwik::translate('General_ColumnRevenue'),
            'revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit'),
        );
        $properties->metrics_documentation['nb_visits'] = Piwik::translate('Goals_ColumnVisits');

        if (Common::getRequestVar('documentationForGoalsPage', 0, 'int') == 1) { // TODO: should not use query parameter
            $properties->documentation = Piwik::translate('Goals_ConversionByTypeReportDocumentation',
                array('<br />', '<br />', '<a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank">', '</a>'));
        }

        if (!$properties->visualization_properties->disable_subtable_when_show_goals) {
            $properties->subtable_controller_action = null;
        }

        $this->setShowGoalsColumnsProperties();

        parent::configureVisualization($properties);
    }

    private function setShowGoalsColumnsProperties()
    {
        $view = $this->viewDataTable;

        // set view properties based on goal requested
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $idGoal = Common::getRequestVar('idGoal', AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string');
        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            $this->setPropertiesForEcommerceView($view);
        } else if ($idGoal == AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE) {
            $this->setPropertiesForGoals($view, $idSite, 'all');
        } else if ($idGoal == AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW) {
            $this->setPropertiesForGoalsOverview($view, $idSite);
        } else {
            $this->setPropertiesForGoals($view, $idSite, array($idGoal));
        }

        // add goals columns
        $view->filters[] = array('AddColumnsProcessedMetricsGoal', array($ignore = true, $idGoal), $priority = true);

        // prettify columns
        $setRatePercent = function ($rate, $thang = false) {
            return $rate == 0 ? "0%" : $rate;
        };
        foreach ($view->columns_to_display as $columnName) {
            if (strpos($columnName, 'conversion_rate') !== false) {
                $view->filters[] = array('ColumnCallbackReplace', array($columnName, $setRatePercent));
            }
        }

        $formatPercent = function ($value) use ($idSite) {
            return MetricsFormatter::getPrettyMoney(sprintf("%.1f", $value), $idSite);
        };

        foreach ($view->columns_to_display as $columnName) {
            if ($this->isRevenueColumn($columnName)) {
                $view->filters[] = array('ColumnCallbackReplace', array($columnName, $formatPercent));
            }
        }

        // this ensures that the value is set to zero for all rows where the value was not set (no conversion)
        $identityFunction = function ($value) {
            return $value;
        };
        foreach ($view->columns_to_display as $columnName) {
            if (!$this->isRevenueColumn($columnName)) {
                $view->filters[] = array('ColumnCallbackReplace', array($columnName, $identityFunction));
            }
        }
    }

    private function setPropertiesForEcommerceView($view)
    {
        $view->filter_sort_column = 'goal_ecommerceOrder_revenue';
        $view->filter_sort_order = 'desc';

        $view->columns_to_display = array(
            'label', 'nb_visits', 'goal_ecommerceOrder_nb_conversions', 'goal_ecommerceOrder_revenue',
            'goal_ecommerceOrder_conversion_rate', 'goal_ecommerceOrder_avg_order_revenue', 'goal_ecommerceOrder_items',
            'goal_ecommerceOrder_revenue_per_visit'
        );

        $view->translations += array(
            'goal_ecommerceOrder_conversion_rate'   => Piwik::translate('Goals_ConversionRate', Piwik::translate('Goals_EcommerceOrder')),
            'goal_ecommerceOrder_nb_conversions'    => Piwik::translate('General_EcommerceOrders'),
            'goal_ecommerceOrder_revenue'           => Piwik::translate('General_TotalRevenue'),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit'),
            'goal_ecommerceOrder_avg_order_revenue' => Piwik::translate('General_AverageOrderValue'),
            'goal_ecommerceOrder_items'             => Piwik::translate('General_PurchasedProducts')
        );

        $goalName = Piwik::translate('General_EcommerceOrders');
        $view->metrics_documentation += array(
            'goal_ecommerceOrder_conversion_rate'   => Piwik::translate('Goals_ColumnConversionRateDocumentation', $goalName),
            'goal_ecommerceOrder_nb_conversions'    => Piwik::translate('Goals_ColumnConversionsDocumentation', $goalName),
            'goal_ecommerceOrder_revenue'           => Piwik::translate('Goals_ColumnRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik::translate('Goals_ColumnAverageOrderRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_avg_order_revenue' => Piwik::translate('Goals_ColumnAverageOrderRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_items'             => Piwik::translate('Goals_ColumnPurchasedProductsDocumentation', $goalName),
            'revenue_per_visit'                     => Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', $goalName)
        );
    }

    private function setPropertiesForGoalsOverview($view, $idSite)
    {
        $allGoals = $this->getGoals($idSite);

        // set view properties
        $view->columns_to_display = array('label', 'nb_visits');

        foreach ($allGoals as $goal) {
            $column = "goal_{$goal['idgoal']}_conversion_rate";

            $view->columns_to_display[] = $column;
            $view->translations[$column] = Piwik::translate('Goals_ConversionRate', $goal['name']);
            $view->metrics_documentation[$column]
                = Piwik::translate('Goals_ColumnConversionRateDocumentation', $goal['quoted_name'] ? : $goal['name']);
        }

        $view->columns_to_display[] = 'revenue_per_visit';
        $view->metrics_documentation['revenue_per_visit'] =
            Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik::translate('Goals_EcommerceAndGoalsMenu'));
    }

    private function setPropertiesForGoals($view, $idSite, $idGoals)
    {
        $allGoals = $this->getGoals($idSite);

        if ($idGoals == 'all') {
            $idGoals = array_keys($allGoals);
        } else {
            // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
            $view->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions';
            $view->filter_sort_order = 'desc';
        }

        $view->columns_to_display = array('label', 'nb_visits');

        $goalColumnTemplates = array(
            'goal_%s_nb_conversions',
            'goal_%s_conversion_rate',
            'goal_%s_revenue',
            'goal_%s_revenue_per_visit',
        );

        // set columns to display (columns of same type but different goals will be next to each other,
        // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
        foreach ($goalColumnTemplates as $idx => $columnTemplate) {
            foreach ($idGoals as $idGoal) {
                $column = sprintf($columnTemplate, $idGoal);
                $view->columns_to_display[] = $column;
            }
        }

        // set translations & metric docs for goal specific metrics
        foreach ($idGoals as $idGoal) {
            $goalName = $allGoals[$idGoal]['name'];
            $quotedGoalName = $allGoals[$idGoal]['quoted_name'] ? : $goalName;

            $view->translations += array(
                'goal_' . $idGoal . '_nb_conversions'    => Piwik::translate('Goals_Conversions', $goalName),
                'goal_' . $idGoal . '_conversion_rate'   => Piwik::translate('Goals_ConversionRate', $goalName),
                'goal_' . $idGoal . '_revenue'           =>
                Piwik::translate('%s ' . Piwik::translate('General_ColumnRevenue'), $goalName),
                'goal_' . $idGoal . '_revenue_per_visit' =>
                Piwik::translate('%s ' . Piwik::translate('General_ColumnValuePerVisit'), $goalName),
            );

            $view->metrics_documentation += array(
                'goal_' . $idGoal . '_nb_conversions'    => Piwik::translate('Goals_ColumnConversionsDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_conversion_rate'   => Piwik::translate('Goals_ColumnConversionRateDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_revenue'           => Piwik::translate('Goals_ColumnRevenueDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_revenue_per_visit' =>
                Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik::translate('Goals_EcommerceAndGoalsMenu')),
            );
        }

        $view->columns_to_display[] = 'revenue_per_visit';
    }

    private function getGoals($idSite)
    {
        // get all goals to display info for
        $allGoals = array();

        // add the ecommerce goal if ecommerce is enabled for the site
        if (Site::isEcommerceEnabledFor($idSite)) {
            $ecommerceGoal = array(
                'idgoal'      => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                'name'        => Piwik::translate('Goals_EcommerceOrder'),
                'quoted_name' => false
            );
            $allGoals[$ecommerceGoal['idgoal']] = $ecommerceGoal;
        }

        // add the site's goals (and escape all goal names)
        $siteGoals = APIGoals::getInstance()->getGoals($idSite);
        foreach ($siteGoals as &$goal) {
            $goal['name'] = Common::sanitizeInputValue($goal['name']);

            $goal['quoted_name'] = '"' . $goal['name'] . '"';
            $allGoals[$goal['idgoal']] = $goal;
        }

        return $allGoals;
    }

    private function isRevenueColumn($name)
    {
        return strpos($name, '_revenue') !== false || $name == 'revenue_per_visit';
    }
}