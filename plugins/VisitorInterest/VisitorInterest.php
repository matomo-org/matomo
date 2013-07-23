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
use Piwik\ArchiveProcessor;
use Piwik\FrontController;
use Piwik\Plugin;

/**
 *
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest extends Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
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

    public function getReportMetadata(&$reports)
    {
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
                'nb_visits_percentage' => Piwik_Translate('General_ColumnPercentageVisits'),
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
        WidgetsList::add('General_Visitors', 'VisitorInterest_WidgetLengths', 'VisitorInterest', 'getNumberOfVisitsPerVisitDuration');
        WidgetsList::add('General_Visitors', 'VisitorInterest_WidgetPages', 'VisitorInterest', 'getNumberOfVisitsPerPage');
        WidgetsList::add('General_Visitors', 'VisitorInterest_visitsByVisitCount', 'VisitorInterest', 'getNumberOfVisitsByVisitCount');
        WidgetsList::add('General_Visitors', 'VisitorInterest_WidgetVisitsByDaysSinceLast', 'VisitorInterest', 'getNumberOfVisitsByDaysSinceLast');
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

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Piwik_VisitorInterest_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Piwik_VisitorInterest_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    static public function headerVisitsFrequency(&$out)
    {
        $out = '<div id="leftcolumn">';
    }

    static public function footerVisitsFrequency(&$out)
    {
        $out = '</div>
			<div id="rightcolumn">
			';
        $out .= FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
        $out .= '</div>';
    }
}
