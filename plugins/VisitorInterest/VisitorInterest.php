<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorInterest
 */

/**
 *
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('VisitorInterest_PluginDescription'),
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
            'Menu.add'                         => 'addMenu',
            'API.getReportMetadata'            => 'getReportMetadata',
        );
        return $hooks;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();
        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_WidgetLengths'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerVisitDuration',
            'dimension'         => Piwik_Translate('VisitorInterest_ColumnVisitDuration'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetLengthsDocumentation')
                . '<br />' . Piwik_Translate('General_ChangeTagCloudView'),
            'order'             => 15
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_WidgetPages'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerPage',
            'dimension'         => Piwik_Translate('VisitorInterest_ColumnPagesPerVisit'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetPagesDocumentation')
                . '<br />' . Piwik_Translate('General_ChangeTagCloudView'),
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_visitsByVisitCount'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByVisitCount',
            'dimension'         => Piwik_Translate('VisitorInterest_visitsByVisitCount'),
            'metrics'           => array(
                'nb_visits',
                'nb_visits_percentage' => Piwik_Translate('General_ColumnPercentageVisits')
            ),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetVisitsByNumDocumentation')
                . '<br />' . Piwik_Translate('General_ChangeTagCloudView'),
            'order'             => 25
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_Visitors'),
            'name'              => Piwik_Translate('VisitorInterest_VisitsByDaysSinceLast'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByDaysSinceLast',
            'dimension'         => Piwik_Translate('VisitorInterest_VisitsByDaysSinceLast'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik_Translate('VisitorInterest_WidgetVisitsByDaysSinceLastDocumentation'),
            'order'             => 30
        );
    }

    function addWidgets()
    {
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetLengths', 'VisitorInterest', 'getNumberOfVisitsPerVisitDuration');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetPages', 'VisitorInterest', 'getNumberOfVisitsPerPage');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_visitsByVisitCount', 'VisitorInterest', 'getNumberOfVisitsByVisitCount');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetVisitsByDaysSinceLast', 'VisitorInterest', 'getNumberOfVisitsByDaysSinceLast');
    }

    function addMenu()
    {
        Piwik_RenameMenuEntry('General_Visitors', 'VisitFrequency_SubmenuFrequency',
            'General_Visitors', 'VisitorInterest_Engagement');
    }

    function postLoad()
    {
        Piwik_AddAction('template_headerVisitsFrequency', array('Piwik_VisitorInterest', 'headerVisitsFrequency'));
        Piwik_AddAction('template_footerVisitsFrequency', array('Piwik_VisitorInterest', 'footerVisitsFrequency'));
    }

    function archivePeriod($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();

        $archiving = new Piwik_VisitorInterest_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();

        $archiving = new Piwik_VisitorInterest_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }


    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    static public function headerVisitsFrequency($notification)
    {
        $out =& $notification->getNotificationObject();
        $out = '<div id="leftcolumn">';
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    static public function footerVisitsFrequency($notification)
    {
        $out =& $notification->getNotificationObject();
        $out = '</div>
			<div id="rightcolumn">
			';
        $out .= Piwik_FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
        $out .= '</div>';
    }
}

