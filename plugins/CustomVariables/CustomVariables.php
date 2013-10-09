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
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'Menu.Reporting.addItems'                  => 'addMenus',
            'Goals.getReportsWithGoalMetrics'          => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
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

    /**
     * Hooks on daily archive to trigger various log processing
     */
    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['CustomVariables.getCustomVariables'] = $this->getDisplayPropertiesForGetCustomVariables();
        $properties['CustomVariables.getCustomVariablesValuesFromNameId'] =
            $this->getDisplayPropertiesForGetCustomVariablesValuesFromNameId();
    }

    private function getDisplayPropertiesForGetCustomVariables()
    {
        $footerMessage = Piwik::translate('CustomVariables_TrackingHelp',
            array('<a target="_blank" href="http://piwik.org/docs/custom-variables/">', '</a>'));

        return array(
            'columns_to_display'         => array('label', 'nb_actions', 'nb_visits'),
            'filter_sort_column'         => 'nb_actions',
            'filter_sort_order'          => 'desc',
            'show_goals'                 => true,
            'subtable_controller_action' => 'getCustomVariablesValuesFromNameId',
            'translations'               => array('label' => Piwik::translate('CustomVariables_ColumnCustomVariableName')),
            'show_footer_message'        => $footerMessage
        );
    }

    private function getDisplayPropertiesForGetCustomVariablesValuesFromNameId()
    {
        return array(
            'columns_to_display'          => array('label', 'nb_actions', 'nb_visits'),
            'filter_sort_column'          => 'nb_actions',
            'filter_sort_order'           => 'desc',
            'show_goals'                  => true,
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik::translate('CustomVariables_ColumnCustomVariableValue'))
        );
    }
}
