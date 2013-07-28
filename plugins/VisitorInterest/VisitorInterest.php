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
            'ViewDataTable.getReportDisplayProperties' => 'getReportDisplayProperties',
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

    public function addWidgets()
    {
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetLengths', 'VisitorInterest', 'getNumberOfVisitsPerVisitDuration');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetPages', 'VisitorInterest', 'getNumberOfVisitsPerPage');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_visitsByVisitCount', 'VisitorInterest', 'getNumberOfVisitsByVisitCount');
        Piwik_AddWidget('General_Visitors', 'VisitorInterest_WidgetVisitsByDaysSinceLast', 'VisitorInterest', 'getNumberOfVisitsByDaysSinceLast');
    }

    public function addMenu()
    {
        Piwik_RenameMenuEntry('General_Visitors', 'VisitFrequency_SubmenuFrequency',
            'General_Visitors', 'VisitorInterest_Engagement');
    }

    function postLoad()
    {
        Piwik_AddAction('template_headerVisitsFrequency', array('Piwik_VisitorInterest', 'headerVisitsFrequency'));
        Piwik_AddAction('template_footerVisitsFrequency', array('Piwik_VisitorInterest', 'footerVisitsFrequency'));
    }

    public function archivePeriod(Piwik_ArchiveProcessor_Period $archiveProcessor)
    {
        $archiving = new Piwik_VisitorInterest_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay(Piwik_ArchiveProcessor_Day $archiveProcessor)
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
        $out .= Piwik_FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
        $out .= '</div>';
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['VisitorInterest.getNumberOfVisitsPerVisitDuration'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsPerVisitDuration();
        $properties['VisitorInterest.getNumberOfVisitsPerPage'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsPerPage();
        $properties['VisitorInterest.getNumberOfVisitsByVisitCount'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsByVisitCount();
        $properties['VisitorInterest.getNumberOfVisitsByDaysSinceLast'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsByDaysSinceLast();
    }

    private function getDisplayPropertiesForGetNumberOfVisitsPerVisitDuration()
    {
        return array(
            'default_view_type' => 'cloud',
            'filter_sort_column' => 'label',
            'filter_sort_order' => 'asc',
            'translations' => array('label' => Piwik_Translate('VisitorInterest_ColumnVisitDuration')),
            'graph_limit' => 10,
            'enable_sort' => false,
            'show_exclude_low_population' => false,
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'show_search' => false,
            'show_table_all_columns' => false,
        );
    }

    private function getDisplayPropertiesForGetNumberOfVisitsPerPage()
    {
        return array(
            'default_view_type' => 'cloud',
            'filter_sort_column' => 'label',
            'filter_sort_order' => 'asc',
            'translations' => array('label' => Piwik_Translate('VisitorInterest_ColumnPagesPerVisit')),
            'graph_limit' => 10,
            'enable_sort' => false,
            'show_exclude_low_population' => false,
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'show_search' => false,
            'show_table_all_columns' => false,
        );
    }

    private function getDisplayPropertiesForGetNumberOfVisitsByVisitCount()
    {
        return array(
            'columns_to_display' => array('label', 'nb_visits', 'nb_visits_percentage'),
            'filter_sort_column' => 'label',
            'filter_sort_order' => 'asc',
            'translations' => array('label' => Piwik_Translate('VisitorInterest_VisitNum'),
                                    'nb_visits_percentage' => Piwik_Metrics::getPercentVisitColumn()),
            'show_exclude_low_population' => false,
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'filter_limit' => 15,
            'show_search' => false,
            'enable_sort' => false,
            'show_table_all_columns' => false,
            'show_all_views_icons' => false,
        );
    }

    private function getDisplayPropertiesForGetNumberOfVisitsByDaysSinceLast()
    {
        return array(
            'filter_sort_column' => 'label',
            'filter_sort_order' => 'asc',
            'translations' => array('label' => Piwik_Translate('General_DaysSinceLastVisit')),
            'show_exclude_low_population' => false,
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'show_all_views_icons' => false,
            'filter_limit' => 15,
            'show_search' => false,
            'enable_sort' => false,
            'show_table_all_columns' => false
        );
    }
}