<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary\Widgets;

use Piwik\Plugin\WidgetConfig;

class GetEvolutionGraph extends \Piwik\Plugin\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategory('VisitsSummary_VisitsSummary');
        $config->setName('VisitsSummary_WidgetLastVisits');
        $config->setParameters(array('columns' => array('nb_visits')));
    }
}
