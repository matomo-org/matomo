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
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Piwik;
use Piwik\DataTable;
use Piwik\View;
use Piwik\Config;
use Piwik\Common;
use Piwik\Site;
use Piwik\DataTableVisualization;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\Plugins\Goals\API as Goals_API;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable/AllColumns.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/Visualizations/HtmlTable/Goals.php';

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 */
class HtmlTable extends DataTableVisualization
{
    const ID = 'table';

    /**
     * Custom template used if displaying a subtable.
     *
     * TODO: Should be replaced w/ allowing custom visualization for
     *       subtables. Should not directly touch template.
     */
    const SUBTABLE_TEMPLATE = 'subtable_template';

    /**
     * Controls whether the entire DataTable should be rendered (including subtables) or just one
     * specific table in the tree.
     */
    const SHOW_EXPANDED = 'show_expanded';

    /**
     * When showing an expanded datatable, this property controls whether rows with subtables are
     * replaced with their subtables, or if they are shown alongside their subtables.
     */
    const REPLACE_ROW_WITH_SUBTABLE = 'replace_row_with_subtable';

    /**
     * Controls whether any DataTable Row Action icons are shown. If true, no icons are shown.
     *
     * @see also self::DISABLE_ROW_EVOLUTION
     */
    const DISABLE_ROW_ACTIONS = 'disable_row_actions';

    /**
     * Controls whether the row evolution DataTable Row Action icon is shown or not.
     *
     * @see also self::DISABLE_ROW_ACTIONS
     */
    const DISABLE_ROW_EVOLUTION = 'disable_row_evolution';

    /**
     * If true, the 'label', 'nb_visits', 'nb_uniq_visitors' (if present), 'nb_actions',
     * 'nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate' and 'conversion_rate' (if
     * goals view is not allowed) are displayed.
     */
    const SHOW_EXTRA_COLUMNS = 'show_extra_columns';

    /**
     * If true, conversions for each existing goal will be displayed for the visits in
     * each row.
     */
    const SHOW_GOALS_COLUMNS = 'show_goals_columns';

    /**
     * If true, subtables will not be loaded when rows are clicked, but only if the
     * 'show_goals_columns' property is also true.
     *
     * @see also self::SHOW_GOALS_COLUMNS
     */
    const DISABLE_SUBTABLE_IN_GOALS_VIEW = 'disable_subtable_when_show_goals';

    static public $clientSideParameters = array(
        'search_recursive',
        'filter_limit',
        'filter_sort_column',
        'filter_sort_order',
    );

    static public $clientSideProperties = array(
        'show_extra_columns',
        'show_goals_columns',
        'disable_row_evolution',
        'disable_row_actions'
    );

    /**
     * Constructor.
     */
    public function __construct($view)
    {
        if (Common::getRequestVar('idSubtable', false)
            && $view->visualization_properties->subtable_template
        ) {
            $view->datatable_template = $view->visualization_properties->subtable_template;
        }

        if ($view->visualization_properties->show_extra_columns) {
            $this->setShowExtraColumnsProperties($view);
        }

        if ($view->visualization_properties->show_goals_columns) {
            $this->setShowGoalsColumnsProperties($view);
        }
    }

    /**
     * Renders this visualization.
     *
     * @param DataTable $dataTable
     * @param array $properties View Properties.
     * @return string
     */
    public function render($dataTable, $properties) // TODO: $properties should be a viewdatatable, I think.
    {
        $view = new View("@CoreVisualizations/_dataTableViz_htmlTable.twig");
        $view->properties = $properties;
        $view->dataTable = $dataTable;
        return $view->render();
    }

    public static function getDefaultPropertyValues()
    {
        $defaults = array(
            'enable_sort' => true,
            'datatable_js_type' => 'dataTable',
            'filter_limit' => Config::getInstance()->General['datatable_default_limit'],
            'visualization_properties' => array(
                'table' => array(
                    'disable_row_evolution' => false,
                    'disable_row_actions' => false,
                    'subtable_template' => "@CoreHome/_dataTable.twig",
                    'show_extra_columns' => false,
                    'show_goals_columns' => false,
                    'disable_subtable_when_show_goals' => false,
                ),
            ),
        );

        if (Common::getRequestVar('enable_filter_excludelowpop', false) == '1') {
            $defaults['filter_excludelowpop'] = 'nb_visits';
            $defaults['filter_excludelowpop_value'] = null;
        }

        return $defaults;
    }

    private function setShowExtraColumnsProperties($view)
    {
        $view->filters[] = array('AddColumnsProcessedMetrics', array(), $priority = true);

        $view->filters[] = function ($dataTable, $view) {
            $columnsToDisplay = array('label', 'nb_visits');

            if (in_array('nb_uniq_visitors', $dataTable->getColumns())) {
                $columnsToDisplay[] = 'nb_uniq_visitors';
            }

            $columnsToDisplay = array_merge(
                $columnsToDisplay, array('nb_actions', 'nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate')
            );

            // only display conversion rate for the plugins that do not provide "per goal" metrics
            // otherwise, conversion rate is meaningless as a whole (since we don't process 'cross goals' conversions)
            if (!$view->show_goals) {
                $columnsToDisplay[] = 'conversion_rate';
            }

            $view->columns_to_display = $columnsToDisplay;
        };

        $prettifyTime = array('\Piwik\Piwik', 'getPrettyTimeFromSeconds');
        $view->filters[] = array('ColumnCallbackReplace', array('avg_time_on_site', $prettifyTime));

        $view->show_exclude_low_population = true;

        $view->datatable_css_class = 'dataTableVizAllColumns';
    }

    private function setShowGoalsColumnsProperties($view)
    {
        $view->datatable_css_class = 'dataTableVizGoals';
        $view->show_exclude_low_population = true;
        $view->show_goals = true;
        $view->translations += array(
            'nb_conversions'            => Piwik_Translate('Goals_ColumnConversions'),
            'conversion_rate'           => Piwik_Translate('General_ColumnConversionRate'),
            'revenue'                   => Piwik_Translate('Goals_ColumnRevenue'),
            'revenue_per_visit'         => Piwik_Translate('General_ColumnValuePerVisit'),
        );
        $view->metrics_documentation['nb_visits'] = Piwik_Translate('Goals_ColumnVisits');

        if (Common::getRequestVar('documentationForGoalsPage', 0, 'int') == 1) { // TODO: should not use query parameter
            $view->documentation = Piwik_Translate('Goals_ConversionByTypeReportDocumentation',
                array('<br />', '<br />', '<a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank">', '</a>'));
        }

        if (!$view->visualization_properties->disable_subtable_when_show_goals) {
            $view->subtable_controller_action = null;
        }

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
        $view->filters[] = array(
            'AddColumnsProcessedMetricsGoal', array($ignore = true, $idGoal), $priority = true);

        // prettify columns
        $setRatePercent = function ($rate, $thang = false) { return $rate == 0 ? "0%" : $rate; };
        foreach ($view->columns_to_display as $columnName) {
            if (strpos($columnName, 'conversion_rate') !== false) {
                $view->filters[] = array('ColumnCallbackReplace', array($columnName, $setRatePercent));
            }
        }

        $formatPercent = function ($value) use($idSite) {
            return Piwik::getPrettyMoney(sprintf("%.1f", $value), $idSite);
        };

        foreach ($view->columns_to_display as $columnName) {
            if ($this->isRevenueColumn($columnName)) {
                $view->filters[] = array('ColumnCallbackReplace', array($columnName, $formatPercent));
            }
        }

        // this ensures that the value is set to zero for all rows where the value was not set (no conversion)
        $identityFunction = function ($value) { return $value; };
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
            'goal_ecommerceOrder_conversion_rate'   => Piwik_Translate('Goals_ConversionRate', Piwik_Translate('Goals_EcommerceOrder')),
            'goal_ecommerceOrder_nb_conversions'    => Piwik_Translate('General_EcommerceOrders'),
            'goal_ecommerceOrder_revenue'           => Piwik_Translate('General_TotalRevenue'),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik_Translate('General_ColumnValuePerVisit'),
            'goal_ecommerceOrder_avg_order_revenue' => Piwik_Translate('General_AverageOrderValue'),
            'goal_ecommerceOrder_items'             => Piwik_Translate('General_PurchasedProducts')
        );

        $goalName = Piwik_Translate('General_EcommerceOrders');
        $view->metrics_documentation += array(
            'goal_ecommerceOrder_conversion_rate'   => Piwik_Translate('Goals_ColumnConversionRateDocumentation', $goalName),
            'goal_ecommerceOrder_nb_conversions'    => Piwik_Translate('Goals_ColumnConversionsDocumentation', $goalName),
            'goal_ecommerceOrder_revenue'           => Piwik_Translate('Goals_ColumnRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_revenue_per_visit' => Piwik_Translate('Goals_ColumnAverageOrderRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_avg_order_revenue' => Piwik_Translate('Goals_ColumnAverageOrderRevenueDocumentation', $goalName),
            'goal_ecommerceOrder_items'             => Piwik_Translate('Goals_ColumnPurchasedProductsDocumentation', $goalName),
            'revenue_per_visit'                     => Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', $goalName)
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
            $view->translations[$column] = Piwik_Translate('Goals_ConversionRate', $goal['name']);
            $view->metrics_documentation[$column]
                = Piwik_Translate('Goals_ColumnConversionRateDocumentation', $goal['quoted_name'] ?: $goal['name']);
        }

        $view->columns_to_display[] = 'revenue_per_visit';
        $view->metrics_documentation['revenue_per_visit'] =
            Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik_Translate('Goals_EcommerceAndGoalsMenu'));
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
            $quotedGoalName = $allGoals[$idGoal]['quoted_name'] ?: $goalName;

            $view->translations += array(
                'goal_' . $idGoal . '_nb_conversions' => Piwik_Translate('Goals_Conversions', $goalName),
                'goal_' . $idGoal . '_conversion_rate' => Piwik_Translate('Goals_ConversionRate', $goalName),
                'goal_' . $idGoal . '_revenue' =>
                Piwik_Translate('%s ' . Piwik_Translate('Goals_ColumnRevenue'), $goalName),
                'goal_' . $idGoal . '_revenue_per_visit' =>
                Piwik_Translate('%s ' . Piwik_Translate('General_ColumnValuePerVisit'), $goalName),
            );

            $view->metrics_documentation += array(
                'goal_' . $idGoal . '_nb_conversions' => Piwik_Translate('Goals_ColumnConversionsDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_conversion_rate' => Piwik_Translate('Goals_ColumnConversionRateDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_revenue' => Piwik_Translate('Goals_ColumnRevenueDocumentation', $quotedGoalName),
                'goal_' . $idGoal . '_revenue_per_visit' =>
                Piwik_Translate('Goals_ColumnRevenuePerVisitDocumentation', Piwik_Translate('Goals_EcommerceAndGoalsMenu')),
            );
        }

        $view->columns_to_display[] = 'revenue_per_visit';
    }

    private function getGoals($idSite)
    {
        // get all goals to display info for
        $allGoals = array();

        if (Site::isEcommerceEnabledFor($idSite)) {
            $ecommerceGoal = array(
                'idgoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER,
                'name'   => Piwik_Translate('Goals_EcommerceOrder'),
                'quoted_name' => false
            );
            $allGoals[$ecommerceGoal['idgoal']] = $ecommerceGoal;
        }

        $siteGoals = Goals_API::getInstance()->getGoals($idSite);
        foreach ($siteGoals as &$goal) {
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