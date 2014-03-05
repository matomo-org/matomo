<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\WidgetsList;

/**
 */
class Insights extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'WidgetsList.addWidgets' => 'addWidgets',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'ViewDataTable.addViewDataTable' => 'getAvailableVisualizations'
        );
    }

    public function getAvailableVisualizations(&$visualizations)
    {
        $visualizations[] = __NAMESPACE__ . '\\Visualizations\\Insight';
    }

    public function addWidgets()
    {
        WidgetsList::add('Insights_WidgetCategory', 'Insights_OverviewWidgetTitle', 'Insights', 'getInsightsOverview');
        WidgetsList::add('Insights_WidgetCategory', 'Insights_MoversAndShakersWidgetTitle', 'Insights', 'getOverallMoversAndShakers');
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Insights/stylesheets/insightVisualization.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Insights/javascripts/insightsDataTable.js";
    }

}
