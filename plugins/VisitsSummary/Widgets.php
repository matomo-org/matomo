<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'VisitsSummary_VisitsSummary';

    public function init()
    {
        $this->addWidget('VisitsSummary_WidgetLastVisits', 'getEvolutionGraph', array('columns' => array('nb_visits')));
        $this->addWidget('VisitsSummary_WidgetVisits', 'getSparklines');
        $this->addWidget('VisitsSummary_WidgetOverviewGraph', 'index');
    }

}
