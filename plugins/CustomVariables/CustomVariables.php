<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CustomVariables
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\ArchiveProcessor;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Tracker;
use Piwik\WidgetsList;

/**
 * @package CustomVariables
 */
class CustomVariables extends \Piwik\Plugin
{
    public function getInformation()
    {
        $info = parent::getInformation();
        $info['description'] .= ' <br/>Required to use <a href="http://piwik.org/docs/ecommerce-analytics/">Ecommerce Analytics</a> feature!';
        return $info;
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'WidgetsList.addWidgets'          => 'addWidgets',
            'Menu.Reporting.addItems'         => 'addMenus',
            'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
        );
        return $hooks;
    }

    public function addWidgets()
    {
        WidgetsList::add('General_Visitors', 'CustomVariables_CustomVariables', 'CustomVariables', 'getCustomVariables');
    }

    public function addMenus()
    {
        MenuMain::getInstance()->add('General_Visitors', 'CustomVariables_CustomVariables', array('module' => 'CustomVariables', 'action' => 'index'), $display = true, $order = 50);
    }

    /**
     * Returns metadata for available reports
     */
    public function getReportMetadata(&$reports)
    {
        $documentation = Piwik::translate('CustomVariables_CustomVariablesReportDocumentation',
            array('<br />', '<a href="http://piwik.org/docs/custom-variables/" target="_blank">', '</a>'));

        $reports[] = array('category'              => Piwik::translate('General_Visitors'),
                           'name'                  => Piwik::translate('CustomVariables_CustomVariables'),
                           'module'                => 'CustomVariables',
                           'action'                => 'getCustomVariables',
                           'actionToLoadSubTables' => 'getCustomVariablesValuesFromNameId',
                           'dimension'             => Piwik::translate('CustomVariables_ColumnCustomVariableName'),
                           'documentation'         => $documentation,
                           'order'                 => 10);

        $reports[] = array('category'         => Piwik::translate('General_Visitors'),
                           'name'             => Piwik::translate('CustomVariables_CustomVariables'),
                           'module'           => 'CustomVariables',
                           'action'           => 'getCustomVariablesValuesFromNameId',
                           'dimension'        => Piwik::translate('CustomVariables_ColumnCustomVariableValue'),
                           'documentation'    => $documentation,
                           'isSubtableReport' => true,
                           'order'            => 15);
    }

    public function getSegmentsMetadata(&$segments)
    {
        for ($i = 1; $i <= Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableName' . $i,
                'sqlSegment' => 'log_visit.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableValue' . $i,
                'sqlSegment' => 'log_visit.custom_var_v' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageName' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageValue' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_v' . $i,
            );
        }
    }

    /**
     * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
     */
    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions[] = array('category' => Piwik::translate('General_Visit'),
                              'name'     => Piwik::translate('CustomVariables_CustomVariables'),
                              'module'   => 'CustomVariables',
                              'action'   => 'getCustomVariables',
        );
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'CustomVariables.getCustomVariables':
                $this->configureViewForGetCustomVariables($view);
                break;
            case 'CustomVariables.getCustomVariablesValuesFromNameId':
                $this->configureViewForGetCustomVariablesValuesFromNameId($view);
                break;
        }
    }

    private function configureViewForGetCustomVariables(ViewDataTable $view)
    {
        $footerMessage = Piwik::translate('CustomVariables_TrackingHelp',
            array('<a target="_blank" href="http://piwik.org/docs/custom-variables/">', '</a>'));

        $view->config->columns_to_display = array('label', 'nb_actions', 'nb_visits');
        $view->config->show_goals = true;
        $view->config->subtable_controller_action = 'getCustomVariablesValuesFromNameId';
        $view->config->show_footer_message = $footerMessage;
        $view->config->addTranslation('label', Piwik::translate('CustomVariables_ColumnCustomVariableName'));
        $view->requestConfig->filter_sort_column = 'nb_actions';
        $view->requestConfig->filter_sort_order  = 'desc';
    }

    private function configureViewForGetCustomVariablesValuesFromNameId(ViewDataTable $view)
    {
        $view->config->columns_to_display = array('label', 'nb_actions', 'nb_visits');
        $view->config->show_goals  = true;
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('CustomVariables_ColumnCustomVariableValue'));
        $view->requestConfig->filter_sort_column = 'nb_actions';
        $view->requestConfig->filter_sort_order  = 'desc';
    }
}
