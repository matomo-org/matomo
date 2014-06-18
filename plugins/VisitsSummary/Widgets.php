<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'VisitsSummary_VisitsSummary';
        $controller = 'VisitsSummary';

        $widgetsList->add($category, 'VisitsSummary_WidgetLastVisits', $controller, 'getEvolutionGraph', array('columns' => array('nb_visits')));
        $widgetsList->add($category, 'VisitsSummary_WidgetVisits', $controller, 'getSparklines');
        $widgetsList->add($category, 'VisitsSummary_WidgetOverviewGraph', $controller, 'index');
    }

}
