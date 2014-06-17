<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'Insights_WidgetCategory';
        $controller = 'Insights';

        $widgetsList->add($category, 'Insights_OverviewWidgetTitle', $controller, 'getInsightsOverview');
        $widgetsList->add($category, 'Insights_MoversAndShakersWidgetTitle', $controller, 'getOverallMoversAndShakers');
    }

}
