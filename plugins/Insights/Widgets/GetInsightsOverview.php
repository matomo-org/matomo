<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\Widgets;

use Piwik\Widget\WidgetConfig;

class GetInsightsOverview extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Insights_WidgetCategory');
        $config->setName('Insights_OverviewWidgetTitle');
    }
}
