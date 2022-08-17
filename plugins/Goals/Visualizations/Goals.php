<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Visualizations;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Site;

require_once PIWIK_INCLUDE_PATH . '/core/Twig.php';

/**
 * DataTable Visualization that derives from HtmlTable and sets show_goals_columns to true.
 */
class Goals extends HtmlTable
{
    const ID = 'tableGoals';
    const FOOTER_ICON       = 'icon-goal';
    const FOOTER_ICON_TITLE = 'General_DisplayTableWithGoalMetrics';

    const GOALS_DISPLAY_NORMAL = 0;
    const GOALS_DISPLAY_PAGES = 1;
    const GOALS_DISPLAY_ENTRY_PAGES = 2;

    private $displayType = self::GOALS_DISPLAY_NORMAL;

    public function beforeLoadDataTable()
    {
        $request = $this->getRequestArray();
        $idGoal = $request['idGoal'] ?? null;

        // Check if one of the pages display types should be used
        $requestMethod = $this->requestConfig->getApiModuleToRequest() . '.' . $this->requestConfig->getApiMethodToRequest();
        if (in_array($requestMethod, ['Actions.getPageUrls', 'Actions.getPageTitles'])) {
            $this->displayType = self::GOALS_DISPLAY_PAGES;
            $this->config->filters[] = ['Piwik\Plugins\Goals\DataTable\Filter\RemoveUnusedGoalRevenueColumns'];
            if ($idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER || $idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                $this->requestConfig->request_parameters_to_modify['idGoal'] = AddColumnsProcessedMetricsGoal::GOALS_ENTRY_PAGES_ECOMMERCE;
            } else {
                $this->requestConfig->request_parameters_to_modify['idGoal'] = AddColumnsProcessedMetricsGoal::GOALS_PAGES;
            }
        } elseif (in_array($requestMethod, ['Actions.getEntryPageUrls', 'Actions.getEntryPageTitles'])) {
            $this->displayType = self::GOALS_DISPLAY_ENTRY_PAGES;
            $this->config->filters[] = ['Piwik\Plugins\Goals\DataTable\Filter\RemoveUnusedGoalRevenueColumns'];
            if ($idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER || $idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                $this->requestConfig->request_parameters_to_modify['idGoal'] = AddColumnsProcessedMetricsGoal::GOALS_ENTRY_PAGES_ECOMMERCE;
            } else {
                $this->requestConfig->request_parameters_to_modify['idGoal'] = AddColumnsProcessedMetricsGoal::GOALS_ENTRY_PAGES;
            }
        }

        parent::beforeLoadDataTable();

        $this->config->show_totals_row = false;

        if ($this->config->disable_subtable_when_show_goals) {
            $this->config->subtable_controller_action = null;
        }

        $this->setShowGoalsColumnsProperties();
    }

    public function beforeRender()
    {
        $this->config->show_totals_row = false;
        $this->config->show_goals = true;
        $this->config->show_goals_columns  = true;
        $this->config->datatable_css_class = 'dataTableVizGoals';
        $this->config->show_exclude_low_population = true;


        if (1 == Common::getRequestVar('documentationForGoalsPage', 0, 'int')) {
            // TODO: should not use query parameter
            $this->config->documentation = Piwik::translate(
                'Goals_ConversionByTypeReportDocumentation',
                ['<br />', '<br />', '<a href="https://matomo.org/docs/tracking-goals-web-analytics/" rel="noreferrer noopener" target="_blank">', '</a>']
            );
        }

        if ($this->displayType == self::GOALS_DISPLAY_NORMAL) {
            $this->config->metrics_documentation['nb_visits'] = Piwik::translate('Goals_ColumnVisits');
        }

        if ($this->displayType == self::GOALS_DISPLAY_PAGES) {
            $this->config->addTranslation('nb_visits', Piwik::translate('General_ColumnUniquePageviews'));
            $this->config->metrics_documentation['nb_visits'] = Piwik::translate('General_ColumnUniquePageviewsDocumentation');
            $this->removeUnusedRevenueColumns();
        }

        if ($this->displayType == self::GOALS_DISPLAY_ENTRY_PAGES) {
            $this->config->metrics_documentation['entry_nb_visits'] = Piwik::translate('General_ColumnEntrancesDocumentation');
            $this->removeUnusedRevenueColumns();
        }

        parent::beforeRender();
    }

    /**
     * Remove all *revenue* columns from being displayed that had been removed by RemoveUnusedGoalRevenueColumns filter
     */
    private function removeUnusedRevenueColumns()
    {
        if ($this->dataTable instanceof DataTable\DataTableInterface) {
            foreach ($this->config->columns_to_display as $key => $column) {
                if (false === strpos($column, 'revenue')) {
                    continue;
                }
                $columnValues = $this->dataTable->getColumn($column);
                $columnValues = array_filter($columnValues);
                if (empty($columnValues)) {
                    unset($this->config->columns_to_display[$key]);
                }
            }
        }
    }

    private function setShowGoalsColumnsProperties()
    {
        // set view properties based on goal requested
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $idGoal = Common::getRequestVar('idGoal', AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string');

        $goalsToProcess = null;
        if (Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER == $idGoal) {
            $this->setPropertiesForEcommerceView();

            $goalsToProcess = [$idGoal];
        } elseif (AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE == $idGoal) {
            $this->setPropertiesForGoals($idSite, 'all');

            $goalsToProcess = $this->getAllGoalIds($idSite);
        } elseif (AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW == $idGoal) {
            $this->setPropertiesForGoalsOverview($idSite);

            $goalsToProcess = $this->getAllGoalIds($idSite);
        } else {
            $this->setPropertiesForGoals($idSite, [$idGoal]);

            $goalsToProcess = [$idGoal];
        }

        // add goals columns
        $this->requestConfig->request_parameters_to_modify['filter_update_columns_when_show_all_goals'] = $idGoal;
        $this->requestConfig->request_parameters_to_modify['filter_show_goal_columns_process_goals'] = implode(',', $goalsToProcess);
    }

    private function setPropertiesForEcommerceView()
    {
        $this->requestConfig->filter_sort_column = 'goal_ecommerceOrder_revenue';
        $this->requestConfig->filter_sort_order = 'desc';

        $this->config->columns_to_display = [
            'label', 'nb_visits', 'goal_ecommerceOrder_nb_conversions', 'goal_ecommerceOrder_revenue',
            'goal_ecommerceOrder_conversion_rate', 'goal_ecommerceOrder_avg_order_revenue', 'goal_ecommerceOrder_items',
            'goal_ecommerceOrder_revenue_per_visit'
        ];

        $this->config->translations = array_merge($this->config->translations, [
            'goal_ecommerceOrder_nb_conversions'    => Piwik::translate('General_EcommerceOrders'),
            'goal_ecommerceOrder_revenue'           => Piwik::translate('General_TotalRevenue'),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik::translate('General_ColumnValuePerVisit')
        ]);

        $goalName = Piwik::translate('General_EcommerceOrders');
        $this->config->metrics_documentation['revenue_per_visit'] =
            Piwik::translate('Goals_ColumnRevenuePerVisitDocumentation', $goalName);
    }

    protected function setPropertiesForGoalsOverview($idSite)
    {
        $allGoals = $this->getGoals($idSite);

        // set view properties
        if ($this->displayType == self::GOALS_DISPLAY_NORMAL) {
            $this->config->columns_to_display = ['label', 'nb_visits'];

            foreach ($allGoals as $goal) {
                $column = "goal_{$goal['idgoal']}_conversion_rate";
                $this->config->columns_to_display[] = $column;
            }

            $this->config->columns_to_display[] = 'revenue_per_visit';
        }

        if ($this->displayType == self::GOALS_DISPLAY_PAGES) {
            $this->config->columns_to_display = ['label', 'nb_visits']; // Should be uniques
            $goalColumnTemplates = [
                'goal_%s_nb_conversions_attrib',
                'goal_%s_revenue_attrib',
                'goal_%s_nb_conversions_page_rate',
            ];

            // set columns to display (columns of same type but different goals will be next to each other,
            // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
            foreach ($allGoals as $goal) {
                foreach ($goalColumnTemplates as $columnTemplate) {
                    $this->config->columns_to_display[] = sprintf($columnTemplate, $goal['idgoal']);
                }
            }
        }

        if ($this->displayType == self::GOALS_DISPLAY_ENTRY_PAGES) {
            $this->config->columns_to_display = ['label', 'entry_nb_visits'];

            $goalColumnTemplates = [
                'goal_%s_nb_conversions_entry',
                'goal_%s_nb_conversions_entry_rate',
                'goal_%s_revenue_entry',
                'goal_%s_revenue_per_entry',
            ];

            // set columns to display (columns of same type but different goals will be next to each other,
            // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
            foreach ($allGoals as $goal) {
                foreach ($goalColumnTemplates as $columnTemplate) {
                    $this->config->columns_to_display[] = sprintf($columnTemplate, $goal['idgoal']);
                }
            }
        }
    }

    protected function setPropertiesForGoals($idSite, $idGoals)
    {
        $allGoals = $this->getGoals($idSite);

        if ($this->displayType == self::GOALS_DISPLAY_NORMAL) {
            if ('all' == $idGoals) {
                $idGoals = array_keys($allGoals);
            } else {
                // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
                $this->requestConfig->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions';
                $this->requestConfig->filter_sort_order = 'desc';
            }

            $this->config->columns_to_display = ['label', 'nb_visits'];

            $goalColumnTemplates = [
                'goal_%s_nb_conversions',
                'goal_%s_conversion_rate',
                'goal_%s_revenue',
                'goal_%s_revenue_per_visit',
            ];

            // set columns to display (columns of same type but different goals will be next to each other,
            // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
            foreach ($goalColumnTemplates as $columnTemplate) {
                foreach ($idGoals as $idGoal) {
                    $this->config->columns_to_display[] = sprintf($columnTemplate, $idGoal);
                }
            }

            $this->config->columns_to_display[] = 'revenue_per_visit';
        }

        if ($this->displayType == self::GOALS_DISPLAY_PAGES) {
            if ('all' === $idGoals) {
                $idGoals = array_keys($allGoals);
                $this->requestConfig->filter_sort_column = 'nb_visits';
            } else {
                // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
                $this->requestConfig->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions_attrib';
            }
            $this->requestConfig->filter_sort_order  = 'desc';

            $this->config->columns_to_display = ['label', 'nb_visits'];
            $goalColumnTemplates = [
                'goal_%s_nb_conversions_attrib',
                'goal_%s_revenue_attrib',
                'goal_%s_nb_conversions_page_rate',
            ];

            // set columns to display (columns of same type but different goals will be next to each other,
            // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
            foreach ($idGoals as $idGoal) {
                foreach ($goalColumnTemplates as $columnTemplate) {
                    $this->config->columns_to_display[] = sprintf($columnTemplate, $idGoal);
                }
            }
        }

        if ($this->displayType == self::GOALS_DISPLAY_ENTRY_PAGES) {
            if ('all' === $idGoals) {
                $idGoals = array_keys($allGoals);
                $this->requestConfig->filter_sort_column = 'entry_nb_visits';
            } else {
                // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
                $this->requestConfig->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions_entry';
            }
            $this->requestConfig->filter_sort_order  = 'desc';
            $this->config->columns_to_display = ['label', 'entry_nb_visits'];
            $goalColumnTemplates = [
                'goal_%s_nb_conversions_entry',
                'goal_%s_nb_conversions_entry_rate',
                'goal_%s_revenue_entry',
                'goal_%s_revenue_per_entry',
            ];

            foreach ($idGoals as $idGoal) {
                foreach ($goalColumnTemplates as $columnTemplate) {
                    $this->config->columns_to_display[] = sprintf($columnTemplate, $idGoal);
                }
            }
        }
    }

    protected $goalsForCurrentSite = null;

    protected function getGoals($idSite)
    {
        if ($this->goalsForCurrentSite === null) {
            // get all goals to display info for
            $allGoals = [];

            // add the ecommerce goal if ecommerce is enabled for the site
            if (Site::isEcommerceEnabledFor($idSite)) {
                $ecommerceGoal = [
                    'idgoal'      => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                    'name'        => Piwik::translate('Goals_EcommerceOrder'),
                    'quoted_name' => false
                ];
                $allGoals[$ecommerceGoal['idgoal']] = $ecommerceGoal;
            }

            // add the site's goals (and escape all goal names)
            $siteGoals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);

            foreach ($siteGoals as &$goal) {
                $goal['quoted_name'] = '"' . $goal['name'] . '"';
                $allGoals[$goal['idgoal']] = $goal;
            }

            $this->goalsForCurrentSite = $allGoals;
        }

        return $this->goalsForCurrentSite;
    }

    protected function getAllGoalIds($idSite)
    {
        $allGoals = $this->getGoals($idSite);
        return array_map(function ($data) {
            return $data['idgoal'];
        }, $allGoals);
    }
}
