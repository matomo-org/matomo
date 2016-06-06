<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Visualizations;

use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
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
    const FOOTER_ICON_TITLE = 'General_DisplayTableWithGoalMetrics';

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

        $this->config->metrics_documentation['nb_visits'] = Piwik::translate('Goals_ColumnVisits');

        if (1 == Common::getRequestVar('documentationForGoalsPage', 0, 'int')) {
            // TODO: should not use query parameter
            $this->config->documentation = Piwik::translate('Goals_ConversionByTypeReportDocumentation',
                array('<br />', '<br />', '<a href="http://piwik.org/docs/tracking-goals-web-analytics/" rel="noreferrer"  target="_blank">', '</a>'));
        }

        parent::beforeRender();
    }

    private function setShowGoalsColumnsProperties()
    {
        // set view properties based on goal requested
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $idGoal = Common::getRequestVar('idGoal', AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string');

        $goalsToProcess = null;
        if (Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER == $idGoal) {
            $this->setPropertiesForEcommerceView();

            $goalsToProcess = array($idGoal);
        } else if (AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE == $idGoal) {
            $this->setPropertiesForGoals($idSite, 'all');

            $goalsToProcess = $this->getAllGoalIds($idSite);
        } else if (AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW == $idGoal) {
            $this->setPropertiesForGoalsOverview($idSite);

            $goalsToProcess = $this->getAllGoalIds($idSite);
        } else {
            $this->setPropertiesForGoals($idSite, array($idGoal));

            $goalsToProcess = array($idGoal);
        }

        // add goals columns
        $this->config->filters[] = array('AddColumnsProcessedMetricsGoal', array($enable = true, $idGoal, $goalsToProcess), $priority = true);
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

        $this->config->translations = array_merge($this->config->translations, array(
            'goal_ecommerceOrder_nb_conversions'    => Piwik::translate('General_EcommerceOrders'),
            'goal_ecommerceOrder_revenue'           => Piwik::translate('General_TotalRevenue'),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit')
        ));

        $goalName = Piwik::translate('General_EcommerceOrders');
        $this->config->metrics_documentation['revenue_per_visit'] =
            Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', $goalName);
    }

    private function setPropertiesForGoalsOverview($idSite)
    {
        $allGoals = $this->getGoals($idSite);

        // set view properties
        $this->config->columns_to_display = array('label', 'nb_visits');

        foreach ($allGoals as $goal) {
            $column        = "goal_{$goal['idgoal']}_conversion_rate";
            $this->config->columns_to_display[]  = $column;
        }

        $this->config->columns_to_display[] = 'revenue_per_visit';
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

        $this->config->columns_to_display[] = 'revenue_per_visit';
    }

    private $goalsForCurrentSite = null;

    private function getGoals($idSite)
    {
        if ($this->goalsForCurrentSite === null) {
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

            $this->goalsForCurrentSite = $allGoals;
        }

        return $this->goalsForCurrentSite;
    }

    private function getAllGoalIds($idSite)
    {
        $allGoals = $this->getGoals($idSite);
        return array_map(function ($data) {
            return $data['idgoal'];
        }, $allGoals);
    }
}
