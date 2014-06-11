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
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata'
        );
        return $hooks;
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
