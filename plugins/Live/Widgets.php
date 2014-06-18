<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'Live!';
        $controller = 'Live';

        $widgetsList->add($category, 'Live_VisitorsInRealTime', $controller, 'widget');
        $widgetsList->add($category, 'Live_VisitorLog', $controller, 'getVisitorLog', array('small' => 1));
        $widgetsList->add($category, 'Live_RealTimeVisitorCount', $controller, 'getSimpleLastVisitCount');
        $widgetsList->add($category, 'Live_VisitorProfile', $controller, 'getVisitorProfilePopup');
    }

}
