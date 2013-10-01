<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitTime
 */
namespace Piwik\Plugins\VisitTime;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Period;
use Piwik\Plugins\VisitTime\Archiver;
use Piwik\Site;
use Piwik\WidgetsList;

/**
 *
 * @package VisitTime
 */
class VisitTime extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'WidgetsList.addWidget'                    => 'addWidgets',
            'Menu.addMenuEntry'                        => 'addMenu',
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
            'documentation'     => Piwik_Translate('VisitTime_WidgetLocalTimeDocumentation', array('<strong>', '</strong>')),
            'constantRowsCount' => true,
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik_Translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik_Translate('VisitTime_WidgetServerTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerServerTime',
            'dimension'         => Piwik_Translate('VisitTime_ColumnServerTime'),
            'documentation'     => Piwik_Translate('VisitTime_WidgetServerTimeDocumentation', array('<strong>', '</strong>')),
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
        WidgetsList::add('VisitsSummary_VisitsSummary', 'VisitTime_WidgetLocalTime', 'VisitTime', 'getVisitInformationPerLocalTime');
        WidgetsList::add('VisitsSummary_VisitsSummary', 'VisitTime_WidgetServerTime', 'VisitTime', 'getVisitInformationPerServerTime');
        WidgetsList::add('VisitsSummary_VisitsSummary', 'VisitTime_VisitsByDayOfWeek', 'VisitTime', 'getByDayOfWeek');
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

    public function getReportDisplayProperties(&$properties)
    {
        $commonProperties = array(
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_limit_control'          => false,
            'default_view_type'           => 'graphVerticalBar'
        );

        $properties['VisitTime.getVisitInformationPerServerTime'] = array_merge($commonProperties, array(
            'filter_limit' => 24,
            'show_goals' => true,
            'translations' => array('label' => Piwik_Translate('VisitTime_ColumnServerTime')),
            'request_parameters_to_modify' => array('hideFutureHoursWhenToday' => 1),
            'visualization_properties' => array(
                'graph' => array(
                    'max_graph_elements' => false,
                )
            )
        ));
            
        $properties['VisitTime.getVisitInformationPerLocalTime'] = array_merge($commonProperties, array(
            'filter_limit' => 24,
            'title' => Piwik_Translate('VisitTime_ColumnLocalTime'),
            'translations' => array('label' => Piwik_Translate('VisitTime_LocalTime')),
            'visualization_properties' => array(
                'graph' => array(
                    'max_graph_elements' => false,
                )
            )
        ));
            
        $properties['VisitTime.getByDayOfWeek'] = array_merge($commonProperties, array(
            'filter_limit' => 7,
            'enable_sort' => false,
            'show_footer_message' =>
                Piwik_Translate('General_ReportGeneratedFrom', self::getDateRangeForFooterMessage()),
            'translations' => array('label' => Piwik_Translate('VisitTime_DayOfWeek')),
            'visualization_properties' => array(
                'graph' => array(
                    'show_all_ticks' => true,
                    'max_graph_elements' => false,
                )
            )
        ));

        // add the visits by day of week as a related report, if the current period is not 'day'
        if (Common::getRequestVar('period', 'day') != 'day') {
            $properties['VisitTime.getVisitInformationPerLocalTime']['related_reports'] = array(
                'VisitTime.getByDayOfWeek' => Piwik_Translate('VisitTime_VisitsByDayOfWeek')
            );
        }
    }

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    private static function getDateRangeForFooterMessage()
    {
        // get query params
        $idSite = Common::getRequestVar('idSite', false);
        $date = Common::getRequestVar('date', false);
        $period = Common::getRequestVar('period', false);

        // create a period instance
        try {
            $oPeriod = Period::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date);
        } catch (Exception $ex) {
            return ''; // if query params are incorrect, forget about the footer message
        }

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