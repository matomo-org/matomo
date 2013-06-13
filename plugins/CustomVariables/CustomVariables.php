<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CustomVariables
 */

/**
 * @package Piwik_CustomVariables
 */
class Piwik_CustomVariables extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('CustomVariables_PluginDescription')
                . ' <br/>Required to use <a href="http://piwik.org/docs/ecommerce-analytics/">Ecommerce Analytics</a> feature!',
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );

        return $info;
    }

    function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenus',
            'Goals.getReportsWithGoalMetrics'  => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
        );
        return $hooks;
    }

    function addWidgets()
    {
        Piwik_AddWidget('General_Visitors', 'CustomVariables_CustomVariables', 'CustomVariables', 'getCustomVariables');
    }

    function addMenus()
    {
        Piwik_AddMenu('General_Visitors', 'CustomVariables_CustomVariables', array('module' => 'CustomVariables', 'action' => 'index'), $display = true, $order = 50);
    }

    /**
     * Returns metadata for available reports
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();

        $documentation = Piwik_Translate('CustomVariables_CustomVariablesReportDocumentation',
            array('<br />', '<a href="http://piwik.org/docs/custom-variables/" target="_blank">', '</a>'));

        $reports[] = array('category'              => Piwik_Translate('General_Visitors'),
                           'name'                  => Piwik_Translate('CustomVariables_CustomVariables'),
                           'module'                => 'CustomVariables',
                           'action'                => 'getCustomVariables',
                           'actionToLoadSubTables' => 'getCustomVariablesValuesFromNameId',
                           'dimension'             => Piwik_Translate('CustomVariables_ColumnCustomVariableName'),
                           'documentation'         => $documentation,
                           'order'                 => 10);

        $reports[] = array('category'         => Piwik_Translate('General_Visitors'),
                           'name'             => Piwik_Translate('CustomVariables_CustomVariables'),
                           'module'           => 'CustomVariables',
                           'action'           => 'getCustomVariablesValuesFromNameId',
                           'dimension'        => Piwik_Translate('CustomVariables_ColumnCustomVariableValue'),
                           'documentation'    => $documentation,
                           'isSubtableReport' => true,
                           'order'            => 15);
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
        for ($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik_Translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik_Translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableName' . $i,
                'sqlSegment' => 'log_visit.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik_Translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik_Translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableValue' . $i,
                'sqlSegment' => 'log_visit.custom_var_v' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik_Translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik_Translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageName' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik_Translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik_Translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageValue' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_v' . $i,
            );
        }
    }

    /**
     * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getReportsWithGoalMetrics($notification)
    {
        $dimensions =& $notification->getNotificationObject();
        $dimensions = array_merge($dimensions, array(
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('CustomVariables_CustomVariables'),
                                                          'module'   => 'CustomVariables',
                                                          'action'   => 'getCustomVariables',
                                                    ),
                                               ));
    }

    /**
     * Hooks on daily archive to trigger various log processing
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function archiveDay($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();

        $archiving = new Piwik_CustomVariables_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function archivePeriod($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();
        $archiving = new Piwik_CustomVariables_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

}
