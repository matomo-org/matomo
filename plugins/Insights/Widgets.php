<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Insights_WidgetCategory';

    public function init()
    {
        $this->addWidget('Insights_OverviewWidgetTitle', 'getInsightsOverview');
        $this->addWidget('Insights_MoversAndShakersWidgetTitle', 'getOverallMoversAndShakers');
    }
}
