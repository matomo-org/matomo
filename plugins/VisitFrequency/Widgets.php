<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'General_Visitors';

    public function init()
    {
        $this->addWidget('VisitFrequency_WidgetOverview', 'getSparklines');
        $this->addWidget('VisitFrequency_WidgetGraphReturning',
                         'getEvolutionGraph',
                         array('columns' => array('nb_visits_returning')));
    }

}
