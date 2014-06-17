<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'VisitsSummary_VisitsSummary';
        $controller = 'VisitTime';

        $widgetsList->add($category, 'VisitTime_WidgetLocalTime', $controller, 'getVisitInformationPerLocalTime');
        $widgetsList->add($category, 'VisitTime_WidgetServerTime', $controller, 'getVisitInformationPerServerTime');
        $widgetsList->add($category, 'VisitTime_VisitsByDayOfWeek', $controller, 'getByDayOfWeek');
    }

}
