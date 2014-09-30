<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals\Visualizations;

use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Site;
use Piwik\View;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_goals_columns to true.
 */
class Goals extends HtmlTable
{
    const ID = 'tableGoals';
    const FOOTER_ICON       = 'plugins/Morpheus/images/goal.png';
    const FOOTER_ICON_TITLE = 'General_DisplayTableWithMoreMetrics';

    public function beforeLoadDataTable()
    {
        parent::beforeLoadDataTable();

        if ($this->config->disable_subtable_when_show_goals) {
            $this->config->subtable_controller_action = null;
        }

        $this->setShowGoalsColumnsProperties();
    }

    public function beforeRender()
    {
        $this->config->show_goals = true;
        $this->config->show_goals_columns  = true;
        $this->config->datatable_css_class = 'dataTableVizGoals';
        $this->config->show_exclude_low_population = true;

        $this->config->translations += array(
            'nb_conversions'    => Piwik::translate('Goals_ColumnConversions'),
            'conversion_rate'   => Piwik::translate('General_ColumnConversionRate'),
            'revenue'           => Piwik::translate('General_ColumnRevenue'),
            'revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit'),
        );

        $this->config->metrics_documentation['nb_visits'] = Piwik::translate('Goals_ColumnVisits');

        if (1 == Common::getRequestVar('documentationForGoalsPage', 0, 'int')) {
            // TODO: should not use query parameter
            $this->config->documentation = Piwik::translate('Goals_ConversionByTypeReportDocumentation',
                array('<br />', '<br />', '<a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank">', '</a>'));
        }

        parent::beforeRender();
    }

    private function setShowGoalsColumnsProperties()
    {
        // set view properties based on goal requested
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $idGoal = Common::getRequestVar('idGoal', AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string');

        if (Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER == $idGoal) {
            $this->setPropertiesForEcommerceView();
        } else if (AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE == $idGoal) {
            $this->setPropertiesForGoals($idSite, 'all');
        } else if (AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW == $idGoal) {
            $this->setPropertiesForGoalsOverview($idSite);
        } else {
            $this->setPropertiesForGoals($idSite, array($idGoal));
        }

        // add goals columns
        $this->config->filters[] = array('AddColumnsProcessedMetricsGoal', array($ignore = true, $idGoal), $priority = true);

        // prettify columns
        $setRatePercent = function ($rate, $thang = false) {
            return $rate == 0 ? "0%" : $rate;
        };

        foreach ($this->config->columns_to_display as $columnName) {
            if (false !== strpos($columnName, 'conversion_rate')) {
                $this->config->filters[] = array('ColumnCallbackReplace', array($columnName, $setRatePercent));
            }
        }

        $formatPercent = function ($value) use ($idSite) {
            return MetricsFormatter::getPrettyMoney(sprintf("%.1f", $value), $idSite);
        };

        foreach ($this->config->columns_to_display as $columnName) {
            if ($this->isRevenueColumn($columnName)) {
                $this->config->filters[] = array('ColumnCallbackReplace', array($columnName, $formatPercent));
            }
        }

        // this ensures that the value is set to zero for all rows where the value was not set (no conversion)
        $identityFunction = function ($value) {
            return $value;
        };

        foreach ($this->config->columns_to_display as $columnName) {
            if (!$this->isRevenueColumn($columnName)) {
                $this->config->filters[] = array('ColumnCallbackReplace', array($columnName, $identityFunction));
            }
        }
    }

    private function setPropertiesForEcommerceView()
    {
        $this->requestConfig->filter_sort_column = 'goal_ecommerceOrder_revenue';
        $this->requestConfig->filter_sort_order = 'desc';

        $this->config->columns_to_display = array(
            'label', 'nb_visits', 'goal_ecommerceOrder_nb_conversions', 'goal_ecommerceOrder_revenue',
            'goal_ecommerceOrder_conversion_rate', 'goal_ecommerceOrder_avg_order_revenue', 'goal_ecommerceOrder_items',
            'goal_ecommerceOrder_revenue_per_visit'
        );

        $this->config->translations += array(
            'goal_ecommerceOrder_conversion_rate'   => Piwik::translate('Goals_ConversionRate', Piwik::translate('Goals_EcommerceOrder')),
            'goal_ecommerceOrder_nb_conversions'    => Piwik::translate('General_EcommerceOrders'),
            'goal_ecommerceOrder_revenue'           => Piwik::translate('General_TotalRevenue'),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit'),
            'goal_ecommerceOrder_avg_order_revenue' => Piwik::translate('General_AverageOrderValue'),
            'goal_ecommerceOrder_items'             => Piwik::translate('General_PurchasedProducts')
        );

        $goalName = Piwik::translate('General_EcommerceOrders');
        $this->config->metrics_documentation += array(
            'goal_ecommerceOrder_conversion_rate'   => Piwik::translate('Goals_ColumnConversionRateDocumentation', $goalName),
            'goal_ecommerceOrder_nb_conversions'    => Piwik::translate('Goals_ColumnConversionsDocumentation', $goalName),
            'goal_ecommerceOrder_revenue'           => Piwik::translate('Goals_ColumnRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik::translate('Goals_ColumnAverageOrderRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_avg_order_revenue' => Piwik::translate('Goals_ColumnAverageOrderRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_items'             => Piwik::translate('Goals_ColumnPurchasedProductsDocumentation', $goalName),
            'revenue_per_visit'                     => Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', $goalName)
        );
    }

    private function setPropertiesForGoalsOverview($idSite)
    {
        $allGoals = $this->getGoals($idSite);

        // set view properties
        $this->config->columns_to_display = array('label', 'nb_visits');

        foreach ($allGoals as $goal) {
            $column        = "goal_{$goal['idgoal']}_conversion_rate";
            $documentation = Piwik::translate('Goals_ColumnConversionRateDocumentation', $goal['quoted_name'] ? : $goal['name']);

            $this->config->columns_to_display[]  = $column;
            $this->config->translations[$column] = Piwik::translate('Goals_ConversionRate', $goal['name']);
            $this->config->metrics_documentation[$column] = $documentation;
        }

        $this->config->columns_to_display[] = 'revenue_per_visit';
        $this->config->metrics_documentation['revenue_per_visit'] =
            Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik::translate('Goals_EcommerceAndGoalsMenu'));
    }

    private function setPropertiesForGoals($idSite, $idGoals)
    {
        $allGoals = $this->getGoals($idSite);

        if ('all' == $idGoals) {
            $idGoals = array_keys($allGoals);
        } else {
            // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
            $this->requestConfig->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions';
            $this->requestConfig->filter_sort_order  = 'desc';
        }

        $this->config->columns_to_display = array('label', 'nb_visits');

        $goalColumnTemplates = array(
            'goal_%s_nb_conversions',
            'goal_%s_conversion_rate',
            'goal_%s_revenue',
            'goal_%s_revenue_per_visit',
        );

        // set columns to display (columns of same type but different goals will be next to each other,
        // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
        foreach ($goalColumnTemplates as $columnTemplate) {
            foreach ($idGoals as $idGoal) {
                $this->config->columns_to_display[] = sprintf($columnTemplate, $idGoal);
            }
        }

        // set translations & metric docs for goal specific metrics
        foreach ($idGoals as $idGoal) {
            $goalName = $allGoals[$idGoal]['name'];
            $quotedGoalName = $allGoals[$idGoal]['quoted_name'] ? : $goalName;

            $this->config->translations += array(
                'goal_' . $idGoal . '_nb_conversions'    => Piwik::translate('Goals_Conversions', $goalName),
                'goal_' . $idGoal . '_conversion_rate'   => Piwik::translate('Goals_ConversionRate', $goalName),
                'goal_' . $idGoal . '_revenue'           =>
                Piwik::translate('%s ' . Piwik::translate('General_ColumnRevenue'), $goalName),
                'goal_' . $idGoal . '_revenue_per_visit' =>
                Piwik::translate('%s ' . Piwik::translate('General_ColumnValuePerVisit'), $goalName),
            );

            $this->config->metrics_documentation += array(
                'goal_' . $idGoal . '_nb_conversions'    => Piwik::translate('Goals_ColumnConversionsDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_conversion_rate'   => Piwik::translate('Goals_ColumnConversionRateDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_revenue'           => Piwik::translate('Goals_ColumnRevenueDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_revenue_per_visit' =>
                Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik::translate('Goals_EcommerceAndGoalsMenu')),
            );
        }

        $this->config->columns_to_display[] = 'revenue_per_visit';
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
