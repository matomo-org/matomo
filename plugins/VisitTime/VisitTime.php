<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;
use Piwik\Site;

/**
 *
 */
class VisitTime extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata'
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'          => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik::translate('VisitTime_WidgetLocalTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerLocalTime',
            'dimension'         => Piwik::translate('VisitTime_ColumnLocalTime'),
            'documentation'     => Piwik::translate('VisitTime_WidgetLocalTimeDocumentation', array('<strong>', '</strong>')),
            'constantRowsCount' => true,
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik::translate('VisitTime_WidgetServerTime'),
            'module'            => 'VisitTime',
            'action'            => 'getVisitInformationPerServerTime',
            'dimension'         => Piwik::translate('VisitTime_ColumnServerTime'),
            'documentation'     => Piwik::translate('VisitTime_WidgetServerTimeDocumentation', array('<strong>', '</strong>')),
            'constantRowsCount' => true,
            'order'             => 15,
        );

        $reports[] = array(
            'category'          => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'              => Piwik::translate('VisitTime_VisitsByDayOfWeek'),
            'module'            => 'VisitTime',
            'action'            => 'getByDayOfWeek',
            'dimension'         => Piwik::translate('VisitTime_DayOfWeek'),
            'documentation'     => Piwik::translate('VisitTime_WidgetByDayOfWeekDocumentation'),
            'constantRowsCount' => true,
            'order'             => 25,
        );
    }

    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions[] = array('category' => Piwik::translate('VisitTime_ColumnServerTime'),
                              'name'     => Piwik::translate('VisitTime_ColumnServerTime'),
                              'module'   => 'VisitTime',
                              'action'   => 'getVisitInformationPerServerTime',
        );
    }

    public function getSegmentsMetadata(&$segments)
    {
        $acceptedValues = "0, 1, 2, 3, ..., 20, 21, 22, 23";
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => Piwik::translate('VisitTime_ColumnServerTime'),
            'segment'        => 'visitServerHour',
            'sqlSegment'     => 'HOUR(log_visit.visit_last_action_time)',
            'acceptedValues' => $acceptedValues
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => Piwik::translate('General_Visit'),
            'name'           => Piwik::translate('VisitTime_ColumnLocalTime'),
            'segment'        => 'visitLocalHour',
            'sqlSegment'     => 'HOUR(log_visit.visitor_localtime)',
            'acceptedValues' => $acceptedValues
        );
    }

}
