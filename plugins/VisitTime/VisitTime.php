<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
 *
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('VisitTime_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
        return $info;
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'            => 'archiveDay',
            'ArchiveProcessing_Period.compute'         => 'archivePeriod',
            'WidgetsList.add'                          => 'addWidgets',
            'Menu.add'                                 => 'addMenu',
            'Goals.getReportsWithGoalMetrics'          => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'ViewDataTable.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_WidgetLocalTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerLocalTime',
            'dimension'         => Piwik_Translate('VisitTime_ColumnLocalTime'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetLocalTimeDocumentation', array('<b>', '</b>')),
            'constantRowsCount' => true,
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_WidgetServerTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerServerTime',
            'dimension'         => Piwik_Translate('VisitTime_ColumnServerTime'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetServerTimeDocumentation', array('<b>', '</b>')),
            'constantRowsCount' => true,
            'order'             => 15,
        );

        $reports[] = array(
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_VisitsByDayOfWeek'),
            'module'            => 'VisitTime',
            'action'            => 'getByDayOfWeek',
            'dimension'         => Piwik_Translate('VisitTime_DayOfWeek'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetByDayOfWeekDocumentation'),
            'constantRowsCount' => true,
            'order'             => 25,
        );
    }

    function addWidgets()
    {
        Piwik_AddWidget('VisitsSummary_VisitsSummary', 'VisitTime_WidgetLocalTime', 'VisitTime', 'getVisitInformationPerLocalTime');
        Piwik_AddWidget('VisitsSummary_VisitsSummary', 'VisitTime_WidgetServerTime', 'VisitTime', 'getVisitInformationPerServerTime');
        Piwik_AddWidget('VisitsSummary_VisitsSummary', 'VisitTime_VisitsByDayOfWeek', 'VisitTime', 'getByDayOfWeek');
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'VisitTime_SubmenuTimes', array('module' => 'VisitTime', 'action' => 'index'));
    }

    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions[] = array('category' => Piwik_Translate('VisitTime_ColumnServerTime'),
                              'name'     => Piwik_Translate('VisitTime_ColumnServerTime'),
                              'module'   => 'VisitTime',
                              'action'   => 'getVisitInformationPerServerTime',
        );
    }

    public function getSegmentsMetadata(&$segments)
    {
        $acceptedValues = "0, 1, 2, 3, ..., 20, 21, 22, 23";
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => Piwik_Translate('VisitTime_ColumnServerTime'),
            'segment'        => 'visitServerHour',
            'sqlSegment'     => 'HOUR(log_visit.visit_last_action_time)',
            'acceptedValues' => $acceptedValues
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik_Translate('General_Visit'),
            'name'           => Piwik_Translate('VisitTime_ColumnLocalTime'),
            'segment'        => 'visitLocalHour',
            'sqlSegment'     => 'HOUR(log_visit.visitor_localtime)',
            'acceptedValues' => $acceptedValues
        );
    }
    
    public function getReportDisplayProperties(&$properties, $apiAction)
    {
        $commonProperties = array(
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'default_view_type'           => 'graphVerticalBar'
        );
        
        $reportViewProperties = array(
            'VisitTime.getVisitInformationPerServerTime' => array_merge($commonProperties, array(
                'filter_limit' => 24,
                'graph_limit' => 24,
                'show_goals' => true,
                'translations' => array('label' => Piwik_Translate('VisitTime_ColumnServerTime')),
                
                // custom parameter
                'hideFutureHoursWhenToday' => 1,
            )),
            
            'VisitTime.getVisitInformationPerLocalTime' => array_merge($commonProperties, array(
                'filter_limit' => 24,
                'graph_limit' => 24,
                'title' => Piwik_Translate('VisitTime_ColumnLocalTime'),
                'translations' => array('label' => Piwik_Translate('VisitTime_LocalTime')),
            )),
            
            'VisitTime.getByDayOfWeek' => array_merge($commonProperties, array(
                'filter_limit' => 7,
                'graph_limit' => 7,
                'enable_sort' => false,
                'show_all_ticks' => true,
                'show_footer_message' =>
                    Piwik_Translate('General_ReportGeneratedFrom', self::getDateRangeForFooterMessage()),
                'translations' => array('label' => Piwik_Translate('VisitTime_DayOfWeek')),
            )),
        );

        // add the visits by day of week as a related report, if the current period is not 'day'
        if (Piwik_Common::getRequestVar('period', 'day') != 'day') {
            $reportViewProperties['VisitTime.getVisitInformationPerLocalTime']['relatedReports'] = array(
                'VisitTime.getByDayOfWeek' => Piwik_Translate('VisitTime_VisitsByDayOfWeek')
            );
        }
        
        if (isset($reportViewProperties[$apiAction])) {
            $properties = $reportViewProperties[$apiAction];
        }
    }

    public function archivePeriod(Piwik_ArchiveProcessor_Period $archiveProcessor)
    {
        $archiving = new Piwik_VisitTime_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }


    public function archiveDay(Piwik_ArchiveProcessor_Day $archiveProcessor)
    {
        $archiving = new Piwik_VisitTime_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }
    
    private static function getDateRangeForFooterMessage()
    {
        // get query params
        $idSite = Piwik_Common::getRequestVar('idSite');
        $date = Piwik_Common::getRequestVar('date');
        $period = Piwik_Common::getRequestVar('period');

        // create a period instance
        $oPeriod = Piwik_Period::makePeriodFromQueryParams(Piwik_Site::getTimezoneFor($idSite), $period, $date);

        // set the footer message using the period start & end date
        $start = $oPeriod->getDateStart()->toString();
        $end = $oPeriod->getDateEnd()->toString();
        if ($start == $end) {
            $dateRange = $start;
        } else {
            $dateRange = $start . " &ndash; " . $end;
        }
        return $dateRange;
    }
}
