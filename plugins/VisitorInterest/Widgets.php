<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'General_Visitors';
        $controller = 'VisitorInterest';

        $widgetsList->add($category, 'VisitorInterest_WidgetLengths', $controller, 'getNumberOfVisitsPerVisitDuration');
        $widgetsList->add($category, 'VisitorInterest_WidgetPages', $controller, 'getNumberOfVisitsPerPage');
        $widgetsList->add($category, 'VisitorInterest_visitsByVisitCount', $controller, 'getNumberOfVisitsByVisitCount');
        $widgetsList->add($category, 'VisitorInterest_WidgetVisitsByDaysSinceLast', $controller, 'getNumberOfVisitsByDaysSinceLast');
    }

}
