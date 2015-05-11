<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary\Widgets;

class GetEvolutionGraph extends \Piwik\Plugin\Widget
{
    protected $category = 'VisitsSummary_VisitsSummary';
    protected $name = 'VisitsSummary_WidgetLastVisits';

    public function getParameters()
    {
        return array('columns' => array('nb_visits'));
    }

}
