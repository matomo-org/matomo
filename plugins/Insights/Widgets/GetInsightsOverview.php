<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\Widgets;

use Piwik\Plugin\WidgetConfig;

class GetInsightsOverview extends \Piwik\Plugin\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategory('Insights_WidgetCategory');
        $config->setName('Insights_OverviewWidgetTitle');
    }
}
