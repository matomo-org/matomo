<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'General_Visitors';
        $controller = 'VisitFrequency';

        $widgetsList->add($category, 'VisitFrequency_WidgetOverview', $controller, 'getSparklines');
        $widgetsList->add($category, 'VisitFrequency_WidgetGraphReturning', $controller, 'getEvolutionGraph',
                         array('columns' => array('nb_visits_returning')));
    }

}
